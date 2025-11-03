<?php
// resultados_secundaria.php
require_once __DIR__ . '/../../../back-end/connect-db/conexion-db.php';
session_start();
if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header('Location: /SIMPINNA/front-end/frames/panel-admin/login.php'); exit;
}
$encuestaId = 0;
$sql = "SELECT id_encuesta FROM encuestas WHERE id_nivel = 3 ORDER BY id_encuesta LIMIT 1";
if ($rs = $conn->query($sql)) { if ($row = $rs->fetch_assoc()) $encuestaId = (int)$row['id_encuesta']; $rs->close(); }
$conn->close();
require __DIR__ . '/resultados_base.php';
