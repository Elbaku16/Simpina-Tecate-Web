<?php
require_once __DIR__ . '/../../controllers/ContactoController.php';

header('Content-Type: application/json');

$controller = new ContactoController();
echo json_encode($controller->obtenerDatosFormulario());
exit;
