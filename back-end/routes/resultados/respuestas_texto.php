<?php
header('Content-Type: application/json; charset=utf-8');

require_once $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/back-end/auth/verificar-sesion.php';
requerir_admin();

require_once $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/back-end/controllers/ResultadosTextoController.php';

$controller = new ResultadosTextoController();

// Detectar acción
$accion = $_GET['accion'] ?? $_POST['accion'] ?? 'obtener';

try {
    switch ($accion) {

        case 'obtener':
            $idPregunta = (int)($_GET['id_pregunta'] ?? 0);
            $idEscuela  = (int)($_GET['escuela'] ?? 0);

            echo json_encode(
                $controller->obtener($idPregunta, $idEscuela),
                JSON_UNESCAPED_UNICODE
            );
            break;

        case 'eliminar':
            $idRespuesta = (int)($_POST['id_respuesta'] ?? 0);

            echo json_encode(
                $controller->eliminar($idRespuesta),
                JSON_UNESCAPED_UNICODE
            );
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Acción no válida']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
