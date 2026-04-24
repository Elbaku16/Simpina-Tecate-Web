<?php
declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

try {

    $baseBackend = __DIR__ . '/../../';

    if (!file_exists($baseBackend . 'auth/verificar-sesion.php')) {
        throw new Exception("Error interno: No se encuentra el sistema de autenticación.");
    }

    require_once $baseBackend . 'auth/verificar-sesion.php';
    requerir_admin();

    
    $nivel = $_GET['nivel'] ?? '';

    if (empty($nivel)) {
        throw new Exception('Nivel educativo no especificado');
    }

    
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    

    $baseUrl = "{$protocol}://{$host}/simpinna";

    $rutaEncuesta = $baseUrl . '/front-end/frames/encuestas/demo-encuestas.php';


    $urls = [
        'preescolar'   => "{$rutaEncuesta}?nivel=preescolar",
        'primaria'     => "{$rutaEncuesta}?nivel=primaria",
        'secundaria'   => "{$rutaEncuesta}?nivel=secundaria",
        'preparatoria' => "{$rutaEncuesta}?nivel=preparatoria",
    ];

    if (!isset($urls[$nivel])) {
        throw new Exception('Nivel inválido. Opciones válidas: preescolar, primaria, secundaria, preparatoria.');
    }

    $urlFinal = $urls[$nivel];

  
    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&format=png&margin=10&data=' . urlencode($urlFinal);

    echo json_encode([
        'success'      => true,
        'qr_url'       => $qrUrl,
        'encuesta_url' => $urlFinal,
        'nivel'        => ucfirst($nivel)
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}
exit;
?>