<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

$currentFile = basename($_SERVER['SCRIPT_NAME'] ?? '');
$isLoginPage = ($currentFile === 'login.php');
?>
<header class="header">
  <div class="header-izq">
    <img
      src="/SIMPINNA/front-end/assets/img/global/logo-simpinna.png"
      alt="Logo SIMPINNA"
      class="logo-simpinna"
    >
    <div class="titulo-header">
      <span>Panel Administrativo</span>

      <?php if ($isLoginPage): ?>
        <a href="/SIMPINNA/front-end/frames/inicio/inicio.php" class="btn-exit">Salir</a>
      <?php else: ?>
        <a href="/SIMPINNA/front-end/frames/panel-admin/logout.php" class="btn-logout">Cerrar sesión</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="header-der">
    <img
      src="/SIMPINNA/front-end/assets/img/global/logo-gobierno.png"
      alt="Logo Gobierno"
      class="logo-gobierno"
    >
  </div>
</header>
