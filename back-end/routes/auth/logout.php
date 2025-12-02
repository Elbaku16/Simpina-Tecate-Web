<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/core/bootstrap_session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/database/conexion-db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/controllers/AuthController.php';

$controller = new AuthController();
$controller->logout();

// Cerrar la conexión ANTES de redirigir
$conn->close();

header('Location: /front-end/frames/inicio/inicio.php');
exit;
