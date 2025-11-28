<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Anteriormente solo verificaba si era el rol 'admin'. Ahora verifica si CUALQUIER rol está presente.
$esUsuarioAdmin = isset($_SESSION['rol']);

// Lógica de nombre de usuario (solo para admins logueados)
$textoUsuario = 'Administrador';
if ($esUsuarioAdmin) { 
    if (isset($_SESSION['nombre_completo']) && !empty($_SESSION['nombre_completo'])) {
        $textoUsuario = $_SESSION['nombre_completo']; 
    } elseif (isset($_SESSION['usuario']) && !empty($_SESSION['usuario'])) {
        $textoUsuario = $_SESSION['usuario'];
    }
}

$currentScript = $_SERVER['SCRIPT_NAME'] ?? '';
$isLoginPage = (strpos($currentScript, '/frames/admin/login.php') !== false);
$tituloCentral = $isLoginPage ? 'Inicio de sesión' : 'Panel Administrativo';
?>

<header class="header header-admin">
  
  <div class="header-izq">
    <img src="/front-end/assets/img/global/logo-simpinna.png"
         alt="Logo SIMPINNA" class="logo-simpinna">

    <div class="admin-info">
      <?php if ($esUsuarioAdmin): ?>
        <span class="user-label">
            <i class="fa-solid fa-user"></i> 
            <?php echo htmlspecialchars($textoUsuario); ?>
        </span>
        <a href="/back-end/routes/auth/logout.php" class="btn-logout">
            <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar sesión
        </a>
      <?php endif; ?>
      </div>
  </div>

  <div class="header-center">
      <h1 class="center-title"><?php echo $tituloCentral; ?></h1>
  </div>

  <div class="header-der">
    <img src="/front-end/assets/img/global/gobierno-logo.png"
         alt="Logo Gobierno Tecate" class="logo-gobierno">
  </div>
  
  <script src="https://kit.fontawesome.com/cba4ea3b6f.js" crossorigin="anonymous"></script>
</header>