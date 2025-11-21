<?php
declare(strict_types=1);

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
     * Obtener respuestas de una pregunta (texto o dibujo)
     */
    public function obtener(int $idPregunta, int $idEscuela = 0, string $cicloEscolar = ''): array
    {
        if ($idPregunta <= 0) {
            return [
                'success' => false,
                'error'   => 'id_pregunta inválido'
            ];
        }

        // 1) Averiguar tipo de pregunta
        $tipo = $this->obtenerTipoPregunta($idPregunta);
        $tipoNorm = $this->normalizarTipo($tipo);
        $esDibujo = in_array($tipoNorm, ['dibujo'], true);

        // 2) Construir consulta base
        $sql = "
            SELECT 
                r.id_respuesta_usuario,
                r.respuesta_texto,
                r.dibujo_ruta,
                r.fecha_respuesta,
                e.nombre_escuela
            FROM respuestas_usuario r
            LEFT JOIN escuelas e ON e.id_escuela = r.id_escuela
            WHERE r.id_pregunta = ?
        ";

        $types  = "i";
        $params = [$idPregunta];

        if ($idEscuela > 0) {
            $sql    .= " AND r.id_escuela = ?";
            $types  .= "i";
            $params[] = $idEscuela;
        }

        // 3) Filtrar según si es texto o dibujo
        if ($esDibujo) {
            $sql .= " AND r.dibujo_ruta IS NOT NULL";
        } else {
            $sql .= " AND r.respuesta_texto IS NOT NULL AND r.respuesta_texto <> ''";
        }

        $sql .= " ORDER BY r.fecha_respuesta DESC, r.id_respuesta_usuario DESC";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [
                'success' => false,
                'error'   => 'Error al preparar consulta'
            ];
        }

        // bind dinámico
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();

        $salida = [];
        while ($row = $res->fetch_assoc()) {
            $idRespuesta = (int)$row['id_respuesta_usuario'];
            $rutaDibujo  = $row['dibujo_ruta'] ?? null;

            $item = [
                'id'        => $idRespuesta,
                'escuela'   => $row['nombre_escuela'] ?? 'Sin escuela',
                'fecha'     => $row['fecha_respuesta'],
                'es_dibujo' => $esDibujo,
            ];

            if ($esDibujo) {
                $item['ruta_dibujo']    = $this->resolverRutaPublica($rutaDibujo);
                $item['existe_archivo'] = $this->existeArchivo($rutaDibujo);
                $item['tamaño']         = $this->tamañoArchivoLegible($rutaDibujo);
            } else {
                $item['texto'] = $row['respuesta_texto'] ?? '';
            }

            $salida[] = $item;
        }

        $stmt->close();

        return [
            'success'       => true,
            'tipo_pregunta' => $tipoNorm, // "texto" o "dibujo"
            'respuestas'    => $salida
        ];
    }

    /**
     * Eliminar respuesta (y borrar dibujo si aplica)
     */
    public function eliminar(int $idRespuesta): array
    {
        if ($idRespuesta <= 0) {
            return [
                'success' => false,
                'error'   => 'id_respuesta inválido'
            ];
        }

        // 1) Obtener ruta de dibujo si existe
        $sql = "SELECT dibujo_ruta FROM respuestas_usuario WHERE id_respuesta_usuario = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $idRespuesta);
        $stmt->execute();
        $stmt->bind_result($rutaDibujo);
        $stmt->fetch();
        $stmt->close();

        // 2) Borrar registro
        $stmt = $this->db->prepare("DELETE FROM respuestas_usuario WHERE id_respuesta_usuario = ? LIMIT 1");
        $stmt->bind_param("i", $idRespuesta);
        $stmt->execute();
        $afectadas = $stmt->affected_rows;
        $stmt->close();

        if ($afectadas <= 0) {
            return [
                'success' => false,
                'error'   => 'No se encontró la respuesta o no se pudo eliminar'
            ];
        }

        // 3) Borrar dibujo del disco si existía
        if (!empty($rutaDibujo)) {
            try {
                DibujoHelper::eliminar($rutaDibujo);

                } catch (Throwable $e) {
                // No romper si falla, solo avisar
                return [
                    'success' => true,
                    'warning' => 'Respuesta borrada, pero hubo un problema al eliminar el archivo de dibujo.'
                ];
            }
        }

        return ['success' => true];
    }

    /* ===========================================================
       Helpers privados
       =========================================================== */

    private function obtenerTipoPregunta(int $idPregunta): string
    {
        $stmt = $this->db->prepare("SELECT COALESCE(tipo_pregunta,'texto') FROM preguntas WHERE id_pregunta = ?");
        $stmt->bind_param("i", $idPregunta);
        $stmt->execute();
        $stmt->bind_result($tipo);
        $stmt->fetch();
        $stmt->close();

        return $tipo ?? 'texto';
    }

    private function normalizarTipo(string $tipo): string
    {
        $t = strtolower(trim($tipo));

        if (in_array($t, ['dibujo', 'imagen', 'canvas'], true)) {
            return 'dibujo';
        }

        return 'texto';
    }

    private function resolverRutaPublica(?string $ruta): ?string
    {
        if (!$ruta) return null;

        // Si ya parece URL absoluta o empieza con "/", regresarla tal cual
        if (str_starts_with($ruta, 'http://') || str_starts_with($ruta, 'https://') || str_starts_with($ruta, '/')) {
            return $ruta;
        }

        // Si se guardó relativa al proyecto
        return '/' . ltrim($ruta, '/');
    }

    private function existeArchivo(?string $ruta): bool
    {
        if (!$ruta) return false;

        // Asumimos que se guarda como ruta relativa a la raíz del proyecto
        $abs = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($ruta, '/');
        return is_file($abs);
    }

    private function tamañoArchivoLegible(?string $ruta): ?string
    {
        if (!$ruta) return null;

        $abs = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($ruta, '/');
        if (!is_file($abs)) {
            return null;
        }

        $bytes = filesize($abs);
        if ($bytes === false) return null;

        $kb = $bytes / 1024;
        if ($kb < 1024) {
            return round($kb, 1) . ' KB';
        }

        $mb = $kb / 1024;
        return round($mb, 2) . ' MB';
    }
}
