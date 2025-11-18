<?php
/**
 * Modelo Historial
 * Gestiona el registro de todos los cambios realizados en los comentarios
 */
class Historial
{
    /**
     * Registra una acción en el historial
     * 
     * @param mysqli $db Conexión a la base de datos
     * @param array $datos Datos de la acción (id_contacto, accion, usuario, detalles)
     * @return bool True si se guardó correctamente
     */
    public static function registrar(mysqli $db, array $datos): bool
    {
        $sql = "INSERT INTO historial_comentarios 
                (id_contacto, accion, usuario, detalles, estado_anterior, estado_nuevo) 
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        // Preparar valores para evitar null en bind_param
        $id_contacto = $datos['id_contacto'];
        $accion = $datos['accion'];
        $usuario = $datos['usuario'];
        $detalles = $datos['detalles'];
        $estado_anterior = $datos['estado_anterior'] ?? '';
        $estado_nuevo = $datos['estado_nuevo'] ?? '';

        $stmt->bind_param(
            "isssss",
            $id_contacto,
            $accion,
            $usuario,
            $detalles,
            $estado_anterior,
            $estado_nuevo
        );

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    /**
     * Obtiene todo el historial de cambios
     * 
     * @param mysqli $db Conexión a la base de datos
     * @param array $filtros Filtros opcionales
     * @return array Lista de registros del historial
     */
    public static function obtenerTodo(mysqli $db, array $filtros = []): array
    {
        $sql = "SELECT 
                    h.id_historial,
                    h.id_contacto,
                    h.accion,
                    h.usuario,
                    h.detalles,
                    h.estado_anterior,
                    h.estado_nuevo,
                    h.fecha_accion,
                    c.nombre as nombre_contacto,
                    c.comentarios
                FROM historial_comentarios h
                LEFT JOIN contactos c ON h.id_contacto = c.id_contacto
                WHERE 1=1";

        $params = [];
        $types = "";

        // Filtrar por comentario específico
        if (!empty($filtros['id_contacto'])) {
            $sql .= " AND h.id_contacto = ?";
            $params[] = (int)$filtros['id_contacto'];
            $types .= "i";
        }

        // Filtrar por tipo de acción
        if (!empty($filtros['accion'])) {
            $sql .= " AND h.accion = ?";
            $params[] = $filtros['accion'];
            $types .= "s";
        }

        // Filtrar por usuario
        if (!empty($filtros['usuario'])) {
            $sql .= " AND h.usuario LIKE ?";
            $params[] = "%".$filtros['usuario']."%";
            $types .= "s";
        }

        // Filtrar por rango de fechas
        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND DATE(h.fecha_accion) >= ?";
            $params[] = $filtros['fecha_desde'];
            $types .= "s";
        }

        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(h.fecha_accion) <= ?";
            $params[] = $filtros['fecha_hasta'];
            $types .= "s";
        }

        $sql .= " ORDER BY h.fecha_accion DESC";

        // Limitar resultados si se especifica
        if (!empty($filtros['limite'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filtros['limite'];
            $types .= "i";
        }

        $stmt = $db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();

        $res = $stmt->get_result();
        $data = $res->fetch_all(MYSQLI_ASSOC);

        $stmt->close();
        return $data;
    }

    /**
     * Obtiene el historial de un comentario específico
     * 
     * @param mysqli $db Conexión a la base de datos
     * @param int $idContacto ID del comentario
     * @return array Lista de cambios del comentario
     */
    public static function obtenerPorComentario(mysqli $db, int $idContacto): array
    {
        return self::obtenerTodo($db, ['id_contacto' => $idContacto]);
    }

    /**
     * Cuenta el total de registros en el historial
     * 
     * @param mysqli $db Conexión a la base de datos
     * @return int Total de registros
     */
    public static function contarTotal(mysqli $db): int
    {
        $sql = "SELECT COUNT(*) as total FROM historial_comentarios";
        $result = $db->query($sql);
        $row = $result->fetch_assoc();
        return (int)$row['total'];
    }

    /**
     * Obtiene estadísticas del historial
     * 
     * @param mysqli $db Conexión a la base de datos
     * @return array Estadísticas agrupadas por acción
     */
    public static function obtenerEstadisticas(mysqli $db): array
    {
        $sql = "SELECT 
                    accion,
                    COUNT(*) as total,
                    DATE(MAX(fecha_accion)) as ultima_fecha
                FROM historial_comentarios
                GROUP BY accion
                ORDER BY total DESC";

        $result = $db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Elimina registros antiguos del historial (limpieza)
     * 
     * @param mysqli $db Conexión a la base de datos
     * @param int $dias Eliminar registros más antiguos que X días
     * @return bool True si se eliminó correctamente
     */
    public static function limpiarAntiguos(mysqli $db, int $dias = 365): bool
    {
        $sql = "DELETE FROM historial_comentarios 
                WHERE fecha_accion < DATE_SUB(NOW(), INTERVAL ? DAY)";
        
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $dias);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }
}