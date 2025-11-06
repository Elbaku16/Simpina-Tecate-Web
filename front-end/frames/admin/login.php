<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/back-end/auth/verificar-sesion.php';

// Si ya hay sesión activa, redirigir al panel
if (usuario_autenticado() && rol_es('admin')) {
    header('Location: /SIMPINNA/front-end/frames/panel/panel-admin.php');
    exit;
}

// Generar token CSRF
$csrf_token = generar_csrf('login_admin');

// Mostrar mensajes de error opcionales
$error = '';
if (isset($_GET['e'])) {
    switch ($_GET['e']) {
        case 'csrf':
            $error = 'Error de seguridad. Intenta de nuevo.';
            break;
        case 'credenciales':
            $error = 'Usuario o contraseña incorrectos.';
            break;
        case 'bloqueo':
            $error = 'Demasiados intentos fallidos. Intenta en unos minutos.';
            break;
        default:
            $error = '';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIMPINNA | Panel administrativo</title>
  <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/layout.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/admin/admin.css">
</head>
<body>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/header-admin.php'; ?>

  <main class="login-container">
    <h1 class="login-title">Inicio de sesión</h1>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" role="alert" style="margin-bottom: 1.5rem;">
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
      </div>
    <?php endif; ?>

    <form class="login-form" method="POST" action="/SIMPINNA/back-end/auth/login.php">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">

      <div class="form-group">
        <label for="usuario">Usuario</label>
        <input type="text" id="usuario" name="usuario" required autocomplete="username">
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
