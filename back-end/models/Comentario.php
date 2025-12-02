<?php
require_once __DIR__ . '/Historial.php';

class Comentario
{
    public static function listar(mysqli $db, array $filtros): array
    {
        $sql = "SELECT 
                  c.id_contacto, c.nombre, c.comentarios, c.fecha_envio, c.estado,
                  n.nombre_nivel, e.nombre_escuela
                FROM contactos c
                INNER JOIN niveles_educativos n ON c.id_nivel = n.id_nivel
                INNER JOIN escuelas e ON c.id_escuela = e.id_escuela
                WHERE 1=1";

        $params = [];
        $types = "";

        if (!empty($filtros['estado'])) {
            $sql .= " AND c.estado = ?";
            $params[] = $filtros['estado'];
            $types .= "s";
        }

        if (!empty($filtros['nivel'])) {
            $sql .= " AND c.id_nivel = ?";
            $params[] = (int)$filtros['nivel'];
            $types .= "i";
        }

        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (c.nombre LIKE ? OR c.comentarios LIKE ? OR e.nombre_escuela LIKE ?)";
            $term = "%".$filtros['busqueda']."%";
            $params = array_merge($params, [$term,$term,$term]);
            $types .= "sss";
        }

        $sql .= " ORDER BY c.fecha_envio DESC";

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

    public static function eliminar(mysqli $db, int $id, string $usuario = 'Sistema'): bool
    {
        // Obtener información del comentario antes de eliminarlo
        $stmt = $db->prepare("SELECT nombre, comentarios, estado FROM contactos WHERE id_contacto = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $comentario = $result->fetch_assoc();
        $stmt->close();

        if (!$comentario) {
            return false;
        }

        // Eliminar el comentario
        $stmt = $db->prepare("DELETE FROM contactos WHERE id_contacto = ?");
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();

        // Registrar en el historial
        if ($ok) {
            Historial::registrar($db, [
                'id_contacto' => $id,
                'accion' => 'eliminado',
                'usuario' => $usuario,
                'detalles' => "Se eliminó el comentario de '{$comentario['nombre']}': " . 
                             substr($comentario['comentarios'], 0, 100) . 
                             (strlen($comentario['comentarios']) > 100 ? '...' : ''),
                'estado_anterior' => $comentario['estado'],
                'estado_nuevo' => ''
            ]);
        }

        return $ok;
    }

    public static function cambiarEstado(mysqli $db, int $id, string $estadoNuevo, string $usuario = 'Sistema'): bool
    {
        // Obtener el estado actual
        $stmt = $db->prepare("SELECT estado, nombre FROM contactos WHERE id_contacto = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $comentario = $result->fetch_assoc();
        $stmt->close();

        if (!$comentario) {
            return false;
        }

        $estadoAnterior = $comentario['estado'];

        // Cambiar el estado
        $stmt = $db->prepare("UPDATE contactos SET estado = ? WHERE id_contacto = ?");
        $stmt->bind_param("si", $estadoNuevo, $id);
        $ok = $stmt->execute();
        $stmt->close();

        // Registrar en el historial
        if ($ok && $estadoAnterior !== $estadoNuevo) {
            $estadosNombres = [
                'pendiente' => 'Pendiente',
                'en_revision' => 'En Revisión',
                'resuelto' => 'Resuelto'
            ];

            Historial::registrar($db, [
                'id_contacto' => $id,
                'accion' => 'cambio_estado',
                'usuario' => $usuario,
                'detalles' => "Se cambió el estado del comentario de '{$comentario['nombre']}' " .
                             "de '{$estadosNombres[$estadoAnterior]}' a '{$estadosNombres[$estadoNuevo]}'",
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $estadoNuevo
            ]);
        }

        return $ok;
    }

    /**
     * Obtiene un comentario por su ID
     */
    public static function obtenerPorId(mysqli $db, int $id): ?array
    {
        $sql = "SELECT 
                  c.id_contacto, c.nombre, c.comentarios, c.fecha_envio, c.estado,
                  n.nombre_nivel, e.nombre_escuela
                FROM contactos c
                INNER JOIN niveles_educativos n ON c.id_nivel = n.id_nivel
                INNER JOIN escuelas e ON c.id_escuela = e.id_escuela
                WHERE c.id_contacto = ?";

        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        return $data;
    }

    /**
     * Cuenta el total de comentarios
     */
    public static function contarTotal(mysqli $db, array $filtros = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM contactos c WHERE 1=1";
        $params = [];
        $types = "";

        if (!empty($filtros['estado'])) {
            $sql .= " AND c.estado = ?";
            $params[] = $filtros['estado'];
            $types .= "s";
        }

        if (!empty($filtros['nivel'])) {
            $sql .= " AND c.id_nivel = ?";
            $params[] = (int)$filtros['nivel'];
            $types .= "i";
        }

        $stmt = $db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return (int)$row['total'];
    }
}