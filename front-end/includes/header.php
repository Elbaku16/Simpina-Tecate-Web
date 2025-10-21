<?php
  $currentPage = $_SERVER['REQUEST_URI'];
?>

<header class="header">
  <div class="header-izq">
      <img src="/SIMPINNA/front-end/assets/img/global/logo-simpinna.png" 
           alt="Logo SIMPINNA" class="logo-simpinna">
  </div>

  <nav class="header-nav">
    <ul>    
        <li>
          <a href="/SIMPINNA/front-end/frames/inicio/inicio.php"
             class="<?php echo (strpos($currentPage, 'inicio.php') !== false) ? 'active' : ''; ?>">
             Inicio
          </a>
        </li>
        <li>
          <a href="/SIMPINNA/front-end/frames/inicio/encuesta.php"
             class="<?php echo (strpos($currentPage, 'encuesta.php') !== false) ? 'active' : ''; ?>">
             Encuesta
          </a>
        </li>
        <li>
          <a href="/SIMPINNA/front-end/frames/contacto/contacto.php"
             class="<?php echo (strpos($currentPage, 'contacto.php') !== false) ? 'active' : ''; ?>">
             Contáctanos
          </a>
        </li>
        <li>
          <a href="/SIMPINNA/front-end/frames/ayuda/ayuda.php"
             class="<?php echo (strpos($currentPage, 'ayuda.php') !== false) ? 'active' : ''; ?>">
             Ayuda
          </a>
        </li>
    </ul>
  </nav>

  <div class="header-der">
      <img src="/SIMPINNA/front-end/assets/img/global/gobierno-logo.png" 
           alt="Logo Gobierno Tecate" class="logo-gobierno">
  </div>
</header>
