<?php
session_start();
if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /SIMPINNA/front-end/frames/panel-admin/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/admin.css">
  <title>Encuestas registradas</title>
</head>
<body>
    <header>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/header-admin.php'); ?>
    </header>

  <main class="admin-main">
    <section class="encuestas-section">
      <h1 class="titulo-principal">Encuestas registradas</h1>
      <p class="subtitulo">Consulta los resultados de las encuestas aplicadas.</p>

      <div class="cards-container">

        <div class="card-encuesta preescolar">
          <img src="/SIMPINNA/front-end/assets/img/icons/icon-preescolar.png" alt="Preescolar">
          <h2>Preescolar</h2>
          <button class="btn-ver">Ver resultados</button>
        </div>

        <div class="card-encuesta primaria">
          <img src="/SIMPINNA/front-end/assets/img/icons/icon-primaria.png" alt="Primaria">
          <h2>Primaria</h2>
          <button class="btn-ver">Ver resultados</button>
        </div>

        <div class="card-encuesta secundaria">
          <img src="/SIMPINNA/front-end/assets/img/icons/icon-secundaria.png" alt="Secundaria">
          <h2>Secundaria</h2>
          <button class="btn-ver">Ver resultados</button>
        </div>

        <div class="card-encuesta preparatoria">
          <img src="/SIMPINNA/front-end/assets/img/icons/icon-preparatoria.png" alt="Preparatoria">
          <h2>Preparatoria</h2>
          <button class="btn-ver">Ver resultados</button>
        </div>

      </div>
    </section>
  </main>

  <footer>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/footer.php'); ?>
    </footer>
</body>
</html>
