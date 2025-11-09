<?php 
// front-end/frames/encuestas/demo-encuestas.php
include($_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/back-end/connect-db/conexion-db.php');

$nivel = isset($_GET['nivel']) ? strtolower(trim($_GET['nivel'])) : 'primaria';

$niveles = [
  'preescolar'   => 1,
  'primaria'     => 4,
  'secundaria'   => 5,
  'preparatoria' => 6
];
$id_encuesta = $niveles[$nivel] ?? 4;

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
  $stmt->bind_param('i', $id_encuesta);
  if ($stmt->execute()) {
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
      $pid = (int)$row['id_pregunta'];
      if (!isset($preguntas[$pid])) {
        $preguntas[$pid] = [
          'id'       => $pid,
          'texto'    => $row['texto_pregunta'],
          'tipo'     => $row['tipo_pregunta'],
          'opciones' => []
        ];
      }
      if ($row['id_opcion'] !== null) {
        $preguntas[$pid]['opciones'][] = [
          'id'    => (int)$row['id_opcion'],
          'texto' => isset($row['texto_opcion']) ? trim((string)$row['texto_opcion']) : ''
        ];
      }
    }
  }
  $stmt->close();
}
$conn->close();

$preguntas = array_values($preguntas);

$nivelTitulo = ucfirst($nivel);
$claseAncho  = ($nivel === 'primaria') ? ' encuesta-container--wide' : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIMPINNA | Encuestas</title>
  <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/layout.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/encuestas/encuestas.css">
  <!-- Estilos del canvas -->
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/encuestas/canvas-paint.css">
<style>
  /* Indicadores de progreso (respondidas y página) */
  .encuesta-progress{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:1.25rem;
    margin:0 0 1.25rem 0;
  }

  .badge{
    background:#fffaf0;
    border:2px solid #e6d9a3;
    padding:.6rem 1.1rem;   /* un poco más grande */
    border-radius:999px;
    color:#5a2a2a;
    font-size:1.25rem;      /* ↑ más grande */
    font-weight:700;
    line-height:1;
    box-shadow:0 2px 2px rgba(0,0,0,.06);
    white-space:nowrap;
    transition:transform .2s ease;
  }

  .badge:hover{
    transform:scale(1.03);
  }

  .badge-page{
    background:#eaf4ff;
    border-color:#bcdcff;
    color:#114a7a;
  }

  /* Ajuste en pantallas chicas */
  @media (max-width:600px){
    .badge{ font-size:1.1rem; padding:.55rem .9rem; }
  }
</style>


</head>
<body>
<header><?php include($_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/header.php'); ?></header>

<main class="encuesta-container<?php echo $claseAncho; ?>">
  <h1>Encuesta para <?php echo htmlspecialchars($nivelTitulo, ENT_QUOTES, 'UTF-8'); ?></h1>

  <!-- Indicadores: izquierda (respondidas), derecha (página) -->
  <div class="encuesta-progress">
    <span id="encuestaProgresoResp" class="badge">0 de 0</span>
    <span id="encuestaProgresoPag"  class="badge badge-page">Página 1 de 1</span>
  </div>

  <!-- El JS pinta las preguntas aquí -->
  <div id="contenedorPreguntas" data-nivel="<?php echo htmlspecialchars($nivel, ENT_QUOTES, 'UTF-8'); ?>"></div>

  <div class="acciones-encuesta" style="margin-top:16px;">
    <button id="btnAnterior"  type="button">Anterior</button>
    <button id="btnSiguiente" type="button">Siguiente</button>
  </div>
</main>

<footer><?php include($_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/footer.php'); ?></footer>

<script>
  const preguntas  = <?php echo json_encode($preguntas, JSON_UNESCAPED_UNICODE); ?>;
  const idEncuesta = <?php echo (int)$id_encuesta; ?>;
</script>

<!-- JS principal de la encuesta -->
<script src="/SIMPINNA/front-end/scripts/encuesta.js?v=2025-11-06-dual-progress"></script>

<!-- JS del canvas -->
<script src="/SIMPINNA/front-end/scripts/canvas/canvas-paint.js"></script>
<script src="/SIMPINNA/front-end/scripts/canvas/canvas-paint.mount.js?v=2025-11-06-dpr"></script>
</body>
</html>
