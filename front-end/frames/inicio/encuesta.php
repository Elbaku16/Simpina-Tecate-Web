<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Encuestas</title>
  <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/inicio.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/overrides_encuesta.css">
</head>
<body>

<header>
  <?php include $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/front-end/includes/header.php'; ?>
</header>

<main class="subtitle">
  <h3>Elige tu escolaridad:</h3>
  <h6>Toca el botón correcto o pídele ayuda a tu maestro.</h6>

  <div class="strip">
    <div class="container escolaridad">
      <ul class="grade-grid">
        <li class="grade-item">
          <img src="/SIMPINNA/front-end/assets/img/escolaridad/preescolar.png" alt="Preescolar">
          <a class="pill pill--green" href="/SIMPINNA/front-end/frames/preescolar/preescolar_seccion1.php">Preescolar</a>
        </li>
        <li class="grade-item">
          <img src="/SIMPINNA/front-end/assets/img/escolaridad/primaria.png" alt="Primaria">
          <a class="pill pill--blue" href="/SIMPINNA/front-end/frames/primaria/primaria_seccion1.php">Primaria</a>
        </li>
        <li class="grade-item">
          <img src="/SIMPINNA/front-end/assets/img/escolaridad/secundaria.png" alt="Secundaria">
          <a class="pill pill--red" href="/SIMPINNA/front-end/frames/secunaria/secundaria_seccion1.php">Secundaria</a>
        </li>
        <li class="grade-item">
          <img src="/SIMPINNA/front-end/assets/img/escolaridad/preparatoria.png" alt="Preparatoria">
          <a class="pill pill--magenta" href="/SIMPINNA/front-end/frames/preaparatoria/preparatoria_seccion1.php">Preparatoria</a>
        </li>
      </ul>
    </div>
  </div>
  <h6>¡Tu voz importa!</h6>
</main>


<footer>
  <?php include $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/front-end/includes/footer.php'; ?>
</footer>

</body>
</html>
