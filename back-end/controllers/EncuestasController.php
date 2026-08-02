<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/conexion-db.php';
require_once __DIR__ . '/../models/Pregunta.php';
require_once __DIR__ . '/../helpers/DibujoHelper.php';

class EncuestasController
{
    private mysqli $db;

    public function __construct()
    {
        // Usamos la conexión clásica
        global $conn;
        $this->db = $conn;
    }

    /* ==========================================================
       OBTENER ENCUESTA POR NIVEL
    ========================================================== */
    public function obtenerEncuestaPorNivel(string $nivel): array
    {
        $nivel = strtolower(trim($nivel));

        $niveles = [
            'preescolar'   => 1,
            'primaria'     => 2,
            'secundaria'   => 3,
            'preparatoria' => 4,
        ];

        $idNivel = $niveles[$nivel] ?? null;
        if ($idNivel === null) {
            throw new InvalidArgumentException('Nivel de encuesta inválido');
        }

        $idEncuesta = $this->obtenerEncuestaActivaPorNivel($idNivel);

        return [
            'id_encuesta' => $idEncuesta,
            'nivel'       => $nivel,
            'preguntas'   => $this->obtenerPreguntas($idEncuesta)
        ];
    }

    /* ==========================================================
       CREAR REGISTRO DE USUARIO ENCUESTA (Sesión)
    ========================================================== */
    private function obtenerEncuestaActivaPorNivel(int $idNivel): int
    {
        $sql = "SELECT id_encuesta
                FROM encuestas
                WHERE id_nivel = ? AND estado = 'activa'
                ORDER BY id_encuesta DESC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $idNivel);
        $stmt->execute();
        $stmt->bind_result($idEncuesta);
        $encontrada = $stmt->fetch();
        $stmt->close();

        if (!$encontrada) {
            throw new RuntimeException('No hay una encuesta activa para este nivel');
        }

