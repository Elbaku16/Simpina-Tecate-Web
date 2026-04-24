<?php



define('BASE_URL', '/simpinna');


define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . BASE_URL);


function redirect($path)
{
    header("Location: " . BASE_URL . $path);
    exit;
}
ini_set('display_errors', 0);
error_reporting(0);

?>
