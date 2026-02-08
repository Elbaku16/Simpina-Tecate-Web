<?php
declare(strict_types=1);

class Conexion
{
    private static ?mysqli $instancia = null;

    public static function getConexion(): mysqli
    {
        if (self::$instancia === null) {

            $servername = "sql107.infinityfree.com";
            $username   = "if0_40468916";
            $password   = "cugiL98bGoD0";
            $dbname     = "if0_40468916_simpinna";

            $db = new mysqli($servername, $username, $password, $dbname);

            if ($db->connect_error) {
                die("Error de conexión: " . $db->connect_error);
            }

            $db->set_charset("utf8mb4");

            self::$instancia = $db;
        }

        return self::$instancia;
    }
}
