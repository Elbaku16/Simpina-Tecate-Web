<?php
declare(strict_types=1);

class Conexion
{
    private static ?mysqli $instancia = null;

    public static function getConexion(): mysqli
    {
        if (self::$instancia === null) {

            $servername = "svdm056.serverneubox.com.mx";
            $username   = "glevanco_simpina";
            $password   = "zMHnH2u8cbQuqsFsZjUh";
            $dbname     = "glevanco_simpina";

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
