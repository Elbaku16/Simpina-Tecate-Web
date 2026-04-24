<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', '0'); 
error_reporting(E_ALL);

try {
   
    $baseBackend = __DIR__ . '/../../';

    if (!file_exists($baseBackend . 'core/bootstrap_session.php')) {
        throw new Exception("Error interno: No se encuentra el núcleo del sistema.");
    }

    require_once $baseBackend . 'core/bootstrap_session.php';
    require_once $baseBackend . 'controllers/ContactoController.php';

   
    $controller = new ContactoController();
    
    $data = $controller->obtenerDatosFormulario();

    echo json_encode([
        'ok'               => true,
        'niveles'          => $data['niveles'] ?? [],
        'escuelasPorNivel' => $data['escuelas'] ?? [] 
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
exit;
?>