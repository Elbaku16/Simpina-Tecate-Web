<?php
declare(strict_types=1);
$start = microtime(true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/database/conexion-db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/controllers/EncuestasController.php';

header('Content-Type: application/json; charset=utf-8');

$nivel = $_GET['nivel'] ?? 'primaria';

$controller = new EncuestasController();
$data = $controller->obtenerEncuestaPorNivel($nivel);

$phpTime = microtime(true) - $start;

// CERRAR la conexión ANTES de enviar respuesta
$conn->close();

echo json_encode($data, JSON_UNESCAPED_UNICODE);
exit;
