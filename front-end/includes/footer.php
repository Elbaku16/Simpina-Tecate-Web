<?php
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

      <?php if ($isInicio): ?>
        <div style="margin-top: 0.5rem; font-size: 0.75rem; color: #666; text-align: center;">
          <a href="/SIMPINNA/front-end/frames/admin/login.php" style="color: inherit; text-decoration: none;">
            <i class="fa-solid fa-user-gear"></i>
            Simpinna — Acceso administrativo
          </a>
        </div>
      <?php endif; ?>
    </div>

    <!-- Espaciador para equilibrar el bloque central -->
    <div class="footer-spacer" aria-hidden="true"></div>

  </div>
</footer>
