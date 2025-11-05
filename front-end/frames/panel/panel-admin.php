<?php
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../admin/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel administrativo</title>
  <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/layout.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/admin/admin.css">
</head>
<body>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/header-admin.php'; ?>

  <main class="admin-encuestas">
    <h1 class="titulo-admin">Encuestas registradas</h1>
    <p class="subtitulo-admin">Consulta los resultados de las encuestas aplicadas.</p>

    <section class="encuestas-section">
      <div class="cards-container">
        <div class="card-admin preescolar">
          <img src="/SIMPINNA/front-end/assets/img/escolaridad/preescolar.png" alt="Preescolar">
          <h2>Preescolar</h2>
          <a href="../panel-admin/resultados.php?nivel=preescolar" class="btn-ver">Ver resultados</a>
        </div>

        <div class="card-admin primaria">
          <img src="/SIMPINNA/front-end/assets/img/escolaridad/primaria.png" alt="Primaria">
          <h2>Primaria</h2>
          <a href="../panel-admin/resultados.php?nivel=primaria" class="btn-ver">Ver resultados</a>
        </div>

        <div class="card-admin secundaria">
          <img src="/SIMPINNA/front-end/assets/img/escolaridad/secundaria.png" alt="Secundaria">
          <h2>Secundaria</h2>
          <a href="../panel-admin/resultados.php?nivel=secundaria" class="btn-ver">Ver resultados</a>
        </div>

        <div class="card-admin preparatoria">
          <img src="/SIMPINNA/front-end/assets/img/escolaridad/preparatoria.png" alt="Preparatoria">
          <h2>Preparatoria</h2>
          <a href="../panel-admin/resultados.php?nivel=preparatoria" class="btn-ver">Ver resultados</a>
        </div>
      </div>
    </section>
  </main>

  <footer>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/footer-admin.php'; ?>
  </footer>
</body>
</html>
