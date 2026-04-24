<?php
declare(strict_types=1);


$baseBackend = __DIR__ . '/../../';


require_once $baseBackend . 'core/bootstrap_session.php';
require_once $baseBackend . 'controllers/AuthController.php';


try {
    $controller = new AuthController();
    
    $controller->logout();

} catch (Exception $e) {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}


header('Location: /simpinna/front-end/frames/inicio/inicio.php');
exit;
?>