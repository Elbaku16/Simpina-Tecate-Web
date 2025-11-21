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

    /* ==========================================================
       CREAR REGISTRO DE USUARIO ENCUESTA
    ========================================================== */
    private function crearUsuarioEncuesta(int $idEncuesta, int $idEscuela): int
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

        $stmt->close();
        return array_values($preguntas);
    }

    /* ==========================================================
       GUARDAR RESPUESTAS
    ========================================================== */
    public function enviarRespuestas(array $payload): array
    {
        $idEncuesta = (int)($payload['id_encuesta'] ?? 0);
        if ($idEncuesta === 0) {
            throw new Exception('ID de encuesta inválido');
        }

        $idEscuela = (int)($_SESSION['id_escuela'] ?? 1);

        $idUsuarioEncuesta = $this->crearUsuarioEncuesta($idEncuesta, $idEscuela);

        $respuestas = $payload['respuestas'] ?? [];
        $dibujos    = $payload['dibujos']    ?? [];

        $this->db->begin_transaction();

        try {
            $total = 0;

            /* ---------------- TEXTO ---------------- */
            if (!empty($respuestas['texto'])) {
                foreach ($respuestas['texto'] as $idPregunta => $texto) {
                    $this->guardarTexto(
                        $idUsuarioEncuesta,
                        $idEncuesta,
                        (int)$idPregunta,
                        $idEscuela,
                        trim($texto)
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
                        $data['texto_otro'] ?? null
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
                            null
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

                        $sql = "INSERT INTO respuestas_ranking
                                (id_usuario_encuesta, id_pregunta, id_opcion, posicion)
                                VALUES (?, ?, ?, ?)";

                        $stmt = $this->db->prepare($sql);
                        $stmt->bind_param("iiii",
                            $idUsuarioEncuesta,
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
                    $this->guardarDibujo(
                        $idUsuarioEncuesta,
                        $idEncuesta,
                        (int)$idPregunta,
                        $idEscuela,
                        $base64
                    );
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
            throw $e;
        }
    }

    /* ==========================================================
       MÉTODOS PRIVADOS DE GUARDAR
    ========================================================== */

    private function guardarTexto(
        int $idUsuarioEncuesta,
        int $idEncuesta,
        int $idPregunta,
        int $idEscuela,
        string $texto
    ): void {
        $sql = "INSERT INTO respuestas_usuario
            (id_usuario_encuesta, id_encuesta, id_pregunta, respuesta_texto, id_escuela, fecha_respuesta)
            VALUES (?, ?, ?, ?, ?, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iiisi",
            $idUsuarioEncuesta,
            $idEncuesta,
            $idPregunta,
            $texto,
            $idEscuela
        );
        $stmt->execute();
        $stmt->close();
    }

    private function guardarOpcion(
        int $idUsuarioEncuesta,
        int $idEncuesta,
        int $idPregunta,
        int $idEscuela,
        int $idOpcion,
        ?string $textoOtro = null
    ): void {
        $sql = "INSERT INTO respuestas_usuario
            (id_usuario_encuesta, id_encuesta, id_pregunta, id_opcion, respuesta_texto, id_escuela, fecha_respuesta)
            VALUES (?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iiiisi",
            $idUsuarioEncuesta,
            $idEncuesta,
            $idPregunta,
            $idOpcion,
            $textoOtro,
            $idEscuela
        );
        $stmt->execute();
        $stmt->close();
    }

    private function guardarDibujo(
        int $idUsuarioEncuesta,
        int $idEncuesta,
        int $idPregunta,
        int $idEscuela,
        string $base64
    ): void {
        if (strlen($base64) < 50) return;

        $sql = "INSERT INTO respuestas_usuario
                (id_usuario_encuesta, id_encuesta, id_pregunta, id_escuela, fecha_respuesta)
                VALUES (?, ?, ?, ?, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iiii",
            $idUsuarioEncuesta,
            $idEncuesta,
            $idPregunta,
            $idEscuela
        );
        $stmt->execute();

        $idRespuesta = $this->db->insert_id;
        $stmt->close();

        $ruta = DibujoHelper::guardar($base64, $idRespuesta);

        $sql = "UPDATE respuestas_usuario SET dibujo_ruta=? WHERE id_respuesta_usuario=?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $ruta, $idRespuesta);
        $stmt->execute();
        $stmt->close();
    }
}
