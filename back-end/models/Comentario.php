<?php
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

    public static function eliminar(mysqli $db, int $id): bool
    {
        $stmt = $db->prepare("DELETE FROM contactos WHERE id_contacto = ?");
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public static function cambiarEstado(mysqli $db, int $id, string $estado): bool
    {
        $stmt = $db->prepare("UPDATE contactos SET estado = ? WHERE id_contacto = ?");
        $stmt->bind_param("si", $estado, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
