<?php
declare(strict_types=1);
$start = microtime(true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/back-end/controllers/EncuestasController.php';

header('Content-Type: application/json; charset=utf-8');

$nivel = $_GET['nivel'] ?? 'primaria';

$controller = new EncuestasController();
$data = $controller->obtenerEncuestaPorNivel($nivel);
$phpTime = microtime(true) - $start;

echo json_encode($data, JSON_UNESCAPED_UNICODE);
exit;
