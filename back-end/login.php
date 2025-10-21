<?php
session_start();

// Usuario y contraseña de prueba
$validUser = 'admin';
$validPass = '123';

// Recoger datos del POST
$user = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$pass = isset($_POST['password']) ? trim($_POST['password']) : '';

// Validar
if ($user === $validUser && $pass === $validPass) {
    // Credenciales válidas: crear sesión y redirigir
    $_SESSION['user'] = $user;
    $_SESSION['logged_in'] = true;

    header('Location: /SIMPINNA/front-end/frames/panel-admin/admin-encuestas.php');
    exit;
} else {
    // Credenciales inválidas: volver al login con mensaje (puedes mostrarlo en la UI)
    $_SESSION['login_error'] = 'Usuario o contraseña incorrectos';
    header('Location: /SIMPINNA/front-end/frames/login/login.php');
    exit;
}
