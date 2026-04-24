<?php
require_once __DIR__ . '/../core/env_loader.php';


try {
    cargarEnv(__DIR__ . '/../../.env');
} catch (Exception $e) {
    die("Error de configuración: " . $e->getMessage());
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // 3. Usamos getenv()
    $conn = new mysqli(
        getenv('DB_HOST'), 
        getenv('DB_USER'), 
        getenv('DB_PASS'), 
        getenv('DB_NAME')
    );
    
    $charset = getenv('DB_CHARSET') ?: 'utf8mb4';
    $conn->set_charset($charset);

} catch (mysqli_sql_exception $e) {
    error_log("Error DB: " . $e->getMessage()); 
    die("Error al conectar con la base de datos.");
}
?>