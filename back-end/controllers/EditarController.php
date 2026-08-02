<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/conexion-db.php';

final class EditarController
{
    private mysqli $db;

    public function __construct()
    {
        global $conn;
        $this->db = $conn;
    }

    public function obtenerEncuestaPorNivel(string $nivel): array
    {
        $idNivel = $this->idNivel($nivel);
        $stmt = $this->db->prepare(
            "SELECT id_encuesta, version FROM encuestas
             WHERE id_nivel = ? AND estado = 'activa'
             ORDER BY version DESC, id_encuesta DESC LIMIT 1"
        );
        $stmt->bind_param('i', $idNivel);
        $stmt->execute();
        $encuesta = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$encuesta) {
            throw new RuntimeException('No hay una encuesta activa para este nivel.');
        }

        return [
            'id_encuesta' => (int) $encuesta['id_encuesta'],
            'version' => (int) $encuesta['version'],
            'nivel' => strtolower(trim($nivel)),
            'preguntas' => $this->obtenerPreguntas((int) $encuesta['id_encuesta']),
        ];
    }

    public function guardarCambios(
        string $nivel,
        array $preguntas,
        int $idEncuestaOrigen,
        int $versionOrigen
    ): array {
        $idNivel = $this->idNivel($nivel);
        if ($idEncuestaOrigen <= 0 || $versionOrigen <= 0) {
            throw new InvalidArgumentException('La versión de origen no es válida.');
        }
        if (!$preguntas) {
            throw new InvalidArgumentException('La encuesta debe conservar al menos una pregunta.');
        }

        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare(
                'SELECT titulo, descripcion, id_ciclo, version, estado
                 FROM encuestas WHERE id_encuesta = ? AND id_nivel = ? FOR UPDATE'
            );
            $stmt->bind_param('ii', $idEncuestaOrigen, $idNivel);
            $stmt->execute();
            $origen = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$origen) {
                throw new InvalidArgumentException('La encuesta de origen no pertenece al nivel seleccionado.');
            }
            if ($origen['estado'] !== 'activa' || (int) $origen['version'] !== $versionOrigen) {
                throw new DomainException('CONFLICTO_VERSION');
            }

            [$preguntasOrigen, $opcionesOrigen] = $this->cargarElementosOrigen($idEncuestaOrigen);
            $nuevaVersion = $versionOrigen + 1;
            $estadoInicial = 'inactiva';
            $stmt = $this->db->prepare(
                'INSERT INTO encuestas
                 (titulo,descripcion,id_nivel,id_ciclo,fecha_creacion,estado,version,id_encuesta_anterior,publicada_en)
                 VALUES (?,?,?,?,NOW(),?,?,?,NOW())'
            );
            $titulo = (string) $origen['titulo'];
            $descripcion = $origen['descripcion'];
            $idCiclo = (int) $origen['id_ciclo'];
            $stmt->bind_param(
                'ssiisii',
                $titulo,
                $descripcion,
                $idNivel,
                $idCiclo,
                $estadoInicial,
                $nuevaVersion,
                $idEncuestaOrigen
            );
            $stmt->execute();
            $idNuevaEncuesta = $this->db->insert_id;
            $stmt->close();

            $insertPregunta = $this->db->prepare(
                'INSERT INTO preguntas
                 (id_encuesta,clave_logica,texto_pregunta,tipo_pregunta,icono,orden)
                 VALUES (?,?,?,?,?,?)'
            );
            $insertOpcion = $this->db->prepare(
                'INSERT INTO opciones_respuesta
                 (id_pregunta,clave_logica,texto_opcion,icono,valor)
                 VALUES (?,?,?,?,?)'
            );

            foreach (array_values($preguntas) as $indice => $pregunta) {
                $idAnterior = (int) ($pregunta['id'] ?? 0);
                $texto = trim((string) ($pregunta['texto'] ?? ''));
                $tipo = strtolower(trim((string) ($pregunta['tipo'] ?? 'texto')));
                if ($texto === '' || mb_strlen($texto) > 2000) {
                    throw new InvalidArgumentException('Todas las preguntas deben tener un texto válido.');
                }
                if (!in_array($tipo, ['opcion', 'multiple', 'ranking', 'texto', 'dibujo'], true)) {
                    throw new InvalidArgumentException('Tipo de pregunta inválido.');
                }

                if ($idAnterior > 0) {
                    if (!isset($preguntasOrigen[$idAnterior])) {
                        throw new InvalidArgumentException('Una pregunta no pertenece a la encuesta de origen.');
                    }
                    $clavePregunta = $preguntasOrigen[$idAnterior]['clave'];
                    $iconoPregunta = !empty($pregunta['icono_eliminado'])
                        ? null
                        : (!empty($pregunta['icono_nuevo'])
                            ? $pregunta['icono']
                            : $preguntasOrigen[$idAnterior]['icono']);
                } else {
                    $clavePregunta = self::uuid();
                    $iconoPregunta = !empty($pregunta['icono_nuevo']) ? $pregunta['icono'] : null;
                }
                $orden = $indice + 1;
                $insertPregunta->bind_param(
                    'issssi',
                    $idNuevaEncuesta,
                    $clavePregunta,
                    $texto,
                    $tipo,
                    $iconoPregunta,
                    $orden
                );
                $insertPregunta->execute();
                $idNuevaPregunta = $this->db->insert_id;

                $opciones = $pregunta['opciones'] ?? [];
                if (in_array($tipo, ['opcion', 'multiple', 'ranking'], true) && !$opciones) {
                    throw new InvalidArgumentException('Las preguntas de selección deben tener opciones.');
                }
                foreach (array_values(is_array($opciones) ? $opciones : []) as $posicion => $opcion) {
                    $idOpcionAnterior = (int) ($opcion['id'] ?? 0);
                    $textoOpcion = trim((string) ($opcion['texto'] ?? ''));
                    if ($textoOpcion === '' || mb_strlen($textoOpcion) > 255) {
                        throw new InvalidArgumentException('Todas las opciones deben tener un texto válido.');
                    }
                    if ($idOpcionAnterior > 0) {
                        if (!isset($opcionesOrigen[$idOpcionAnterior])
                            || $opcionesOrigen[$idOpcionAnterior]['id_pregunta'] !== $idAnterior) {
                            throw new InvalidArgumentException('Una opción no pertenece a su pregunta de origen.');
                        }
                        $claveOpcion = $opcionesOrigen[$idOpcionAnterior]['clave'];
                        $iconoOpcion = !empty($opcion['icono_eliminado'])
                            ? null
                            : (!empty($opcion['icono_nuevo'])
                                ? $opcion['icono']
                                : $opcionesOrigen[$idOpcionAnterior]['icono']);
                    } else {
                        $claveOpcion = self::uuid();
                        $iconoOpcion = !empty($opcion['icono_nuevo']) ? $opcion['icono'] : null;
                    }
                    $valor = $posicion + 1;
                    $insertOpcion->bind_param(
                        'isssi',
                        $idNuevaPregunta,
                        $claveOpcion,
                        $textoOpcion,
                        $iconoOpcion,
                        $valor
                    );
                    $insertOpcion->execute();
                }
            }
            $insertPregunta->close();
            $insertOpcion->close();

            $stmt = $this->db->prepare(
                "UPDATE encuestas
                 SET estado = 'inactiva', acepta_respuestas_hasta = DATE_ADD(NOW(), INTERVAL 2 HOUR)
                 WHERE id_encuesta = ? AND estado = 'activa'"
            );
            $stmt->bind_param('i', $idEncuestaOrigen);
            $stmt->execute();
            if ($stmt->affected_rows !== 1) {
                throw new DomainException('CONFLICTO_VERSION');
            }
            $stmt->close();

            $stmt = $this->db->prepare("UPDATE encuestas SET estado = 'activa' WHERE id_encuesta = ?");
            $stmt->bind_param('i', $idNuevaEncuesta);
            $stmt->execute();
            $stmt->close();

            $this->db->commit();
            return ['success' => true, 'id_encuesta' => $idNuevaEncuesta, 'version' => $nuevaVersion];
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    private function obtenerPreguntas(int $idEncuesta): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.id_pregunta,p.id_encuesta,p.texto_pregunta,p.tipo_pregunta,p.orden,p.icono,
                    o.id_opcion,o.texto_opcion,o.icono AS icono_opcion,o.valor
             FROM preguntas p
             LEFT JOIN opciones_respuesta o ON o.id_pregunta = p.id_pregunta
             WHERE p.id_encuesta = ? ORDER BY p.orden,p.id_pregunta,o.valor,o.id_opcion'
        );
        $stmt->bind_param('i', $idEncuesta);
        $stmt->execute();
        $result = $stmt->get_result();
        $preguntas = [];
        while ($row = $result->fetch_assoc()) {
            $id = (int) $row['id_pregunta'];
            if (!isset($preguntas[$id])) {
                $preguntas[$id] = [
                    'id_pregunta' => $id,
                    'id_encuesta' => (int) $row['id_encuesta'],
                    'texto_pregunta' => $row['texto_pregunta'],
                    'tipo_pregunta' => $row['tipo_pregunta'],
                    'orden' => (int) $row['orden'],
                    'icono' => $row['icono'],
                    'opciones' => [],
                ];
            }
            if ($row['id_opcion'] !== null) {
                $preguntas[$id]['opciones'][] = [
                    'id_opcion' => (int) $row['id_opcion'],
                    'texto_opcion' => $row['texto_opcion'],
                    'icono' => $row['icono_opcion'],
                    'valor' => $row['valor'],
                ];
            }
        }
        $stmt->close();
        return array_values($preguntas);
    }

    /** @return array{0:array<int,array{clave:string,icono:?string}>,1:array<int,array{clave:string,icono:?string,id_pregunta:int}>} */
    private function cargarElementosOrigen(int $idEncuesta): array
    {
        $preguntas = [];
        $opciones = [];
        $stmt = $this->db->prepare(
            'SELECT p.id_pregunta,p.clave_logica,p.icono,o.id_opcion,o.clave_logica AS clave_opcion,o.icono AS icono_opcion
             FROM preguntas p LEFT JOIN opciones_respuesta o ON o.id_pregunta=p.id_pregunta
             WHERE p.id_encuesta=?'
        );
        $stmt->bind_param('i', $idEncuesta);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $idPregunta = (int) $row['id_pregunta'];
            $preguntas[$idPregunta] = ['clave' => $row['clave_logica'], 'icono' => $row['icono']];
            if ($row['id_opcion'] !== null) {
                $opciones[(int) $row['id_opcion']] = [
                    'clave' => $row['clave_opcion'],
                    'icono' => $row['icono_opcion'],
                    'id_pregunta' => $idPregunta,
                ];
            }
        }
        $stmt->close();
        return [$preguntas, $opciones];
    }

    private function idNivel(string $nivel): int
    {
        $map = ['preescolar' => 1, 'primaria' => 2, 'secundaria' => 3, 'preparatoria' => 4];
        $id = $map[strtolower(trim($nivel))] ?? null;
        if ($id === null) {
            throw new InvalidArgumentException('Nivel de encuesta inválido.');
        }
        return $id;
    }

    private static function uuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);
        return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4)
            . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20);
    }
}
