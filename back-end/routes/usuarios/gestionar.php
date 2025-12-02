<?php
declare(strict_types=1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/auth/verificar-sesion.php';
requerir_admin(); 

// RESTRICCIÓN MÁXIMA: Solo el rol 'admin' puede crear/eliminar otros usuarios
if (!rol_es('admin')) {
    header('Location: /front-end/frames/panel/panel-admin.php');
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/database/conexion-db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/controllers/UsuariosAdminController.php';

header('Content-Type: application/json; charset=utf-8');

$controller = new UsuariosAdminController();
$action = $_GET['accion'] ?? $_POST['accion'] ?? null;
$response = ['success' => false];

try {
    switch ($action) {
        case 'listar':
            $response = ['success' => true, 'usuarios' => $controller->listar()];
            break;
            
        case 'crear':
            $usuario  = $_POST['usuario'] ?? '';
            $password = $_POST['password'] ?? '';
            $nombre   = $_POST['nombre'] ?? '';
            $rol      = $_POST['rol'] ?? '';
            $response = $controller->crear($usuario, $password, $nombre, $rol);
            break;
            
        case 'eliminar':
            $id = (int)($_POST['id'] ?? 0);
            $response = $controller->eliminar($id);
            break;

        default:
            $response = ['success' => false, 'error' => 'Acción no válida.'];
            break;
    }
} catch (Throwable $e) {
    $response = ['success' => false, 'error' => $e->getMessage()];
}

// CERRAR conexión
$conn->close();

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;