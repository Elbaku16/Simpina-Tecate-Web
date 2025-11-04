<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Encuestas</title>
  <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/layout.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/overrides_encuesta.css">
</head>
<body>

<header>
  <?php include $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/front-end/includes/header.php'; ?>
</header>

<section class="container escolaridad">

  <div class="strip">
    <div>
      <h3 class="title">Elige tu escolaridad:</h3>
      <h6 class="subtitle">Toca el botón correcto o pídele ayuda a tu maestro.</h6>
    </div>
  </div>

  <ul class="grade-grid">
    <li class="grade-item preescolar">
      <img src="/SIMPINNA/front-end/assets/img/escolaridad/preescolar.png" alt="Preescolar">
      <h3>Preescolar</h3>
      <a href="/SIMPINNA/front-end/frames/encuestas/demo-encuestas.php?nivel=preescolar" class="btn-nivel nivel--green">Comenzar</a>
    </li>

    <li class="grade-item primaria">
      <img src="/SIMPINNA/front-end/assets/img/escolaridad/primaria.png" alt="Primaria">
      <h3>Primaria</h3>
      <a href="/SIMPINNA/front-end/frames/encuestas/demo-encuestas.php?nivel=primaria" class="btn-nivel nivel--blue">Comenzar</a>
    </li>

    <li class="grade-item secundaria">
      <img src="/SIMPINNA/front-end/assets/img/escolaridad/secundaria.png" alt="Secundaria">
      <h3>Secundaria</h3>
      <a href="/SIMPINNA/front-end/frames/encuestas/demo-encuestas.php?nivel=secundaria" class="btn-nivel nivel--red">Comenzar</a>
    </li>

    <li class="grade-item preparatoria">
      <img src="/SIMPINNA/front-end/assets/img/escolaridad/preparatoria.png" alt="Preparatoria">
      <h3>Preparatoria</h3>
      <a href="/SIMPINNA/front-end/frames/encuestas/demo-encuestas.php?nivel=preparatoria" class="btn-nivel nivel--magenta">Comenzar</a>
    </li>
  </ul>
</section>

<footer>
  <?php include $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/front-end/includes/footer.php'; ?>
</footer>

</body>
</html>
