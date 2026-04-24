<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once __DIR__ . '/../../../back-end/auth/verificar-sesion.php';
require_once __DIR__ . '/../../includes/config.php';

if (usuario_autenticado() && rol_es('admin')) {
    header('Location: ' . FRAMES_URL . 'panel/panel-admin.php');
    exit;
}

require_once __DIR__ . '/../../../back-end/controllers/AuthController.php';

try {
    $auth = new AuthController();
    $csrf_token = $auth->generarTokenCSRF('login_admin');
} catch (Exception $e) {
    die("Error al iniciar el sistema de autenticación: " . $e->getMessage());
}

$mensaje = $_GET['m'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIMPINNA | Inicio de sesión</title> 
  <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
  
  <link rel="stylesheet" href="../../assets/css/global/layout.css">
  <link rel="stylesheet" href="../../assets/css/admin/admin.css">
</head>

<body>

<?php 

include __DIR__ . '/../../includes/header-admin.php'; 
?>

<main class="login-container">
    
    <div class="login-back-wrapper">
        <a href="../inicio/inicio.php" class="btn-back-home" title="Regresar al inicio">
            <i class="fa-solid fa-angle-left"></i>Regresar
        </a>
    </div>

    <?php if (!empty($mensaje)): ?>
      <div class="alert alert-danger" role="alert" style="margin-bottom: 1.5rem;">
        <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
      </div>
    <?php endif; ?>

    <form class="login-form" method="POST" action="<?php echo API_URL; ?>auth/login.php">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token); ?>">

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
    <?php 
    include __DIR__ . '/../../includes/footer-admin.php'; 
    ?>
</footer>

</body>
</html>