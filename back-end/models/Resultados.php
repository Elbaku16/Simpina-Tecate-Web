<?php
declare(strict_types=1);

final class Resultados
{
    public static function obtenerIdNivel(array $map, string $nivelNombre): ?int
    {
        return $map[strtolower(trim($nivelNombre))] ?? null;
    }

    public static function obtenerEncuestaId(mysqli $db, int $nivelId): ?int
    {
        $stmt = $db->prepare(
            "SELECT id_encuesta FROM encuestas
             WHERE id_nivel=? AND estado='activa'
             ORDER BY version DESC,id_encuesta DESC LIMIT 1"
        );
        $stmt->bind_param('i', $nivelId);
        $stmt->execute();
        $id = $stmt->get_result()->fetch_column();
        $stmt->close();
        return $id ? (int) $id : null;
    }

    public static function obtenerEscuelasPorNivel(mysqli $db, int $nivelId): array
    {
        $stmt = $db->prepare(
            'SELECT id_escuela,nombre_escuela FROM escuelas WHERE id_nivel=? ORDER BY nombre_escuela'
        );
        $stmt->bind_param('i', $nivelId);
        $stmt->execute();
        $result = $stmt->get_result();
        $salida = [];
        while ($row = $result->fetch_assoc()) {
            $salida[] = ['id' => (int) $row['id_escuela'], 'nombre' => $row['nombre_escuela']];
        }
        $stmt->close();
        return $salida;
    }

    /** Devuelve la definición más reciente y conserva preguntas retiradas con historia. */
    public static function obtenerPreguntasAcumuladas(mysqli $db, int $nivelId): array
    {
        $sql = "SELECT p.id_pregunta,p.id_encuesta,p.clave_logica,p.texto_pregunta,
                       COALESCE(p.tipo_pregunta,'opcion') AS tipo_pregunta,
                       COALESCE(p.orden,p.id_pregunta) AS orden,
                       EXISTS(
                         SELECT 1 FROM preguntas pa JOIN encuestas ea ON ea.id_encuesta=pa.id_encuesta
                         WHERE pa.clave_logica=p.clave_logica AND ea.estado='activa'
                       ) AS vigente
                FROM preguntas p
                JOIN encuestas e ON e.id_encuesta=p.id_encuesta
                WHERE e.id_nivel=?
                  AND NOT EXISTS (
                    SELECT 1 FROM preguntas pn JOIN encuestas en ON en.id_encuesta=pn.id_encuesta
                    WHERE pn.clave_logica=p.clave_logica AND en.id_nivel=e.id_nivel
                      AND (en.version>e.version OR (en.version=e.version AND pn.id_pregunta>p.id_pregunta))
                  )
                ORDER BY vigente DESC,orden,id_pregunta";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $nivelId);
        $stmt->execute();
        $result = $stmt->get_result();
        $salida = [];
        while ($row = $result->fetch_assoc()) {
            $row['id_pregunta'] = (int) $row['id_pregunta'];
            $row['id_encuesta'] = (int) $row['id_encuesta'];
            $row['orden'] = (int) $row['orden'];
            $row['tipo_pregunta'] = strtolower(trim((string) $row['tipo_pregunta']));
            $row['vigente'] = (bool) $row['vigente'];
            $salida[] = $row;
        }
        $stmt->close();
        return $salida;
    }

