<?php
$servername = "sql107.infinityfree.com";
$username   = "if0_40468916";
$password   = "cugiL98bGoD0";
$dbname     = "if0_40468916_simpinna";

// Reportar errores de forma estricta
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {

    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Configuración de caracteres
    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {
    die($e->getMessage());
}
?>
