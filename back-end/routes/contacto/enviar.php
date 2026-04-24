<?php
declare(strict_types=1);

ini_set('display_errors', 1);
error_reporting(E_ALL);



$baseBackend = __DIR__ . '/../../';

require_once $baseBackend . 'core/bootstrap_session.php';
require_once $baseBackend . 'controllers/ContactoController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /simpinna/front-end/frames/inicio/contacto.php');
    exit;
}


$controller = new ContactoController();

$result = $controller->procesarFormulario($_POST);


$urlDestino = '/simpinna/front-end/frames/inicio/contacto.php';

if ($result['ok']) {
    header("Location: $urlDestino?ok=1");
} else {
    $mensaje = urlencode($result['mensaje'] ?? 'Error desconocido');
    header("Location: $urlDestino?ok=0&m=$mensaje");
}
exit;
?>