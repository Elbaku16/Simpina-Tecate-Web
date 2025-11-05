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
  <style>
    :root {
      --cream: #F7EEDC;
      --burgundy: #7A1E2C;
      --gold: #D4B056;
      --slate: #2D1B1F;
      --muted: rgba(122, 30, 44, 0.12);
    }

    body {
      margin: 0;
      background: var(--cream);
      font-family: 'Montserrat', 'Helvetica Neue', Arial, sans-serif;
      color: var(--slate);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    main.admin-encuestas {
      flex: 1;
      width: 100%;
      max-width: 1200px;
      margin: 0 auto;
      padding: clamp(2rem, 5vw, 4rem) 1.5rem 3rem;
      display: flex;
      flex-direction: column;
      gap: clamp(1.5rem, 3vw, 2.5rem);
    }

    .admin-summary {
      background: linear-gradient(135deg, rgba(122, 30, 44, 0.95), rgba(122, 30, 44, 0.85));
      color: #fff8f0;
      border-radius: 28px;
      padding: clamp(1.75rem, 3vw, 2.5rem);
      box-shadow: 0 24px 48px rgba(122, 30, 44, 0.25);
      display: grid;
      gap: 1rem;
      position: relative;
      overflow: hidden;
    }

    .admin-summary::after {
      content: "";
      position: absolute;
      width: 220px;
      height: 220px;
      background: radial-gradient(circle at center, rgba(212, 176, 86, 0.35), transparent 70%);
      top: -80px;
      right: -80px;
    }

    .titulo-admin {
      margin: 0;
      font-size: clamp(2rem, 3.5vw, 2.6rem);
      font-weight: 700;
      letter-spacing: 0.02em;
    }

    .subtitulo-admin {
      margin: 0;
      font-size: clamp(1rem, 2vw, 1.15rem);
      max-width: 540px;
      line-height: 1.6;
      color: #fff0d6;
    }

    .admin-quick-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 0.75rem;
      z-index: 1;
    }

    .admin-quick-actions .btn-action {
      background: #fff;
      color: var(--burgundy);
      padding: 0.65rem 1.4rem;
      border-radius: 999px;
      text-decoration: none;
      font-weight: 600;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .admin-quick-actions .btn-action:hover {
      transform: translateY(-3px);
      box-shadow: 0 16px 28px rgba(0, 0, 0, 0.18);
    }

    .encuestas-section {
      background: rgba(255, 255, 255, 0.72);
      backdrop-filter: blur(4px);
      border-radius: 28px;
      padding: clamp(1.5rem, 3vw, 2.5rem);
      box-shadow: 0 20px 45px rgba(58, 42, 37, 0.15);
      border: 1px solid var(--muted);
    }

    .encuestas-section__header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 1rem;
      margin-bottom: clamp(1.5rem, 3vw, 2.5rem);
    }

    .encuestas-section__header h2 {
      margin: 0;
      font-size: clamp(1.3rem, 2.5vw, 1.6rem);
      color: var(--burgundy);
    }

    .encuestas-section__header p {
      margin: 0;
      font-size: 0.95rem;
      color: rgba(45, 27, 31, 0.78);
      max-width: 420px;
    }

    .cards-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: clamp(1rem, 3vw, 1.75rem);
    }

    .card-admin {
      background: #fffaf3;
      border-radius: 22px;
      padding: 1.8rem 1.4rem 1.6rem;
      text-align: center;
      box-shadow: 0 14px 30px rgba(58, 42, 37, 0.18);
      border: 1px solid rgba(212, 176, 86, 0.25);
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 1.1rem;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .card-admin::before {
      content: "";
      position: absolute;
      inset: auto -60px -60px auto;
      width: 150px;
      height: 150px;
      background: radial-gradient(circle at center, rgba(122, 30, 44, 0.18), transparent 70%);
      transform: rotate(-15deg);
    }

    .card-admin:hover {
      transform: translateY(-6px);
      box-shadow: 0 22px 44px rgba(122, 30, 44, 0.25);
    }

    .card-admin img {
      width: 90px;
      height: auto;
      filter: drop-shadow(0 10px 18px rgba(122, 30, 44, 0.12));
    }

    .card-admin h2 {
      margin: 0;
      font-size: 1.25rem;
      color: var(--burgundy);
      letter-spacing: 0.01em;
    }

    .btn-ver {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.4rem;
      padding: 0.7rem 1.4rem;
      border-radius: 999px;
      background: var(--burgundy);
      color: #fff;
      text-decoration: none;
      font-weight: 600;
      box-shadow: 0 12px 24px rgba(122, 30, 44, 0.25);
      transition: background 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
      position: relative;
      z-index: 1;
    }

    .btn-ver::after {
      content: '\203A';
      font-size: 1.1rem;
      transition: transform 0.3s ease;
    }

    .btn-ver:hover {
      background: var(--gold);
      color: var(--burgundy);
      transform: translateY(-2px);
      box-shadow: 0 16px 28px rgba(212, 176, 86, 0.28);
    }

    .btn-ver:hover::after {
      transform: translateX(6px);
    }

    footer {
      flex-shrink: 0;
    }

    @media (max-width: 768px) {
      .admin-summary {
        text-align: center;
      }

      .admin-quick-actions {
        justify-content: center;
      }

      .encuestas-section__header {
        text-align: center;
        justify-content: center;
      }

      .encuestas-section__header p {
        max-width: none;
      }
    }

    @media (max-width: 480px) {
      .card-admin {
        padding: 1.6rem 1.2rem;
      }

      .btn-ver {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/header-admin.php'; ?>

  <main class="admin-encuestas">
    <section class="admin-summary">
      <h1 class="titulo-admin">Encuestas registradas</h1>
      <p class="subtitulo-admin">Consulta, supervisa y analiza los resultados de los diferentes niveles educativos para fortalecer la protección de los derechos de niñas, niños y adolescentes.</p>
      <div class="admin-quick-actions">
        <a class="btn-action" href="../panel-admin/resultados.php?nivel=preescolar">Preescolar</a>
        <a class="btn-action" href="../panel-admin/resultados.php?nivel=primaria">Primaria</a>
        <a class="btn-action" href="../panel-admin/resultados.php?nivel=secundaria">Secundaria</a>
        <a class="btn-action" href="../panel-admin/resultados.php?nivel=preparatoria">Preparatoria</a>
      </div>
    </section>

    <section class="encuestas-section">
      <div class="encuestas-section__header">
        <h2>Resultados por nivel</h2>
        <p>Selecciona un nivel educativo para revisar el desempeño general, indicadores clave y reportes descargables.</p>
      </div>

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
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/footer.php'; ?>
  </footer>
</body>
</html>