        return (int)$idEncuesta;
    }

    private function crearUsuarioEncuesta(int $idEncuesta, ?int $idEscuela): int
    {
        $sql = "INSERT INTO encuestas_usuarios
                (id_encuesta, id_escuela, fecha_inicio)
                VALUES (?, ?, NOW())";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar encuestas_usuarios: " . $this->db->error);
        }

        $stmt->bind_param("ii", $idEncuesta, $idEscuela);

        if (!$stmt->execute()) {
            throw new Exception("No se pudo crear el registro de encuestas_usuarios");
        }

        $id = $this->db->insert_id;
        $stmt->close();
        return $id;
    }

    /* ==========================================================
       OBTENER PREGUNTAS
    ========================================================== */
    public function obtenerPreguntas(int $id_encuesta): array
    {
        $sql = "
            SELECT 
                p.id_pregunta,
                p.id_encuesta,
                p.texto_pregunta,
                p.tipo_pregunta,
                p.orden,
                p.icono AS icono_pregunta,
                o.id_opcion,
                o.texto_opcion,
                o.icono AS icono_opcion
            FROM preguntas p
            LEFT JOIN opciones_respuesta o ON p.id_pregunta = o.id_pregunta
            WHERE p.id_encuesta = ?
            ORDER BY p.orden ASC, o.id_opcion ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id_encuesta);
        $stmt->execute();

        $res = $stmt->get_result();
        $preguntas = [];

        while ($row = $res->fetch_assoc()) {
            $pid = (int)$row['id_pregunta'];

            if (!isset($preguntas[$pid])) {
                // Aseguramos que el icono de la pregunta se pase correctamente
                $row['icono'] = $row['icono_pregunta'];
                $preguntas[$pid] = new Pregunta($row);
            }

            if ($row['id_opcion'] !== null) {
                // Aseguramos que el icono de la opción se pase correctamente
                $row['icono'] = $row['icono_opcion'];
                $preguntas[$pid]->agregarOpcion($row);
            }
        }

        $stmt->close();
        return array_values($preguntas);
    }

    /* ==========================================================
       GUARDAR RESPUESTAS (MODIFICADO PARA GÉNERO)
    ========================================================== */
    public function enviarRespuestas(array $payload): array
    {
        $idEncuesta = (int)($payload['id_encuesta'] ?? 0);
        if ($idEncuesta === 0) {
            throw new Exception('ID de encuesta inválido');
        }

        $configuracion = $this->obtenerConfiguracionEncuesta($idEncuesta);
        $idEscuela = $this->normalizarEscuela(
            (int)($payload['id_escuela'] ?? 0),
            $configuracion['id_nivel']
        );
        
        $genero = strtoupper(trim((string)($payload['genero'] ?? '')));
        if (!in_array($genero, ['M', 'F', 'O', 'X'], true)) {
            throw new InvalidArgumentException('El sexo seleccionado no es válido');
        }

        $respuestas = $payload['respuestas'] ?? [];
        $dibujos    = $payload['dibujos']    ?? [];

        if (!is_array($respuestas) || !is_array($dibujos)) {
            throw new InvalidArgumentException('El formato de las respuestas no es válido');
        }

        $this->validarRespuestas($configuracion['preguntas'], $respuestas, $dibujos);

        $this->db->begin_transaction();
        $dibujosGuardados = [];

        try {
            // La sesión forma parte de la misma transacción que sus respuestas.
            $idUsuarioEncuesta = $this->crearUsuarioEncuesta($idEncuesta, $idEscuela);
            $total = 0;

            /* ---------------- TEXTO ---------------- */
            if (!empty($respuestas['texto'])) {
                foreach ($respuestas['texto'] as $idPregunta => $texto) {
                    $this->guardarTexto(
                        $idUsuarioEncuesta,
                        $idEncuesta,
                        (int)$idPregunta,
                        $idEscuela,
                        trim($texto),
                        $genero // Pasamos género
                    );
                    $total++;
                }
            }

            /* ---------------- OPCIÓN ---------------- */
            if (!empty($respuestas['opcion'])) {
                foreach ($respuestas['opcion'] as $idPregunta => $data) {
                    $this->guardarOpcion(
                        $idUsuarioEncuesta,
                        $idEncuesta,
                        (int)$idPregunta,
                        $idEscuela,
                        (int)$data['id_opcion'],
                        $data['texto_otro'] ?? null,
                        $genero // Pasamos género
                    );
                    $total++;
                }
            }

            /* ---------------- MULTIPLE ---------------- */
            if (!empty($respuestas['multiple'])) {
                foreach ($respuestas['multiple'] as $idPregunta => $ops) {
                    foreach ($ops as $opcion) {
                        $this->guardarOpcion(
                            $idUsuarioEncuesta,
                            $idEncuesta,
                            (int)$idPregunta,
                            $idEscuela,
                            (int)$opcion['id_opcion'],
                            isset($opcion['texto_otro']) ? trim((string)$opcion['texto_otro']) : null,
                            $genero // Pasamos género
                        );
                        $total++;
                    }
                }
            }

            /* ---------------- RANKING ---------------- */
            if (!empty($respuestas['ranking'])) {
                foreach ($respuestas['ranking'] as $idPregunta => $lista) {
                    foreach ($lista as $item) {
                        if (!isset($item['id_opcion'], $item['posicion'])) continue;

                        // Nota: Ranking no lleva género en su tabla específica por ahora, 
                        // pero está vinculado por id_usuario_encuesta
                        $sql = "INSERT INTO respuestas_ranking
                                (id_usuario_encuesta, id_encuesta, id_pregunta, id_opcion, posicion)
                                VALUES (?, ?, ?, ?, ?)";

                        $stmt = $this->db->prepare($sql);
                        $stmt->bind_param("iiiii",
                            $idUsuarioEncuesta,
                            $idEncuesta,
                            $idPregunta,
                            $item['id_opcion'],
                            $item['posicion']
                        );
                        $stmt->execute();
                        $stmt->close();

                        $total++;
                    }
                }
            }

            /* ---------------- DIBUJOS ---------------- */
            if (!empty($dibujos)) {
                foreach ($dibujos as $idPregunta => $base64) {
                    $rutaGuardada = $this->guardarDibujo(
                        $idUsuarioEncuesta,
                        $idEncuesta,
                        (int)$idPregunta,
                        $idEscuela,
                        $base64,
                        $genero // Pasamos género
                    );
                    if ($rutaGuardada !== null) {
                        $dibujosGuardados[] = $rutaGuardada;
                    }
                    $total++;
                }
            }

            $this->db->commit();

            return [
                'success' => true,
                'total'   => $total
            ];

        } catch (Throwable $e) {
            $this->db->rollback();
            foreach ($dibujosGuardados as $dibujo) {
                try {
                    DibujoHelper::eliminar($dibujo['driver'], $dibujo['key']);
                } catch (Throwable $cleanupError) {
                    error_log('No se pudo limpiar dibujo tras rollback: ' . $cleanupError->getMessage());
                }
            }
            throw $e;
        }
    }

    private function obtenerConfiguracionEncuesta(int $idEncuesta): array
    {
        $sql = "SELECT e.id_nivel, e.estado, e.acepta_respuestas_hasta,
                       (e.acepta_respuestas_hasta IS NOT NULL
                        AND e.acepta_respuestas_hasta >= NOW()) AS en_gracia,
                       p.id_pregunta, p.tipo_pregunta, o.id_opcion
                FROM encuestas e
                LEFT JOIN preguntas p ON p.id_encuesta = e.id_encuesta
                LEFT JOIN opciones_respuesta o ON o.id_pregunta = p.id_pregunta
                WHERE e.id_encuesta = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $idEncuesta);
        $stmt->execute();
        $result = $stmt->get_result();

        $idNivel = null;
        $estado = null;
        $enGracia = false;
        $preguntas = [];

        while ($row = $result->fetch_assoc()) {
            $idNivel = (int)$row['id_nivel'];
            $estado = $row['estado'];
            $enGracia = (bool) $row['en_gracia'];

            if ($row['id_pregunta'] === null) {
                continue;
            }

            $idPregunta = (int)$row['id_pregunta'];
            if (!isset($preguntas[$idPregunta])) {
                $preguntas[$idPregunta] = [
                    'tipo' => strtolower(trim((string)$row['tipo_pregunta'])),
                    'opciones' => [],
                ];
            }

            if ($row['id_opcion'] !== null) {
                $preguntas[$idPregunta]['opciones'][(int)$row['id_opcion']] = true;
            }
        }

        $stmt->close();

        if ($idNivel === null) {
            throw new InvalidArgumentException('La encuesta indicada no existe');
        }
        if ($estado !== 'activa' && !$enGracia) {
            throw new InvalidArgumentException('La encuesta ya no está activa');
        }

        return [
            'id_nivel' => $idNivel,
            'preguntas' => $preguntas,
        ];
    }

    private function normalizarEscuela(int $idEscuela, int $idNivel): ?int
    {
        // 0 representa “No estudio actualmente”. 9999 se acepta únicamente
        // para que pestañas antiguas abiertas antes de esta corrección no fallen.
        if ($idEscuela <= 0 || $idEscuela === 9999) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT id_nivel FROM escuelas WHERE id_escuela = ? LIMIT 1'
        );
        $stmt->bind_param('i', $idEscuela);
        $stmt->execute();
        $stmt->bind_result($nivelEscuela);
        $existe = $stmt->fetch();
        $stmt->close();

        if (!$existe || (int)$nivelEscuela !== $idNivel) {
            throw new InvalidArgumentException('La escuela seleccionada no pertenece al nivel de la encuesta');
        }

        return $idEscuela;
    }

    private function validarRespuestas(array $preguntas, array $respuestas, array $dibujos): void
    {
        $grupos = [
            'texto' => ['texto'],
            'opcion' => ['opcion'],
            'multiple' => ['multiple'],
            'ranking' => ['ranking'],
        ];

        foreach ($grupos as $grupo => $tiposPermitidos) {
            $datosGrupo = $respuestas[$grupo] ?? [];
            if (!is_array($datosGrupo)) {
                throw new InvalidArgumentException("El grupo de respuestas '$grupo' no es válido");
            }

            foreach ($datosGrupo as $idPreguntaRaw => $valor) {
                $idPregunta = (int)$idPreguntaRaw;
                $this->validarTipoPregunta($preguntas, $idPregunta, $tiposPermitidos);

                if ($grupo === 'texto' && !is_string($valor)) {
                    throw new InvalidArgumentException('Una respuesta de texto tiene un formato inválido');
                }

                if ($grupo === 'opcion') {
                    if (!is_array($valor) || !isset($valor['id_opcion'])) {
                        throw new InvalidArgumentException('Una respuesta de opción tiene un formato inválido');
                    }
                    $this->validarOpcion($preguntas, $idPregunta, (int)$valor['id_opcion']);
                    if (isset($valor['texto_otro']) && !is_string($valor['texto_otro'])) {
                        throw new InvalidArgumentException('El texto de la opción "Otro" no es válido');
                    }
                }

                if ($grupo === 'multiple' || $grupo === 'ranking') {
                    if (!is_array($valor)) {
                        throw new InvalidArgumentException('Una respuesta con varias opciones tiene un formato inválido');
                    }
                    foreach ($valor as $item) {
                        if (!is_array($item) || !isset($item['id_opcion'])) {
                            throw new InvalidArgumentException('Una opción seleccionada tiene un formato inválido');
                        }
                        $this->validarOpcion($preguntas, $idPregunta, (int)$item['id_opcion']);
                        if (isset($item['texto_otro']) && !is_string($item['texto_otro'])) {
                            throw new InvalidArgumentException('El texto de la opción "Otro" no es válido');
                        }
                        if ($grupo === 'ranking' && (!isset($item['posicion']) || (int)$item['posicion'] <= 0)) {
                            throw new InvalidArgumentException('Una posición del ranking no es válida');
                        }
                    }
                }
            }
        }

        foreach ($dibujos as $idPreguntaRaw => $base64) {
            $idPregunta = (int)$idPreguntaRaw;
            $this->validarTipoPregunta($preguntas, $idPregunta, ['dibujo']);
            if (!is_string($base64)) {
                throw new InvalidArgumentException('Un dibujo tiene un formato inválido');
            }
        }
    }

    private function validarTipoPregunta(
        array $preguntas,
        int $idPregunta,
        array $tiposPermitidos
    ): void {
        if (!isset($preguntas[$idPregunta])) {
            throw new InvalidArgumentException('Una pregunta no pertenece a la encuesta enviada');
        }

        if (!in_array($preguntas[$idPregunta]['tipo'], $tiposPermitidos, true)) {
            throw new InvalidArgumentException('El tipo de una respuesta no coincide con su pregunta');
        }
    }

    private function validarOpcion(array $preguntas, int $idPregunta, int $idOpcion): void
    {
        if (!isset($preguntas[$idPregunta]['opciones'][$idOpcion])) {
            throw new InvalidArgumentException('Una opción no pertenece a la pregunta enviada');
        }
    }

    /* ==========================================================
       MÉTODOS PRIVADOS DE GUARDAR (Con Género)
    ========================================================== */

    private function guardarTexto(
        int $idUsuarioEncuesta,
        int $idEncuesta,
        int $idPregunta,
        ?int $idEscuela,
        string $texto,
        string $genero
    ): void {
        // Agregamos campo 'genero'
        $sql = "INSERT INTO respuestas_usuario
            (id_usuario_encuesta, id_encuesta, id_pregunta, respuesta_texto, id_escuela, genero, fecha_respuesta)
            VALUES (?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $this->db->prepare($sql);
        // Tipos: i=int, s=string -> iiisis
        $stmt->bind_param("iiisis",
            $idUsuarioEncuesta,
            $idEncuesta,
            $idPregunta,
            $texto,
            $idEscuela,
            $genero
        );
        $stmt->execute();
        $stmt->close();
    }

    private function guardarOpcion(
        int $idUsuarioEncuesta,
        int $idEncuesta,
        int $idPregunta,
        ?int $idEscuela,
        int $idOpcion,
        ?string $textoOtro = null,
        string $genero = 'X'
    ): void {
        // Agregamos campo 'genero'
        $sql = "INSERT INTO respuestas_usuario
            (id_usuario_encuesta, id_encuesta, id_pregunta, id_opcion, respuesta_texto, id_escuela, genero, fecha_respuesta)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $this->db->prepare($sql);
        // Tipos: iiiisis
        $stmt->bind_param("iiiisis",
            $idUsuarioEncuesta,
            $idEncuesta,
            $idPregunta,
            $idOpcion,
            $textoOtro,
            $idEscuela,
            $genero
        );
        $stmt->execute();
        $stmt->close();
    }

    private function guardarDibujo(
        int $idUsuarioEncuesta,
        int $idEncuesta,
        int $idPregunta,
        ?int $idEscuela,
        string $base64,
        string $genero
    ): ?array {
        if (strlen($base64) < 50) return null;

        // Agregamos campo 'genero'
        $sql = "INSERT INTO respuestas_usuario
                (id_usuario_encuesta, id_encuesta, id_pregunta, id_escuela, genero, fecha_respuesta)
                VALUES (?, ?, ?, ?, ?, NOW())";

        $stmt = $this->db->prepare($sql);
        // Tipos: iiiis
        $stmt->bind_param("iiiis",
            $idUsuarioEncuesta,
            $idEncuesta,
            $idPregunta,
            $idEscuela,
            $genero
        );
        $stmt->execute();

        $idRespuesta = $this->db->insert_id;
        $stmt->close();

        // Guardar archivo físico
        try {
            $guardado = DibujoHelper::guardar($base64, $idRespuesta);

            $sql = "UPDATE respuestas_usuario
                    SET dibujo_ruta=NULL,dibujo_storage=?,dibujo_objeto=?,dibujo_bytes=?
                    WHERE id_respuesta_usuario=?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param(
                "ssii",
                $guardado['driver'],
                $guardado['key'],
                $guardado['bytes'],
                $idRespuesta
            );
            $stmt->execute();
            $stmt->close();

            return $guardado;
        } catch (Throwable $e) {
            if (isset($guardado)) {
                try {
                    DibujoHelper::eliminar($guardado['driver'], $guardado['key']);
                } catch (Throwable $cleanupError) {
                    error_log('No se pudo limpiar dibujo fallido: ' . $cleanupError->getMessage());
                }
            }
            throw $e;
        }
    }
}
