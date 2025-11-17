<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/back-end/auth/verificar-sesion.php';
requerir_admin();

// Estas variables ya vienen desde el controlador
// $nivelNombre, $escuelaFiltro, $escuelasDelNivel
// $preguntas, $opcionesPorPregunta, $palette

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
  
  <!-- Framework y estilos base -->
  <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/layout.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/admin/admin.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/admin/resultados.css">
  
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
  <?php include $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/front-end/includes/header-admin.php'; ?>

  <div class="toolbar">
    <a class="btn" href="/SIMPINNA/front-end/frames/panel/panel-admin.php">Volver</a>

    
    <!-- BOTONES DE EXPORTACIÓN GLOBAL -->
    <div class="export-buttons-global">
      <button class="btn-export-global btn-csv-global" onclick="exportarTodosCSV()" title="Exportar todas las respuestas a CSV">
        CSV
      </button>
      <button class="btn-export-global btn-excel-global" onclick="exportarTodosExcel()" title="Exportar todas las respuestas a Excel">
        Excel
      </button>
      <button class="btn-export-global btn-pdf-global" onclick="exportarTodosPDF()" title="Exportar todas las respuestas a PDF">
        PDF
      </button>
      <button class="btn-export-global btn-print-global" onclick="exportarTodosPrint()" title="Imprimir todas las respuestas">
        Imprimir
      </button>
    </div>
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
            <!-- Preguntas de texto: icono + botón -->
            <div class="encuesta-icon-container">
              <div class="encuesta-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                  <polyline points="14 2 14 8 20 8"></polyline>
                  <line x1="12" y1="19" x2="12" y2="11"></line>
                  <line x1="9" y1="14" x2="15" y2="14"></line>
                </svg>
              </div>
            </div>
            <button class="btn-ver-respuestas" onclick="abrirRespuestas(<?php echo $pid; ?>, '<?php echo $nivelNombre; ?>', <?php echo $escuelaFiltro; ?>)">
              Ver respuestas de texto
            </button>

        <?php else: ?>
  <!-- Preguntas de opciones múltiples -->
  <div
    class="pie-slot"
    id="pie-q<?php echo $pid; ?>"
    data-pregunta-id="<?php echo $pid; ?>"
    data-titulo="<?php echo htmlspecialchars($p['texto_pregunta'], ENT_QUOTES); ?>"
    data-labels='<?php echo json_encode(array_column($lista, "texto"), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>'
    data-values='<?php echo json_encode(array_column($lista, "total"), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>'
    data-colors='<?php echo json_encode(array_column($lista, "color"), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>'
  >
    <canvas id="chart-<?php echo $pid; ?>"></canvas>
  </div>

  <!-- BOTÓN DESPLEGABLE LEYENDA -->
  <button class="toggle-legend"
          onclick="SimpinnaResultados.toggleLegend(<?php echo $pid; ?>)"
          id="toggle-<?php echo $pid; ?>">
    <span>Ver leyenda</span>
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
    </svg>
  </button>

  <!-- LEYENDA DESPLEGABLE -->
  <div class="legend collapsed" id="legend-<?php echo $pid; ?>">
    <?php if (empty($lista)): ?>
      <div class="legend-item" style="color:#9aa4b2">No hay opciones configuradas.</div>
    <?php else: ?>
      <?php 
        $totalPregunta = array_sum(array_column($lista, 'total'));
        foreach ($lista as $op): 
          $porcentaje = $totalPregunta > 0 ? round(($op['total'] / $totalPregunta) * 100, 1) : 0;
      ?>
        <div class="legend-item">
          <span class="legend-color" style="background: <?php echo htmlspecialchars($op['color']); ?>"></span>
          <span class="legend-text"><?php echo htmlspecialchars($op['texto']); ?></span>
          <span class="legend-total"><?php echo $op['total']; ?> (<?php echo $porcentaje; ?>%)</span>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

        <!-- BOTONES DE EXPORTACIÓN (INDIVIDUALES) -->
          <div class="export-buttons">
            <button class="btn-export btn-csv"  onclick="exportarGrafica(<?php echo $pid; ?>, 'csv')">CSV</button>
            <button class="btn-export btn-excel"  onclick="exportarGrafica(<?php echo $pid; ?>, 'excel')">Excel</button>
            <button class="btn-export btn-pdf"   onclick="exportarGrafica(<?php echo $pid; ?>, 'pdf')">PDF</button>
            <button class="btn-export btn-print" onclick="exportarGrafica(<?php echo $pid; ?>, 'print')">Imprimir</button>
            </div>
          <?php endif; ?>

        </article>
      <?php endforeach; ?>
    </main>
  <?php endif; ?>

  <!-- Modal para respuestas de texto -->
  <div id="modalRespuestas" class="modal-respuestas hidden">
    <div class="modal-overlay" onclick="cerrarRespuestas()"></div>
    <div class="modal-contenedor">
      <div class="modal-header">
        <h2 id="modalTitulo">Respuestas de texto</h2>
        <button class="modal-close" onclick="cerrarRespuestas()">&times;</button>
      </div>
      <div id="modalContenido" class="modal-contenido">
        <div class="loading">Cargando respuestas...</div>
      </div>
    </div>
  </div>

  <?php include $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/front-end/includes/footer-admin.php'; ?>


  
    <!-- Módulos JS de resultados -->
  <script src="/SIMPINNA/front-end/scripts/admin/resultados/helpers.js"></script>
  <script src="/SIMPINNA/front-end/scripts/admin/resultados/graficas.js"></script>
  <script src="/SIMPINNA/front-end/scripts/admin/resultados/export-pregunta.js"></script>
  <script src="/SIMPINNA/front-end/scripts/admin/resultados/export-global.js"></script>
  <script src="/SIMPINNA/front-end/scripts/admin/resultados/modal-respuestas.js"></script>
  <!-- Librerías para exportar -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</body>
</html>