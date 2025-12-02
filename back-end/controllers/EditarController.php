<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/conexion-db.php';
require_once __DIR__ . '/../models/Pregunta.php';

class EditarController
{
    private mysqli $db;

    public function __construct()
    {
        global $conn;
        $this->db = $conn;
    }

    /* ==================================================================
       GUARDADO MASIVO CON IMÁGENES
    ================================================================== */
    public function guardarCambios(string $nivel, array $preguntas, array $eliminadas): array
    {
        $idEncuesta = $this->obtenerIdEncuestaPorNivel($nivel);
        if (!$idEncuesta) {
            return ['success' => false, 'error' => 'Nivel no válido o encuesta no encontrada'];
        }

        $this->db->begin_transaction();

        try {
            /* -------------------------------------------
               A) BORRAR PREGUNTAS ELIMINADAS
            ------------------------------------------- */
            foreach ($eliminadas as $idDel) {
                $idDel = (int)$idDel;
                if ($idDel > 0) {
                    $this->borrarPreguntaCompleta($idDel);
                }
            }

            /* -------------------------------------------
               B) PROCESAR PREGUNTAS (INSERT O UPDATE)
            ------------------------------------------- */
            foreach ($preguntas as $p) {

                $texto = trim($p['texto'] ?? "");
                if ($texto === "") continue;

                $idPregunta = (int)($p['id'] ?? 0);
                $tipo = strtolower(trim($p['tipo'] ?? "texto"));
                $orden = (int)($p['orden'] ?? 0);
                
                // Aquí llega la ruta final (ya sea la nueva o la vieja) procesada por guardar.php
                $icono = $p['icono'] ?? null; 

                $tiposValidos = ['opcion','multiple','ranking','texto','dibujo'];
                if (!in_array($tipo, $tiposValidos, true)) {
                    $tipo = 'texto';
                }

                /* -----------------------------
                   1. ACTUALIZAR O INSERTAR PREGUNTA
                ----------------------------- */
                if ($idPregunta > 0) {
                    // UPDATE: Actualizamos TODO, incluyendo el icono (sea null, viejo o nuevo)
                    $stmt = $this->db->prepare("
                        UPDATE preguntas 
                        SET texto_pregunta=?, tipo_pregunta=?, orden=?, icono=?
                        WHERE id_pregunta=?
                    ");
                    // Tipos: s=string, s=string, i=int, s=string(path), i=int
                    $stmt->bind_param("ssisi", $texto, $tipo, $orden, $icono, $idPregunta);
                    $stmt->execute();
                    $stmt->close();

                } else {
                    // INSERT: Nueva pregunta
                    $stmt = $this->db->prepare("
                        INSERT INTO preguntas (id_encuesta, texto_pregunta, tipo_pregunta, orden, icono)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    // Tipos: i=int, s=string, s=string, i=int, s=string(path)
                    $stmt->bind_param("issis", $idEncuesta, $texto, $tipo, $orden, $icono);
                    $stmt->execute();
                    $idPregunta = $stmt->insert_id;
                    $stmt->close();
                }

                /* -----------------------------
                   2. GESTIONAR OPCIONES
                ----------------------------- */
                
                // a) Borrar opciones anteriores para evitar duplicados/desorden
                $stmtDel = $this->db->prepare("DELETE FROM opciones_respuesta WHERE id_pregunta=?");
                $stmtDel->bind_param("i", $idPregunta);
                $stmtDel->execute();
                $stmtDel->close();

                // b) Insertar las opciones actuales
                if (in_array($tipo, ['opcion','multiple','ranking'], true)
                    && !empty($p['opciones'])
                    && is_array($p['opciones'])) {

                    $stmtOp = $this->db->prepare("
                        INSERT INTO opciones_respuesta (id_pregunta, texto_opcion, icono)
                        VALUES (?, ?, ?)
                    ");

                    foreach ($p['opciones'] as $op) {
                        $textoOp = trim($op['texto'] ?? "");
                        // Aquí llega la ruta final de la opción (vieja o nueva)
                        $iconoOp = $op['icono'] ?? null;

                        // Si no hay texto ni imagen, saltamos
                        if ($textoOp === "" && !$iconoOp) {
                            continue; 
                        }

                        $stmtOp->bind_param("iss", $idPregunta, $textoOp, $iconoOp);
                        $stmtOp->execute();
                    }

                    $stmtOp->close();
                }
            }

            $this->db->commit();
            return ['success' => true];

        } catch (Throwable $e) {
            $this->db->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /* ==================================================================
       LECTURA
    ================================================================= */
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
            $pid = (int)$row["id_pregunta"];

            if (!isset($preguntas[$pid])) {
                $preguntas[$pid] = new Pregunta($row);
                // Asignar icono de pregunta al objeto
                $preguntas[$pid]->icono = $row["icono_pregunta"];
            }

            if ($row["id_opcion"] !== null) {
                // Asignar icono de opción antes de agregarla
                $row["icono"] = $row["icono_opcion"];
                $preguntas[$pid]->agregarOpcion($row);
            }
        }

        return array_values($preguntas);
    }

    /* ==================================================================
       ACCIONES INDIVIDUALES (AUXILIARES)
    ================================================================== */

    public function crearPregunta(int $id_encuesta): int
    {
        $sql = "INSERT INTO preguntas (id_encuesta, texto_pregunta, tipo_pregunta, orden)
                VALUES (?, 'Nueva pregunta', 'texto', 999)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id_encuesta);
        $stmt->execute();
        return $stmt->insert_id;
    }

    public function crearOpcion(int $id_pregunta): int
    {
        $sql = "INSERT INTO opciones_respuesta (id_pregunta, texto_opcion) 
                VALUES (?, 'Nueva opción')";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id_pregunta);
        $stmt->execute();
        return $stmt->insert_id;
    }

    public function actualizarPregunta(int $id_pregunta, string $texto): bool
    {
        $sql = "UPDATE preguntas SET texto_pregunta = ? WHERE id_pregunta = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $texto, $id_pregunta);
        return $stmt->execute();
    }

    public function actualizarOpcion(int $id_opcion, string $texto): bool
    {
        $sql = "UPDATE opciones_respuesta SET texto_opcion = ? WHERE id_opcion = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $texto, $id_opcion);
        return $stmt->execute();
    }

    public function eliminarPregunta(int $id_pregunta): bool
    {
        $stmt1 = $this->db->prepare("DELETE FROM opciones_respuesta WHERE id_pregunta = ?");
        $stmt1->bind_param("i", $id_pregunta);
        $stmt1->execute();
        $stmt1->close();

        $stmt2 = $this->db->prepare("DELETE FROM preguntas WHERE id_pregunta = ?");
        $stmt2->bind_param("i", $id_pregunta);
        $res = $stmt2->execute();
        $stmt2->close();

        return $res;
    }

    public function eliminarOpcion(int $id_opcion): bool
    {
        $stmt = $this->db->prepare("DELETE FROM opciones_respuesta WHERE id_opcion = ?");
        $stmt->bind_param("i", $id_opcion);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    /* ==================================================================
       HELPERS
    ================================================================== */
    private function obtenerIdEncuestaPorNivel(string $nivel): ?int
    {
        $map = [
            'preescolar'   => 1,
            'primaria'     => 4,
            'secundaria'   => 5,
            'preparatoria' => 6,
        ];
        return $map[strtolower(trim($nivel))] ?? null;
    }

    private function borrarPreguntaCompleta(int $idPregunta): void
    {
        $tablas = [
            'respuestas_usuario',
            'respuestas_ranking',
            'opciones_respuesta',
            'preguntas'
        ];

        foreach ($tablas as $t) {
            $stmt = $this->db->prepare("DELETE FROM $t WHERE id_pregunta = ?");
            $stmt->bind_param("i", $idPregunta);
            $stmt->execute();
            $stmt->close();
        }
    }
}