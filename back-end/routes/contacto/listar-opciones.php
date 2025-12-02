<?php
declare(strict_types=1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/core/bootstrap_session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/database/conexion-db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/controllers/ContactoController.php';

header('Content-Type: application/json; charset=utf-8');

$controller = new ContactoController();
$data = $controller->obtenerDatosFormulario();

// CERRAR conexión ANTES de responder
$conn->close();

echo json_encode([
    'ok' => true,
    'niveles'         => $data['niveles'],
    'escuelasPorNivel'=> $data['escuelas']
], JSON_UNESCAPED_UNICODE);
exit;
