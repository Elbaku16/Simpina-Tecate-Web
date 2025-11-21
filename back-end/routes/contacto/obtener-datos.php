<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/database/conexion-db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/controllers/ContactoController.php';

header('Content-Type: application/json');

$controller = new ContactoController();
$data = $controller->obtenerDatosFormulario();

// Cerrar conexión ANTES de responder
$conn->close();

echo json_encode($data);
exit;
