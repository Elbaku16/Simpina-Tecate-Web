<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
error_reporting(E_ALL);

try {
    $baseBackend = __DIR__ . '/../../';
    require_once $baseBackend . 'auth/verificar-sesion.php';
    requerir_admin();
    if (!tiene_permiso('modificar_encuesta')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Permiso denegado.']);
        exit;
    }
    require_once $baseBackend . 'controllers/EditarController.php';
    $controller = new EditarController();
    echo json_encode(
        $controller->obtenerEncuestaPorNivel((string) ($_GET['nivel'] ?? 'primaria')),
        JSON_UNESCAPED_UNICODE
    );
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('Error al abrir editor: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'No se pudo cargar la encuesta.']);
}
exit;
