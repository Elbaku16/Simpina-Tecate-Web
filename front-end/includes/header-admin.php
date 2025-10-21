<header class="header">
  <div class="header-izq">
      <img src="/SIMPINNA/front-end/assets/img/global/logo-simpinna.png" 
           alt="Logo SIMPINNA" class="logo-simpinna">

    <div class="titulo-header">
      <span class="titulo-header">Panel Administrativo</span>
       <?php if (isset($_SESSION['user'])): ?>
          <a href="/SIMPINNA/front-end/frames/panel-admin/logout.php" class="btn-logout">Cerrar sesión</a>
        <?php endif; ?>
    </div>
  </div>



  <div class="header-der">
      <img src="/SIMPINNA/front-end/assets/img/global/gobierno-logo.png" 
           alt="Logo Gobierno Tecate" class="logo-gobierno">
  </div>
</header>
