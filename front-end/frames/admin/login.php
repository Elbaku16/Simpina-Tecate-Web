<?php
session_start();

if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
    header('Location: ../panel/panel-admin.php');
    exit;
}

$loginError = '';

if (isset($_SESSION['login_error'])) {
    $loginError = (string) $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $credencialesValidas = [
        'admin' => '123'
    ];

    if (isset($credencialesValidas[$usuario]) && hash_equals($credencialesValidas[$usuario], $password)) {
        session_regenerate_id(true);
        $_SESSION['rol'] = 'admin';
        $_SESSION['usuario'] = $usuario;

        header('Location: ../panel/panel-admin.php');
        exit;
    }

    $loginError = 'Usuario o contraseña incorrectos.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso administrativo</title>
    <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
    <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/layout.css">
    <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/admin/admin.css">
</head>
<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/header-admin.php'; ?>

    <main class="login-container">
        <h1 class="login-title">Inicio de sesión</h1>
        <?php if (!empty($loginError)): ?>
            <div class="alert alert-danger" role="alert" style="margin-bottom: 1.5rem;">
                <?php echo htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form class="login-form" method="POST" action="">
            <div class="form-group">
                <label for="usuario">Usuario</label>
                <input type="text" id="usuario" name="usuario" required autocomplete="username" value="<?= isset($usuario) ? htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8') : '' ?>">
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn-primary btn-login">Iniciar sesión</button>
            <p class="login-note">Solo personal autorizado SIMPINNA</p>
        </form>
    </main>

    <footer>
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/footer.php'; ?>
    </footer>
</body>
</html>
