<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/auth/verificar-sesion.php';
requerir_admin();

header('Content-Type: application/json; charset=utf-8');

$nivel = $_GET['nivel'] ?? '';

if (empty($nivel)) {
    echo json_encode(['success' => false, 'error' => 'Nivel no especificado']);
    exit;
}

// ============================================================
// CONFIGURACIÓN DEL DOMINIO PÚBLICO
// ============================================================
// Cambia este valor por tu dominio REAL cuando estés en producción
// Ejemplos:
// - 'https://simpina.gob.mx'
// - 'https://www.simpinna.com'
// - 'https://tusitio.com'
// 
// IMPORTANTE: NO uses localhost, 127.0.0.1 o IP local
// ============================================================
$DOMINIO_PUBLICO = 'https://simpina.gob.mx'; // <--- CAMBIA ESTO POR TU DOMINIO REAL

// Si no has configurado el dominio, detectamos automáticamente (solo para desarrollo local)
if ($DOMINIO_PUBLICO === 'https://simpina.gob.mx') {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $DOMINIO_PUBLICO = "{$protocol}://{$host}";
}

// Mapeo de niveles a URLs
$urls = [
    'preescolar'   => "{$DOMINIO_PUBLICO}/front-end/frames/encuestas/preescolar.php",
    'primaria'     => "{$DOMINIO_PUBLICO}/front-end/frames/encuestas/demo-encuestas.php?nivel=primaria",
    'secundaria'   => "{$DOMINIO_PUBLICO}/front-end/frames/encuestas/secundaria.php",
    'preparatoria' => "{$DOMINIO_PUBLICO}/front-end/frames/encuestas/preparatoria.php",
];

if (!isset($urls[$nivel])) {
    echo json_encode(['success' => false, 'error' => 'Nivel inválido']);
    exit;
}

$url = $urls[$nivel];

// Usar API de QR Code con mayor tamaño y mejor calidad
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&format=png&data=' . urlencode($url);

echo json_encode([
    'success' => true,
    'qr_url'  => $qrUrl,
    'encuesta_url' => $url,
    'nivel' => ucfirst($nivel)
], JSON_UNESCAPED_SLASHES);
exit;