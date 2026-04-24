<?php
if (defined('BASE_URL')) return;

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isLocalhost = (strpos($host, 'localhost') !== false || $host === '127.0.0.1');

define('BASE_URL',    $isLocalhost ? '' : '/simpinna');
define('ASSETS_URL',  BASE_URL . '/front-end/assets/');
define('CSS_URL',     ASSETS_URL . 'css/');
define('IMG_URL',     ASSETS_URL . 'img/');
define('JS_URL',      BASE_URL . '/front-end/scripts/');
define('FRAMES_URL',  BASE_URL . '/front-end/frames/');
define('API_URL',     BASE_URL . '/back-end/routes/');
define('UPLOADS_URL', BASE_URL . '/uploads/');
?>
