<?php
require_once __DIR__ . '/../database/Conexion.php';
require_once __DIR__ . '/../helpers/DibujoHelper.php';

class ResultadosTextoController
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    /**
     * MODIFICADO: Ahora incluye filtro por ciclo escolar
     */
    public function obtener(int $idPregunta, int $idEscuela = 0, string $cicloEscolar = ''): array
    {
        if ($idPregunta <= 0) {
            return [
                'success' => false,
                'error' => 'ID de pregunta inválido',
                'respuestas' => []
            ];
        }

        // NUEVO: Extraer años del ciclo escolar
        $cicloInicio = null;
        $cicloFin = null;
        if ($cicloEscolar && strpos($cicloEscolar, '-') !== false) {
            list($cicloInicio, $cicloFin) = explode('-', $cicloEscolar);
            $cicloInicio = (int)$cicloInicio;
            $cicloFin = (int)$cicloFin;
        }

        $sql = "SELECT 
                    r.id_respuesta_usuario,
                    r.respuesta_texto,
                    r.dibujo_ruta,
                    r.fecha_respuesta,
                    e.nombre_escuela,
                    p.tipo_pregunta
                FROM respuestas_usuario r
                INNER JOIN escuelas e ON r.id_escuela = e.id_escuela
                INNER JOIN preguntas p ON r.id_pregunta = p.id_pregunta
                WHERE r.id_pregunta = ?";

        $params = [$idPregunta];
        $types  = "i";

        if ($idEscuela > 0) {
            $sql .= " AND r.id_escuela = ?";
            $params[] = $idEscuela;
            $types .= "i";
        }

        // NUEVO: Filtro por ciclo escolar
        if ($cicloInicio !== null && $cicloFin !== null) {
            $sql .= " AND (
                (YEAR(r.fecha_respuesta) = ? AND MONTH(r.fecha_respuesta) >= 8) OR
                (YEAR(r.fecha_respuesta) = ? AND MONTH(r.fecha_respuesta) <= 7)
            )";
            $params[] = $cicloInicio;
            $params[] = $cicloFin;
            $types .= "ii";
        }

        $sql .= " ORDER BY r.fecha_respuesta DESC";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [
                'success' => false,
                'error' => 'Error en la consulta: ' . $this->db->error,
                'respuestas' => []
            ];
        }

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $respuestas = [];
        $tipoPregunta = null;

        while ($row = $result->fetch_assoc()) {
            if ($tipoPregunta === null) {
                $tipoPregunta = strtolower($row['tipo_pregunta']);
            }

            $respuesta = [
                'id' => (int)$row['id_respuesta_usuario'],
                'fecha' => $row['fecha_respuesta'],
                'escuela' => $row['nombre_escuela'],
                'tipo' => $tipoPregunta
            ];

            // Determinar si es texto o dibujo
            if (!empty($row['dibujo_ruta'])) {
                $respuesta['es_dibujo'] = true;
                $respuesta['ruta_dibujo'] = $row['dibujo_ruta'];
                $respuesta['existe_archivo'] = DibujoHelper::existe($row['dibujo_ruta']);
                
                // Info adicional del archivo si existe
                if ($respuesta['existe_archivo']) {
                    $info = DibujoHelper::obtenerInfo($row['dibujo_ruta']);
                    $respuesta['tamaño'] = $info['tamaño_legible'] ?? 'N/A';
                }
            } else {
                $respuesta['es_dibujo'] = false;
                $respuesta['texto'] = $row['respuesta_texto'];
            }

            $respuestas[] = $respuesta;
        }

        return [
            'success' => true,
            'respuestas' => $respuestas,
            'total' => count($respuestas),
            'tipo_pregunta' => $tipoPregunta
        ];
    }

    /**
     * Elimina respuesta y archivo de dibujo si existe
     */
    public function eliminar(int $idRespuesta): array
    {
        if ($idRespuesta <= 0) {
            return [
                'success' => false,
                'error' => 'ID inválido'
            ];
        }

        // Obtener ruta del dibujo antes de eliminar
        $stmt = $this->db->prepare("SELECT dibujo_ruta FROM respuestas_usuario WHERE id_respuesta_usuario = ?");
        if (!$stmt) {
            return [
                'success' => false,
                'error' => 'Error en la consulta: ' . $this->db->error
            ];
        }

        $stmt->bind_param("i", $idRespuesta);
        $stmt->execute();
        $stmt->bind_result($rutaDibujo);
        $stmt->fetch();
        $stmt->close();

        // Eliminar archivo si existe
        if (!empty($rutaDibujo)) {
            DibujoHelper::eliminar($rutaDibujo);
        }

        // Eliminar registro de DB
        $stmt = $this->db->prepare("DELETE FROM respuestas_usuario WHERE id_respuesta_usuario = ? LIMIT 1");
        if (!$stmt) {
            return [
                'success' => false,
                'error' => 'Error en la consulta: ' . $this->db->error
            ];
        }

        $stmt->bind_param("i", $idRespuesta);
        $stmt->execute();

        return [
            'success' => $stmt->affected_rows > 0,
            'message' => ($stmt->affected_rows > 0)
                ? "Respuesta eliminada"
                : "No se pudo eliminar"
        ];
    }
}