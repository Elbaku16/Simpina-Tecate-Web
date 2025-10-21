<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
    <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/inicio.css">
    <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/overrides_primaria_seccion1.css">
    <title>Document</title>
</head>
<body>
<header>
    <?php include $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/front-end/includes/header.php'; ?>
</header>
<main class="questions">
    <h3>Sección 1: Conocimiento de los Derechos Humanos</h3>
    <h6>1.1 ¿Has oido hablar de los derechos humanos? (Marca la casilla)</h6>
    <!-- preguntas 1.1 -->
    <div class="q-block" role="radiogroup" aria-label="¿Has oído hablar de los derechos humanos?">
    <label class="q-option">
        <input type="checkbox" class="q-check" data-group="q1-1" value="si">
        <img class="q-icon" src="/SIMPINNA/front-end/assets/img/primaria/thumb-up.png" alt="pulgar arriba" aria-hidden="true">
        <span>Sí</span>
    </label>

    <label class="q-option">
        <input type="checkbox" class="q-check" data-group="q1-1" value="no">
        <img class="q-icon" src="/SIMPINNA/front-end/assets/img/primaria/thumb-down.png" alt="pulgar abajo" aria-hidden="true">
        <span>No</span>
    </label>

    <label class="q-option">
        <input type="checkbox" class="q-check" data-group="q1-1" value="no-seguro">
        <img class="q-icon" src="/SIMPINNA/front-end/assets/img/primaria/question.png" alt="signo de interrogcion" aria-hidden="true">
        <span>No estoy seguro</span>
    </label>
    </div>

</main>
<footer>
    <?php include $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/front-end/includes/footer.php'; ?>
</footer>
<!-- parte del java script -->
<script>
  //todos los checkbox que participen en exclusividad deben tener data-group="..."
  document.addEventListener('change', function (e) {
    const cb = e.target;
    if (!cb.matches('.q-check[data-group]')) return;

    const group = cb.getAttribute('data-group');
    //si se marca uno, se desmarcan las otras opciones
    if (cb.checked) {
      document.querySelectorAll('.q-check[data-group="' + group + '"]').forEach(el => {
        if (el !== cb) el.checked = false;
      });
    }
  });
</script>

</body>
</html>
