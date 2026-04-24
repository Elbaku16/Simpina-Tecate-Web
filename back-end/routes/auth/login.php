<?php
declare(strict_types=1);

ini_set('display_errors', 1);
error_reporting(E_ALL);


$baseBackend = __DIR__ . '/../../';

require_once $baseBackend . 'core/bootstrap_session.php';
require_once $baseBackend . 'controllers/AuthController.php';
require_once __DIR__ . '/../../../front-end/includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . FRAMES_URL . 'admin/login.php?m=' . urlencode('Método no permitido'));
    exit;
}

try {

    $controller = new AuthController();
    
    $res = $controller->login($_POST);

    // CASO A: Login Correcto
    if (isset($res['success']) && $res['success'] === true) {
        header('Location: ' . FRAMES_URL . 'panel/panel-admin.php');
        exit;
    }

   
    $errorKey = $res['error'] ?? 'default';

    $mensaje = match($errorKey) {
        'csrf'         => 'Error de seguridad (token inválido). Recarga e intenta de nuevo.',
        'vacio'        => 'Por favor completa todos los campos.',
        'credenciales' => 'Usuario o contraseña incorrectos.',
        'bloqueo'      => 'Has excedido el número de intentos. Espera unos minutos.',
        'db_error'     => 'Error de conexión con la base de datos.',
        default        => 'Ocurrió un error inesperado.'
    };

    header('Location: ' . FRAMES_URL . 'admin/login.php?m=' . urlencode($mensaje));
    exit;

} catch (Exception $e) {
    error_log("Error critico en login: " . $e->getMessage());
    header('Location: ' . FRAMES_URL . 'admin/login.php?m=' . urlencode('Error del sistema. Contacte soporte.'));
    exit;
}
?>