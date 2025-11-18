<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/back-end/auth/verificar-sesion.php';
requerir_admin();

require_once $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/back-end/controllers/ComentariosController.php';

header('Content-Type: application/json');

$controller = new ComentariosController();

// Obtener filtros si existen
$filtros = [];
if (!empty($_GET['accion'])) {
    $filtros['accion'] = $_GET['accion'];
}
if (!empty($_GET['fecha_desde'])) {
    $filtros['fecha_desde'] = $_GET['fecha_desde'];
}
if (!empty($_GET['fecha_hasta'])) {
    $filtros['fecha_hasta'] = $_GET['fecha_hasta'];
}
if (!empty($_GET['limite'])) {
    $filtros['limite'] = (int)$_GET['limite'];
}

$historial = $controller->obtenerHistorial($filtros);
$estadisticas = $controller->obtenerEstadisticasHistorial();

echo json_encode([
    'success' => true,
    'historial' => $historial,
    'estadisticas' => $estadisticas,
    'total' => count($historial)
]);