    /** @return array{opciones:array<int,array<int,array{texto:string,total:int,promedio:float}>>,total:int} */
    public static function obtenerOpcionesAcumuladas(
        mysqli $db,
        int $nivelId,
        array $preguntas,
        int $escuelaFiltro,
        string $generoFiltro,
        ?array $cicloRango
    ): array {
        $preguntaPorClave = [];
        foreach ($preguntas as $pregunta) {
            $preguntaPorClave[$pregunta['clave_logica']] = (int) $pregunta['id_pregunta'];
        }

        $sqlOpciones = "SELECT o.id_opcion,o.clave_logica,o.texto_opcion,p.clave_logica AS clave_pregunta
                        FROM opciones_respuesta o
                        JOIN preguntas p ON p.id_pregunta=o.id_pregunta
                        JOIN encuestas e ON e.id_encuesta=p.id_encuesta
                        WHERE e.id_nivel=?
                          AND NOT EXISTS (
                            SELECT 1 FROM opciones_respuesta onueva
                            JOIN preguntas pn ON pn.id_pregunta=onueva.id_pregunta
                            JOIN encuestas en ON en.id_encuesta=pn.id_encuesta
                            WHERE onueva.clave_logica=o.clave_logica AND en.id_nivel=e.id_nivel
                              AND (en.version>e.version OR (en.version=e.version AND onueva.id_opcion>o.id_opcion))
                          )
                        ORDER BY p.orden,o.valor,o.id_opcion";
        $stmt = $db->prepare($sqlOpciones);
        $stmt->bind_param('i', $nivelId);
        $stmt->execute();
        $result = $stmt->get_result();
        $opcionPorClave = [];
        $salida = [];
        while ($row = $result->fetch_assoc()) {
            $clavePregunta = $row['clave_pregunta'];
            if (!isset($preguntaPorClave[$clavePregunta])) continue;
            $pid = $preguntaPorClave[$clavePregunta];
            $oid = (int) $row['id_opcion'];
            $opcionPorClave[$row['clave_logica']] = [$pid, $oid];
            $salida[$pid][$oid] = [
                'texto' => $row['texto_opcion'],
                'total' => 0,
                'promedio' => 0.0,
            ];
        }
        $stmt->close();

        [$whereRu, $typesRu, $paramsRu] = self::filtros(
            'ru.id_escuela', 'ru.genero', 'ru.fecha_respuesta', $escuelaFiltro, $generoFiltro, $cicloRango
        );
        $sql = "SELECT p.clave_logica AS clave_pregunta,o.clave_logica AS clave_opcion,COUNT(*) AS total
                FROM respuestas_usuario ru
                JOIN preguntas p ON p.id_pregunta=ru.id_pregunta
                JOIN encuestas e ON e.id_encuesta=p.id_encuesta
                JOIN opciones_respuesta o ON o.id_opcion=ru.id_opcion AND o.id_pregunta=ru.id_pregunta
                WHERE e.id_nivel=? AND ru.id_opcion IS NOT NULL {$whereRu}
                GROUP BY p.clave_logica,o.clave_logica";
        self::aplicarConteos($db, $sql, 'i' . $typesRu, array_merge([$nivelId], $paramsRu), $opcionPorClave, $salida);

        [$whereRr, $typesRr, $paramsRr] = self::filtros(
            'eu.id_escuela', 'meta.genero', 'rr.fecha_respuesta', $escuelaFiltro, $generoFiltro, $cicloRango
        );
        $sql = "SELECT p.clave_logica AS clave_pregunta,o.clave_logica AS clave_opcion,
                       COUNT(*) AS total,AVG(rr.posicion) AS promedio
                FROM respuestas_ranking rr
                JOIN preguntas p ON p.id_pregunta=rr.id_pregunta
                JOIN encuestas e ON e.id_encuesta=p.id_encuesta
                JOIN opciones_respuesta o ON o.id_opcion=rr.id_opcion AND o.id_pregunta=rr.id_pregunta
                JOIN encuestas_usuarios eu ON eu.id_usuario_encuesta=rr.id_usuario_encuesta
                LEFT JOIN (
                    SELECT id_usuario_encuesta,MAX(genero) AS genero
                    FROM respuestas_usuario GROUP BY id_usuario_encuesta
                ) meta ON meta.id_usuario_encuesta=rr.id_usuario_encuesta
                WHERE e.id_nivel=? {$whereRr}
                GROUP BY p.clave_logica,o.clave_logica";
        $stmt = $db->prepare($sql);
        self::bind($stmt, 'i' . $typesRr, array_merge([$nivelId], $paramsRr));
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            if (!isset($opcionPorClave[$row['clave_opcion']])) continue;
            [$pid, $oid] = $opcionPorClave[$row['clave_opcion']];
            $salida[$pid][$oid]['total'] = (int) $row['total'];
            $salida[$pid][$oid]['promedio'] = (float) $row['promedio'];
        }
        $stmt->close();

        $normalizada = [];
        $maximo = 0;
        foreach ($salida as $pid => $opciones) {
            $suma = 0;
            foreach ($opciones as $opcion) {
                $normalizada[$pid][] = $opcion;
                $suma += $opcion['total'];
            }
            $maximo = max($maximo, $suma);
        }
        return ['opciones' => $normalizada, 'total' => $maximo];
    }

    public static function obtenerCiclos(mysqli $db, int $nivelId): array
    {
        $stmt = $db->prepare(
            'SELECT DISTINCT YEAR(ru.fecha_respuesta) anio,MONTH(ru.fecha_respuesta) mes
             FROM respuestas_usuario ru JOIN encuestas e ON e.id_encuesta=ru.id_encuesta
             WHERE e.id_nivel=? ORDER BY anio DESC,mes DESC'
        );
        $stmt->bind_param('i', $nivelId);
        $stmt->execute();
        $result = $stmt->get_result();
        $unicos = [];
        while ($row = $result->fetch_assoc()) {
            $anio = (int) $row['anio'];
            $inicio = (int) $row['mes'] >= 8 ? $anio : $anio - 1;
            $unicos[$inicio] = ['inicio' => $inicio, 'fin' => $inicio + 1, 'label' => "$inicio - " . ($inicio + 1)];
        }
        $stmt->close();
        krsort($unicos);
        return array_values($unicos);
    }

    private static function aplicarConteos(
        mysqli $db,
        string $sql,
        string $types,
        array $params,
        array $opcionPorClave,
        array &$salida
    ): void {
        $stmt = $db->prepare($sql);
        self::bind($stmt, $types, $params);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            if (!isset($opcionPorClave[$row['clave_opcion']])) continue;
            [$pid, $oid] = $opcionPorClave[$row['clave_opcion']];
            $salida[$pid][$oid]['total'] = (int) $row['total'];
        }
        $stmt->close();
    }

    /** @return array{0:string,1:string,2:array} */
    private static function filtros(
        string $campoEscuela,
        string $campoGenero,
        string $campoFecha,
        int $escuela,
        string $genero,
        ?array $ciclo
    ): array {
        $sql = '';
        $types = '';
        $params = [];
        if ($escuela > 0) {
            $sql .= " AND {$campoEscuela}=?";
            $types .= 'i';
            $params[] = $escuela;
        } elseif ($escuela < 0) {
            $sql .= " AND {$campoEscuela} IS NULL";
        }
        if ($genero !== '') {
            $sql .= " AND {$campoGenero}=?";
            $types .= 's';
            $params[] = $genero;
        }
        if ($ciclo !== null) {
            $sql .= " AND {$campoFecha} BETWEEN ? AND ?";
            $types .= 'ss';
            $params[] = $ciclo[0] . '-08-01 00:00:00';
            $params[] = $ciclo[1] . '-07-31 23:59:59';
        }
        return [$sql, $types, $params];
    }

    private static function bind(mysqli_stmt $stmt, string $types, array $params): void
    {
        if ($types === '') return;
        $refs = [];
        foreach ($params as $i => $_) $refs[$i] = &$params[$i];
        $stmt->bind_param($types, ...$refs);
    }
}
