<?php
session_start();

$validUser = 'admin';
$validPass = '123';

// Recoger datos del POST
$user = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$pass = isset($_POST['password']) ? trim($_POST['password']) : '';

if ($user === $validUser && $pass === $validPass) {
    $_SESSION['user'] = $user;
    $_SESSION['logged_in'] = true;

    header('Location: /SIMPINNA/front-end/frames/panel-admin/admin-encuestas.php');
    exit;
} else {
    $_SESSION['login_error'] = 'Usuario o contraseña incorrectos';
    header('Location: /SIMPINNA/front-end/frames/panel-admin/login.php');
    exit;
}
