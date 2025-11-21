<?php
declare(strict_types=1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/core/bootstrap_session.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/database/Conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/controllers/EncuestasController.php';

header('Content-Type: application/json; charset=utf-8');

$nivel = $_GET['nivel'] ?? 'primaria';

$controller = new EncuestasController();
$data = $controller->obtenerEncuestaPorNivel($nivel);

echo json_encode($data, JSON_UNESCAPED_UNICODE);
exit;
