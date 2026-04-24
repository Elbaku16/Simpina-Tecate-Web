<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', '0');
error_reporting(E_ALL);

try {

    $baseBackend = __DIR__ . '/../../';

    require_once $baseBackend . 'auth/verificar-sesion.php';
    requerir_admin(); 


    if (!rol_es('secretario_ejecutivo')) {
        http_response_code(403);
        echo json_encode([
            'success' => false, 
            'error' => 'Permiso denegado: Solo el Secretario Ejecutivo puede realizar esta acción.'
        ]);
        exit;
    }

    require_once $baseBackend . 'controllers/UsuariosAdminController.php';

  
    $controller = new UsuariosAdminController();
    $action = $_GET['accion'] ?? $_POST['accion'] ?? null;
    $response = ['success' => false];

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

    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
exit;
?>