<?php
// Estado opcional para mostrar mensaje después de enviar el formulario.
// La ruta /back-end/routes/contacto/enviar.php redirige de vuelta con ?ok=1 o ?ok=0.
$ok = $_GET['ok'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
    <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/layout.css">
    <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/header-responsive.css">
    <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/contacto.css">
    <style>
      .required { color: #d32f2f; font-weight: 700; }
      select {
        width: 100%;
        border: 1.5px solid #d7d7d7;
        border-radius: 8px;
        padding: 12px 14px;
        font-size: 16px;
        transition: border-color .15s ease, box-shadow .15s ease;
        outline: none;
        background: #fff;
        cursor: pointer;
      }
      select:focus {
        border-color: var(--gold-brd, #d6bd55);
        box-shadow: 0 0 0 3px rgba(214,189,85,.25);
      }
      select:disabled {
        background: #f5f5f5;
        cursor: not-allowed;
        color: #999;
      }
    </style>
    <title>SIMPINNA | Contáctanos</title>
</head>

<body>
<header>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/header.php'); ?>
</header>

<main class="contacto-layout">
  <section class="contacto-wrap">
    <h1 class="contacto-title">Contáctanos</h1>
    <p class="contacto-subtitle">
      Por favor llena el siguiente formulario con los datos de la situación que deseas reportar.
    </p>

    <div class="card-form">
      <?php if ($ok === '1'): ?>
        <div class="alert success" role="status">
          ¡Gracias! Tu mensaje será analizado y respondido por las autoridades correspondientes.
        </div>
      <?php elseif ($ok === '0'): ?>
        <div class="alert error" role="alert">
          Ocurrió un problema al guardar tu reporte. Por favor intenta nuevamente.
        </div>
      <?php endif; ?>

      <form method="post"
            action="/SIMPINNA/back-end/routes/contacto/enviar.php"
            id="contactoForm"
            novalidate>
        
        <!-- Honeypot anti-bots -->
        <input type="text" name="website" tabindex="-1" autocomplete="off" class="hp">

        <div class="field">
          <label for="nombre">Nombre (opcional)</label>
          <input id="nombre" name="nombre" type="text"
                 value=""
                 placeholder="Escribe tu nombre">
          <small class="hint">Si no proporcionas un nombre, tu reporte será anónimo.</small>
        </div>

        <div class="field">
          <label for="nivel">Nivel educativo <span class="required">*</span></label>
          <select id="nivel" name="nivel" required>
            <option value="0">Cargando niveles...</option>
          </select>
        </div>

        <div class="field">
          <label for="escuela">Escuela <span class="required">*</span></label>
          <select id="escuela" name="escuela" required disabled>
            <option value="0">Primero selecciona un nivel</option>
          </select>
        </div>

        <div class="field">
          <label for="comentarios">Describe la situación <span class="required">*</span></label>
          <textarea id="comentarios" name="comentarios" rows="6" required
                    placeholder="Describe detalladamente la situación que deseas reportar..."></textarea>
          <small class="hint">Es importante que describas con detalle la situación para poder ayudarte mejor.</small>
        </div>

        <div class="actions">
          <button class="btn-primary" type="submit">Enviar reporte</button>
        </div>
      </form>
    </div>
  </section>
</main>

<footer>
  <?php include $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/front-end/includes/footer.php'; ?>
</footer>


<script src="/SIMPINNA/front-end/scripts/contacto/contacto.js"></script>
<script src="/SIMPINNA/front-end/scripts/header-menu.js"></script>


</body>
</html>
