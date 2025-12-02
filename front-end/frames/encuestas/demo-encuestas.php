<?php
$nivel = $_GET['nivel'] ?? 'primaria';
$nivelTitulo = ucfirst($nivel);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIMPINNA | Encuesta <?= htmlspecialchars($nivelTitulo) ?></title>

  <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
  <link rel="stylesheet" href="/front-end/assets/css/global/layout.css">
  <link rel="stylesheet" href="/front-end/assets/css/global/header-responsive.css">
  
  <link rel="stylesheet" href="/front-end/assets/css/encuestas/encuestas.css">

  <style>
    #contenedorPreguntas {
      visibility: hidden;
      min-height: 300px;
    }
    #contenedorPreguntas.visible {
      visibility: visible;
      animation: fadeIn 0.5s ease;
    }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    
    #loaderEncuesta {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      height: 300px;
      font-size: 1.5rem;
      font-weight: 600;
      color: #611232;
      gap: 1rem;
    }
    .spinner {
      width: 40px; height: 40px;
      border: 4px solid #f3f3f3;
      border-top: 4px solid #611232;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
  </style>
</head>

<body>
<header>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/front-end/includes/header.php'); ?>
</header>

<main>
  <div class="encuesta-wrap">
    
    <div class="encuesta-card">
        
        <div class="card-header-row">
            <div class="header-titles">
                <h1>Encuesta para <?= htmlspecialchars($nivelTitulo) ?></h1>
            </div>
            <div class="header-progress">
                <span id="encuestaProgresoPag" class="progress-text">Página 1 de 1</span>
                <div class="progress-track">
                    <div id="progressFill" class="progress-fill"></div>
                </div>
            </div>
        </div>

        <hr class="card-divider">

        <div id="loaderEncuesta">
            <div class="spinner"></div>
            <span>Cargando preguntas...</span>
        </div>

        <div id="contenedorPreguntas" data-nivel="<?= htmlspecialchars($nivel) ?>"></div>

        <div class="acciones-encuesta">
            <button id="btnAnterior" type="button" class="btn-nav btn-prev">Anterior</button>
            <button id="btnSiguiente" type="button" class="btn-nav btn-next">Siguiente</button>
        </div>
    </div>

  </div>
</main>

<div id="schoolModal" class="modal-overlay" style="display: none;">
  <div class="modal-card">
    <button id="btnCloseModal" class="modal-close" title="Cerrar">&times;</button>
    <div class="modal-header">
        <h3>Bienvenido(a)</h3>
        <p>Para comenzar, necesitamos unos datos básicos.</p>
    </div>
    <div class="modal-body">
        <div class="form-group">
            <label>1. Tu Escuela:</label>
            <div class="select-wrapper">
                <select id="selectEscuelaModal">
                    <option value="">Cargando escuelas...</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label>2. Tu Género:</label>
            <div class="select-wrapper">
                <select id="selectGeneroModal">
                    <option value="">-- Selecciona --</option>
                    <option value="M">Hombre</option>
                    <option value="F">Mujer</option>
                    <option value="O">Otro</option>
                    <option value="X">Prefiero no decirlo</option>
                    
                </select>
            </div>
        </div>
    </div>
    <div class="modal-actions">
        <button id="btnConfirmarEscuela" class="btn-primary btn-full" disabled>Comenzar Encuesta</button>
    </div>
  </div>
</div>

<footer>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/front-end/includes/footer.php'); ?>
</footer>

<script>
  const NIVEL = "<?= htmlspecialchars($nivel) ?>";
  document.addEventListener("encuesta:lista", () => {
      const loader = document.getElementById("loaderEncuesta");
      const cont = document.getElementById("contenedorPreguntas");
      if (loader) loader.style.display = "none";
      if (cont) cont.classList.add("visible");
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="/front-end/scripts/modal-escuela.js"></script>
<script type="module" src="/front-end/scripts/encuesta.js"></script>
<script src="/front-end/scripts/canvas/canvas-paint.mount.js"></script>
<script src="/front-end/scripts/header-menu.js"></script>

</body>
</html>