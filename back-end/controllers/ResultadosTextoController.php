<?php
require_once __DIR__ . '/../database/Conexion.php';

class ResultadosTextoController
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    /**
     * Obtener respuestas de texto para una pregunta
     */
    public function obtener(int $idPregunta, int $idEscuela = 0): array
    {
        if ($idPregunta <= 0) {
            return [
                'success' => false,
                'error' => 'ID de pregunta inválido',
                'respuestas' => []
            ];
        }

        $sql = "SELECT 
                    r.id_respuesta_usuario,
                    r.respuesta_texto,
                    r.fecha_respuesta,
                    e.nombre_escuela
                FROM respuestas_usuario r
                INNER JOIN escuelas e ON r.id_escuela = e.id_escuela
                WHERE r.id_pregunta = ?";

        $params = [$idPregunta];
        $types  = "i";

        if ($idEscuela > 0) {
            $sql .= " AND r.id_escuela = ?";
            $params[] = $idEscuela;
            $types .= "i";
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
        while ($row = $result->fetch_assoc()) {
            $respuestas[] = [
                'id' => (int)$row['id_respuesta'],
                'texto' => $row['respuesta_texto'],
                'fecha' => $row['fecha_respuesta'],
                'escuela' => $row['nombre_escuela']
            ];
        }

        return [
            'success' => true,
            'respuestas' => $respuestas,
            'total' => count($respuestas)
        ];
    }

    /**
     * Eliminar respuesta de texto
     */
    public function eliminar(int $idRespuesta): array
    {
        if ($idRespuesta <= 0) {
            return [
                'success' => false,
                'error' => 'ID inválido'
            ];
        }

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
