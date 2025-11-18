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
        // Validar sesión y usuario
        session_start();
        if (!isset($_SESSION['id_usuario'])) {
            echo json_encode(["success" => false, "error" => "Sesión no iniciada"]);
            exit;
        }
        $id_usuario = (int)$_SESSION['id_usuario'];

        $id_encuesta = (int)$payload['id_encuesta'];
        $respuestas  = $payload['respuestas'] ?? [];
        $dibujos     = $payload['dibujos'] ?? [];

        foreach ($respuestas as $id_pregunta => $valor) {
            // Normalizar id_pregunta y preparar variable respuesta
            $id_pregunta = (int)$id_pregunta;

            // Si la pregunta pertenece a la encuesta
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM preguntas WHERE id_pregunta = ? AND id_encuesta = ?");
            $stmt->bind_param("ii", $id_pregunta, $id_encuesta);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();
            if ((int)$count === 0) {
                echo json_encode(["success" => false, "error" => "Pregunta no pertenece a la encuesta"]);
                exit;
            }

            // Validar que la encuesta está activa
            $stmt = $this->db->prepare("SELECT estado, fecha_fin FROM encuestas WHERE id_encuesta = ?");
            $stmt->bind_param("i", $id_encuesta);
            $stmt->execute();
            $stmt->bind_result($estado, $fechaFin);
            $stmt->fetch();
            $stmt->close();
            if ($estado !== "activa" || (isset($fechaFin) && strtotime($fechaFin) < time())) {
                echo json_encode(["success" => false, "error" => "Encuesta inactiva o expirada"]);
                exit;
            }

            // Obtener tipo de pregunta
            $stmt = $this->db->prepare("SELECT tipo_pregunta FROM preguntas WHERE id_pregunta = ?");
            $stmt->bind_param("i", $id_pregunta);
            $stmt->execute();
            $stmt->bind_result($tipo);
            $stmt->fetch();
            $stmt->close();

            // Validaciones según tipo
            if ($tipo === "texto") {
                $respuesta = (string)$valor;
                if (strlen($respuesta) > 5000) {
                    echo json_encode(["success" => false, "error" => "Texto demasiado largo"]);
                    exit;
                }

                // Validar duplicado por texto (no hay id_usuario en respuestas_usuario)
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM respuestas_usuario WHERE id_pregunta = ? AND respuesta_texto = ?");
                $stmt->bind_param("is", $id_pregunta, $respuesta);
                $stmt->execute();
                $stmt->bind_result($yaRespondio);
                $stmt->fetch();
                $stmt->close();
                if ((int)$yaRespondio > 0) {
                    echo json_encode(["success" => false, "error" => "La pregunta ya fue respondida"]);
                    exit;
                }

                $this->guardarTexto($id_pregunta, $respuesta, $id_encuesta);
                continue;
            }

            if ($tipo === "multiple" || $tipo === "opcion") {
                // Para respuestas tipo array (multiple) se validan las opciones dentro de guardarArrayRespuesta
                if (is_array($valor)) {
                    // validar cada opcion
                    foreach ($valor as $item) {
                        $respuesta = (int)$item['id_opcion'];

                        $stmt = $this->db->prepare("SELECT COUNT(*) FROM opciones_respuesta WHERE id_opcion = ? AND id_pregunta = ?");
                        $stmt->bind_param("ii", $respuesta, $id_pregunta);
                        $stmt->execute();
                        $stmt->bind_result($validaOpcion);
                        $stmt->fetch();
                        $stmt->close();
                        if ((int)$validaOpcion === 0) {
                            echo json_encode(["success" => false, "error" => "Opción inválida"]);
                            exit;
                        }
                    }

                    // Validar duplicado por usuario en tabla respuestas_ranking
                    $stmt = $this->db->prepare("SELECT COUNT(*) FROM respuestas_ranking WHERE id_usuario = ? AND id_pregunta = ?");
                    $stmt->bind_param("ii", $id_usuario, $id_pregunta);
                    $stmt->execute();
                    $stmt->bind_result($yaRespondioRank);
                    $stmt->fetch();
                    $stmt->close();
                    if ((int)$yaRespondioRank > 0) {
                        echo json_encode(["success" => false, "error" => "La pregunta ya fue respondida"]);
                        exit;
                    }

                    $this->guardarArrayRespuesta($id_pregunta, $valor);
                    continue;
                }

                // caso de opcion simple
                $respuesta = (int)$valor;
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM opciones_respuesta WHERE id_opcion = ? AND id_pregunta = ?");
                $stmt->bind_param("ii", $respuesta, $id_pregunta);
                $stmt->execute();
                $stmt->bind_result($validaOpcion);
                $stmt->fetch();
                $stmt->close();
                if ((int)$validaOpcion === 0) {
                    echo json_encode(["success" => false, "error" => "Opción inválida"]);
                    exit;
                }

                // Intentar guardar como texto de opción en respuestas_usuario (columna id_opcion existe)
                $sql = "INSERT INTO respuestas_usuario (id_encuesta, id_pregunta, id_opcion) VALUES (?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("iii", $id_encuesta, $id_pregunta, $respuesta);
                $stmt->execute();
                $stmt->close();

                continue;
            }

            if ($tipo === "imagen" || $tipo === "dibujo") {
                $respuesta = (string)$valor;
                if (strpos($respuesta, "data:image/") !== 0) {
                    echo json_encode(["success" => false, "error" => "Formato de imagen inválido"]);
                    exit;
                }

                // No hay id_usuario en respuestas_usuario, validar duplicado por texto/imagen
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM respuestas_usuario WHERE id_pregunta = ? AND respuesta_texto = ?");
                $stmt->bind_param("is", $id_pregunta, $respuesta);
                $stmt->execute();
                $stmt->bind_result($yaRespondioImg);
                $stmt->fetch();
                $stmt->close();
                if ((int)$yaRespondioImg > 0) {
                    echo json_encode(["success" => false, "error" => "La pregunta ya fue respondida"]);
                    exit;
                }

                $this->guardarTexto($id_pregunta, $respuesta, $id_encuesta);
                continue;
            }

            // Si no coincidió ningún tipo, intentar guardar como texto por defecto
            $this->guardarTexto($id_pregunta, (string)$valor, $id_encuesta);
        }

        foreach ($dibujos as $id_pregunta => $base64) {
            $id_pregunta = (int)$id_pregunta;

            // Validar que la pregunta pertenece a la encuesta
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM preguntas WHERE id_pregunta = ? AND id_encuesta = ?");
            $stmt->bind_param("ii", $id_pregunta, $id_encuesta);
            $stmt->execute();
            $stmt->bind_result($countD);
            $stmt->fetch();
            $stmt->close();
            if ((int)$countD === 0) {
                echo json_encode(["success" => false, "error" => "Pregunta no pertenece a la encuesta"]);
                exit;
            }

            // Obtener tipo y validar dibujo
            $stmt = $this->db->prepare("SELECT tipo_pregunta FROM preguntas WHERE id_pregunta = ?");
            $stmt->bind_param("i", $id_pregunta);
            $stmt->execute();
            $stmt->bind_result($tipoD);
            $stmt->fetch();
            $stmt->close();

            if ($tipoD !== "imagen" && $tipoD !== "dibujo") {
                echo json_encode(["success" => false, "error" => "Tipo de pregunta no admite dibujo"]);
                exit;
            }

            if (strpos($base64, "data:image/") !== 0) {
                echo json_encode(["success" => false, "error" => "Formato de imagen inválido"]);
                exit;
            }

            $this->guardarDibujo($id_pregunta, $base64, $id_encuesta);
        }

        return [ "success" => true ];
    }

    private function guardarTexto(int $id_pregunta, string $valor, int $id_encuesta): void
    {
        $sql = "INSERT INTO respuestas_usuario 
            (id_encuesta, id_pregunta, respuesta_texto)
            VALUES (?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iis", $id_encuesta, $id_pregunta, $valor);
        $stmt->execute();
        $stmt->close();
    }

   private function guardarArrayRespuesta(int $id_pregunta, array $arr): void
    {
        foreach ($arr as $item) {
            $id_op = (int)$item['id_opcion'];
            $pos   = (int)$item['posicion'];

            $sql = "INSERT INTO respuestas_ranking
                (id_usuario, id_pregunta, id_opcion, posicion)
                VALUES (0, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("iii", $id_pregunta, $id_op, $pos);
            $stmt->execute();
            $stmt->close();
        }
    }

    private function guardarDibujo(int $id_pregunta, string $base64, int $id_encuesta): void
    {
        $sql = "INSERT INTO respuestas_usuario
            (id_encuesta, id_pregunta, respuesta_texto)
            VALUES (?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iis", $id_encuesta, $id_pregunta, $base64);
        $stmt->execute();
        $stmt->close();
    }
}
