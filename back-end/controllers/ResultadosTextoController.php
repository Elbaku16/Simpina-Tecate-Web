<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/conexion-db.php';
require_once __DIR__ . '/../helpers/DibujoHelper.php';

class ResultadosTextoController
{
    private mysqli $db;

    public function __construct()
    {
        // Usar conexión global del archivo conexion-db.php
        global $conn;
        $this->db = $conn;
    }

    /**
     * Obtener respuestas de una pregunta (texto o dibujo)
     */
    public function obtener(
        int $idPregunta, 
        int $idEscuela = 0, 
        string $cicloEscolar = '', 
        string $generoFiltro = '' // <--- NUEVO FILTRO DE GÉNERO
    ): array
    {
        if ($idPregunta <= 0) {
            return [
                'success' => false,
                'error'   => 'id_pregunta inválido'
            ];
        }

        // 1) Tipo de pregunta
        $tipo = $this->obtenerTipoPregunta($idPregunta);
        $tipoNorm = $this->normalizarTipo($tipo);
        $esDibujo = ($tipoNorm === 'dibujo');

        // 2) Consulta base
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

        // Filtro por escuela
        if ($idEscuela > 0) {
            $sql    .= " AND r.id_escuela = ?";
            $types  .= "i";
            $params[] = $idEscuela;
        }

        // Filtro por género <--- INICIO CAMBIO
        if (!empty($generoFiltro)) {
            $sql    .= " AND r.genero = ?";
            $types  .= "s";
            $params[] = $generoFiltro;
        }
        // Filtro por ciclo escolar
        if (!empty($cicloEscolar) && strpos($cicloEscolar, '-') !== false) {
            list($inicio, $fin) = explode('-', $cicloEscolar);
            $sql     .= " AND YEAR(r.fecha_respuesta) >= ? AND YEAR(r.fecha_respuesta) < ?";
            $types   .= 'ii';
            $params[] = (int)$inicio; 
            $params[] = (int)$fin;
        }
        // <--- FIN CAMBIO

        // Filtro según tipo (texto o dibujo)
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
// --- INICIO CORRECCIÓN ---
        // bind_param requiere que los argumentos se pasen por referencia.
        // Creamos un array nuevo donde cada elemento es una referencia a los valores de $params.
        $paramsReferencias = [];
        foreach ($params as $key => $value) {
            $paramsReferencias[$key] = &$params[$key];
        }

        // Unimos los tipos al principio
        $args = array_merge([$types], $paramsReferencias);

        // Llamamos a bind_param usando call_user_func_array (Funciona en todas las versiones de PHP)
        call_user_func_array([$stmt, 'bind_param'], $args);
        // --- FIN CORRECCIÓN ---

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
            'tipo_pregunta' => $tipoNorm,
            'respuestas'    => $salida
        ];
    }

    /**
     * Eliminar respuesta (y el dibujo si aplica)
     */
    public function eliminar(int $idRespuesta): array
    {
        if ($idRespuesta <= 0) {
            return [
                'success' => false,
                'error'   => 'id_respuesta inválido'
            ];
        }

        // 1) Obtener ruta del dibujo
        $sql = "SELECT dibujo_ruta FROM respuestas_usuario WHERE id_respuesta_usuario = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $idRespuesta);
        $stmt->execute();
        $stmt->bind_result($rutaDibujo);
        $stmt->fetch();
        $stmt->close();

        // 2) Borrar registro
        $stmt = $this->db->prepare(
            "DELETE FROM respuestas_usuario WHERE id_respuesta_usuario = ? LIMIT 1"
        );
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

        // 3) Borrar archivo del servidor
        if (!empty($rutaDibujo)) {
            try {
                DibujoHelper::eliminar($rutaDibujo);
            } catch (Throwable $e) {
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
        $stmt = $this->db->prepare(
            "SELECT COALESCE(tipo_pregunta,'texto') FROM preguntas WHERE id_pregunta = ?"
        );

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

        if (str_starts_with($ruta, 'http://') ||
            str_starts_with($ruta, 'https://') ||
            str_starts_with($ruta, '/')) {
            return $ruta;
        }

        return '/' . ltrim($ruta, '/');
    }

    private function existeArchivo(?string $ruta): bool
    {
        if (!$ruta) return false;

        $abs = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($ruta, '/');
        return is_file($abs);
    }

    private function tamañoArchivoLegible(?string $ruta): ?string
    {
        if (!$ruta) return null;

        $abs = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($ruta, '/');
        if (!is_file($abs)) return null;

        $bytes = filesize($abs);
        if ($bytes === false) return null;

        $kb = $bytes / 1024;
        if ($kb < 1024) return round($kb, 1) . ' KB';

        $mb = $kb / 1024;
        return round($mb, 2) . ' MB';
    }
}