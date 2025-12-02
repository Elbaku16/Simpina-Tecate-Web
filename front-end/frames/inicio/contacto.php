<?php
// Estado opcional para mostrar mensaje
$ok = $_GET['ok'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMPINNA | Contáctanos</title>
    <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
    <link rel="stylesheet" href="/front-end/assets/css/global/layout.css">
    <link rel="stylesheet" href="/front-end/assets/css/global/header-responsive.css">
    <link rel="stylesheet" href="/front-end/assets/css/global/contacto.css">
</head>

<body>
<header>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/front-end/includes/header.php'); ?>
</header>

<main class="contacto-layout">
  <section class="contacto-wrap">
    
    <div class="contacto-header">
        <h1 class="contacto-title">Reportes y Contacto</h1>
        <p class="contacto-subtitle">
          Tu voz es importante. Utiliza este espacio para reportar situaciones o solicitar apoyo. 
          Tus datos serán tratados con confidencialidad.
        </p>
    </div>

    <div class="card-form">
      
      <?php if ($ok === '1'): ?>
        <div class="alert success" role="status">
            <div class="alert-icon">✓</div>
            <div class="alert-content">
                <strong>¡Reporte enviado!</strong>
                Tu mensaje ha sido recibido y será atendido por las autoridades correspondientes.
            </div>
        </div>
      <?php elseif ($ok === '0'): ?>
        <div class="alert error" role="alert">
            <div class="alert-icon">✕</div>
            <div class="alert-content">
                <strong>Error de envío</strong>
                Hubo un problema técnico. Por favor intenta nuevamente más tarde.
            </div>
        </div>
      <?php endif; ?>

      <form method="post"
            action="/back-end/routes/contacto/enviar.php"
            id="contactoForm"
            novalidate>
        
        <input type="text" name="website" tabindex="-1" autocomplete="off" class="hp">

        <div class="field full-width">
          <label for="nombre">Nombre completo <span class="optional">(Opcional)</span></label>
          <div class="input-wrapper">
              <i class="input-icon user-icon"></i>
              <input id="nombre" name="nombre" type="text" placeholder="Ej: María González">
          </div>
          <small class="hint">Si lo dejas en blanco, el reporte será anónimo.</small>
        </div>

        <div class="form-row">
            <div class="field">
              <label for="nivel">Nivel educativo <span class="required">*</span></label>
              <div class="input-wrapper">
                  <select id="nivel" name="nivel" required>
                    <option value="0">Selecciona un nivel...</option>
                  </select>
              </div>
            </div>

            <div class="field">
              <label for="escuela">Escuela <span class="required">*</span></label>
              <div class="input-wrapper">
                  <select id="escuela" name="escuela" required disabled>
                    <option value="0">Selecciona nivel primero</option>
                  </select>
              </div>
            </div>
        </div>

        <div class="field full-width">
          <label for="comentarios">Descripción de la situación <span class="required">*</span></label>
          <textarea id="comentarios" name="comentarios" rows="5" required
                    placeholder="Cuéntanos qué sucedió, dónde y cuándo..."></textarea>
        </div>

        <div class="actions">
          <button class="btn-primary" type="submit">
             Enviar reporte
             <svg style="width:20px; height:20px; margin-left:8px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
          </button>
        </div>

      </form>
    </div>
  </section>
</main>

<footer>
  <?php include $_SERVER['DOCUMENT_ROOT'].'/front-end/includes/footer.php'; ?>
</footer>

<script src="/front-end/scripts/contacto/contacto.js"></script>
<script src="/front-end/scripts/header-menu.js"></script>

</body>
</html>