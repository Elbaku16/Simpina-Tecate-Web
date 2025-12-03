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
 <style>
    /* ============================================
       REDISEÑO DE TOOLBAR Y BOTONES - VERSIÓN GRANDE GUINDA
       ============================================ */
    
    .toolbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1.5rem 0;
      gap: 2rem;
      background: linear-gradient(135deg, #ffffff 0%, #f9f5f0 100%);
      border-radius: 12px;
      padding: 1.5rem 2rem;
      box-shadow: 0 2px 8px rgba(107, 46, 46, 0.08);
      margin-bottom: 2rem;
    }

    /* Botón Regresar mejorado */
    .toolbar .btn {
      background: white;
      color: #6b2e2e;
      border: 2px solid #6b2e2e;
      padding: 0.9rem 1.8rem;
      border-radius: 8px;
      font-weight: 600;
      font-size: 1rem;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      white-space: nowrap;
      box-shadow: 0 2px 4px rgba(107, 46, 46, 0.1);
    }

    .toolbar .btn:hover {
      background: #6b2e2e;
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(107, 46, 46, 0.2);
    }

    .toolbar .btn .icon {
      font-size: 1.2em;
    }

    /* Contenedor de exportación */
    .export-controls {
      display: flex;
      align-items: center;
      gap: 1.5rem;
    }

    /* Leyenda mejorada */
    .export-legend {
      font-size: 1rem;
      color: #6b2e2e;
      font-weight: 600;
      padding-right: 1rem;
      border-right: 2px solid #d6bd55;
    }

    /* Botones de exportación GRANDES con color GUINDA */
    .export-buttons-global {
      display: flex;
      gap: 0.75rem;
    }

    .btn-export-global {
      padding: 1rem 2rem;
      border: none;
      border-radius: 8px;
      font-weight: 700;
      font-size: 1.05rem;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 3px 8px rgba(107, 46, 46, 0.2);
      position: relative;
      overflow: hidden;
      background: linear-gradient(135deg, #7A1E2C 0%, #6b2e2e 100%);
      color: white;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .btn-export-global::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
      transition: left 0.5s;
    }

    .btn-export-global:hover::before {
      left: 100%;
    }

    .btn-export-global:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(107, 46, 46, 0.35);
      background: linear-gradient(135deg, #611232 0%, #4a1f1f 100%);
    }

    .btn-export-global:active {
      transform: translateY(-1px);
    }

    /* Colores específicos para cada botón - TODOS GUINDA */
    .btn-csv-global {
      background: linear-gradient(135deg, #7A1E2C 0%, #6b2e2e 100%);
    }

    .btn-csv-global:hover {
      background: linear-gradient(135deg, #611232 0%, #4a1f1f 100%);
    }

    .btn-excel-global {
      background: linear-gradient(135deg, #8B2635 0%, #7A1E2C 100%);
    }

    .btn-excel-global:hover {
      background: linear-gradient(135deg, #7A1E2C 0%, #611232 100%);
    }

    .btn-pdf-global {
      background: linear-gradient(135deg, #611232 0%, #4a1f1f 100%);
    }

    .btn-pdf-global:hover {
      background: linear-gradient(135deg, #4a1f1f 0%, #2d1212 100%);
    }

    .btn-print-global {
      background: linear-gradient(135deg, #4a1f1f 0%, #2d1212 100%);
    }

    .btn-print-global:hover {
      background: linear-gradient(135deg, #2d1212 0%, #1a0808 100%);
    }

    /* RESPONSIVE MÓVIL */
    @media (max-width: 768px) {
      .toolbar {
        flex-direction: column;
        gap: 1.5rem;
        padding: 1.25rem 1rem;
      }

      .toolbar .btn {
        width: 100%;
        justify-content: center;
        font-size: 1rem;
      }

      .export-controls {
        flex-direction: column;
        width: 100%;
        gap: 1rem;
      }

      .export-legend {
        text-align: center;
        border-right: none;
        border-bottom: 2px solid #d6bd55;
        padding-right: 0;
        padding-bottom: 0.75rem;
        width: 100%;
        font-size: 0.95rem;
      }

      .export-buttons-global {
        width: 100%;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.75rem;
      }

      .btn-export-global {
        flex: 1;
        min-width: calc(50% - 0.375rem);
        padding: 1.1rem 1rem;
        font-size: 1rem;
      }
    }

    @media (max-width: 480px) {
      .export-buttons-global {
        flex-direction: column;
      }

      .btn-export-global {
        width: 100%;
        min-width: 100%;
        padding: 1.2rem 1rem;
      }
    }
  </style>
</head>
<body>
  <?php include $_SERVER['DOCUMENT_ROOT'].'/front-end/includes/header-admin.php'; ?>

  <div class="toolbar">
    <a class="btn" href="/front-end/frames/panel/panel-admin.php">
      <span class="icon">↩</span> Regresar al Panel
    </a>

    <div class="export-controls">
      <span class="export-legend">
        Exportar datos de preguntas gráficas
      </span>
      <div class="export-buttons-global">
        <button class="btn-export-global btn-csv-global" onclick="exportarTodosCSV()" title="Exportar a CSV">
          CSV
        </button>
        <button class="btn-export-global btn-excel-global" onclick="exportarTodosExcel()" title="Exportar a Excel">
          Excel
        </button>
        <button class="btn-export-global btn-pdf-global" onclick="exportarTodosPDF()" title="Exportar a PDF">
          PDF
        </button>
        <button class="btn-export-global btn-print-global" onclick="exportarTodosPrint()" title="Imprimir">
          Imprimir
        </button>
      </div>
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
            <option value="0" <?php echo ($escuelaFiltro ?? 0) == 0 ? 'selected' : ''; ?>>
                Todas las escuelas
            </option>
            <option value="9999" <?php echo ($escuelaFiltro ?? 0) == 9999 ? 'selected' : ''; ?>>
                No estudia actualmente
            </option>
            <?php foreach ($escuelasDelNivel as $esc): ?>
                <option value="<?php echo $esc['id']; ?>" 
                    <?php echo ($escuelaFiltro ?? 0) == $esc['id'] ? 'selected' : ''; ?>>
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
        $qNum = $i + 1;
        $lista = $opcionesPorPregunta[$pid] ?? [];
        
        foreach ($lista as $k => $op) { $lista[$k]['color'] = $palette[$k % count($palette)]; }
        
        $esNoGraficable = in_array($tipo, ['texto', 'dibujo', 'imagen', 'canvas']);
        $esRanking = $tipo === 'ranking';
      ?>
        <article class="res-card" id="pregunta-<?php echo $pid; ?>">
          <h2 class="pregunta-titulo"><?php echo htmlspecialchars($qNum.'. '.$p['texto_pregunta']); ?></h2>
          
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
            <button class="btn-ver-respuestas" onclick="abrirRespuestas(<?php echo $pid; ?>, '<?php echo $nivelNombre; ?>', <?php echo $escuelaFiltro; ?>, <?php echo $qNum; ?>)">
              Ver respuestas de <?php echo $tipo === 'texto' ? 'texto' : 'dibujo'; ?>
            </button>

            <div class="export-buttons">
              <button class="btn-export btn-csv"  onclick="abrirRespuestasYExportar(<?php echo $pid; ?>, '<?php echo $nivelNombre; ?>', <?php echo $escuelaFiltro; ?>, 'csv', <?php echo $qNum; ?>)">CSV</button>
              <button class="btn-export btn-excel"  onclick="abrirRespuestasYExportar(<?php echo $pid; ?>, '<?php echo $nivelNombre; ?>', <?php echo $escuelaFiltro; ?>, 'excel', <?php echo $qNum; ?>)">Excel</button>
              <button class="btn-export btn-pdf"   onclick="abrirRespuestasYExportar(<?php echo $pid; ?>, '<?php echo $nivelNombre; ?>', <?php echo $escuelaFiltro; ?>, 'pdf', <?php echo $qNum; ?>)">PDF</button>
              <button class="btn-export btn-print" onclick="abrirRespuestasYExportar(<?php echo $pid; ?>, '<?php echo $nivelNombre; ?>', <?php echo $escuelaFiltro; ?>, 'print', <?php echo $qNum; ?>)">Imprimir</button>
            </div>

          <?php elseif ($esRanking): ?>
            <div class="ranking-slot" id="ranking-q<?php echo $pid; ?>">
                <div class="ranking-header">
                  <div class="ranking-label">Posición</div>
                  <div class="ranking-label">Opciones</div>
                </div>

                <?php 
                  usort($lista, fn($a, $b) => $a['promedio'] <=> $b['promedio']);
                  
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
                            <div class="ranking-posicion-num">#<?php echo $idx + 4; ?></div>
                            <div class="ranking-barra-wrap">
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
              <button class="btn-export btn-csv"  onclick="exportarGrafica(<?php echo $pid; ?>, 'csv')">CSV</button>
              <button class="btn-export btn-excel"  onclick="exportarGrafica(<?php echo $pid; ?>, 'excel')">Excel</button>
              <button class="btn-export btn-pdf"   onclick="exportarGrafica(<?php echo $pid; ?>, 'pdf')">PDF</button>
              <button class="btn-export btn-print" onclick="exportarGrafica(<?php echo $pid; ?>, 'print')">Imprimir</button>
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
              <span>Ver leyenda</span>
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

  <?php include $_SERVER['DOCUMENT_ROOT'].'/front-end/includes/footer-admin.php'; ?>

  <script src="/front-end/scripts/admin/resultados/helpers.js"></script>
  <script src="/front-end/scripts/admin/resultados/graficas.js"></script>
  <script src="/front-end/scripts/admin/resultados/export-pregunta.js"></script>
  <script src="/front-end/scripts/admin/resultados/export-global.js"></script>
  <script src="/front-end/scripts/admin/resultados/modal-respuestas.js"></script>
  
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  
  <script>
    function abrirRespuestasYExportar(idPregunta, nivel, escuela, formato, qNum) {
      abrirRespuestas(idPregunta, nivel, escuela, qNum);
      setTimeout(function() {
        exportarRespuestasTexto(formato);
      }, 1000);
    }
  </script>
</body>
</html>