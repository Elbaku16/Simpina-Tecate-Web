<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/core/bootstrap_session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/controllers/AuthController.php';

$controller = new AuthController();
$controller->logout();

// Después del logout redirigir al inicio público
header('Location: /front-end/frames/inicio/inicio.php');
exit;
