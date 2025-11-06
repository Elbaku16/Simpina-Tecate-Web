<?php
$ok = null; 
$errores = [];
$nombre = $nivel = $escuela = $comentarios = "";
$hp = isset($_POST['website']) ? trim($_POST['website']) : "";

require_once __DIR__ . '/../../../back-end/connect-db/conexion-db.php';

$niveles = [];
$sqlNiveles = "SELECT id_nivel, nombre_nivel FROM niveles_educativos ORDER BY id_nivel";
$resultNiveles = $conn->query($sqlNiveles);
if ($resultNiveles) {
  while ($row = $resultNiveles->fetch_assoc()) {
    $niveles[] = $row;
  }
}

$escuelasPorNivel = [];
$sqlEscuelas = "SELECT id_escuela, id_nivel, nombre_escuela FROM escuelas ORDER BY id_nivel, nombre_escuela";
$resultEscuelas = $conn->query($sqlEscuelas);
if ($resultEscuelas) {
  while ($row = $resultEscuelas->fetch_assoc()) {
    $nid = (int)$row['id_nivel'];
    if (!isset($escuelasPorNivel[$nid])) {
      $escuelasPorNivel[$nid] = [];
    }
    $escuelasPorNivel[$nid][] = [
      'id' => (int)$row['id_escuela'],
      'nombre' => $row['nombre_escuela']
    ];
  }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  if ($hp !== "") {
    $errores[] = "Validación fallida.";
  } else {
    $nombre      = trim($_POST["nombre"] ?? "");
    $nivel       = trim($_POST["nivel"] ?? "");
    $escuela     = trim($_POST["escuela"] ?? "");
    $comentarios = trim($_POST["comentarios"] ?? "");

    if ($nivel === "" || $nivel === "0") {
      $errores[] = "Por favor selecciona un nivel educativo.";
    }

    if ($escuela === "" || $escuela === "0") {
      $errores[] = "Por favor selecciona una escuela.";
    }

    if ($comentarios === "") {
      $errores[] = "Por favor escribe tus comentarios sobre la situación.";
    }

    if ($nombre === "") {
      $nombre = "Anónimo";
    }

    if (!$errores) {
      $stmt = $conn->prepare("INSERT INTO contactos (nombre, id_nivel, id_escuela, comentarios) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("siis", $nombre, $nivel, $escuela, $comentarios);
      
      if ($stmt->execute()) {
        $ok = true;
        $nombreTemp = $nombre;
        $nombre = $nivel = $escuela = $comentarios = "";
      } else {
        $ok = false;
        $errores[] = "Error al guardar el reporte. Por favor intenta nuevamente.";
      }
      $stmt->close();
    } else {
      $ok = false;
    }
  }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
    <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/layout.css">
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
    <title>SIMPINNA | Contactanos</title>
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
      <?php if ($ok === true): ?>
        <div class="alert success" role="status">
          ¡Gracias! Tu mensaje sera analizado y responido por las autoridades correspondientes.
        </div>
      <?php elseif ($ok === false): ?>
        <div class="alert error" role="alert">
          <strong>Revisa lo siguiente:</strong>
          <ul><?php foreach ($errores as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul>
        </div>
      <?php endif; ?>

      <form method="post" action="" id="contactoForm" novalidate>
        <input type="text" name="website" tabindex="-1" autocomplete="off" class="hp">

        <div class="field">
          <label for="nombre">Nombre (opcional)</label>
          <input id="nombre" name="nombre" type="text"
                 value="<?= htmlspecialchars($nombre === 'Anónimo' ? '' : $nombre) ?>" 
                 placeholder="Escribe tu nombre">
          <small class="hint">Si no proporcionas un nombre, tu reporte será anónimo.</small>
        </div>

        <div class="field">
          <label for="nivel">Nivel educativo <span class="required">*</span></label>
          <select id="nivel" name="nivel" required onchange="actualizarEscuelas()">
            <option value="0">Selecciona un nivel</option>
            <?php foreach ($niveles as $niv): ?>
              <option value="<?= $niv['id_nivel'] ?>" <?= $nivel == $niv['id_nivel'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($niv['nombre_nivel']) ?>
              </option>
            <?php endforeach; ?>
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
                    placeholder="Describe detalladamente la situación que deseas reportar..."><?= htmlspecialchars($comentarios) ?></textarea>
          <small class="hint">Es importante que describas con detalle la situación para poder ayudarte mejor.</small>
        </div>

        <div class="actions">
          <button class="btn-maroon" type="submit">Enviar reporte</button>
        </div>
      </form>
    </div>
  </section>
</main>

<footer>
  <?php include $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/front-end/includes/footer.php'; ?>
</footer>

<script>
  const escuelasPorNivel = <?= json_encode($escuelasPorNivel) ?>;

  function actualizarEscuelas() {
    const nivelSelect = document.getElementById('nivel');
    const escuelaSelect = document.getElementById('escuela');
    const nivelId = parseInt(nivelSelect.value);

    escuelaSelect.innerHTML = '<option value="0">Selecciona una escuela</option>';

    if (nivelId > 0 && escuelasPorNivel[nivelId]) {
      escuelaSelect.disabled = false;
      
      escuelasPorNivel[nivelId].forEach(escuela => {
        const option = document.createElement('option');
        option.value = escuela.id;
        option.textContent = escuela.nombre;
        escuelaSelect.appendChild(option);
      });
    } else {
      escuelaSelect.disabled = true;
      escuelaSelect.innerHTML = '<option value="0">Primero selecciona un nivel</option>';
    }
  }

  window.addEventListener('DOMContentLoaded', function() {
    const nivelValue = document.getElementById('nivel').value;
    if (nivelValue && nivelValue !== '0') {
      actualizarEscuelas();
      
      const escuelaValue = '<?= htmlspecialchars($escuela) ?>';
      if (escuelaValue) {
        document.getElementById('escuela').value = escuelaValue;
      }
    }
  });
</script>

</body>
</html>