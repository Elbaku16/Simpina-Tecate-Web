<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/conexion-db.php';
require_once __DIR__ . '/../helpers/DibujoHelper.php';
require_once __DIR__ . '/../../front-end/includes/config.php';

final class ResultadosTextoController
{
    private mysqli $db;

    public function __construct()
    {
        global $conn;
        $this->db = $conn;
    }

    public function obtener(int $idPregunta, int $idEscuela = 0, string $ciclo = '', string $genero = ''): array
    {
        $stmt = $this->db->prepare(
            "SELECT clave_logica,COALESCE(tipo_pregunta,'texto') tipo FROM preguntas WHERE id_pregunta=?"
        );
        $stmt->bind_param('i', $idPregunta);
        $stmt->execute();
        $pregunta = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$pregunta) throw new InvalidArgumentException('La pregunta solicitada no existe.');

        $tipo = in_array(strtolower((string) $pregunta['tipo']), ['dibujo','imagen','canvas'], true)
            ? 'dibujo' : 'texto';
        if (!in_array($genero, ['', 'M', 'F', 'O', 'X'], true)) {
            throw new InvalidArgumentException('Filtro de sexo inválido.');
        }

        $sql = "SELECT ru.id_respuesta_usuario,ru.respuesta_texto,ru.dibujo_ruta,
                       ru.dibujo_storage,ru.dibujo_objeto,ru.dibujo_bytes,
                       ru.fecha_respuesta,e.nombre_escuela
                FROM respuestas_usuario ru
                JOIN preguntas p ON p.id_pregunta=ru.id_pregunta
                LEFT JOIN escuelas e ON e.id_escuela=ru.id_escuela
                WHERE p.clave_logica=?";
        $types = 's';
        $params = [$pregunta['clave_logica']];
        if ($idEscuela > 0) {
            $sql .= ' AND ru.id_escuela=?'; $types .= 'i'; $params[] = $idEscuela;
        } elseif ($idEscuela < 0) {
            $sql .= ' AND ru.id_escuela IS NULL';
        }
        if ($genero !== '') {
            $sql .= ' AND ru.genero=?'; $types .= 's'; $params[] = $genero;
        }
        if ($ciclo !== '') {
            if (!preg_match('/^(\d{4})\s*-\s*(\d{4})$/', $ciclo, $m) || (int) $m[2] !== (int) $m[1] + 1) {
                throw new InvalidArgumentException('Filtro de ciclo escolar inválido.');
            }
            $sql .= ' AND ru.fecha_respuesta BETWEEN ? AND ?';
            $types .= 'ss';
            $params[] = $m[1] . '-08-01 00:00:00';
            $params[] = $m[2] . '-07-31 23:59:59';
        }
        $sql .= $tipo === 'dibujo'
            ? ' AND (ru.dibujo_objeto IS NOT NULL OR ru.dibujo_ruta IS NOT NULL)'
            : " AND ru.respuesta_texto IS NOT NULL AND ru.respuesta_texto<>''";
        $sql .= ' ORDER BY ru.fecha_respuesta DESC,ru.id_respuesta_usuario DESC';

        $stmt = $this->db->prepare($sql);
        $refs = [];
        foreach ($params as $i => $_) $refs[$i] = &$params[$i];
        $stmt->bind_param($types, ...$refs);
        $stmt->execute();
        $result = $stmt->get_result();
        $salida = [];
        while ($row = $result->fetch_assoc()) {
            $id = (int) $row['id_respuesta_usuario'];
            $item = [
                'id' => $id,
                'escuela' => $row['nombre_escuela'] ?? 'Sin escuela',
                'fecha' => $row['fecha_respuesta'],
                'es_dibujo' => $tipo === 'dibujo',
            ];
            if ($tipo === 'dibujo') {
                $item['ruta_dibujo'] = BASE_URL . '/back-end/routes/resultados/dibujo.php?id_respuesta=' . $id;
                $item['existe_archivo'] = !empty($row['dibujo_objeto'])
                    || DibujoHelper::rutaLegacy($row['dibujo_ruta']) !== null;
                $bytes = (int) ($row['dibujo_bytes'] ?? 0);
                $item['tamaño'] = $bytes > 0 ? self::formatear($bytes) : '';
            } else {
                $item['texto'] = $row['respuesta_texto'] ?? '';
            }
            $salida[] = $item;
        }
        $stmt->close();
        return ['success' => true, 'tipo_pregunta' => $tipo, 'respuestas' => $salida];
    }

    public function eliminar(int $idRespuesta): array
    {
        if ($idRespuesta <= 0) return ['success' => false, 'error' => 'Respuesta inválida.'];
        $stmt = $this->db->prepare(
            'SELECT dibujo_ruta,dibujo_storage,dibujo_objeto FROM respuestas_usuario WHERE id_respuesta_usuario=?'
        );
        $stmt->bind_param('i', $idRespuesta);
        $stmt->execute();
        $archivo = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$archivo) return ['success' => false, 'error' => 'La respuesta no existe.'];

        $stmt = $this->db->prepare('DELETE FROM respuestas_usuario WHERE id_respuesta_usuario=? LIMIT 1');
        $stmt->bind_param('i', $idRespuesta);
        $stmt->execute();
        $ok = $stmt->affected_rows === 1;
        $stmt->close();
        if (!$ok) return ['success' => false, 'error' => 'No se pudo eliminar la respuesta.'];

        try {
            if ($archivo['dibujo_storage'] && $archivo['dibujo_objeto']) {
                DibujoHelper::eliminar($archivo['dibujo_storage'], $archivo['dibujo_objeto']);
            } elseif ($legacy = DibujoHelper::rutaLegacy($archivo['dibujo_ruta'])) {
                @unlink($legacy);
            }
        } catch (Throwable $e) {
            $driver = (string) $archivo['dibujo_storage'];
            $key = (string) $archivo['dibujo_objeto'];
            $error = substr($e->getMessage(), 0, 500);
            $stmt = $this->db->prepare(
                'INSERT INTO storage_delete_queue (driver,object_key,ultimo_error) VALUES (?,?,?)'
            );
            $stmt->bind_param('sss', $driver, $key, $error);
            $stmt->execute();
            $stmt->close();
            return ['success' => true, 'warning' => 'La limpieza del archivo quedó programada.'];
        }
        return ['success' => true];
    }

    private static function formatear(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1024 * 1024) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / (1024 * 1024), 1) . ' MB';
    }
}
