<?php
session_start();


function redirectTo(string $path): void
{
    if (!headers_sent()) {
        header('Location: ' . $path);
        exit;
    }

    echo '<script>window.location.href=' . json_encode($path) . ';</script>';
    exit;
}

if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
    redirectTo('front-end/frames/panel/panel-admin.php');
}

redirectTo('front-end/frames/inicio/inicio.php');

