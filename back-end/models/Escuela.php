<?php

class Escuela
{
    public static function obtenerEscuelasPorNivel(mysqli $db): array
    {
        $sql = "SELECT id_escuela, id_nivel, nombre_escuela 
                FROM escuelas 
                ORDER BY id_nivel, nombre_escuela";

        $res = $db->query($sql);

        $out = [];

        while ($row = $res->fetch_assoc()) {
            $nid = (int)$row['id_nivel'];
            if (!isset($out[$nid])) {
                $out[$nid] = [];
            }

            $out[$nid][] = [
                'id'     => (int)$row['id_escuela'],
                'nombre' => $row['nombre_escuela']
            ];
        }

        return $out;
    }
}
