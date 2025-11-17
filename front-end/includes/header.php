<?php
  $currentPage = $_SERVER['REQUEST_URI'];
?>

<header class="header">
  <div class="header-izq">
      <img src="/SIMPINNA/front-end/assets/img/global/logo-simpinna.png" 
           alt="Logo SIMPINNA" class="logo-simpinna">
  </div>

  <!-- Menú Desktop -->
  <nav class="header-nav">
    <ul>    
        <li>
          <a href="/SIMPINNA/front-end/frames/inicio/inicio.php"
             class="<?php echo (strpos($currentPage, 'inicio.php') !== false) ? 'active' : ''; ?>">
             Inicio
          </a>
        </li>
        <li>
          <a href="/SIMPINNA/front-end/frames/inicio/seleccion-encuesta.php"
             class="<?php echo (strpos($currentPage, 'encuesta.php') !== false || strpos($currentPage, 'demo-encuestas.php') !== false) ? 'active' : ''; ?>">
             Encuesta
          </a>
        </li>
        <li>
          <a href="/SIMPINNA/front-end/frames/inicio/contacto.php"
             class="<?php echo (strpos($currentPage, 'contacto.php') !== false) ? 'active' : ''; ?>">
             Contáctanos
          </a>
        </li>
        <li>
          <a href="/SIMPINNA/front-end/frames/inicio/ayuda.php"
             class="<?php echo (strpos($currentPage, 'ayuda.php') !== false) ? 'active' : ''; ?>">
             Ayuda
          </a>
        </li>
    </ul>
  </nav>

  <!-- Botón Hamburguesa (SVG) -->
  <button class="menu-toggle" id="menuToggle" aria-label="Abrir menú">
    <svg class="hamburger-icon" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path class="line line-top" d="M5 8 L27 8" stroke="#611232" stroke-width="2.5" stroke-linecap="round"/>
      <path class="line line-middle" d="M5 16 L27 16" stroke="#611232" stroke-width="2.5" stroke-linecap="round"/>
      <path class="line line-bottom" d="M5 24 L27 24" stroke="#611232" stroke-width="2.5" stroke-linecap="round"/>
    </svg>
  </button>
</header>

<!-- Menú Mobile Lateral -->
<div class="mobile-menu" id="mobileMenu">
  <div class="mobile-menu-header">
    <button class="menu-close" id="menuClose" aria-label="Cerrar menú">
      <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M7 7 L21 21" stroke="#611232" stroke-width="2.5" stroke-linecap="round"/>
        <path d="M21 7 L7 21" stroke="#611232" stroke-width="2.5" stroke-linecap="round"/>
      </svg>
    </button>
  </div>
  
  <nav class="mobile-nav">
    <ul>
      <li>
        <a href="/SIMPINNA/front-end/frames/inicio/inicio.php"
           class="<?php echo (strpos($currentPage, 'inicio.php') !== false) ? 'active' : ''; ?>">
           <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
             <path d="M3 10 L10 3 L17 10 L17 17 L3 17 Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
           </svg>
           Inicio
        </a>
      </li>
      <li>
        <a href="/SIMPINNA/front-end/frames/inicio/seleccion-encuesta.php"
           class="<?php echo (strpos($currentPage, 'encuesta.php') !== false || strpos($currentPage, 'demo-encuestas.php') !== false) ? 'active' : ''; ?>">
           <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
             <path d="M6 3 L14 3 L14 17 L6 17 Z M9 7 L11 7 M9 10 L13 10 M9 13 L12 13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
           </svg>
           Encuesta
        </a>
      </li>
      <li>
        <a href="/SIMPINNA/front-end/frames/inicio/contacto.php"
           class="<?php echo (strpos($currentPage, 'contacto.php') !== false) ? 'active' : ''; ?>">
           <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
             <path d="M3 6 L17 6 L17 14 L3 14 Z M3 6 L10 11 L17 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
           </svg>
           Contáctanos
        </a>
      </li>
      <li>
        <a href="/SIMPINNA/front-end/frames/inicio/ayuda.php"
           class="<?php echo (strpos($currentPage, 'ayuda.php') !== false) ? 'active' : ''; ?>">
           <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
             <circle cx="10" cy="10" r="7" stroke="currentColor" stroke-width="1.5"/>
             <path d="M10 13 L10 13.5 M10 7 C10 7 10 9 10 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
           </svg>
           Ayuda
        </a>
      </li>
    </ul>
  </nav>
</div>

<!-- Overlay para cerrar menú -->
<div class="menu-overlay" id="menuOverlay"></div>