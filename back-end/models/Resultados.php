<?php
declare(strict_types=1);

class Resultados
{
    /**
     * Mapea nombre de nivel → id_nivel
     */
    public static function obtenerIdNivel(array $map, string $nivelNombre): ?int
    {
        $nivelNombre = strtolower(trim($nivelNombre));
        return $map[$nivelNombre] ?? null;
    }

    /**
     * Obtiene el id_encuesta asociado a un nivel (id_nivel)
     */
    public static function obtenerEncuestaId(mysqli $db, int $nivelId): ?int
    {
        $sql = "SELECT id_encuesta
                FROM encuestas
                WHERE id_nivel = ?
                ORDER BY id_encuesta
                LIMIT 1";

        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar consulta de encuesta: " . $db->error);
        }

        $stmt->bind_param("i", $nivelId);
        $stmt->execute();
        $stmt->bind_result($id);
        $stmt->fetch();
        $stmt->close();

        return $id ? (int)$id : null;
    }

    /**
     * Obtiene las escuelas de un nivel
     */
    public static function obtenerEscuelasPorNivel(mysqli $db, int $nivelId): array
    {
        $sql = "SELECT id_escuela, nombre_escuela
                FROM escuelas
                WHERE id_nivel = ?
                ORDER BY nombre_escuela";

        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar consulta de escuelas: " . $db->error);
        }

        $stmt->bind_param("i", $nivelId);
        $stmt->execute();

        $res = $stmt->get_result();
        $out = [];

        while ($row = $res->fetch_assoc()) {
            $out[] = [
                'id'     => (int)$row['id_escuela'],
                'nombre' => $row['nombre_escuela']
            ];
        }

        $stmt->close();
        return $out;
    }

    /**
     * Preguntas de una encuesta
     */
    public static function obtenerPreguntas(mysqli $db, int $encuestaId): array
    {
        $sql = "SELECT 
                    id_pregunta,
                    id_encuesta,
                    texto_pregunta,
                    COALESCE(tipo_pregunta, 'opcion') AS tipo_pregunta,
                    COALESCE(orden, id_pregunta)      AS orden
                FROM preguntas
                WHERE id_encuesta = ?
                ORDER BY orden ASC";

        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar consulta de preguntas: " . $db->error);
        }

        $stmt->bind_param("i", $encuestaId);
        $stmt->execute();

        $result    = $stmt->get_result();
        $preguntas = [];

        while ($p = $result->fetch_assoc()) {
            $p['id_pregunta']   = (int)$p['id_pregunta'];
            $p['id_encuesta']   = (int)$p['id_encuesta'];
            $p['tipo_pregunta'] = strtolower(trim((string)$p['tipo_pregunta']));
            $p['orden']         = (int)$p['orden'];
            $preguntas[]        = $p;
        }

        $stmt->close();
        return $preguntas;
    }

    /**
     * Calcula estadísticas combinadas:
     *  - opcion / multiple → desde respuestas_usuario
     *  - ranking          → desde respuestas_ranking
     *
     * Devuelve:
     *  [ id_pregunta => [ id_opcion => total_respuestas, ... ], ... ]
     *
     * $escuelaFiltro: id_escuela (0 = todas)
     */
    public static function obtenerEstadisticasMixta(
        mysqli $db,
        array $preguntas,
        int $escuelaFiltro = 0
    ): array {
        if (empty($preguntas)) {
            return [];
        }

        $idsOpciones = []; // opcion, multiple
        $idsRanking  = []; // ranking

        foreach ($preguntas as $p) {
            $tipo = strtolower(trim($p['tipo_pregunta'] ?? ''));
            $pid  = (int)$p['id_pregunta'];

            if (in_array($tipo, ['opcion', 'multiple'], true)) {
                $idsOpciones[] = $pid;
            } elseif ($tipo === 'ranking') {
                $idsRanking[] = $pid;
            }
        }

        $estadisticas = [];

        // 1) Estadísticas de opcion / multiple (respuestas_usuario)
        if (!empty($idsOpciones)) {
            $statsOpc = self::obtenerEstadisticasOpciones($db, $idsOpciones, $escuelaFiltro);
            $estadisticas = $statsOpc; // se puede mezclar porque los ids no se repiten con ranking
        }

        // 2) Estadísticas de ranking (respuestas_ranking)
        if (!empty($idsRanking)) {
            $statsRank = self::obtenerEstadisticasRanking($db, $idsRanking);

            foreach ($statsRank as $pid => $porOpcion) {
                if (!isset($estadisticas[$pid])) {
                    $estadisticas[$pid] = [];
                }
                foreach ($porOpcion as $oid => $total) {
                    // Si existiera, se sumaría (pero no debería solaparse)
                    $estadisticas[$pid][$oid] = ($estadisticas[$pid][$oid] ?? 0) + $total;
                }
            }
        }

        return $estadisticas;
    }

    /**
     * Estadísticas para preguntas tipo opcion / multiple
     * Cuenta cuántas veces se selecciona cada id_opcion
     * usando respuestas_usuario.
     *
     * Formato:
     *  [ id_pregunta => [ id_opcion => total_respuestas, ... ], ... ]
     */
    public static function obtenerEstadisticasOpciones(
        mysqli $db,
        array $idsPreguntas,
        int $escuelaFiltro = 0
    ): array {
        if (empty($idsPreguntas)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($idsPreguntas), '?'));
        $types        = str_repeat('i', count($idsPreguntas));
        $params       = $idsPreguntas;

        $sql = "SELECT 
                    r.id_pregunta,
                    r.id_opcion,
                    COUNT(*) AS total_respuestas
                FROM respuestas_usuario r
                WHERE r.id_pregunta IN ($placeholders)
                  AND r.id_opcion IS NOT NULL";

        if ($escuelaFiltro > 0) {
            $sql     .= " AND r.id_escuela = ?";
            $types   .= 'i';
            $params[] = $escuelaFiltro;
        }

        $sql .= " GROUP BY r.id_pregunta, r.id_opcion";

        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar consulta de estadísticas (opciones): " . $db->error);
        }

        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        $res = $stmt->get_result();
        $out = [];

        while ($row = $res->fetch_assoc()) {
            $pid   = (int)$row['id_pregunta'];
            $oid   = (int)$row['id_opcion'];
            $total = (int)$row['total_respuestas'];

            if (!isset($out[$pid])) {
                $out[$pid] = [];
            }
            $out[$pid][$oid] = $total;
        }

        $stmt->close();
        return $out;
    }

    /**
     * Estadísticas para preguntas tipo ranking
     * Cuenta cuántas veces aparece cada id_opcion en respuestas_ranking
     * (no se filtra por escuela porque la tabla no guarda id_escuela).
     *
     * Formato:
     *  [ id_pregunta => [ id_opcion => total_respuestas, ... ], ... ]
     */
    public static function obtenerEstadisticasRanking(
        mysqli $db,
        array $idsPreguntas
    ): array {
        if (empty($idsPreguntas)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($idsPreguntas), '?'));
        $types        = str_repeat('i', count($idsPreguntas));

        $sql = "SELECT 
                    id_pregunta,
                    id_opcion,
                    COUNT(*) AS total_respuestas
                FROM respuestas_ranking
                WHERE id_pregunta IN ($placeholders)
                GROUP BY id_pregunta, id_opcion";

        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar consulta de estadísticas (ranking): " . $db->error);
        }

        $stmt->bind_param($types, ...$idsPreguntas);
        $stmt->execute();

        $res = $stmt->get_result();
        $out = [];

        while ($row = $res->fetch_assoc()) {
            $pid   = (int)$row['id_pregunta'];
            $oid   = (int)$row['id_opcion'];
            $total = (int)$row['total_respuestas'];

            if (!isset($out[$pid])) {
                $out[$pid] = [];
            }
            $out[$pid][$oid] = $total;
        }

        $stmt->close();
        return $out;
    }

    /**
     * Obtiene las opciones de cada pregunta + asigna el total de respuestas
     *
     * $estadisticas:
     *  [ id_pregunta => [ id_opcion => total, ... ], ... ]
     *
     * Devuelve:
     *  [ id_pregunta => [
     *      [
     *        'id_opcion' => int,
     *        'texto'     => string,
     *        'icono'     => ?string,
     *        'valor'     => ?int,
     *        'total'     => int
     *      ], ...
     *    ], ...
     *  ]
     */
    public static function obtenerOpciones(
        mysqli $db,
        array $idsPreguntas,
        array $estadisticas
    ): array {
        if (empty($idsPreguntas)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($idsPreguntas), '?'));
        $types        = str_repeat('i', count($idsPreguntas));

        $sql = "SELECT 
                    id_opcion,
                    id_pregunta,
                    texto_opcion,
                    icono,
                    valor
                FROM opciones_respuesta
                WHERE id_pregunta IN ($placeholders)
                ORDER BY id_pregunta, id_opcion";

        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar consulta de opciones: " . $db->error);
        }

        $stmt->bind_param($types, ...$idsPreguntas);
        $stmt->execute();

        $res = $stmt->get_result();
        $out = [];

        while ($row = $res->fetch_assoc()) {
            $pid = (int)$row['id_pregunta'];
            $oid = (int)$row['id_opcion'];

            $total = $estadisticas[$pid][$oid] ?? 0;

            if (!isset($out[$pid])) {
                $out[$pid] = [];
            }

            $out[$pid][] = [
                'id_opcion' => $oid,
                'texto'     => $row['texto_opcion'],
                'icono'     => $row['icono'],
                'valor'     => isset($row['valor']) ? (int)$row['valor'] : null,
                'total'     => $total
            ];
        }

        $stmt->close();
        return $out;
    }
}
