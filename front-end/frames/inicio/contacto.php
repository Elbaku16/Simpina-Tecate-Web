<?php
// permite el anonimo
$ok = null; $errores = [];
$nombre = $asunto = $email = $telefono = $comentarios = "";
$hp = isset($_POST['website']) ? trim($_POST['website']) : "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  if ($hp !== "") {
    $errores[] = "Validación fallida.";
  } else {
    $nombre      = trim($_POST["nombre"] ?? "");
    $asunto      = trim($_POST["asunto"] ?? "");
    $email       = trim($_POST["email"] ?? "");
    $telefono    = trim($_POST["telefono"] ?? "");
    $comentarios = trim($_POST["comentarios"] ?? "");

    //Reglas de anonimato / flexibles:
    //1) Nombre: si viene vacio = anonimo
    if ($nombre === "") $nombre = "Anónimo";

    //2) Asunto:si viene vacío ¿ contacto anonimo
    if ($asunto === "") $asunto = "Contacto anónimo";

    //3) Email: opcional NO validamos formato
    if (strlen($email) > 200) $email = substr($email, 0, 200);

    //4) Telefono: opcional, aceptamos espacios, digitos simbolos, etc
    $tel_limpio = preg_replace('/[0-9+\s\-\(\)]/', '', $telefono);
    //$tel_limpio contendra caracteres no permitidos; pero si el usuario escribe "anonimo", lo aceptamos igual.
    // No generamos error por el teléfono.

    //5)Comentarios: estos si son obligatorios ya que de aqui dependra como actuamos si se tiene que contactar con una autoridad.
    if ($comentarios === "") $errores[] = "Por favor escribe tus comentarios.";

    if (!$errores) {
      $ok = true;
      $nombre = $asunto = $email = $telefono = $comentarios = "";
    } else {
      $ok = false;
    }
  }
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
    <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/inicio.css">
    <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/contacto.css">
    <title>Contacto</title>
</head>

<body>
    <header> 
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/header.php'); ?>
    </header>
<main class="contacto-layout">
  <section class="contacto-wrap">
    <h1 class="contacto-title">Contáctanos</h1>
    <p class="contacto-subtitle">
      Por favor llene la siguiente forma con sus datos y al final presione enviar, gracias.
    </p>

    <div class="card-form">
      <?php if ($ok === true): ?>
        <div class="alert success" role="status">
          ¡Gracias! Tu mensaje se envió correctamente.
        </div>
      <?php elseif ($ok === false): ?>
        <div class="alert error" role="alert">
          <strong>Revisa lo siguiente:</strong>
          <ul><?php foreach ($errores as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul>
        </div>
      <?php endif; ?>

      <form method="post" action="" novalidate>
        <!-- Honeypot (no mostrar) -->
        <input type="text" name="website" tabindex="-1" autocomplete="off" class="hp">

        <div class="field">
          <label for="nombre">Nombre</label>
          <input id="nombre" name="nombre" type="text" required
                 value="<?= htmlspecialchars($nombre) ?>" placeholder="Nombre">
        </div>

        <div class="field">
          <label for="asunto">Asunto</label>
          <input id="asunto" name="asunto" type="text" required
                 value="<?= htmlspecialchars($asunto) ?>" placeholder="Asunto">
        </div>

        <div class="field">
          <label for="email">Correo Electrónico</label>
          <input id="email" name="email" type="email" required
                 value="<?= htmlspecialchars($email) ?>" placeholder="Correo Electrónico">
        </div>

        <div class="field">
          <label for="telefono">Teléfono</label>
          <input id="telefono" name="telefono" type="tel"
                 pattern="[0-9+\s\-()]{7,}" value="<?= htmlspecialchars($telefono) ?>"
                 placeholder="Teléfono">
        </div>

        <div class="field">
          <label for="comentarios">Comentarios</label>
          <textarea id="comentarios" name="comentarios" rows="6" required
                    placeholder="Comentarios"><?= htmlspecialchars($comentarios) ?></textarea>
        </div>

        <div class="actions">
          <button class="btn-maroon" type="submit">Enviar</button>
        </div>
      </form>
    </div>
  </section>
</main>
</body>

<footer>
  <?php include $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/front-end/includes/footer.php'; ?>
</footer>

</html>