<?php
declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

try {
   
    $baseBackend = __DIR__ . '/../../';

    if (!file_exists($baseBackend . 'core/bootstrap_session.php')) {
        throw new Exception("Error interno: Estructura de carpetas inválida.");
    }

    require_once $baseBackend . 'core/bootstrap_session.php';
    require_once $baseBackend . 'controllers/EncuestasController.php';

   
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido', 405);
    }

    $rawBody = file_get_contents('php://input');
    $payload = json_decode($rawBody, true);

    if (!is_array($payload)) {
        throw new Exception('JSON inválido o mal formado');
    }

    if (!isset($payload['id_encuesta'])) {
        throw new Exception('Falta el ID de la encuesta');
    }


    $controller = new EncuestasController();
    $resultado  = $controller->enviarRespuestas($payload);

    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    // Si la excepción tiene código HTTP (como 405), lo usamos
    $code = $e->getCode();
    http_response_code(($code >= 400 && $code < 600) ? $code : 500);

    echo json_encode([
        'success' => false,
        'error'   => 'Error interno',
        'detalle' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
exit;
?>