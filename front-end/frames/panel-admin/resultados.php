<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/back-end/auth/verificar-sesion.php';
requerir_admin();

// Estas variables ya vienen desde el controlador:
// $nivelNombre, $escuelaFiltro, $escuelasDelNivel
// $preguntas, $opcionesPorPregunta, $palette
// $cicloFiltro, $generoFiltro, $ciclosDisponibles, $totalRespuestas

$nombresBonitos = [
    'preescolar'   => 'Preescolar',
    'primaria'     => 'Primaria',
    'secundaria'   => 'Secundaria',
    'preparatoria' => 'Preparatoria'
];

$nombreNivel = $nombresBonitos[$nivelNombre] ?? 'Resultados';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Resultados <?php echo $nombreNivel; ?> | Panel Admin</title>
  
  <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
  <link rel="stylesheet" href="/front-end/assets/css/global/layout.css">
  <link rel="stylesheet" href="/front-end/assets/css/admin/admin.css">
  <link rel="stylesheet" href="/front-end/assets/css/admin/resultados.css">
  <link rel="stylesheet" href="/front-end/assets/css/admin/modal-respuestas.css">
  
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
  <?php include $_SERVER['DOCUMENT_ROOT'].'/front-end/includes/header-admin.php'; ?>

  <div class="toolbar">
    <a class="btn" href="/front-end/frames/panel/panel-admin.php"><span class="icon">↩</span> Regresar al Panel</a>

    
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
    <h1>Resultados de <?php echo htmlspecialchars($nombreNivel); ?></h1>
  </div>

  <section class="filtros-section">
    <form method="GET" action="" class="filtro-group">
      <input type="hidden" name="nivel" value="<?php echo htmlspecialchars($nivelNombre); ?>">
      
      <div class="filtro-item">
        <label class="filtro-label" for="escuela-filter">Escuela:</label>
        <select name="escuela" id="escuela-filter" class="filtro-select">
          <option value="0" <?php echo ($escuelaFiltro ?? 0) === 0 ? 'selected' : ''; ?>>Todas las escuelas</option>
          <?php foreach ($escuelasDelNivel as $esc): ?>
            <option value="<?php echo $esc['id']; ?>" <?php echo ($escuelaFiltro ?? 0) === $esc['id'] ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($esc['nombre']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      
      <div class="filtro-item">
        <label class="filtro-label" for="ciclo-filter">Ciclo Escolar:</label>
        <select name="ciclo" id="ciclo-filter" class="filtro-select">
          <option value="" <?php echo ($cicloFiltro ?? '') === '' ? 'selected' : ''; ?>>Todos</option>
          <?php 
            if (isset($ciclosDisponibles) && is_array($ciclosDisponibles)):
                foreach ($ciclosDisponibles as $ciclo): 
          ?>
            <option value="<?php echo htmlspecialchars($ciclo['label']); ?>" 
                    <?php echo ($cicloFiltro ?? '') === $ciclo['label'] ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($ciclo['label']); ?>
            </option>
          <?php 
                endforeach;
            endif; 
          ?>
        </select>
      </div>

      <div class="filtro-item">
        <label class="filtro-label" for="genero-filter">Género:</label>
        <select name="genero" id="genero-filter" class="filtro-select">
          <option value="" <?php echo ($generoFiltro ?? '') === '' ? 'selected' : ''; ?>>Todos</option>
          <option value="M" <?php echo ($generoFiltro ?? '') === 'M' ? 'selected' : ''; ?>>Hombre</option>
          <option value="F" <?php echo ($generoFiltro ?? '') === 'F' ? 'selected' : ''; ?>>Mujer</option>
          <option value="O" <?php echo ($generoFiltro ?? '') === 'O' ? 'selected' : ''; ?>>Otro</option>
          <option value="X" <?php echo ($generoFiltro ?? '') === 'X' ? 'selected' : ''; ?>>Prefiero no decir</option>
        </select>
      </div>

      <button type="submit" class="btn btn-export-global" style="background: var(--burgundy); color: white;">
        Aplicar Filtros
      </button>
    </form>
  </section>

  <?php if (empty($preguntas)): ?>
    <section style="padding:16px;color:#6b7280">No se encontraron preguntas para esta encuesta.</section>
  <?php else: ?>
    <main class="res-wrapper">
      <?php foreach ($preguntas as $i => $p):
        $pid   = (int)$p['id_pregunta'];
        $tipo  = strtolower(trim($p['tipo_pregunta'])); 
        $lista = $opcionesPorPregunta[$pid] ?? [];
        
        // Asignar color y preparar lista para Ranking o Pie Chart
        foreach ($lista as $k => $op) { $lista[$k]['color'] = $palette[$k % count($palette)]; }
        
        $esNoGraficable = in_array($tipo, ['texto', 'dibujo', 'imagen', 'canvas']);
        $esRanking = $tipo === 'ranking';
      ?>
        <article class="res-card" id="pregunta-<?php echo $pid; ?>">
          <h2 class="pregunta-titulo"><?php echo htmlspecialchars(($i+1).'. '.$p['texto_pregunta']); ?></h2>
          
          <?php if ($esNoGraficable): ?>
            <div class="encuesta-icon-container">
              <div class="encuesta-icon">
                <?php if ($tipo === 'texto'): ?>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="12" y1="19" x2="12" y2="11"></line>
                    <line x1="9" y1="14" x2="15" y2="14"></line>
                  </svg>
                <?php else: ?>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                    <polyline points="21 15 16 10 5 21"></polyline>
                  </svg>
                <?php endif; ?>
              </div>
            </div>
            <button class="btn-ver-respuestas" onclick="abrirRespuestas(<?php echo $pid; ?>, '<?php echo $nivelNombre; ?>', <?php echo $escuelaFiltro; ?>)">
              Ver respuestas de <?php echo $tipo === 'texto' ? 'texto' : 'dibujo'; ?>
            </button>

            <div class="export-buttons">
              <button class="btn-export btn-csv"  onclick="abrirRespuestasYExportar(<?php echo $pid; ?>, '<?php echo $nivelNombre; ?>', <?php echo $escuelaFiltro; ?>, 'csv')">CSV</button>
              <button class="btn-export btn-excel"  onclick="abrirRespuestasYExportar(<?php echo $pid; ?>, '<?php echo $nivelNombre; ?>', <?php echo $escuelaFiltro; ?>, 'excel')">Excel</button>
              <button class="btn-export btn-pdf"   onclick="abrirRespuestasYExportar(<?php echo $pid; ?>, '<?php echo $nivelNombre; ?>', <?php echo $escuelaFiltro; ?>, 'pdf')">PDF</button>
              <button class="btn-export btn-print" onclick="abrirRespuestasYExportar(<?php echo $pid; ?>, '<?php echo $nivelNombre; ?>', <?php echo $escuelaFiltro; ?>, 'print')">Imprimir</button>
            </div>

          <?php elseif ($esRanking): ?>
            <div class="ranking-slot" id="ranking-q<?php echo $pid; ?>">
                
                <div class="ranking-header">
                  <div class="ranking-label">Posición</div>
                  <div class="ranking-label">Opciones</div>
                </div>

                <?php 
                  // 1. Ordenar por promedio (menor promedio = mejor ranking = posición 1)
                  usort($lista, fn($a, $b) => $a['promedio'] <=> $b['promedio']);
                  
                  // 2. Determinar la escala máxima para la longitud de la barra
                  $peorPromedio = 0;
                  foreach ($lista as $op) {
                      if (($op['promedio'] ?? 0) !== 0) {
                          $peorPromedio = max($peorPromedio, $op['promedio']);
                      }
                  }
                  $maxEscala = max(count($lista), 5, $peorPromedio * 1.1);
                  
                  $top3 = array_slice($lista, 0, 3);
                  $restantes = array_slice($lista, 3);
                  $tieneRestantes = !empty($restantes);

                  // Mostrar el Top 3
                  foreach ($top3 as $idx => $op): 
                    if ($op['promedio'] === null || $op['total'] === 0) continue;

                    $promedioPos = round($op['promedio'], 2);
                    $longitudBarra = ($maxEscala - $promedioPos + 1) / $maxEscala * 100;
                ?>
                  <div class="ranking-item-bar" title="Posición promedio: <?php echo $promedioPos; ?> (Total votos: <?php echo $op['total']; ?>)">
                    <div class="ranking-posicion-num">#<?php echo $idx + 1; ?></div>
                    <div class="ranking-barra-wrap">
                      <span class="ranking-texto"><?php echo htmlspecialchars($op['texto']); ?></span>
                      <div class="ranking-barra" style="width: <?php echo $longitudBarra; ?>%; background: <?php echo $palette[$idx % count($palette)]; ?>;">
                        <span class="ranking-promedio"><?php echo $promedioPos; ?></span>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
                
                <?php if ($tieneRestantes): ?>
                    <div class="ranking-restantes collapsed" id="ranking-restantes-<?php echo $pid; ?>">
                        <?php 
                        foreach ($restantes as $idx => $op): 
                            if ($op['promedio'] === null || $op['total'] === 0) continue;

                            $promedioPos = round($op['promedio'], 2);
                            $longitudBarra = ($maxEscala - $promedioPos + 1) / $maxEscala * 100;
                        ?>
                          <div class="ranking-item-bar" title="Posición promedio: <?php echo $promedioPos; ?> (Total votos: <?php echo $op['total']; ?>)">
                            <div class="ranking-posicion-num">#<?php echo $idx + 4; ?></div> <div class="ranking-barra-wrap">
                              <span class="ranking-texto"><?php echo htmlspecialchars($op['texto']); ?></span>
                              <div class="ranking-barra" style="width: <?php echo $longitudBarra; ?>%; background: <?php echo $palette[($idx + 3) % count($palette)]; ?>;">
                                <span class="ranking-promedio"><?php echo $promedioPos; ?></span>
                              </div>
                            </div>
                          </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($lista) || $peorPromedio === 0): ?>
                    <div class="sin-respuestas" style="padding: 16px;">No hay datos de ranking para mostrar con estos filtros.</div>
                <?php endif; ?>

            </div>
            
            <?php if ($tieneRestantes): ?>
                <button class="btn-ver-respuestas ranking-toggle-btn"
                        onclick="SimpinnaResultados.toggleRankingLegend(<?php echo $pid; ?>)"
                        id="toggle-ranking-<?php echo $pid; ?>"
                        style="margin-top: 15px;">
                  Ver más posiciones
                </button>
            <?php endif; ?>

            <div class="legend" style="padding: 10px; font-size: 0.9em; color: var(--burgundy);">
              <p style="margin:0;">**Interpretación:** La barra más larga representa la opción con el promedio de posición más bajo (más cercana al **#1**).</p>
            </div>

            <div class="export-buttons">
              <button class="btn-export btn-csv"  onclick="exportarRanking(<?php echo $pid; ?>, 'csv')">CSV</button>
              <button class="btn-export btn-excel"  onclick="exportarRanking(<?php echo $pid; ?>, 'excel')">Excel</button>
              <button class="btn-export btn-pdf"   onclick="exportarRanking(<?php echo $pid; ?>, 'pdf')">PDF</button>
              <button class="btn-export btn-print" onclick="exportarRanking(<?php echo $pid; ?>, 'print')">Imprimir</button>
            </div>


          <?php else: ?>
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

     <button class="toggle-legend"
        onclick="SimpinnaResultados.toggleLegend(<?php echo $pid; ?>)"
        id="toggle-<?php echo $pid; ?>">
  
  <span class="text-ver">Ver leyenda</span>
  <span class="text-ocultar">Ocultar leyenda</span>
  
  <svg class="icon-arrow" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
  </svg>
</button>

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
<template id="template-card-texto">
  <div class="respuesta-card">
    <div class="respuesta-header">
      <div class="respuesta-info">
        <span class="respuesta-escuela"></span>
        <span class="respuesta-fecha-hora"></span>
      </div>
      <button class="respuesta-eliminar" title="Eliminar respuesta">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="3 6 5 6 21 6"></polyline>
          <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
          <line x1="10" y1="11" x2="10" y2="17"></line>
          <line x1="14" y1="11" x2="14" y2="17"></line>
        </svg>
      </button>
    </div>
    <div class="respuesta-contenido">
      <p class="texto-respuesta"></p>
    </div>
  </div>
</template>

<template id="template-card-dibujo">
  <div class="respuesta-card respuesta-dibujo">
    <div class="respuesta-header">
      <div class="respuesta-info">
        <span class="respuesta-escuela"></span>
        <span class="respuesta-fecha-hora"></span>
      </div>
      <button class="respuesta-eliminar" title="Eliminar respuesta">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="3 6 5 6 21 6"></polyline>
          <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
          <line x1="10" y1="11" x2="10" y2="17"></line>
          <line x1="14" y1="11" x2="14" y2="17"></line>
        </svg>
      </button>
    </div>
    <div class="respuesta-contenido respuesta-imagen-wrapper">
      <img src="" alt="Dibujo" class="respuesta-imagen">
      <span class="imagen-tamano"></span>
      <div class="imagen-no-disponible hidden">Imagen no disponible</div>
    </div>
  </div>
</template>            
  <?php include $_SERVER['DOCUMENT_ROOT'].'/front-end/includes/footer-admin.php'; ?>

  
  <script src="/front-end/scripts/admin/resultados/helpers.js"></script>
  <script src="/front-end/scripts/admin/resultados/graficas.js"></script>
  <script src="/front-end/scripts/admin/resultados/export-pregunta.js"></script>
  <script src="/front-end/scripts/admin/resultados/export-global.js"></script>
  <script src="/front-end/scripts/admin/resultados/modal-respuestas.js"></script>
  
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  
  <script>
    function abrirRespuestasYExportar(idPregunta, nivel, escuela, formato) {
      abrirRespuestas(idPregunta, nivel, escuela);
      setTimeout(function() {
        exportarRespuestasTexto(formato);
      }, 1000); // Esperar a que se carguen los datos
    }
  </script>
</body>
</html>