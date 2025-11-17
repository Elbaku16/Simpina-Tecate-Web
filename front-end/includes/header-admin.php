<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$esAdmin = isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
$usuarioActivo = $esAdmin && isset($_SESSION['usuario']) ? $_SESSION['usuario'] : null;

$currentScript = $_SERVER['SCRIPT_NAME'] ?? '';
$isLoginPage = (strpos($currentScript, '/frames/admin/login.php') !== false);
?>
<header class="header">
  <div class="header-izq">
    <img src="/SIMPINNA/front-end/assets/img/global/logo-simpinna.png"
         alt="Logo SIMPINNA" class="logo-simpinna">

    <div class="titulo-header">
      <span class="titulo-header">Panel Administrativo</span>

      <?php if ($esAdmin): ?>
        <a href="/SIMPINNA/back-end/routes/auth/logout.php" class="btn-logout">Cerrar sesión</a>
      <?php elseif ($isLoginPage): ?>
        <a href="/SIMPINNA/front-end/frames/inicio/inicio.php" class="btn-exit">Salir</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="header-der">
    <img src="/SIMPINNA/front-end/assets/img/global/gobierno-logo.png"
         alt="Logo Gobierno Tecate" class="logo-gobierno">
  </div>
</header>
