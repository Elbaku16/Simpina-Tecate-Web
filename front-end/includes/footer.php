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

      <!-- Sección de Autores -->
      <div class="footer-autores">
        <p class="autores-title">Desarrollado por:</p>
        <p class="autores-institucion">Universidad Autónoma de Baja California: FCITEC</p>
        <div class="autores-grid">
          <span class="autor">Oscar Brito</span>
          <span class="autor">Lenin Cortez</span>
          <span class="autor">Jesus Martinez</span>
          <span class="autor">Marshall Rodriguez</span>
          <span class="autor">Jofiel Ramírez</span>
        </div>
      </div>

    </div>

    <div class="footer-spacer" aria-hidden="true"></div>

  </div>
</footer>

<style>
  /* Estilos para la sección de autores */
  .footer-autores {
    margin-top: 1.5rem;
    padding-top: 1.25rem;
    border-top: 2px solid rgba(214, 189, 85, 0.4);
    text-align: center;
  }

  .autores-title {
    font-size: 0.9rem;
    color: #6b2e2e;
    margin-bottom: 0.5rem;
    font-weight: 600;
    letter-spacing: 0.5px;
  }

  .autores-institucion {
    font-size: 1rem;
    color: #d6bd55;
    font-weight: 700;
    margin-bottom: 1rem;
    letter-spacing: 1px;
  }

  .autores-grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.75rem 1.5rem;
    padding: 0 1rem;
  }

  .autor {
    font-size: 0.95rem;
    color: #6b2e2e;
    font-weight: 600;
    position: relative;
    padding: 0.25rem 0.5rem;
    transition: all 0.3s ease;
  }

  .autor::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, #d6bd55, #6b2e2e);
    transition: width 0.3s ease;
  }

  .autor:hover::after {
    width: 100%;
  }

  /* Responsive para tablets */
  @media (max-width: 768px) {
    .autores-grid {
      gap: 0.5rem 1rem;
    }

    .autor {
      font-size: 0.9rem;
    }

    .autores-institucion {
      font-size: 0.95rem;
    }
  }

  /* Responsive para móviles */
  @media (max-width: 480px) {
    .footer-autores {
      padding-top: 1rem;
    }

    .autores-title {
      font-size: 0.85rem;
    }

    .autores-institucion {
      font-size: 0.9rem;
      margin-bottom: 0.75rem;
    }

    .autores-grid {
      flex-direction: column;
      gap: 0.4rem;
      align-items: center;
    }

    .autor {
      font-size: 0.85rem;
      width: auto;
    }
  }
</style>