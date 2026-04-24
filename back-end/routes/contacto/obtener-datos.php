<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

// Debug
ini_set('display_errors', '0'); 
error_reporting(E_ALL);

try {
  
    $baseBackend = __DIR__ . '/../../';

    require_once $baseBackend . 'controllers/ContactoController.php';

 
    $controller = new ContactoController();
    
    // Obtenemos los datos (niveles y escuelas)
    $data = $controller->obtenerDatosFormulario();


    echo json_encode($data, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'mensaje' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
exit;
?>