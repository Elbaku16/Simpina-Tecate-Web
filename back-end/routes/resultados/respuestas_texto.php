<?php
header('Content-Type: application/json; charset=utf-8');

require_once $_SERVER['DOCUMENT_ROOT'].'/back-end/auth/verificar-sesion.php';
requerir_admin();

require_once $_SERVER['DOCUMENT_ROOT'].'/back-end/database/conexion-db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/back-end/controllers/ResultadosTextoController.php';

$controller = new ResultadosTextoController();

// Detectar acción
$accion = $_GET['accion'] ?? $_POST['accion'] ?? 'obtener';

try {

    switch ($accion) {

        case 'obtener':
            $idPregunta   = (int)($_GET['id_pregunta'] ?? 0);
            $idEscuela    = (int)($_GET['escuela'] ?? 0);
            $cicloEscolar = $_GET['ciclo'] ?? '';
            $generoFiltro = $_GET['genero'] ?? '';

            // Llamada al controlador con el nuevo parámetro
            $respuesta = $controller->obtener($idPregunta, $idEscuela, $cicloEscolar, $generoFiltro); 

            // CERRAR conexión antes de enviar JSON
            $conn->close();
            echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
            break;


        case 'eliminar':
            // RESTRICCIÓN: Solo si tiene permiso de eliminar
            if (!tiene_permiso('eliminar_respuestas')) {
                // CERRAR conexión antes de enviar JSON
                $conn->close();
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Permiso denegado para eliminar respuestas']);
                exit;
            }
            
            $idRespuesta = (int)($_POST['id_respuesta'] ?? 0);

            $respuesta = $controller->eliminar($idRespuesta);

            // CERRAR conexión antes de enviar JSON
            $conn->close();
            echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
            break;


        default:
            // CERRAR conexión antes de enviar JSON
            $conn->close();
            echo json_encode(['success' => false, 'error' => 'Acción no válida']);
    }

} catch (Exception $e) {

    // Siempre cerrar conexión, incluso en excepciones
    if (isset($conn)) {
        $conn->close();
    }

    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

exit;