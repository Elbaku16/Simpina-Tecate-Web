<?php
declare(strict_types=1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/core/bootstrap_session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/controllers/EditarController.php';

header("Content-Type: application/json; charset=utf-8");

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode(["success" => false, "error" => "JSON inválido"]);
    exit;
}

$nivel      = $data['nivel']      ?? null;
$preguntas  = $data['preguntas']  ?? [];
$eliminadas = $data['eliminadas'] ?? [];

if (!$nivel || !is_array($preguntas)) {
    echo json_encode(["success" => false, "error" => "Datos incompletos"]);
    exit;
}


$controller = new EditarController();
$respuesta  = $controller->guardarCambios($nivel, $preguntas, $eliminadas);

global $conn;
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}

echo json_encode($respuesta);
exit;