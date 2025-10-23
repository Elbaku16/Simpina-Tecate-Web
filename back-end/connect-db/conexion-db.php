<?php
// Detectar entorno

    // Conexión remota
    $servername = "svdm056.serverneubox.com.mx";
    $username = "glevanco_simpina";
    $password = "zMHnH2u8cbQuqsFsZjUh";
    $dbname = "glevanco_simpina";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(" Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
