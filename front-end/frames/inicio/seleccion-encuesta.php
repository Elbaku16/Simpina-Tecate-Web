<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Encuestas</title>
  <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/layout.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/overrides_encuesta.css">
  <style>
    :root {
      --cream: #F7EEDC;
      --burgundy: #7A1E2C;
      --gold: #D4B056;
      --text-dark: #3A2A25;
      --card-shadow: 0 12px 28px rgba(122, 30, 44, 0.12);
    }

    body {
      margin: 0;
      font-family: 'Montserrat', 'Helvetica Neue', Helvetica, Arial, sans-serif;
      background: var(--cream);
      color: var(--text-dark);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    header,
    footer {
      flex-shrink: 0;
    }

    .survey-selection {
      flex: 1;
      display: flex;
      align-items: center;
      padding: clamp(2rem, 5vw, 4rem) 1.5rem;
    }

    .survey-selection__wrapper {
      max-width: 1100px;
      margin: 0 auto;
      width: 100%;
      background: rgba(255, 255, 255, 0.7);
      border-radius: 28px;
      box-shadow: 0 20px 48px rgba(58, 42, 37, 0.1);
      padding: clamp(2rem, 4vw, 3rem);
      backdrop-filter: blur(2px);
    }

    .survey-selection__header {
      text-align: center;
      margin-bottom: clamp(1.5rem, 4vw, 2.5rem);
    }

    .survey-selection__header .title {
      font-size: clamp(1.8rem, 3vw, 2.4rem);
      color: var(--burgundy);
      margin: 0;
      font-weight: 700;
      letter-spacing: 0.02em;
    }

    .survey-selection__header .subtitle {
      font-size: clamp(1rem, 2vw, 1.2rem);
      color: var(--text-dark);
      margin: 0.75rem 0 0;
      opacity: 0.85;
    }

    .grade-grid {
      list-style: none;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: clamp(1rem, 3vw, 1.75rem);
      padding: 0;
      margin: 0;
    }

    .grade-item {
      background: #fffaf0;
      border-radius: 24px;
      padding: 2.25rem 1.5rem 2rem;
      text-align: center;
      position: relative;
      overflow: hidden;
      box-shadow: var(--card-shadow);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      border: 1px solid rgba(122, 30, 44, 0.08);
    }

    .grade-item::before {
      content: "";
      position: absolute;
      inset: -60% auto auto -60%;
      width: 140px;
      height: 140px;
      background: radial-gradient(circle at center, rgba(212, 176, 86, 0.4), transparent 70%);
      transform: rotate(12deg);
      z-index: 0;
    }

    .grade-item:hover {
      transform: translateY(-8px);
      box-shadow: 0 16px 32px rgba(122, 30, 44, 0.18);
    }

    .grade-item img {
      width: 110px;
      height: auto;
      margin: 0 auto 1.25rem;
      position: relative;
      z-index: 1;
    }

    .grade-item h3 {
      margin: 0 0 1rem;
      font-size: 1.35rem;
      font-weight: 700;
      color: var(--burgundy);
      position: relative;
      z-index: 1;
    }

    .btn-nivel {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      padding: 0.8rem 1.6rem;
      background: var(--burgundy);
      color: #fff;
      font-weight: 600;
      border-radius: 999px;
      text-decoration: none;
      transition: background 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
      box-shadow: 0 8px 18px rgba(122, 30, 44, 0.25);
      position: relative;
      z-index: 1;
    }

    .btn-nivel::after {
      content: '\2192';
      font-size: 1.1rem;
      transition: transform 0.3s ease;
    }

    .btn-nivel:hover {
      background: var(--gold);
      color: var(--burgundy);
      transform: translateY(-2px);
      box-shadow: 0 12px 24px rgba(212, 176, 86, 0.3);
    }

    .btn-nivel:hover::after {
      transform: translateX(6px);
    }

    .nivel--green { background: var(--burgundy); }
    .nivel--blue { background: var(--burgundy); }
    .nivel--red { background: var(--burgundy); }
    .nivel--magenta { background: var(--burgundy); }

    @media (max-width: 768px) {
      .survey-selection {
        padding: 2rem 1rem;
      }

      .survey-selection__wrapper {
        padding: 2rem 1.5rem;
      }

      .grade-item {
        padding: 2rem 1.2rem 1.8rem;
      }

      .btn-nivel {
        width: 100%;
      }
    }

    @media (max-width: 480px) {
      .survey-selection__wrapper {
        background: rgba(255, 255, 255, 0.82);
      }

      .grade-item img {
        width: 90px;
      }
    }
  </style>
</head>
<body>

<header>
  <?php include $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/front-end/includes/header.php'; ?>
</header>

<section class="survey-selection">
  <div class="survey-selection__wrapper">
    <div class="survey-selection__header">
      <h3 class="title">Elige tu escolaridad</h3>
      <h6 class="subtitle">Selecciona el nivel educativo para comenzar. Si lo necesitas, solicita apoyo a tu docente.</h6>
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
  </div>
</section>

<footer>
  <?php include $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/front-end/includes/footer.php'; ?>
</footer>

</body>
</html>
