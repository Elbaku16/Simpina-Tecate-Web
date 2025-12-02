<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/conexion-db.php';
require_once __DIR__ . '/../models/Pregunta.php';

class EditarController
{
    private mysqli $db;

    public function __construct()
    {
        // Usamos la conexión global que viene desde conexion-db.php
        global $conn;
        $this->db = $conn;
    }

    /* ============================================================
       NUEVO MÉTODO PRINCIPAL: GUARDADO MASIVO (Reemplaza a guardar.php)
    ============================================================ */
    public function guardarCambios(string $nivel, array $preguntas, array $eliminadas): array
    {
        // 1. Obtener ID de encuesta
        $idEncuesta = $this->obtenerIdEncuestaPorNivel($nivel);
        if (!$idEncuesta) {
            return ['success' => false, 'error' => 'Nivel no válido o encuesta no encontrada'];
        }

        // 2. Iniciar Transacción (Todo o nada)
        $this->db->begin_transaction();

        try {
            // A) PROCESAR ELIMINACIONES
            // Borramos preguntas marcadas y todos sus datos asociados
            foreach ($eliminadas as $idDel) {
                $idDel = (int)$idDel;
                if ($idDel > 0) {
                    $this->borrarPreguntaCompleta($idDel);
                }
            }

            // B) PROCESAR PREGUNTAS (Insertar o Actualizar)
            foreach ($preguntas as $p) {
                // Validar datos mínimos
                $texto = trim($p['texto'] ?? "");
                if ($texto === "") continue;

                $idPregunta = (int)($p['id_pregunta'] ?? 0);
                $tipo       = strtolower(trim($p['tipo'] ?? "texto"));
                $orden      = (int)($p['orden'] ?? 0);
                
                // Validar tipo
                $tiposValidos = ['opcion','multiple','ranking','texto','dibujo'];
                if (!in_array($tipo, $tiposValidos, true)) {
                    $tipo = 'texto';
                }

                // 1. Guardar la Pregunta
                if ($idPregunta > 0) {
                    // UPDATE
                    $stmt = $this->db->prepare("UPDATE preguntas SET texto_pregunta=?, tipo_pregunta=?, orden=? WHERE id_pregunta=?");
                    $stmt->bind_param("ssii", $texto, $tipo, $orden, $idPregunta);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    // INSERT
                    $stmt = $this->db->prepare("INSERT INTO preguntas (id_encuesta, texto_pregunta, tipo_pregunta, orden) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("issi", $idEncuesta, $texto, $tipo, $orden);
                    $stmt->execute();
                    $idPregunta = $stmt->insert_id;
                    $stmt->close();
                }

                // 2. Procesar Opciones (Solo si aplica)
                // Estrategia: Borrar opciones viejas e insertar nuevas (más seguro para evitar desorden)
                $stmtDel = $this->db->prepare("DELETE FROM opciones_respuesta WHERE id_pregunta=?");
                $stmtDel->bind_param("i", $idPregunta);
                $stmtDel->execute();
                $stmtDel->close();

                if (in_array($tipo, ['opcion','multiple','ranking'], true) && !empty($p['opciones']) && is_array($p['opciones'])) {
                    $stmtOp = $this->db->prepare("INSERT INTO opciones_respuesta (id_pregunta, texto_opcion) VALUES (?, ?)");
                    foreach ($p['opciones'] as $op) {
                        $textoOp = trim($op['texto'] ?? "");
                        if ($textoOp !== "") {
                            $stmtOp->bind_param("is", $idPregunta, $textoOp);
                            $stmtOp->execute();
                        }
                    }
                    $stmtOp->close();
                }
            }

            // 3. Confirmar cambios
            $this->db->commit();
            return ['success' => true];

        } catch (Throwable $e) {
            // 4. Revertir si hay error
            $this->db->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /* ============================================================
       MÉTODOS DE LECTURA
    ============================================================ */
    public function obtenerPreguntas(int $id_encuesta): array
    {
        $sql = "
            SELECT 
                p.id_pregunta,
                p.id_encuesta,
                p.texto_pregunta,
                p.tipo_pregunta,
                p.orden,
                o.id_opcion,
                o.texto_opcion
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
            }
            if ($row["id_opcion"] !== null) {
                $preguntas[$pid]->agregarOpcion($row);
            }
        }

        return array_values($preguntas);
    }

    /* ============================================================
       MÉTODOS INDIVIDUALES (Para acciones simples AJAX)
    ============================================================ */

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

    // CORREGIDO: Ahora usa sentencias preparadas
    public function eliminarPregunta(int $id_pregunta): bool
    {
        // Primero borrar opciones
        $stmt1 = $this->db->prepare("DELETE FROM opciones_respuesta WHERE id_pregunta = ?");
        $stmt1->bind_param("i", $id_pregunta);
        $stmt1->execute();
        $stmt1->close();

        // Luego borrar pregunta
        $stmt2 = $this->db->prepare("DELETE FROM preguntas WHERE id_pregunta = ?");
        $stmt2->bind_param("i", $id_pregunta);
        $res = $stmt2->execute();
        $stmt2->close();

        return $res;
    }

    // CORREGIDO: Ahora usa sentencias preparadas
    public function eliminarOpcion(int $id_opcion): bool
    {
        $stmt = $this->db->prepare("DELETE FROM opciones_respuesta WHERE id_opcion = ?");
        $stmt->bind_param("i", $id_opcion);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    /* ============================================================
       HELPERS PRIVADOS
    ============================================================ */

    private function obtenerIdEncuestaPorNivel(string $nivel): ?int
    {
        // Idealmente esto vendría de la BD, pero mantenemos tu lógica actual
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
        // Eliminación en cascada manual para asegurar integridad
        $tablas = [
            'respuestas_usuario',
            'respuestas_ranking',
            'opciones_respuesta',
            'preguntas'
        ];

        foreach ($tablas as $tabla) {
            $stmt = $this->db->prepare("DELETE FROM $tabla WHERE id_pregunta = ?");
            $stmt->bind_param("i", $idPregunta);
            $stmt->execute();
            $stmt->close();
        }
    }
}