<?php
if (defined('BASE_URL')) return;

require_once __DIR__ . '/../../back-end/core/env_loader.php';

$envFile = __DIR__ . '/../../.env';
if (is_file($envFile)) {
    cargarEnv($envFile);
}

$httpHost = strtolower((string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
$host = parse_url('http://' . $httpHost, PHP_URL_HOST) ?: $httpHost;
$isLocalhost = in_array($host, ['localhost', '127.0.0.1', '::1'], true);
$configuredBaseUrl = getenv('APP_BASE_URL');
$baseUrl = $configuredBaseUrl !== false
    ? rtrim((string) $configuredBaseUrl, '/')
    : ($isLocalhost ? '' : '/simpinna');

define('BASE_URL',    $baseUrl);
define('ASSETS_URL',  BASE_URL . '/front-end/assets/');
define('CSS_URL',     ASSETS_URL . 'css/');
define('IMG_URL',     ASSETS_URL . 'img/');
define('JS_URL',      BASE_URL . '/front-end/scripts/');
define('FRAMES_URL',  BASE_URL . '/front-end/frames/');
define('API_URL',     BASE_URL . '/back-end/routes/');
define('UPLOADS_URL', BASE_URL . '/uploads/');
?>
