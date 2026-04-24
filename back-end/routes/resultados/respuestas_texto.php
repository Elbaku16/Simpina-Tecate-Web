<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', '0');
error_reporting(E_ALL);

try {
    $baseBackend = __DIR__ . '/../../';

    require_once $baseBackend . 'auth/verificar-sesion.php';
    requerir_admin();

    require_once $baseBackend . 'controllers/ResultadosTextoController.php';

    $controller = new ResultadosTextoController();
    $accion = $_GET['accion'] ?? $_POST['accion'] ?? 'obtener';

    switch ($accion) {
        case 'obtener':
            $idPregunta   = (int)($_GET['id_pregunta'] ?? 0);
            $idEscuela    = (int)($_GET['escuela'] ?? 0);
            $cicloEscolar = $_GET['ciclo'] ?? '';
            $generoFiltro = $_GET['genero'] ?? '';

            $respuesta = $controller->obtener($idPregunta, $idEscuela, $cicloEscolar, $generoFiltro);
            echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
            break;

        case 'eliminar':
            if (!tiene_permiso('eliminar_respuestas')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Permiso denegado']);
                exit;
            }

            $idRespuesta = (int)($_POST['id_respuesta'] ?? 0);
            $respuesta = $controller->eliminar($idRespuesta);
            echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Acción no válida']);
            break;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
exit;
?>