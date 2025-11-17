<?php
declare(strict_types=1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/back-end/core/bootstrap_session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/back-end/controllers/ContactoController.php';

header('Content-Type: application/json; charset=utf-8');

$controller = new ContactoController();
$data = $controller->obtenerDatosFormulario();

echo json_encode([
    'ok' => true,
    'niveles' => $data['niveles'],
    'escuelasPorNivel' => $data['escuelas']
], JSON_UNESCAPED_UNICODE);
exit;
