<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/core/bootstrap_session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/controllers/AuthController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /front-end/frames/admin/login.php?e=metodo');
    exit;
}

$controller = new AuthController();
$res = $controller->login($_POST);

if ($res['success']) {
    header('Location: /front-end/frames/panel/panel-admin.php');
    exit;
}

$mensaje = match($res['error']) {
    'csrf'        => 'Error de seguridad. Intenta de nuevo.',
    'credenciales'=> 'Usuario o contraseña incorrectos.',
    'bloqueo'     => 'Demasiados intentos fallidos. Intenta en unos minutos.',
    'metodo'      => 'Método inválido.',
    default       => 'Ocurrió un error inesperado.'
};

header('Location: /front-end/frames/admin/login.php?m=' . urlencode($mensaje));
exit;

