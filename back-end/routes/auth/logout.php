<?php
declare(strict_types=1);


$baseBackend = __DIR__ . '/../../';

require_once $baseBackend . 'core/bootstrap_session.php';
require_once $baseBackend . 'controllers/AuthController.php';
require_once __DIR__ . '/../../../front-end/includes/config.php';


try {
    $controller = new AuthController();
    
    $controller->logout();

} catch (Exception $e) {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}


header('Location: ' . FRAMES_URL . 'inicio/inicio.php');
exit;
?>