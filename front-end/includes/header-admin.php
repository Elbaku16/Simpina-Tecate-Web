<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$esAdmin = isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
$textoUsuario = $esAdmin ? ($_SESSION['nombre_completo'] ?? $_SESSION['usuario'] ?? 'Administrador') : '';
$tituloCentral = (strpos($_SERVER['SCRIPT_NAME'] ?? '', 'login.php') !== false) ? 'Inicio de sesión' : 'Panel Administrativo';
?>

<header class="header header-admin">
  
  <div class="header-izq">
    <img src="/front-end/assets/img/global/logo-simpinna.png" alt="Logo SIMPINNA" class="logo-simpinna">
    
    <?php if ($esAdmin): ?>
    <div class="admin-info-desktop">
        <span class="user-label">
            <i class="fa-solid fa-user-circle"></i> 
            <?php echo htmlspecialchars($textoUsuario); ?>
        </span>
        <a href="/back-end/routes/auth/logout.php" class="btn-logout">
            <i class="fa-solid fa-sign-out-alt"></i> Salir
        </a>
    </div>
    <?php endif; ?>
  </div>

  <div class="header-center">
      <h1 class="center-title"><?php echo $tituloCentral; ?></h1>
  </div>

  <div class="header-der">
    <button class="menu-toggle" id="menuToggle" aria-label="Abrir menú">
      <svg class="hamburger-icon" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M5 8 L27 8" stroke="#611232" stroke-width="2.5" stroke-linecap="round"/>
        <path d="M5 16 L27 16" stroke="#611232" stroke-width="2.5" stroke-linecap="round"/>
        <path d="M5 24 L27 24" stroke="#611232" stroke-width="2.5" stroke-linecap="round"/>
      </svg>
    </button>

    <img src="/front-end/assets/img/global/gobierno-logo.png" alt="Logo Gobierno Tecate" class="logo-gobierno">
  </div>
  
  <script src="https://kit.fontawesome.com/cba4ea3b6f.js" crossorigin="anonymous"></script>
</header>

<div class="mobile-menu" id="mobileMenu">
  <div class="mobile-menu-header">
    <button class="menu-close" id="menuClose" aria-label="Cerrar menú">
      <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M7 7 L21 21" stroke="#611232" stroke-width="2.5" stroke-linecap="round"/>
        <path d="M21 7 L7 21" stroke="#611232" stroke-width="2.5" stroke-linecap="round"/>
      </svg>
    </button>
  </div>
  
  <div class="mobile-content">
      <h2 class="mobile-title"><?php echo $tituloCentral; ?></h2>
      <hr class="mobile-divider">

      <?php if ($esAdmin): ?>
        <div class="mobile-user-info">
            <span class="mobile-user-name">
                <i class="fa-solid fa-user-circle"></i> 
                <?php echo htmlspecialchars($textoUsuario); ?>
            </span>
            <a href="/back-end/routes/auth/logout.php" class="mobile-btn-logout">
                <i class="fa-solid fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>
      <?php endif; ?>
  </div>
</div>

<div class="menu-overlay" id="menuOverlay"></div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggle = (id) => document.getElementById(id);
    const toggleMenu = () => {
        toggle('mobileMenu').classList.toggle('active');
        toggle('menuOverlay').classList.toggle('active');
    };
    ['menuToggle', 'menuClose', 'menuOverlay'].forEach(id => {
        if(toggle(id)) toggle(id).addEventListener('click', toggleMenu);
    });
});
</script>