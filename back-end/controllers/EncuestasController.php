<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Pregunta.php';
require_once __DIR__ . '/../database/Conexion.php';

class EncuestasController
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    /**
     * Obtiene la encuesta completa basado en un nivel textual.
     */
    public function obtenerEncuestaPorNivel(string $nivel): array
    {
        $nivel = strtolower(trim($nivel));

        $niveles = [
            'preescolar'   => 1,
            'primaria'     => 4,
            'secundaria'   => 5,
            'preparatoria' => 6,
        ];

        $idEncuesta = $niveles[$nivel] ?? 4;

        return [
            'id_encuesta' => $idEncuesta,
            'nivel'       => $nivel,
            'preguntas'   => $this->obtenerPreguntas($idEncuesta)
        ];
    }

    /**
     * Obtiene todas las preguntas + opciones de una encuesta.
     */
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
            $pid = (int)$row['id_pregunta'];

            if (!isset($preguntas[$pid])) {
                $preguntas[$pid] = new Pregunta($row);
            }

            if ($row['id_opcion'] !== null) {
                $preguntas[$pid]->agregarOpcion($row);
            }
        }

        return array_values($preguntas);
    }

    /* ------------ Envío de respuestas ------------ */

    public function enviarRespuestas(array $payload): array
    {
        $id_encuesta = (int)$payload['id_encuesta'];
        $respuestas  = $payload['respuestas'] ?? [];
        $dibujos     = $payload['dibujos'] ?? [];

        foreach ($respuestas as $id_pregunta => $valor) {
            if (is_array($valor)) {
                $this->guardarArrayRespuesta((int)$id_pregunta, $valor);
            } else {
                $this->guardarTexto((int)$id_pregunta, (string)$valor);
            }
        }

        foreach ($dibujos as $id_pregunta => $base64) {
            $this->guardarDibujo((int)$id_pregunta, $base64);
        }

        return [ "success" => true ];
    }

    private function guardarTexto(int $id_pregunta, string $valor): void
    {
        $sql = "INSERT INTO respuestas (id_pregunta, respuesta_texto)
                VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("is", $id_pregunta, $valor);
        $stmt->execute();
        $stmt->close();
    }

    private function guardarArrayRespuesta(int $id_pregunta, array $arr): void
    {
        foreach ($arr as $item) {
            $id_op = (int)$item['id_opcion'];
            $pos   = $item['posicion'] ?? null;

            $sql = "INSERT INTO respuestas (id_pregunta, id_opcion, posicion) 
                    VALUES (?,?,?)";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("iii", $id_pregunta, $id_op, $pos);
            $stmt->execute();
            $stmt->close();
        }
    }

    private function guardarDibujo(int $id_pregunta, string $base64): void
    {
        $sql = "INSERT INTO respuestas (id_pregunta, respuesta_imagen)
                VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("is", $id_pregunta, $base64);
        $stmt->execute();
        $stmt->close();
    }
}
