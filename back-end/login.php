<?php
session_start();

$validUser = 'admin';
$validPass = '123';

// Recoger datos del POST
$user = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$pass = isset($_POST['password']) ? trim($_POST['password']) : '';

if ($user === $validUser && hash_equals($validPass, $pass)) {
    session_regenerate_id(true);
    $_SESSION['rol'] = 'admin';
    $_SESSION['usuario'] = $user;

    header('Location: /SIMPINNA/front-end/frames/panel/panel-admin.php');
    exit;
}

$_SESSION['login_error'] = 'Usuario o contraseña incorrectos';
header('Location: /SIMPINNA/front-end/frames/admin/login.php');
exit;
