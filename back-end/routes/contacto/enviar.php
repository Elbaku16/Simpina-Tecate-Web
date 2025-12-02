<?php
declare(strict_types=1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/core/bootstrap_session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/database/conexion-db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/controllers/ContactoController.php';

$controller = new ContactoController();

// El controlador se encarga de validar, insertar y devolver ok=1/0
$result = $controller->procesarFormulario($_POST);

// CERRAR conexión ANTES de redirigir
$conn->close();

// Redirigir de vuelta al formulario
if ($result['ok']) {
    header("Location: /front-end/frames/inicio/contacto.php?ok=1");
} else {
    header("Location: /front-end/frames/inicio/contacto.php?ok=0");
}
exit;
