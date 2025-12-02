<?php
class Contacto
{
    public static function guardar(mysqli $db, array $input): bool
    {
        $sql = "INSERT INTO contactos (nombre, id_nivel, id_escuela, comentarios)
                VALUES (?, ?, ?, ?)";

        $stmt = $db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "siis",
            $input['nombre'],
            $input['nivel'],
            $input['escuela'],
            $input['comentarios']
        );

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }
}
