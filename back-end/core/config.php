<?php

require_once __DIR__ . '/../../front-end/includes/config.php';

define('BASE_PATH', dirname(__DIR__, 2));


function redirect($path)
{
    header("Location: " . BASE_URL . $path);
    exit;
}
ini_set('display_errors', 0);
error_reporting(0);

?>
