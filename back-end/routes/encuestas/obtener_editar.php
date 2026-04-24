<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0); 
error_reporting(E_ALL);

try {

    $pathToBackend = __DIR__ . '/../../'; 

    if (!file_exists($pathToBackend . 'core/bootstrap_session.php')) {
        throw new Exception("Error de ruta: No encuentro el backend en $pathToBackend");
    }

    require_once $pathToBackend . 'core/bootstrap_session.php';
    
    
    require_once $pathToBackend . 'controllers/EncuestasController.php';


    
    $nivel = $_GET['nivel'] ?? 'primaria';

    $controller = new EncuestasController();
    $data = $controller->obtenerEncuestaPorNivel($nivel);


    echo json_encode($data, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>