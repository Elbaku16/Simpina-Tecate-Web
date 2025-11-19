<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/back-end/auth/verificar-sesion.php';
requerir_admin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIMPINNA | Panel administrativo</title>

  <!-- CSS -->
  <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/layout.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/admin/admin.css">
</head>

<body>

  <?php include $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/header-admin.php'; ?>

  <main class="admin-encuestas">

    <!-- Encabezado -->
    <section class="admin-summary">
      <h1 class="titulo-admin">Encuestas registradas</h1>
      <p class="subtitulo-admin">
        Consulta, supervisa y analiza los resultados de los diferentes niveles educativos.
      </p>
    </section>

    <!-- Gestión de encuestas -->
    <section class="encuestas-section">
      <div class="encuestas-section__header">
        <h2>Gestión de encuestas</h2>
        <p>Selecciona un nivel educativo para ver resultados o modificar su contenido.</p>
      </div>

      <div class="cards-container">

        <!-- PREESCOLAR -->
        <div class="card-admin preescolar">
          <img src="/SIMPINNA/front-end/assets/img/escolaridad/preescolar.png" alt="Preescolar">
          <h2>Preescolar</h2>

          <a href="/SIMPINNA/back-end/routes/resultados/index.php?nivel=preescolar" class="btn-ver">
            Ver resultados
          </a>

          <a href="/SIMPINNA/front-end/frames/panel-admin/editar.php?nivel=preescolar" class="btn-editar">
            Modificar encuesta
          </a>
        </div>

        <!-- PRIMARIA -->
        <div class="card-admin primaria">
          <img src="/SIMPINNA/front-end/assets/img/escolaridad/primaria.png" alt="Primaria">
          <h2>Primaria</h2>

          <a href="/SIMPINNA/back-end/routes/resultados/index.php?nivel=primaria" class="btn-ver">
            Ver resultados
          </a>

          <a href="/SIMPINNA/front-end/frames/panel-admin/editar.php?nivel=primaria" class="btn-editar">
            Modificar encuesta
          </a>
        </div>

        <!-- SECUNDARIA -->
        <div class="card-admin secundaria">
          <img src="/SIMPINNA/front-end/assets/img/escolaridad/secundaria.png" alt="Secundaria">
          <h2>Secundaria</h2>

          <a href="/SIMPINNA/back-end/routes/resultados/index.php?nivel=secundaria" class="btn-ver">
            Ver resultados
          </a>

          <a href="/SIMPINNA/front-end/frames/panel-admin/editar.php?nivel=secundaria" class="btn-editar">
            Modificar encuesta
          </a>
        </div>

        <!-- PREPARATORIA -->
        <div class="card-admin preparatoria">
          <img src="/SIMPINNA/front-end/assets/img/escolaridad/preparatoria.png" alt="Preparatoria">
          <h2>Preparatoria</h2>

          <a href="/SIMPINNA/back-end/routes/resultados/index.php?nivel=preparatoria" class="btn-ver">
            Ver resultados
          </a>

          <a href="/SIMPINNA/front-end/frames/panel-admin/editar.php?nivel=preparatoria" class="btn-editar">
            Modificar encuesta
          </a>
        </div>

      </div>
    </section>

    <!-- Reportes -->
    <section class="comentarios-section">
      <div class="comentarios-box">
        <div class="comentarios-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
          </svg>
        </div>
        <h2>Reportes y Comentarios</h2>
        <p>Revisa y gestiona los reportes enviados desde el formulario de contacto</p>

        <a href="/SIMPINNA/back-end/routes/comentarios/index.php" class="btn-comentarios">
          Ver todos los comentarios
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
        </a>
      </div>
    </section>

  </main>

  <footer>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/footer.php'; ?>
  </footer>

</body>
</html>
