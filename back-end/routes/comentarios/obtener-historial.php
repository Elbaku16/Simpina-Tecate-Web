<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');


$baseBackend = __DIR__ . '/../../';

require_once $baseBackend . 'auth/verificar-sesion.php';
requerir_admin();

require_once $baseBackend . 'controllers/ComentariosController.php';
// No necesitamos conexion-db.php aquí, el controller se encarga.

try {
    $controller = new ComentariosController();

    // Obtener filtros si existen
    $filtros = [];
    if (!empty($_GET['accion']))      $filtros['accion'] = $_GET['accion'];
    if (!empty($_GET['fecha_desde'])) $filtros['fecha_desde'] = $_GET['fecha_desde'];
    if (!empty($_GET['fecha_hasta'])) $filtros['fecha_hasta'] = $_GET['fecha_hasta'];
    if (!empty($_GET['limite']))      $filtros['limite'] = (int)$_GET['limite'];

    $historial = $controller->obtenerHistorial($filtros);
    $estadisticas = $controller->obtenerEstadisticasHistorial();

    echo json_encode([
        'success'      => true,
        'historial'    => $historial,
        'estadisticas' => $estadisticas,
        'total'        => count($historial)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}
?>