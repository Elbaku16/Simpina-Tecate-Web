<?php
$nivel = $_GET['nivel'] ?? 'primaria';

$nivelTitulo = ucfirst($nivel);
$claseAncho  = ($nivel === 'primaria') ? ' encuesta-container--wide' : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIMPINNA | Encuestas</title>

  <!-- CSS -->
  <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
  <link rel="stylesheet" href="/front-end/assets/css/global/layout.css">
  <link rel="stylesheet" href="/front-end/assets/css/encuestas/encuestas.css">
  <link rel="stylesheet" href="/front-end/assets/css/encuestas/progress.css">
  <link rel="stylesheet" href="/front-end/assets/css/encuestas/canvas-paint.css">
  <link rel="stylesheet" href="/front-end/assets/css/global/header-responsive.css">

  <!-- Loader CSS (simple y directo en este archivo) -->
  <style>
    #contenedorPreguntas {
      visibility: hidden;
      min-height: 200px;
    }
    #contenedorPreguntas.visible {
      visibility: visible;
    }
    #loaderEncuesta {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 200px;
      font-size: 20px;
      font-weight: bold;
      color: #7A1E2C;
    }
  </style>

</head>

<body>
<header>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/front-end/includes/header.php'); ?>
</header>

<main class="encuesta-container<?= $claseAncho ?>">
  
  <h1>Encuesta para <?= htmlspecialchars($nivelTitulo) ?></h1>

  <div class="encuesta-progress">
    <span id="encuestaProgresoPag" class="badge badge-page">Página 1 de 1</span>
  </div>

  <div class="progress-wrap">
    <div class="progress-bar">
      <div id="progressFill" class="progress-fill"></div>
    </div>
  </div>

  <!-- ✅ LOADER -->
  <div id="loaderEncuesta">Cargando preguntas...</div>

  <!-- Contenedor real -->
  <div id="contenedorPreguntas" data-nivel="<?= htmlspecialchars($nivel) ?>"></div>

  <div class="acciones-encuesta">
    <button id="btnAnterior" type="button">Anterior</button>
    <button id="btnSiguiente" type="button">Siguiente</button>
  </div>
</main>

<footer>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/front-end/includes/footer.php'); ?>
</footer>

<!-- El JS pedirá las preguntas al backend -->
<script>
  const NIVEL = "<?= htmlspecialchars($nivel) ?>";
</script>

<script>
document.addEventListener("encuesta:lista", () => {
    const loader = document.getElementById("loaderEncuesta");
    const cont = document.getElementById("contenedorPreguntas");
    if (loader) loader.style.display = "none";
    if (cont) cont.classList.add("visible");
});
</script>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script type="module" src="/front-end/scripts/encuesta.js"></script>
<script src="/front-end/scripts/canvas/canvas-paint.mount.js"></script>
<script src="/front-end/scripts/header-menu.js"></script>
</body>
</html>
