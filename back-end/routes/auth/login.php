<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/core/bootstrap_session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/controllers/AuthController.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/database/conexion-db.php'; // ← Asegurar conexión

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Cerrar conexión ANTES de salir
    $conn->close();
    header('Location: /front-end/frames/admin/login.php?e=metodo');
    exit;
}

$controller = new AuthController();
$res = $controller->login($_POST);

// Si login correcto
if ($res['success']) {
    $conn->close(); // ← Cerrar conexión
    header('Location: /front-end/frames/panel/panel-admin.php');
    exit;
}

// Mensajes de error
$mensaje = match($res['error']) {
    'csrf'        => 'Error de seguridad. Intenta de nuevo.',
    'credenciales'=> 'Usuario o contraseña incorrectos.',
    'bloqueo'     => 'Demasiados intentos fallidos. Intenta en unos minutos.',
    'metodo'      => 'Método inválido.',
    default       => 'Ocurrió un error inesperado.'
};

// Cerrar conexión ANTES de redirigir
$conn->close();

header('Location: /front-end/frames/admin/login.php?m=' . urlencode($mensaje));
exit;
