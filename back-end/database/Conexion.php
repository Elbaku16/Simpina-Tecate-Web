<?php
declare(strict_types=1);

// Incluimos el lector (ajusta la ruta si este archivo no está en 'database')
require_once __DIR__ . '/../core/env_loader.php';

class Conexion
{
    private static ?mysqli $instancia = null;

    public static function getConexion(): mysqli
    {
        if (self::$instancia === null) {

            // 1. Cargamos el .env antes de intentar conectar
            // La ruta es relativa a ESTE archivo (__DIR__)
            try {
                // Si este archivo está en back-end/database, subimos 2 niveles a la raíz
                cargarEnv(__DIR__ . '/../../.env');
            } catch (Exception $e) {
                die("Error crítico de configuración del sistema.");
            }

            // 2. Obtenemos las credenciales
            $db = new mysqli(
                getenv('DB_HOST'), 
                getenv('DB_USER'), 
                getenv('DB_PASS'), 
                getenv('DB_NAME')
            );

            if ($db->connect_error) {
                error_log("Error de conexión BD: " . $db->connect_error);
                die("Error de conexión. Intente más tarde.");
            }

            $charset = getenv('DB_CHARSET') ?: 'utf8mb4';
            $db->set_charset($charset);

            self::$instancia = $db;
        }

        return self::$instancia;
    }
}