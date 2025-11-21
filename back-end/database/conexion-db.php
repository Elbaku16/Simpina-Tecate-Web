<?php
$servername = "p:svdm056.serverneubox.com.mx";
$username   = "glevanco_simpina";
$password   = "zMHnH2u8cbQuqsFsZjUh";
$dbname     = "glevanco_simpina";

// Reportar errores de forma estricta
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {

    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Configuración de caracteres
    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {

    die("Error de conexión al sistema. Por favor intente más tarde.");
}
?>