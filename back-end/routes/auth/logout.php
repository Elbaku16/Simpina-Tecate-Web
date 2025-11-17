<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/back-end/core/bootstrap_session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/back-end/controllers/AuthController.php';

$controller = new AuthController();
$controller->logout();

// Después del logout redirigir al inicio público
header('Location: /SIMPINNA/front-end/frames/inicio/inicio.php');
exit;
