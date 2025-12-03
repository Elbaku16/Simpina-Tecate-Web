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
     *
     * @param int $escuelaFiltro id_escuela (0 = todas)
     * @param string $generoFiltro 'M', 'F', 'O', 'X', o '' (todos)
     * @param ?array $cicloRango [inicio_anio, fin_anio] o null (todos)
     */
    public static function obtenerEstadisticasMixta(
        mysqli $db,
        array $preguntas,
        int $escuelaFiltro = 0,
        string $generoFiltro = '', 
        ?array $cicloRango = null
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
            $statsOpc = self::obtenerEstadisticasOpciones($db, $idsOpciones, $escuelaFiltro, $generoFiltro, $cicloRango);
            $estadisticas = $statsOpc;
        }

        // 2) Estadísticas de ranking (respuestas_ranking)
        if (!empty($idsRanking)) {
            // El ranking ahora devuelve promedio y total
            $statsRank = self::obtenerEstadisticasRanking($db, $idsRanking, $escuelaFiltro, $generoFiltro, $cicloRango);

            foreach ($statsRank as $pid => $porOpcion) {
                if (!isset($estadisticas[$pid])) {
                    $estadisticas[$pid] = [];
                }
                foreach ($porOpcion as $oid => $data) {
                    // Almacena el total de votos (para pie chart) y el promedio (para barra)
                    $estadisticas[$pid][$oid] = [
                        'total'    => $data['total_respuestas'],
                        'promedio' => $data['promedio_posicion'] 
                    ];
                }
            }
        }

        return $estadisticas;
    }

    /**
     * Estadísticas para preguntas tipo opcion / multiple
     * Usa campos de filtro nuevos (escuela, genero, ciclo)
     */
    public static function obtenerEstadisticasOpciones(
        mysqli $db,
        array $idsPreguntas,
        int $escuelaFiltro = 0,
        string $generoFiltro = '', 
        ?array $cicloRango = null
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

        // Aplica filtro de escuela
        if ($escuelaFiltro > 0) {
            $sql     .= " AND r.id_escuela = ?";
            $types   .= 'i';
            $params[] = $escuelaFiltro;
        }

        // Aplica filtro de género
        if (!empty($generoFiltro)) {
            $sql     .= " AND r.genero = ?";
            $types   .= 's';
            $params[] = $generoFiltro;
        }
        
        // Aplica filtro de ciclo (usando fecha_respuesta)
        // Aplica filtro de ciclo (CORREGIDO: Rango de fechas exacto)
        if ($cicloRango !== null && count($cicloRango) === 2) {
            $inicio = $cicloRango[0]; // Ej: 2023
            $fin    = $cicloRango[1]; // Ej: 2024

            // Definimos el ciclo: 1 Ago del año inicio -> 31 Jul del año fin
            $fechaInicio = "$inicio-08-01 00:00:00";
            $fechaFin    = "$fin-07-31 23:59:59";

            $sql      .= " AND r.fecha_respuesta BETWEEN ? AND ?";
            $types    .= 'ss'; // 'ss' porque ahora enviamos strings de fecha
            $params[]  = $fechaInicio; 
            $params[]  = $fechaFin;
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
     * Obtiene las opciones de cada pregunta + asigna el total de respuestas y promedio
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
                    o.id_opcion,
                    o.id_pregunta,
                    o.texto_opcion,
                    o.icono,
                    o.valor,
                    p.tipo_pregunta
                FROM opciones_respuesta o
                INNER JOIN preguntas p ON o.id_pregunta = p.id_pregunta
                WHERE o.id_pregunta IN ($placeholders)
                ORDER BY o.id_pregunta, o.id_opcion";

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
            $tipo = strtolower(trim($row['tipo_pregunta']));

            $total = 0;
            $promedio = null;

            if (isset($estadisticas[$pid][$oid])) {
                $data = $estadisticas[$pid][$oid];
                if ($tipo === 'ranking') {
                    // Ranking: la estadística es un array ['total' => int, 'promedio' => float]
                    $total = $data['total'] ?? 0;
                    $promedio = $data['promedio'] ?? null;
                } else {
                    // Opción/Múltiple: la estadística es solo el total (int)
                    $total = $data;
                }
            }

            if (!isset($out[$pid])) {
                $out[$pid] = [];
            }

            $out[$pid][] = [
                'id_opcion' => $oid,
                'texto'     => $row['texto_opcion'],
                'icono'     => $row['icono'],
                'valor'     => isset($row['valor']) ? (int)$row['valor'] : null,
                'total'     => $total,
                'promedio'  => $promedio // Nuevo campo para Ranking
            ];
        }

        $stmt->close();
        return $out;
    }
    public static function obtenerEstadisticasRanking(
        mysqli $db,
        array $idsPreguntas,
        int $escuelaFiltro = 0,
        string $generoFiltro = '', 
        ?array $cicloRango = null
    ): array {
        if (empty($idsPreguntas)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($idsPreguntas), '?'));
        $types        = str_repeat('i', count($idsPreguntas));
        $params       = $idsPreguntas;

        // Utilizamos COALESCE para que AVG devuelva NULL en lugar de 0 si no hay resultados, 
        // y solo contamos r.id_opcion.
        $sql = "SELECT 
                    r.id_pregunta,
                    r.id_opcion,
                    COUNT(r.id_opcion) AS total_respuestas,
                    COALESCE(AVG(r.posicion), 0) AS promedio_posicion
                FROM respuestas_ranking r
                
                /*
                 * Hacemos LEFT JOIN con respuestas_usuario (ru) para obtener el género,
                 * que es el filtro principal que podría causar fallos si la unión fuera INNER
                 * y no existieran respuestas RU para ese EU.
                 */
                LEFT JOIN respuestas_usuario ru ON r.id_usuario_encuesta = ru.id_usuario_encuesta
                
                WHERE r.id_pregunta IN ($placeholders)";

        // Aplica filtro de escuela (usando respuestas_usuario si posible, o eu si fuera necesario)
        if ($escuelaFiltro > 0) {
            $sql     .= " AND ru.id_escuela = ?"; // Usamos ru.id_escuela si el id está en esa tabla
            $types   .= 'i';
            $params[] = $escuelaFiltro;
        }

        // Aplica filtro de género (usando respuestas_usuario)
        if (!empty($generoFiltro)) {
            $sql     .= " AND ru.genero = ?"; 
            $types   .= 's';
            $params[] = $generoFiltro;
        }

        // Aplica filtro de ciclo (usando fecha_respuesta en respuestas_usuario)
        // Aplica filtro de ciclo (CORREGIDO: Rango de fechas exacto)
        if ($cicloRango !== null && count($cicloRango) === 2) {
            $inicio = $cicloRango[0];
            $fin    = $cicloRango[1];

            $fechaInicio = "$inicio-08-01 00:00:00";
            $fechaFin    = "$fin-07-31 23:59:59";

            $sql      .= " AND ru.fecha_respuesta BETWEEN ? AND ?";
            $types    .= 'ss';
            $params[]  = $fechaInicio; 
            $params[]  = $fechaFin;
        }


        $sql .= " GROUP BY r.id_pregunta, r.id_opcion";

