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
    /* ==================================================================
       GUARDADO MASIVO CON IMÁGENES (OPTIMIZADO)
    ================================================================== */
    public function guardarCambios(string $nivel, array $preguntas, array $eliminadas): array
    {
        $idEncuesta = $this->obtenerIdEncuestaPorNivel($nivel);
        if (!$idEncuesta) {
            return ['success' => false, 'error' => 'Nivel no válido o encuesta no encontrada'];
        }

        // Iniciamos la transacción
        $this->db->begin_transaction();

        try {
            /* -------------------------------------------
               1. PREPARAR TODAS LAS SENTENCIAS SQL UNA SOLA VEZ
               (Esto es lo que acelera el proceso)
            ------------------------------------------- */
            
            // A) Para borrar preguntas eliminadas
            $stmtDelRespUser  = $this->db->prepare("DELETE FROM respuestas_usuario WHERE id_pregunta = ?");
            $stmtDelRanking   = $this->db->prepare("DELETE FROM respuestas_ranking WHERE id_pregunta = ?");
            $stmtDelOpciones  = $this->db->prepare("DELETE FROM opciones_respuesta WHERE id_pregunta = ?");
            $stmtDelPregunta  = $this->db->prepare("DELETE FROM preguntas WHERE id_pregunta = ?");

            // B) Para preguntas (Update e Insert)
            $stmtUpdateP = $this->db->prepare("UPDATE preguntas SET texto_pregunta=?, tipo_pregunta=?, orden=?, icono=? WHERE id_pregunta=?");
            $stmtInsertP = $this->db->prepare("INSERT INTO preguntas (id_encuesta, texto_pregunta, tipo_pregunta, orden, icono) VALUES (?, ?, ?, ?, ?)");

            // C) Para opciones (Limpiar e Insertar)
            $stmtCleanOp  = $this->db->prepare("DELETE FROM opciones_respuesta WHERE id_pregunta=?");
            $stmtInsertOp = $this->db->prepare("INSERT INTO opciones_respuesta (id_pregunta, texto_opcion, icono) VALUES (?, ?, ?)");

            /* -------------------------------------------
               2. EJECUTAR BORRADOS (SI HAY)
            ------------------------------------------- */
            if (!empty($eliminadas)) {
                foreach ($eliminadas as $idDel) {
                    $idDel = (int)$idDel;
                    if ($idDel > 0) {
                        // Solo pasamos el ID y ejecutamos (ya está preparado arriba)
                        $stmtDelRespUser->bind_param("i", $idDel); $stmtDelRespUser->execute();
                        $stmtDelRanking->bind_param("i", $idDel);  $stmtDelRanking->execute();
                        $stmtDelOpciones->bind_param("i", $idDel); $stmtDelOpciones->execute();
                        $stmtDelPregunta->bind_param("i", $idDel); $stmtDelPregunta->execute();
                    }
                }
            }

            /* -------------------------------------------
               3. EJECUTAR EL BUCLE PRINCIPAL
            ------------------------------------------- */
            foreach ($preguntas as $p) {
                $texto = trim($p['texto'] ?? "");
                if ($texto === "") continue;

                $idPregunta = (int)($p['id'] ?? 0);
                $tipo       = strtolower(trim($p['tipo'] ?? "texto"));
                $orden      = (int)($p['orden'] ?? 0);
                $icono      = $p['icono'] ?? null;

                if (!in_array($tipo, ['opcion','multiple','ranking','texto','dibujo'], true)) {
                    $tipo = 'texto';
                }

                // --- Guardar Pregunta ---
                if ($idPregunta > 0) {
                    // UPDATE
                    $stmtUpdateP->bind_param("ssisi", $texto, $tipo, $orden, $icono, $idPregunta);
                    $stmtUpdateP->execute();
                } else {
                    // INSERT
                    $stmtInsertP->bind_param("issis", $idEncuesta, $texto, $tipo, $orden, $icono);
                    $stmtInsertP->execute();
                    $idPregunta = $stmtInsertP->insert_id; // Recuperar ID nuevo para usarlo en opciones
                }

                // --- Guardar Opciones ---
                
                // 1. Borrar opciones viejas de esta pregunta (limpieza)
                $stmtCleanOp->bind_param("i", $idPregunta);
                $stmtCleanOp->execute();

                // 2. Insertar nuevas (si aplica)
                if (in_array($tipo, ['opcion','multiple','ranking'], true) && !empty($p['opciones']) && is_array($p['opciones'])) {
                    foreach ($p['opciones'] as $op) {
                        $textoOp = trim($op['texto'] ?? "");
                        $iconoOp = $op['icono'] ?? null;

                        if ($textoOp === "" && !$iconoOp) continue;

                        $stmtInsertOp->bind_param("iss", $idPregunta, $textoOp, $iconoOp);
                        $stmtInsertOp->execute();
                    }
                }
            }

            /* -------------------------------------------
               4. LIMPIEZA Y CIERRE
            ------------------------------------------- */
            // Cerramos todas las sentencias para liberar memoria
            $stmtDelRespUser->close(); $stmtDelRanking->close(); $stmtDelOpciones->close(); $stmtDelPregunta->close();
            $stmtUpdateP->close(); $stmtInsertP->close();
            $stmtCleanOp->close(); $stmtInsertOp->close();

            // Confirmamos la transacción
            $this->db->commit();
            return ['success' => true];

        } catch (Throwable $e) {
            // Si algo falla, revertimos todo
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