<?php
declare(strict_types=1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/back-end/core/bootstrap_session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/back-end/controllers/EncuestasController.php';

header('Content-Type: application/json; charset=utf-8');

// --- Validación del método ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'error'   => 'Metodo no permitido'
    ]);
    exit;
}

// --- Obtener el JSON del body ---
$payload = json_decode(file_get_contents('php://input'), true);

// Si JSON no es válido
if (!is_array($payload)) {
    echo json_encode([
        'success' => false,
        'error'   => 'JSON inválido'
    ]);
    exit;
}

// Validación básica
if (!isset($payload['id_encuesta'])) {
    echo json_encode([
        'success' => false,
        'error'   => 'Falta id_encuesta'
    ]);
    exit;
}

// --- Controlador ---
try {
    $controller = new EncuestasController();
    $resultado  = $controller->enviarRespuestas($payload);

    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {

    echo json_encode([
        'success' => false,
        'error'   => 'Error interno',
        'detalle' => $e->getMessage()
    ]);
}

exit;
