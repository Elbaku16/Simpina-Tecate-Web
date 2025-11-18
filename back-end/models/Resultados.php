<?php

class Resultados
{
    public static function obtenerIdNivel(array $map, string $nivelNombre): ?int
    {
        $nivelNombre = strtolower(trim($nivelNombre));
        return $map[$nivelNombre] ?? null;
    }

    public static function obtenerEncuestaId(mysqli $db, int $nivelId): ?int
    {
        $stmt = $db->prepare("SELECT id_encuesta 
                              FROM encuestas 
                              WHERE id_nivel = ? 
                              ORDER BY id_encuesta LIMIT 1");
        $stmt->bind_param("i", $nivelId);
        $stmt->execute();
        $stmt->bind_result($id);
        $stmt->fetch();
        $stmt->close();
        return $id ?: null;
    }

    public static function obtenerEscuelasPorNivel(mysqli $db, int $nivelId): array
    {
        $out = [];
        $stmt = $db->prepare("SELECT id_escuela, nombre_escuela 
                              FROM escuelas 
                              WHERE id_nivel = ?
                              ORDER BY nombre_escuela");
        $stmt->bind_param("i", $nivelId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $out[] = [
                'id' => (int)$row['id_escuela'],
                'nombre' => $row['nombre_escuela']
            ];
        }
        return $out;
    }

    public static function obtenerPreguntas(mysqli $db, int $encuestaId): array
    {
        $sql = "SELECT id_pregunta, id_encuesta, texto_pregunta,
                       COALESCE(tipo_pregunta,'opcion') AS tipo_pregunta,
                       COALESCE(orden,id_pregunta) AS orden
                FROM preguntas
                WHERE id_encuesta = ?
                ORDER BY orden ASC";

        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $encuestaId);
        $stmt->execute();

        $result = $stmt->get_result();
        $preguntas = [];

        while ($p = $result->fetch_assoc()) {
            $p['id_pregunta']   = (int)$p['id_pregunta'];
            $p['id_encuesta']   = (int)$p['id_encuesta'];
            $p['tipo_pregunta'] = strtolower($p['tipo_pregunta']);
            $preguntas[] = $p;
        }
        return $preguntas;
    }

    public static function obtenerEstadisticas(mysqli $db, array $ids, int $escuelaFiltro = 0): array
    {
        if (empty($ids)) return [];

        $ph = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));
        $params = $ids;

        $sql = "SELECT r.id_pregunta, r.id_opcion, COUNT(*) AS total_respuestas
                FROM respuestas_usuario r
                WHERE r.id_pregunta IN ($ph)";

        if ($escuelaFiltro > 0) {
            $sql .= " AND r.id_escuela = ?";
            $types .= 'i';
            $params[] = $escuelaFiltro;
        }

        $sql .= " GROUP BY r.id_pregunta, r.id_opcion";

        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        $res = $stmt->get_result();
        $out = [];

        while ($row = $res->fetch_assoc()) {
            $pid = (int)$row['id_pregunta'];
            $oid = (int)$row['id_opcion'];
            $total = (int)$row['total_respuestas'];

            if (!isset($out[$pid])) $out[$pid] = [];
            $out[$pid][$oid] = $total;
        }

        return $out;
    }

    public static function obtenerOpciones(mysqli $db, array $ids, array $estadisticas): array
    {
        if (empty($ids)) return [];

        $ph = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));

        $sql = "SELECT id_opcion, id_pregunta, texto_opcion, icono, valor
                FROM opciones_respuesta
                WHERE id_pregunta IN ($ph)
                ORDER BY id_pregunta, id_opcion";

        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();

        $res = $stmt->get_result();
        $out = [];

        while ($row = $res->fetch_assoc()) {
            $pid = (int)$row['id_pregunta'];
            $oid = (int)$row['id_opcion'];

            $total = $estadisticas[$pid][$oid] ?? 0;

            $out[$pid][] = [
                'id_opcion' => $oid,
                'texto'     => $row['texto_opcion'],
                'icono'     => $row['icono'],
                'valor'     => isset($row['valor']) ? (int)$row['valor'] : null,
                'total'     => $total
            ];
        }

        return $out;
    }
}