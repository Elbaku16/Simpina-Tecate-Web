<?php
// front-end/frames/panel-admin/resultados_base.php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// ¿Tenemos conexión? si no, créala.
$__opened_here = false;
if (!isset($conn) || !($conn instanceof mysqli)) {
  require_once __DIR__ . '/../../../back-end/connect-db/conexion-db.php';
  $__opened_here = true;
}

// 1) Resolver $encuestaId: prioridad -> variable / $nivelId / $_GET
if (!isset($encuestaId) || (int)$encuestaId <= 0) {
  if (isset($nivelId) && (int)$nivelId > 0) {
    $stmt = $conn->prepare("SELECT id_encuesta FROM encuestas WHERE id_nivel = ? ORDER BY id_encuesta LIMIT 1");
    $lvl = (int)$nivelId;
    $stmt->bind_param("i", $lvl);
    $stmt->execute();
    $stmt->bind_result($encuestaId);
    $stmt->fetch();
    $stmt->close();
  } else {
    $encuestaId = isset($_GET['encuesta_id']) ? (int)$_GET['encuesta_id'] : 0;
  }
}

if ((int)$encuestaId <= 0) {
  http_response_code(400);
  echo "Falta el parámetro 'encuesta_id'.";
  // si abrimos nosotros la conexión, la cerramos
  if ($__opened_here && isset($conn) && $conn instanceof mysqli) { $conn->close(); }
  exit;
}
// 2) Cargar preguntas
$sqlPreg = "SELECT id_pregunta, id_encuesta, texto_pregunta, 
                   COALESCE(tipo_pregunta,'opcion') AS tipo_pregunta,
                   COALESCE(orden, id_pregunta) AS orden
            FROM preguntas
            WHERE id_encuesta = ?
            ORDER BY orden ASC";
$stmt = $conn->prepare($sqlPreg);
if (!$stmt) { http_response_code(500); echo "Error preparando consulta: ".$conn->error; exit; }
$stmt->bind_param("i", $encuestaId);
$stmt->execute();
$rsPreg = $stmt->get_result();

$preguntas = [];
$ids = [];
while ($row = $rsPreg->fetch_assoc()) {
  $row['id_pregunta']   = (int)$row['id_pregunta'];
  $row['id_encuesta']   = (int)$row['id_encuesta'];
  $row['tipo_pregunta'] = strtolower($row['tipo_pregunta']);
  $preguntas[] = $row;
  $ids[] = $row['id_pregunta'];
}
$stmt->close();

// 3) Opciones
$opcionesPorPregunta = [];
if (!empty($ids)) {
  $ph = implode(',', array_fill(0, count($ids), '?'));
  $types = str_repeat('i', count($ids));
  $sqlOpt = "SELECT id_opcion, id_pregunta, texto_opcion, icono, valor
             FROM opciones_respuesta
             WHERE id_pregunta IN ($ph)
             ORDER BY id_pregunta, id_opcion";
  $stmt = $conn->prepare($sqlOpt);
  if ($stmt) {
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $rsOpt = $stmt->get_result();
    while ($opt = $rsOpt->fetch_assoc()) {
      $pid = (int)$opt['id_pregunta'];
      $opcionesPorPregunta[$pid][] = [
        'id_opcion' => (int)$opt['id_opcion'],
        'texto'     => $opt['texto_opcion'],
        'icono'     => $opt['icono'],
        'valor'     => isset($opt['valor']) ? (int)$opt['valor'] : null,
      ];
    }
    $stmt->close();
  }
}
$conn->close();

