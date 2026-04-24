<?php
declare(strict_types=1);

ini_set('display_errors', 1);
error_reporting(E_ALL);



$baseBackend = __DIR__ . '/../../';

require_once $baseBackend . 'core/bootstrap_session.php';
require_once $baseBackend . 'controllers/ContactoController.php';
require_once __DIR__ . '/../../../front-end/includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . FRAMES_URL . 'inicio/contacto.php');
    exit;
}


$controller = new ContactoController();

$result = $controller->procesarFormulario($_POST);


$urlDestino = FRAMES_URL . 'inicio/contacto.php';

if ($result['ok']) {
    header("Location: $urlDestino?ok=1");
} else {
    $mensaje = urlencode($result['mensaje'] ?? 'Error desconocido');
    header("Location: $urlDestino?ok=0&m=$mensaje");
}
exit;
?>