<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

try {

    $basePath = __DIR__ . '/../../'; 

    $pathController = $basePath . 'controllers/EncuestasController.php';

    if (!file_exists($pathController)) {
        throw new Exception("No encuentro el controlador en: $pathController");
    }

    require_once $pathController;


    
    $nivel = $_GET['nivel'] ?? 'primaria';

    $controller = new EncuestasController();
    $data = $controller->obtenerEncuestaPorNivel($nivel);



    echo json_encode($data, JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage(),
        "file" => $e->getFile(), // Solo para debug
        "line" => $e->getLine()  // Solo para debug
    ]);
}
exit;
?>