$palette = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#84cc16','#f97316','#e11d48','#22c55e'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Resultados | Panel Admin</title>
  <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/admin.css">
  <style>
    .toolbar{display:flex;gap:8px;align-items:center;padding:8px 16px;border-bottom:1px solid #e5e7eb;background:#fff}
    .toolbar .btn{padding:6px 10px;border:1px solid #cfd8e3;border-radius:8px;background:#fff;cursor:pointer}
    .toolbar .btn:hover{background:#f3f4f6}
    .res-header{padding:18px 16px;border-bottom:1px solid #e5e7eb;background:#fff}
    .res-header h1{margin:0 0 4px 0;font-size:20px}
    .res-meta{margin:0;color:#6b7280;font-size:14px}
    .res-wrapper{display:grid;grid-template-columns:repeat(auto-fill,minmax(380px,1fr));gap:16px;padding:16px}
    .res-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:14px}
    .pregunta-titulo{margin:0 0 12px 0;font-size:15px;line-height:1.35}
    .pie-slot{height:240px;border:1px dashed #cdd5df;border-radius:10px;display:grid;place-items:center;background:#fafbfe;margin-bottom:10px}
    .chart-hint{color:#7b8794;font-size:13px}
    .legend{display:flex;flex-direction:column;gap:6px}
    .legend-item{display:flex;align-items:center;gap:8px;font-size:14px}
    .legend-color{width:14px;height:14px;border-radius:3px;border:1px solid #e5e7eb;flex:none}
    .link-open{display:inline-block;margin-top:4px}
  </style>
</head>
<body>
  <div class="toolbar">
    <a class="btn" href="/SIMPINNA/front-end/frames/panel-admin/admin-encuestas.php">← Volver</a>
    <span>Encuesta ID: <strong><?php echo htmlspecialchars($encuestaId); ?></strong></span>
  </div>

  <header class="res-header">
    <h1>Resultados de la encuesta</h1>
    <p class="res-meta">Bajo cada pregunta: espacio para <strong>gráfica de pastel</strong>; después, la <strong>leyenda</strong> con todas las opciones. Si la pregunta es abierta (tipo <code>texto</code>), solo aparece “Ver respuestas”.</p>
  </header>

  <?php if (empty($preguntas)): ?>
    <section style="padding:16px;color:#6b7280">No se encontraron preguntas para esta encuesta.</section>
  <?php else: ?>
    <main class="res-wrapper">
      <?php foreach ($preguntas as $i => $p):
        $pid   = (int)$p['id_pregunta'];
        $tipo  = $p['tipo_pregunta']; // 'texto' = abierta
        $lista = $opcionesPorPregunta[$pid] ?? [];
        foreach ($lista as $k => $op) { $lista[$k]['color'] = $palette[$k % count($palette)]; }
      ?>
        <article class="res-card" id="pregunta-<?php echo $pid; ?>">
          <h2 class="pregunta-titulo"><?php echo htmlspecialchars(($i+1).'. '.$p['texto_pregunta']); ?></h2>
          <div class="pie-slot" id="pie-q<?php echo $pid; ?>" data-pregunta-id="<?php echo $pid; ?>">
            <div class="chart-hint">Gráfica de pastel pendiente…</div>
          </div>
          <?php if ($tipo === 'texto'): ?>
            <a class="link-open" href="#">Ver respuestas</a>
          <?php else: ?>
            <div class="legend" id="legend-q<?php echo $pid; ?>">
              <?php if (empty($lista)): ?>
                <div class="legend-item" style="color:#9aa4b2">No hay opciones configuradas.</div>
              <?php else: foreach ($lista as $op): ?>
                <div class="legend-item" data-opcion-id="<?php echo (int)$op['id_opcion']; ?>">
                  <span class="legend-color" style="background: <?php echo htmlspecialchars($op['color']); ?>"></span>
                  <span><?php echo htmlspecialchars($op['texto']); ?></span>
                </div>
              <?php endforeach; endif; ?>
            </div>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </main>
  <?php endif; ?>

  <script>
    window.ENCUESTA_ID = <?php echo (int)$encuestaId; ?>;
  </script>
  <?php if ($__opened_here && isset($conn) && $conn instanceof mysqli) { $conn->close(); } ?>
</body>
</html>
