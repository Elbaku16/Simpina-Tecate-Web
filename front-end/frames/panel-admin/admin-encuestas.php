<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/back-end/auth/verificar-sesion.php';
requerir_admin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Encuestas registradas | Panel Admin</title>
  <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/layout.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/admin/admin.css">
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
          <a href="resultados.php?nivel=preescolar" class="btn-ver">Ver resultados</a>
        </div>

        <div class="card-admin primaria">
          <img src="/SIMPINNA/front-end/assets/img/escolaridad/primaria.png" alt="Primaria">
          <h2>Primaria</h2>
          <a href="resultados.php?nivel=primaria" class="btn-ver">Ver resultados</a>
        </div>

        <div class="card-admin secundaria">
          <img src="/SIMPINNA/front-end/assets/img/escolaridad/secundaria.png" alt="Secundaria">
          <h2>Secundaria</h2>
          <a href="resultados.php?nivel=secundaria" class="btn-ver">Ver resultados</a>
        </div>

        <div class="card-admin preparatoria">
          <img src="/SIMPINNA/front-end/assets/img/escolaridad/preparatoria.png" alt="Preparatoria">
          <h2>Preparatoria</h2>
          <a href="resultados.php?nivel=preparatoria" class="btn-ver">Ver resultados</a>
        </div>
      </div>

      <div class="comentarios-access">
        <h1 class="titulo-admin">Reportes y Comentarios</h1>
        <p class="subtitulo-admin">Revisa los reportes enviados desde el formulario de contacto</p>
        <a href="admin-comentarios.php" class="btn-comentarios">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
          </svg>
          Ver todos los comentarios
        </a>
      </div>
    </section>
  </main>

  <footer>
    <?php include $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/front-end/includes/footer.php'; ?>
  </footer>
</body>
</html>
