<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Pregunta.php';
require_once __DIR__ . '/../database/Conexion.php';
require_once __DIR__ . '/../helpers/DibujoHelper.php';

class EncuestasController
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

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

    /**
     * ✅ MÉTODO COMPLETAMENTE REESCRITO
     * Ahora procesa el nuevo formato de payload
     */
    public function enviarRespuestas(array $payload): array
    {
        $idEncuesta = (int)($payload['id_encuesta'] ?? 0);
        
        if ($idEncuesta === 0) {
            throw new Exception('ID de encuesta inválido');
        }

        // Obtener ID de escuela desde sesión (ajusta según tu sistema)
        $idEscuela = (int)($_SESSION['id_escuela'] ?? 0);
        
        if ($idEscuela === 0) {
            // Si no hay sesión, usar escuela por defecto o lanzar error
            $idEscuela = 1; // TEMPORAL - ajusta esto según tu lógica
        }

        $respuestas = $payload['respuestas'] ?? [];
        $dibujos = $payload['dibujos'] ?? [];

        $totalGuardadas = 0;

        // ========================================
        // 1. PROCESAR RESPUESTAS DE TEXTO
        // ========================================
        if (isset($respuestas['texto']) && is_array($respuestas['texto'])) {
            foreach ($respuestas['texto'] as $idPregunta => $textoRespuesta) {
                $this->guardarTexto(
                    (int)$idPregunta, 
                    (string)$textoRespuesta, 
                    $idEncuesta, 
                    $idEscuela
                );
                $totalGuardadas++;
            }
        }

        // ========================================
        // 2. PROCESAR RESPUESTAS DE OPCIÓN SIMPLE
        // ========================================
        if (isset($respuestas['opcion']) && is_array($respuestas['opcion'])) {
            foreach ($respuestas['opcion'] as $idPregunta => $datos) {
                $this->guardarOpcion(
                    (int)$idPregunta, 
                    (int)$datos['id_opcion'], 
                    $idEncuesta, 
                    $idEscuela,
                    $datos['texto_otro'] ?? null
                );
                $totalGuardadas++;
            }
        }

        // ========================================
        // 3. PROCESAR RESPUESTAS DE OPCIÓN MÚLTIPLE
        // ========================================
        if (isset($respuestas['multiple']) && is_array($respuestas['multiple'])) {
            foreach ($respuestas['multiple'] as $idPregunta => $opciones) {
                if (is_array($opciones)) {
                    foreach ($opciones as $opcion) {
                        if (isset($opcion['id_opcion'])) {
                            $this->guardarOpcion(
                                (int)$idPregunta, 
                                (int)$opcion['id_opcion'], 
                                $idEncuesta, 
                                $idEscuela
                            );
                            $totalGuardadas++;
                        }
                    }
                }
            }
        }

        // ========================================
        // 4. PROCESAR RESPUESTAS DE RANKING
        // ========================================
        if (isset($respuestas['ranking']) && is_array($respuestas['ranking'])) {
            foreach ($respuestas['ranking'] as $idPregunta => $arr) {
                if (is_array($arr)) {
                    $this->guardarArrayRespuesta((int)$idPregunta, $arr);
                    $totalGuardadas++;
                }
            }
        }

        // ========================================
        // 5. PROCESAR DIBUJOS
        // ========================================
        if (is_array($dibujos)) {
            foreach ($dibujos as $idPregunta => $base64) {
                if (!empty($base64)) {
                    $this->guardarDibujo(
                        (int)$idPregunta, 
                        $base64, 
                        $idEncuesta, 
                        $idEscuela
                    );
                    $totalGuardadas++;
                }
            }
        }

        return [
            'success' => true,
            'message' => "Encuesta guardada exitosamente",
            'total_respuestas' => $totalGuardadas
        ];
    }

    /**
     * Guardar respuesta de texto
     */
    private function guardarTexto(int $idPregunta, string $valor, int $idEncuesta, int $idEscuela): void
    {
        $sql = "INSERT INTO respuestas_usuario 
                (id_encuesta, id_pregunta, id_escuela, respuesta_texto, fecha_respuesta)
                VALUES (?, ?, ?, ?, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iiis", $idEncuesta, $idPregunta, $idEscuela, $valor);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Guardar respuesta de opción (radio o checkbox individual)
     */
    private function guardarOpcion(
        int $idPregunta, 
        int $idOpcion, 
        int $idEncuesta, 
        int $idEscuela, 
        ?string $textoOtro = null
    ): void {
        $sql = "INSERT INTO respuestas_usuario 
                (id_encuesta, id_pregunta, id_opcion, id_escuela, respuesta_texto, fecha_respuesta)
                VALUES (?, ?, ?, ?, ?, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iiiis", $idEncuesta, $idPregunta, $idOpcion, $idEscuela, $textoOtro);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Guardar respuesta de ranking
     */
    private function guardarArrayRespuesta(int $idPregunta, array $arr): void
    {
        foreach ($arr as $item) {
            $idOpcion = (int)$item['id_opcion'];
            $posicion = (int)$item['posicion'];

            $sql = "INSERT INTO respuestas_ranking
                    (id_usuario, id_pregunta, id_opcion, posicion)
                    VALUES (0, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("iii", $idPregunta, $idOpcion, $posicion);
            $stmt->execute();
            $stmt->close();
        }
    }

    /**
     * ✅ ACTUALIZADO: Guardar dibujo como archivo
     */
    private function guardarDibujo(int $idPregunta, string $base64, int $idEncuesta, int $idEscuela): void
    {
        if (empty($base64) || strlen($base64) < 50) {
            throw new Exception("Datos de dibujo vacíos o inválidos");
        }

        try {
            // 1. Insertar registro en DB (sin dibujo todavía)
            $sql = "INSERT INTO respuestas_usuario
                    (id_encuesta, id_pregunta, id_escuela, respuesta_texto, fecha_respuesta)
                    VALUES (?, ?, ?, NULL, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("iii", $idEncuesta, $idPregunta, $idEscuela);
            $stmt->execute();

            $idRespuesta = $this->db->insert_id;
            $stmt->close();

            // 2. Guardar el archivo usando DibujoHelper
            $rutaDibujo = DibujoHelper::guardar($base64, $idRespuesta);

            // 3. Actualizar el registro con la ruta del archivo
            $sql = "UPDATE respuestas_usuario 
                    SET dibujo_ruta = ? 
                    WHERE id_respuesta_usuario = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("si", $rutaDibujo, $idRespuesta);
            $stmt->execute();
            $stmt->close();

        } catch (Exception $e) {
            // Si algo falla, limpiar el archivo si existe
            if (isset($rutaDibujo)) {
                DibujoHelper::eliminar($rutaDibujo);
            }
            throw new Exception("Error al guardar dibujo: " . $e->getMessage());
        }
    }

    /**
     * Eliminar una respuesta con su dibujo
     */
    public function eliminarRespuesta(int $idRespuesta): bool
    {
        // Obtener la ruta del dibujo antes de eliminar
        $stmt = $this->db->prepare("SELECT dibujo_ruta FROM respuestas_usuario WHERE id_respuesta_usuario = ?");
        $stmt->bind_param("i", $idRespuesta);
        $stmt->execute();
        $stmt->bind_result($rutaDibujo);
        $stmt->fetch();
        $stmt->close();

        // Eliminar archivo si existe
        if ($rutaDibujo) {
            DibujoHelper::eliminar($rutaDibujo);
        }

        // Eliminar registro de DB
        $stmt = $this->db->prepare("DELETE FROM respuestas_usuario WHERE id_respuesta_usuario = ? LIMIT 1");
        $stmt->bind_param("i", $idRespuesta);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        return $affected > 0;
    }
}