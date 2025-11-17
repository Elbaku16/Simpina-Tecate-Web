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
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/layout.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/encuestas/encuestas.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/encuestas/progress.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/encuestas/canvas-paint.css">
</head>

<body>
<header>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/header.php'); ?>
</header>

<main class="encuesta-container<?= $claseAncho ?>">
  <h1>Encuesta para <?= htmlspecialchars($nivelTitulo) ?></h1>

  <div class="encuesta-progress">
  
    <span id="encuestaProgresoPag" class="badge badge-page">Página 1 de 1</span>
  </div>

  <div class="progress-wrap">
    <div class="progress-bar"><div id="progressFill" class="progress-fill"></div></div>
  </div>

  <div id="contenedorPreguntas" data-nivel="<?= htmlspecialchars($nivel) ?>"></div>

  <div class="acciones-encuesta">
    <button id="btnAnterior" type="button">Anterior</button>
    <button id="btnSiguiente" type="button">Siguiente</button>
  </div>
</main>

<footer>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/footer.php'); ?>
</footer>

<!-- El JS pedirá las preguntas al backend -->
<script>
  const NIVEL = "<?= htmlspecialchars($nivel) ?>";
</script>

<script type="module" src="/SIMPINNA/front-end/scripts/encuesta.js"></script>
<script src="/SIMPINNA/front-end/scripts/canvas/canvas-paint.mount.js"></script>

</body>
</html>
