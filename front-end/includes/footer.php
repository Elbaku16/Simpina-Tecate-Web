<?php
// sesión segura en includes
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$isAdmin  = isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';

/**
 * Mostrar el enlace de acceso administrativo SOLO en inicio.php.
 * - Usamos SCRIPT_NAME para obtener el nombre del script ejecutado.
 * - Funciona aunque el footer se incluya desde rutas distintas.
 */
$currentScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
$isInicio      = ($currentScript === 'inicio.php');
?>
<footer class="footer-simpinna">
  <div class="footer-container">

    <div class="footer-logo">
      <img
        src="/SIMPINNA/front-end/assets/img/global/tkt-pueblo-magico.png"
        alt="Tecate Pueblo Mágico"
        class="logo-tecate"
      >
    </div>

    <div class="footer-text">
      <p>
        ©SIMPINNA | Sistema Municipal de Protección Integral de los Derechos de las<br>
        Niñas, Niños y Adolescentes
      </p>
      <div style="margin-top: 0.5rem; font-size: 0.75rem; color: #666; text-align: center;">
        <a href="/SIMPINNA/front-end/frames/admin/login.php" style="color: inherit; text-decoration: none;">
          © 2025 Ayuntamiento de Tecate — Acceso administrativo
        </a>
      </div>
    </div>

    <!-- spacer para equilibrar el centrado del bloque de texto -->
    <div class="footer-spacer" aria-hidden="true"></div>

  </div>
</footer>
