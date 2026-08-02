<?php
declare(strict_types=1);

$baseBackend = __DIR__ . '/../../';
require_once $baseBackend . 'auth/verificar-sesion.php';
requerir_admin();
if (!tiene_permiso('ver_resultados')) {
    http_response_code(403);
    exit('Permiso denegado.');
}
require_once $baseBackend . 'database/conexion-db.php';
require_once $baseBackend . 'helpers/DibujoHelper.php';

$id = (int) ($_GET['id_respuesta'] ?? 0);
$stmt = $conn->prepare(
    'SELECT dibujo_ruta,dibujo_storage,dibujo_objeto FROM respuestas_usuario WHERE id_respuesta_usuario=? LIMIT 1'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$row) {
    http_response_code(404);
    exit('Dibujo no encontrado.');
}

header('Cache-Control: private, no-store, max-age=0');
header('X-Content-Type-Options: nosniff');

if ($row['dibujo_storage'] === 'r2' && $row['dibujo_objeto']) {
    $url = DibujoHelper::urlTemporal('r2', $row['dibujo_objeto'], 60);
    if (!$url) {
        http_response_code(404);
        exit('Dibujo no disponible.');
    }
    header('Location: ' . $url, true, 302);
    exit;
}

$path = $row['dibujo_storage'] === 'local' && $row['dibujo_objeto']
    ? DibujoHelper::rutaLocal('local', $row['dibujo_objeto'])
    : DibujoHelper::rutaLegacy($row['dibujo_ruta']);
if (!$path || !is_file($path)) {
    http_response_code(404);
    exit('Dibujo no disponible.');
}
header('Content-Type: image/png');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
