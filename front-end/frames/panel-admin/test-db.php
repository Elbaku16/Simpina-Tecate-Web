<?php
include($_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/back-end/connect-db/conexion-db.php');

$sql = "SHOW TABLES";
$result = $conn->query($sql);

if ($result) {
    echo "<h3>Conexión correcta. Tablas encontradas:</h3><ul>";
    while ($row = $result->fetch_array()) {
        echo "<li>{$row[0]}</li>";
    }
    echo "</ul>";
} else {
    echo " Error al consultar: " . $conn->error;
}
?>
