<?php
// front-end/frames/panel-admin/resultados.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/back-end/auth/verificar-sesion.php';
requerir_admin();

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}



// Obtener el nivel desde la URL (por ejemplo: resultados.php?nivel=primaria)
$nivelNombre = isset($_GET['nivel']) ? strtolower(trim($_GET['nivel'])) : '';

// Mapeo de nombres a IDs de nivel
$nivelesMap = [
    'preescolar'   => 1,
    'primaria'     => 2,
    'secundaria'   => 3,
    'preparatoria' => 4
];

// Validar que el nivel sea válido
if (!array_key_exists($nivelNombre, $nivelesMap)) {
    http_response_code(400);
    echo "Nivel no válido. Use: preescolar, primaria, secundaria o preparatoria.";
    exit;
}

$nivelId = $nivelesMap[$nivelNombre];

// Conectar a la base de datos
$__opened_here = false;
if (!isset($conn) || !($conn instanceof mysqli)) {
  require_once __DIR__ . '/../../../back-end/connect-db/conexion-db.php';
  $__opened_here = true;
}

// Obtener el ID de la encuesta para este nivel
$encuestaId = 0;
$stmt = $conn->prepare("SELECT id_encuesta FROM encuestas WHERE id_nivel = ? ORDER BY id_encuesta LIMIT 1");
$stmt->bind_param("i", $nivelId);
$stmt->execute();
$stmt->bind_result($encuestaId);
$stmt->fetch();
$stmt->close();

// Obtener las escuelas del nivel actual para el filtro
$escuelasDelNivel = [];
$stmt = $conn->prepare("SELECT id_escuela, nombre_escuela FROM escuelas WHERE id_nivel = ? ORDER BY nombre_escuela");
$stmt->bind_param("i", $nivelId);
$stmt->execute();
$rsEscuelas = $stmt->get_result();
while ($escuela = $rsEscuelas->fetch_assoc()) {
  $escuelasDelNivel[] = [
    'id' => (int)$escuela['id_escuela'],
    'nombre' => $escuela['nombre_escuela']
  ];
}
$stmt->close();

// Obtener el filtro de escuela seleccionado (si existe)
$escuelaFiltro = isset($_GET['escuela']) ? (int)$_GET['escuela'] : 0;

if ((int)$encuestaId <= 0) {
  http_response_code(404);
  echo "No se encontró encuesta para el nivel: " . htmlspecialchars($nivelNombre);
  if ($__opened_here && isset($conn) && $conn instanceof mysqli) { $conn->close(); }
  exit;
}

// Cargar preguntas
$sqlPreg = "SELECT id_pregunta, id_encuesta, texto_pregunta, 
                   COALESCE(tipo_pregunta,'opcion') AS tipo_pregunta,
                   COALESCE(orden, id_pregunta) AS orden
            FROM preguntas
            WHERE id_encuesta = ?
            ORDER BY orden ASC";
$stmt = $conn->prepare($sqlPreg);
if (!$stmt) { 
    http_response_code(500); 
    echo "Error preparando consulta: ".$conn->error; 
    exit; 
}
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

// Cargar estadísticas de respuestas por pregunta y opción (filtrado por escuela si aplica)
$estadisticasPorPregunta = [];
if (!empty($ids)) {
  $ph = implode(',', array_fill(0, count($ids), '?'));
  $types = str_repeat('i', count($ids));
  
  // Query base para contar respuestas por opción
  $sqlStats = "SELECT r.id_pregunta, r.id_opcion, COUNT(*) as total_respuestas
               FROM respuestas_usuario r";
  
  // Si hay filtro de escuela específica
  if ($escuelaFiltro > 0) {
    $sqlStats .= " WHERE r.id_pregunta IN ($ph) AND r.id_escuela = ?";
    $types .= 'i';
    $params = array_merge($ids, [$escuelaFiltro]);
  } else {
    // Sin filtro, todas las respuestas
    $sqlStats .= " WHERE r.id_pregunta IN ($ph)";
    $params = $ids;
  }
  
  $sqlStats .= " GROUP BY r.id_pregunta, r.id_opcion";
  
  $stmt = $conn->prepare($sqlStats);
  if ($stmt) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $rsStats = $stmt->get_result();
    
    while ($stat = $rsStats->fetch_assoc()) {
      $pid = (int)$stat['id_pregunta'];
      $oid = (int)$stat['id_opcion'];
      $total = (int)$stat['total_respuestas'];
      
      if (!isset($estadisticasPorPregunta[$pid])) {
        $estadisticasPorPregunta[$pid] = [];
      }
      $estadisticasPorPregunta[$pid][$oid] = $total;
    }
    $stmt->close();
  }
}

