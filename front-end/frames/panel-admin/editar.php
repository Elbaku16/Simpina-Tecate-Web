<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/auth/verificar-sesion.php';
requerir_admin();

// RESTRICCIÓN ADICIONAL: Solo si tiene el permiso de modificar
if (!tiene_permiso('modificar_encuesta')) {
    header('Location: /front-end/frames/panel/panel-admin.php');
    exit;
}

$nivel = $_GET['nivel'] ?? 'primaria';
$nivelTitulo = ucfirst($nivel);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMPINNA | Editar encuesta</title>

    <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
    <link rel="stylesheet" href="/front-end/assets/css/global/layout.css">
    <link rel="stylesheet" href="/front-end/assets/css/admin/admin.css">
    <link rel="stylesheet" href="/front-end/assets/css/encuestas/editarencuestas.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
</head>

<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/front-end/includes/header-admin.php'; ?>

<main class="editor-wrapper">
    
    <div class="editor-header">
        <div class="header-texts">
            <span class="badge-nivel">Nivel <?= htmlspecialchars($nivelTitulo) ?></span>
            <h1>Editar Encuesta</h1>
        </div>
        
        <a href="/front-end/frames/panel/panel-admin.php" class="btn-volver-premium">
            <i class="fa-solid fa-angle-left"></i> Regresar al Panel
        </a>
    </div>

    <div class="editor-card">
        
        <p class="editor-intro">Configura las preguntas, el orden, las opciones y ahora también imágenes por pregunta y por opción.</p>
        
        <div id="editorPreguntas" class="preguntas-list" data-nivel="<?= htmlspecialchars($nivel) ?>"></div>

        <div class="add-section">
            <button id="btnAgregarPregunta" class="btn-add-dashed">
                <span class="plus-icon">+</span> Agregar nueva pregunta
            </button>
        </div>

        <hr class="divider">

        <div class="editor-footer">
            <button id="btnCancelar" class="btn-cancelar-solid">Cancelar edición</button>
            <button id="btnGuardar" class="btn-guardar-solid">Guardar y Publicar</button>
        </div>
    </div>

</main>

<footer>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/front-end/includes/footer.php'; ?>
</footer>

<script src="/front-end/scripts/editarencuesta.js" defer></script>

</body>
</html>
