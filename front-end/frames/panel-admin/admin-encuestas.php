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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Encuestas registradas | Panel Admin</title>
  <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/admin.css">
</head>
<body>
  <header>
    <?php include $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/front-end/includes/header-admin.php'; ?>
  </header>

  <main class="admin-encuestas">
    <h1 class="titulo-admin">Encuestas registradas</h1>
    <p class="subtitulo-admin">Consulta los resultados de las encuestas aplicadas.</p>

    <section class="encuestas-section">
      <div class="cards-container">
        <div class="card-admin preescolar">
          <img src="/SIMPINNA/front-end/assets/img/escolaridad/preescolar.png" alt="Preescolar">
          <h2>Preescolar</h2>
          <a href="resultados_preescolar.php"   class="btn-ver">Ver resultados</a>
        </div>

        <div class="card-admin primaria">
          <img src="/SIMPINNA/front-end/assets/img/escolaridad/primaria.png" alt="Primaria">
          <h2>Primaria</h2>
          <a href="resultados_primaria.php"   class="btn-ver">Ver resultados</a>
        </div>

        <div class="card-admin secundaria">
          <img src="/SIMPINNA/front-end/assets/img/escolaridad/secundaria.png" alt="Secundaria">
          <h2>Secundaria</h2>
          <a href="resultados_secundaria.php"   class="btn-ver">Ver resultados</a>
        </div>

        <div class="card-admin preparatoria">
          <img src="/SIMPINNA/front-end/assets/img/escolaridad/preparatoria.png" alt="Preparatoria">
          <h2>Preparatoria</h2>
          <a href="resultados_preparatoria.php"   class="btn-ver">Ver resultados</a>
        </div>
      </div>
    </section>
  </main>

  <footer>
    <?php include $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/front-end/includes/footer.php'; ?>
  </footer>
</body>
</html>
