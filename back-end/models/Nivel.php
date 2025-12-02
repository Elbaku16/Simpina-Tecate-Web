<?php

class Nivel
{
    public static function obtenerNiveles(mysqli $db): array
    {
        $sql = "SELECT id_nivel, nombre_nivel 
                FROM niveles_educativos 
                ORDER BY id_nivel";

        $res = $db->query($sql);

        $niveles = [];
        while ($row = $res->fetch_assoc()) {
            $niveles[] = [
                'id_nivel' => (int)$row['id_nivel'],
                'nombre_nivel' => $row['nombre_nivel']
            ];
        }

        return $niveles;
    }
}
