<?php
// front-end/frames/encuestas/demo-encuestas.php
include($_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/back-end/connect-db/conexion-db.php');

/*
  Entrada:
  - ?nivel=preescolar|primaria|secundaria|preparatoria
  (Si no viene, se usa primaria).
*/
$nivel = isset($_GET['nivel']) ? strtolower(trim($_GET['nivel'])) : 'primaria';

// Relación nivel → id_encuesta (ajústalo si tu BD cambia)
$niveles = [
    'preescolar'   => 1,
    'primaria'     => 4,
    'secundaria'   => 5,
    'preparatoria' => 6
];

// id_encuesta según el nivel
$id_encuesta = $niveles[$nivel] ?? 4;

// Traer preguntas + opciones del nivel elegido
$sql = "
SELECT p.id_pregunta, p.texto_pregunta, p.tipo_pregunta,
       o.id_opcion, o.texto_opcion
FROM preguntas p
LEFT JOIN opciones_respuesta o ON p.id_pregunta = o.id_pregunta
WHERE p.id_encuesta = ?
ORDER BY p.orden ASC, o.id_opcion ASC;
";

$preguntas = [];
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_encuesta);
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $pid = (int)$row['id_pregunta'];
            if (!isset($preguntas[$pid])) {
                $preguntas[$pid] = [
                    'id'       => $pid,
                    'texto'    => $row['texto_pregunta'],
                    // El JS normaliza a: 'opcion' | 'multiple' | 'texto' | 'ranking'
                    'tipo'     => $row['tipo_pregunta'],
                    'opciones' => []
                ];
            }
            if (!empty($row['texto_opcion'])) {
                $preguntas[$pid]['opciones'][] = [
                    'id'    => (int)$row['id_opcion'],
                    'texto' => $row['texto_opcion']
                ];
            }
        }
    }
    $stmt->close();
}
$conn->close();

// Para el front
$preguntas = array_values($preguntas);
$nivelTitulo = ucfirst($nivel);

// Clase extra SOLO para primaria (cuadro más ancho)
$claseAncho = ($nivel === 'primaria') ? ' encuesta-container--wide' : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Encuesta Demo</title>

  <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/layout.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/encuestas/encuestas.css">
</head>
<body>
  <header>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/header.php'); ?>
  </header>

  <main class="encuesta-container<?php echo $claseAncho; ?>">
    <h1>Encuesta para <?php echo htmlspecialchars($nivelTitulo, ENT_QUOTES, 'UTF-8'); ?></h1>

    <!-- El JS pinta aquí las páginas con 2 preguntas apiladas -->
    <div id="contenedorPreguntas"
         data-nivel="<?php echo htmlspecialchars($nivel, ENT_QUOTES, 'UTF-8'); ?>"></div>

    <div class="acciones-encuesta" style="margin-top:16px;">
      <button id="btnAnterior" type="button">Anterior</button>
      <button id="btnSiguiente" type="button">Siguiente</button>
    </div>
  </main>

  <footer>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/footer.php'); ?>
  </footer>

  <script>
    // PHP → JS
    const preguntas  = <?php echo json_encode($preguntas, JSON_UNESCAPED_UNICODE); ?>;
    const idEncuesta = <?php echo (int)$id_encuesta; ?>;
  </script>

  <!-- cache-buster -->
  <script src="/SIMPINNA/front-end/scripts/encuesta.js?v=2025-11-05-06"></script>
</body>
</html>
