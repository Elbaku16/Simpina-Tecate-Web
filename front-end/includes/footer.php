<?php
$currentScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
$isInicio      = ($currentScript === 'inicio.php' || $currentScript === 'index.php');
?>
<footer class="footer-simpinna">
  <div class="footer-container">

    <div class="footer-logo">
      <img
        src="/front-end/assets/img/global/tkt-pueblo-magico.png"
        alt="Tecate Pueblo Mágico"
        class="logo-tecate"
      >
    </div>

    <div class="footer-text">
      <p>
        ©SIMPINNA | Sistema Municipal de Protección Integral de los Derechos de las<br>
        Niñas, Niños y Adolescentes
      </p>

      <?php if ($isInicio): ?>
        <div class="admin-link-wrapper">
          <a href="/front-end/frames/admin/login.php">
            <i class="fa-solid fa-user-gear"></i>
            Simpinna — Acceso administrativo
          </a>
        </div>
      <?php endif; ?>
    </div>

    <div class="footer-spacer" aria-hidden="true"></div>

  </div>
</footer>