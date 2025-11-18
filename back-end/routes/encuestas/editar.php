<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/back-end/auth/verificar-sesion.php';
requerir_admin();

$nivel = $_GET['nivel'] ?? 'primaria';
$nivelTitulo = ucfirst($nivel);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMPINNA | Editar encuesta</title>

    <!-- CSS global -->
    <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
    <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/layout.css">

    <!-- CSS del editor -->
    <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/encuestas/editarencuestas.css">
</head>

<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/header-admin.php'; ?>

<main class="editor-container">
    
    <h1>Editar encuesta: <?= htmlspecialchars($nivelTitulo) ?></h1>
    <p class="editor-sub">Modifica preguntas, opciones y orden de la encuesta seleccionada.</p>

    <!-- Contenedor dinámico -->
    <div id="editorPreguntas" data-nivel="<?= htmlspecialchars($nivel) ?>"></div>

    <!-- Agregar -->
    <button id="btnAgregarPregunta" class="btn-add-pregunta">+ Agregar pregunta</button>

    <!-- Acciones finales -->
    <div class="editor-final-buttons">
        <button id="btnGuardar" class="btn-guardar">Guardar cambios</button>
        <button id="btnCancelar" class="btn-cancelar">Cancelar</button>
    </div>

</main>

<footer>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/footer.php'; ?>
</footer>

<!-- JS del editor -->
<script src="/SIMPINNA/front-end/scripts/editarencuesta.js" defer></script>

</body>
</html>