// ... (Resto de la función sin cambios)
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar consulta de estadísticas (ranking): " . $db->error);
        }

        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        $res = $stmt->get_result();
        $out = [];

        while ($row = $res->fetch_assoc()) {
            $pid   = (int)$row['id_pregunta'];
            $oid   = (int)$row['id_opcion'];

            if (!isset($out[$pid])) {
                $out[$pid] = [];
            }
            // Almacenamos un array con el total y el promedio
            $out[$pid][$oid] = [
                'total'    => (int)$row['total_respuestas'],
                // El COALESCE en SQL asegura que sea 0.0 si no hay votos, pero la BD lo devuelve como string/float.
                'promedio_posicion'   => (float)$row['promedio_posicion'] 
            ];
        }

        $stmt->close();
        return $out;
    }

    /**
     * Obtiene solo el texto de las opciones por pregunta
     * Retorna: [id_pregunta => [id_opcion => texto_opcion, ...], ...]
     */
    public static function obtenerOpcionesPreguntas(mysqli $db, array $idsPreguntas): array
    {
        if (empty($idsPreguntas)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($idsPreguntas), '?'));
        $types = str_repeat('i', count($idsPreguntas));

        $sql = "SELECT id_opcion, id_pregunta, texto_opcion
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
            
            if (!isset($out[$pid])) {
                $out[$pid] = [];
            }
            $out[$pid][$oid] = $row['texto_opcion'];
        }

        $stmt->close();
        return $out;
    }
}