<?php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
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
    if ($e instanceof InvalidArgumentException) {
        http_response_code(422);
        $mensaje = $e->getMessage();
    } elseif ($e instanceof RuntimeException && str_starts_with($e->getMessage(), 'No hay una encuesta activa')) {
        http_response_code(404);
        $mensaje = $e->getMessage();
    } else {
        http_response_code(500);
        $mensaje = 'No se pudo cargar la encuesta. Intenta de nuevo.';
        error_log('Error al cargar encuesta: ' . $e->getMessage());
    }

    echo json_encode([
        'status' => 'error',
        'message' => $mensaje,
    ], JSON_UNESCAPED_UNICODE);
}
exit;
?>
