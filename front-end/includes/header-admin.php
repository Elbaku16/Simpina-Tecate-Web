<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$esAdmin = isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
$usuarioActivo = $esAdmin && isset($_SESSION['usuario']) ? $_SESSION['usuario'] : null;

$currentScript = $_SERVER['SCRIPT_NAME'] ?? '';
$isLoginPage = (strpos($currentScript, '/frames/admin/login.php') !== false);
?>

<header class="header header-admin">
  <div class="header-izq">
    <img src="/front-end/assets/img/global/logo-simpinna.png"
         alt="Logo SIMPINNA" class="logo-simpinna">

    <div class="admin-info">
      <span class="admin-title">Panel Administrativo</span>

      <?php if ($esAdmin): ?>
        <a href="/back-end/routes/auth/logout.php" class="btn-logout">
            <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar sesión
        </a>
      <?php elseif ($isLoginPage): ?>
        <a href="/front-end/frames/inicio/inicio.php" class="btn-exit">
            <i class="fa-solid fa-door-open"></i> Salir
        </a>
      <?php endif; ?>
    </div>
  </div>

  <div class="header-der">
    <img src="/front-end/assets/img/global/gobierno-logo.png"
         alt="Logo Gobierno Tecate" class="logo-gobierno">
  </div>
  
  <script src="https://kit.fontawesome.com/cba4ea3b6f.js" crossorigin="anonymous"></script>
</header>