// Cargar opciones con sus estadísticas
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
      $oid = (int)$opt['id_opcion'];
      
      // Obtener el total de respuestas para esta opción
      $totalRespuestas = isset($estadisticasPorPregunta[$pid][$oid]) 
        ? $estadisticasPorPregunta[$pid][$oid] 
        : 0;
      
      $opcionesPorPregunta[$pid][] = [
        'id_opcion' => $oid,
        'texto'     => $opt['texto_opcion'],
        'icono'     => $opt['icono'],
        'valor'     => isset($opt['valor']) ? (int)$opt['valor'] : null,
        'total'     => $totalRespuestas
      ];
    }
    $stmt->close();
  }
}
$conn->close();

$palette = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#84cc16','#f97316','#e11d48','#22c55e'];

// Nombres bonitos para mostrar
$nombresBonitos = [
    'preescolar'   => 'Preescolar',
    'primaria'     => 'Primaria',
    'secundaria'   => 'Secundaria',
    'preparatoria' => 'Preparatoria'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Resultados <?php echo $nombresBonitos[$nivelNombre]; ?> | Panel Admin</title>
  <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/layout.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/admin/admin.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/admin/resultados.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/global.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/admin/admin.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/resultados.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
  <?php include $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/front-end/includes/header-admin.php'; ?>

  <div class="toolbar">
    <a class="btn" href="../panel/panel-admin.php">← Volver</a>
  </div>

  <div class="res-header">
    <h1>Resultados de <?php echo htmlspecialchars($nombresBonitos[$nivelNombre]); ?></h1>
  </div>

  <section class="filtros-section">
    <form method="GET" action="" class="filtro-group">
      <input type="hidden" name="nivel" value="<?php echo htmlspecialchars($nivelNombre); ?>">
      <label class="filtro-label" for="escuela-filter">Filtrar por escuela:</label>
      <select name="escuela" id="escuela-filter" class="filtro-select" onchange="this.form.submit()">
        <option value="0" <?php echo $escuelaFiltro === 0 ? 'selected' : ''; ?>>Todas las escuelas</option>
        <?php foreach ($escuelasDelNivel as $esc): ?>
          <option value="<?php echo $esc['id']; ?>" <?php echo $escuelaFiltro === $esc['id'] ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($esc['nombre']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>
  </section>

  <?php if (empty($preguntas)): ?>
    <section style="padding:16px;color:#6b7280">No se encontraron preguntas para esta encuesta.</section>
  <?php else: ?>
    <main class="res-wrapper">
      <?php foreach ($preguntas as $i => $p):
        $pid   = (int)$p['id_pregunta'];
        $tipo  = $p['tipo_pregunta'];
        $lista = $opcionesPorPregunta[$pid] ?? [];
        foreach ($lista as $k => $op) { $lista[$k]['color'] = $palette[$k % count($palette)]; }
      ?>
        <article class="res-card" id="pregunta-<?php echo $pid; ?>">
          <h2 class="pregunta-titulo"><?php echo htmlspecialchars(($i+1).'. '.$p['texto_pregunta']); ?></h2>
          
          <?php if ($tipo === 'texto'): ?>
            <!-- Pregunta de texto: solo mostrar botón -->
            <a class="btn-ver-respuestas" href="ver_respuestas_texto.php?pregunta=<?php echo $pid; ?>&nivel=<?php echo htmlspecialchars($nivelNombre); ?><?php echo $escuelaFiltro > 0 ? '&escuela='.$escuelaFiltro : ''; ?>">
              Ver respuestas de texto
            </a>
          <?php else: ?>
            <!-- Pregunta de opción múltiple: mostrar gráfica y leyenda colapsable -->
            <div class="pie-slot" id="pie-q<?php echo $pid; ?>" data-pregunta-id="<?php echo $pid; ?>" data-tipo="<?php echo $tipo; ?>">
              <canvas id="chart-<?php echo $pid; ?>"></canvas>
            </div>
            
            <button class="toggle-legend" onclick="toggleLegend(<?php echo $pid; ?>)" id="toggle-<?php echo $pid; ?>">
              <span>Ver leyenda</span>
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            
            <div class="legend collapsed" id="legend-q<?php echo $pid; ?>">
              <?php if (empty($lista)): ?>
                <div class="legend-item" style="color:#9aa4b2">No hay opciones configuradas.</div>
              <?php else: 
                $totalPregunta = array_sum(array_column($lista, 'total'));
                foreach ($lista as $op): 
                  $porcentaje = $totalPregunta > 0 ? round(($op['total'] / $totalPregunta) * 100, 1) : 0;
              ?>
                <div class="legend-item" data-opcion-id="<?php echo (int)$op['id_opcion']; ?>" data-total="<?php echo (int)$op['total']; ?>">
                  <span class="legend-color" style="background: <?php echo htmlspecialchars($op['color']); ?>"></span>
                  <span><?php echo htmlspecialchars($op['texto']); ?></span>
                  <span class="legend-total"><?php echo $op['total']; ?> (<?php echo $porcentaje; ?>%)</span>
                </div>
              <?php endforeach; endif; ?>
            </div>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </main>
  <?php endif; ?>

  <footer>
    <?php include $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/front-end/includes/footer-admin.php'; ?>
  </footer>

  <script>
    window.ENCUESTA_ID = <?php echo (int)$encuestaId; ?>;
    window.NIVEL_NOMBRE = '<?php echo htmlspecialchars($nivelNombre); ?>';
    window.ESCUELA_FILTRO = <?php echo (int)$escuelaFiltro; ?>;

    // Función para toggle de leyenda
    function toggleLegend(preguntaId) {
      const legend = document.getElementById('legend-q' + preguntaId);
      const toggleBtn = document.getElementById('toggle-' + preguntaId);
      const toggleText = toggleBtn.querySelector('span');
      
      if (legend.classList.contains('collapsed')) {
        legend.classList.remove('collapsed');
        legend.classList.add('expanded');
        toggleBtn.classList.add('active');
        toggleText.textContent = 'Ocultar leyenda';
      } else {
        legend.classList.remove('expanded');
        legend.classList.add('collapsed');
        toggleBtn.classList.remove('active');
        toggleText.textContent = 'Ver leyenda';
      }
    }

    // Esperar a que el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
      // Obtener todas las preguntas que no son de tipo texto
      const preguntasCards = document.querySelectorAll('.res-card');
      
      preguntasCards.forEach(card => {
        const pieSlot = card.querySelector('.pie-slot');
        const tipoPregunta = pieSlot ? pieSlot.getAttribute('data-tipo') : null;
        
        // Solo procesar preguntas de opción múltiple
        if (!pieSlot || tipoPregunta === 'texto') return;
        
        const preguntaId = pieSlot.getAttribute('data-pregunta-id');
        const legendDiv = card.querySelector('.legend');
        const canvas = card.querySelector('canvas');
        
        if (!canvas || !legendDiv) return;
        
        // Obtener datos de las opciones desde la leyenda
        const legendItems = legendDiv.querySelectorAll('.legend-item[data-opcion-id]');
        const labels = [];
        const data = [];
        const colors = [];
        
        let hasData = false;
        legendItems.forEach(item => {
          const texto = item.querySelector('span:nth-child(2)').textContent.trim();
          const total = parseInt(item.getAttribute('data-total')) || 0;
          const color = item.querySelector('.legend-color').style.background;
          
          labels.push(texto);
          data.push(total);
          colors.push(color);
          
          if (total > 0) hasData = true;
        });
        
        // Si no hay datos, mostrar mensaje pero mantener el canvas
        if (!hasData) {
          const ctx = canvas.getContext('2d');
          ctx.font = '14px Arial';
          ctx.fillStyle = '#8b7d6b';
          ctx.textAlign = 'center';
          ctx.textBaseline = 'middle';
          ctx.fillText('Sin respuestas aún', canvas.width / 2, canvas.height / 2);
          return;
        }
        
        // Crear la gráfica de pastel
        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
          type: 'pie',
          data: {
            labels: labels,
            datasets: [{
              data: data,
              backgroundColor: colors,
              borderColor: '#ffffff',
              borderWidth: 2
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
              legend: {
                display: false // Usamos nuestra propia leyenda
              },
              tooltip: {
                callbacks: {
                  label: function(context) {
                    const label = context.label || '';
                    const value = context.parsed || 0;
                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                    return label + ': ' + value + ' (' + percentage + '%)';
                  }
                }
              }
            }
          }
        });
      });
    });
  </script>
</body>
</html>