<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/back-end/core/bootstrap_session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/back-end/controllers/AuthController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /SIMPINNA/front-end/frames/admin/login.php?e=metodo');
    exit;
}

$controller = new AuthController();
$res = $controller->login($_POST);

if ($res['success']) {
    header('Location: /SIMPINNA/front-end/frames/panel/panel-admin.php');
    exit;
}

$mensaje = match($res['error']) {
    'csrf'        => 'Error de seguridad. Intenta de nuevo.',
    'credenciales'=> 'Usuario o contraseña incorrectos.',
    'bloqueo'     => 'Demasiados intentos fallidos. Intenta en unos minutos.',
    'metodo'      => 'Método inválido.',
    default       => 'Ocurrió un error inesperado.'
};

header('Location: /SIMPINNA/front-end/frames/admin/login.php?m=' . urlencode($mensaje));
exit;

