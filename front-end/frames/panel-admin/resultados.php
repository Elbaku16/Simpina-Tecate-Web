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
            <div class="pie-slot" id="pie-q<?php echo $pid; ?>">
              <canvas id="chart-<?php echo $pid; ?>"></canvas>
            </div>

            <!-- BOTÓN DESPLEGABLE LEYENDA -->
            <button class="toggle-legend" onclick="toggleLegend(<?php echo $pid; ?>)" id="toggle-<?php echo $pid; ?>">
              <span>Ver leyenda</span>
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <!-- LEYENDA DESPLEGABLE -->
            <div class="legend collapsed" id="legend-<?php echo $pid; ?>">
              <?php if (empty($lista)): ?>
                <div class="legend-item" style="color:#9aa4b2">No hay opciones configuradas.</div>
              <?php else: 
                $totalPregunta = array_sum(array_column($lista, 'total'));
                foreach ($lista as $op): 
                  $porcentaje = $totalPregunta > 0 ? round(($op['total'] / $totalPregunta) * 100, 1) : 0;
              ?>
                <div class="legend-item">
                  <span class="legend-color" style="background: <?php echo htmlspecialchars($op['color']); ?>"></span>
                  <span class="legend-text"><?php echo htmlspecialchars($op['texto']); ?></span>
                  <span class="legend-total"><?php echo $op['total']; ?> (<?php echo $porcentaje; ?>%)</span>
                </div>
              <?php endforeach; endif; ?>
            </div>

            <!-- BOTONES DE EXPORTACIÓN -->
            <div class="export-buttons">
              <button class="btn-export btn-csv" onclick="exportarGrafica(<?php echo $pid; ?>, 'csv')" title="Descargar CSV">
                CSV
              </button>
              <button class="btn-export btn-excel" onclick="exportarGrafica(<?php echo $pid; ?>, 'excel')" title="Descargar Excel">
                Excel
              </button>
              <button class="btn-export btn-pdf" onclick="exportarGrafica(<?php echo $pid; ?>, 'pdf')" title="Descargar PDF">
                PDF
              </button>
              <button class="btn-export btn-print" onclick="exportarGrafica(<?php echo $pid; ?>, 'print')" title="Imprimir">
                Imprimir
              </button>
            </div>

            <script>
            (function() {
              const ctx = document.getElementById('chart-<?php echo $pid; ?>').getContext('2d');
              window.chart_<?php echo $pid; ?> = new Chart(ctx, {
                type: 'pie',
                data: {
                  labels: <?php echo json_encode(array_column($lista, 'texto')); ?>,
                  datasets: [{
                    data: <?php echo json_encode(array_column($lista, 'total')); ?>,
                    backgroundColor: <?php echo json_encode(array_column($lista, 'color')); ?>,
                    borderWidth: 2,
                    borderColor: '#fff'
                  }]
                },
                options: {
                  responsive: true,
                  maintainAspectRatio: true,
                  plugins: {
                    legend: { display: false },
                    title: { display: false }
                  }
                }
              });
            })();
            </script>
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

  <script>
  // ========== EXPORTACIÓN GLOBAL (TODAS LAS PREGUNTAS) ==========

  function obtenerDatosGlobales() {
    const datos = [];
    const filtro = obtenerFiltroInfo();
    const fecha = new Date().toLocaleDateString('es-MX');
    
    // Iterar sobre todas las gráficas
    document.querySelectorAll('[id^="pie-q"]').forEach(element => {
      const preguntaId = element.id.replace('pie-q', '');
      const chart = window['chart_' + preguntaId];
      
      if (chart) {
        const titulo = document.querySelector('#pregunta-' + preguntaId + ' .pregunta-titulo').textContent;
        const labels = chart.data.labels;
        const data = chart.data.datasets[0].data;
        const total = data.reduce((a, b) => a + b, 0);
        
        datos.push({
          preguntaId: preguntaId,
          titulo: titulo,
          opciones: labels.map((label, idx) => ({
            label: label,
            respuestas: data[idx],
            porcentaje: total > 0 ? ((data[idx] / total) * 100).toFixed(2) : 0
          })),
          total: total
        });
      }
    });
    
    return { filtro, fecha, datos };
  }

  function exportarTodosCSV() {
    const { filtro, fecha, datos } = obtenerDatosGlobales();
    
    if (datos.length === 0) {
      alert('No hay datos para exportar');
      return;
    }

    let csv = 'RESULTADOS COMPLETOS DE ENCUESTA\n';
    csv += `Filtro: ${filtro}\n`;
    csv += `Fecha de exportación: ${fecha}\n`;
    csv += '\n';

    datos.forEach((pregunta, idx) => {
      csv += `PREGUNTA ${idx + 1}: ${pregunta.titulo}\n`;
      csv += `Opción,Respuestas,Porcentaje\n`;
      
      pregunta.opciones.forEach(op => {
        csv += `"${op.label}",${op.respuestas},${op.porcentaje}%\n`;
      });
      
      csv += `Total,${pregunta.total},100%\n`;
      csv += '\n';
    });

    descargarArchivo(csv, `resultados_completos_${Date.now()}.csv`, 'text/csv');
  }

  function exportarTodosExcel() {
    const { filtro, fecha, datos } = obtenerDatosGlobales();
    
    if (datos.length === 0) {
      alert('No hay datos para exportar');
      return;
    }

    if (typeof XLSX === 'undefined') {
      alert('Error: Librería XLSX no cargada');
      return;
    }

    try {
      const wb = XLSX.utils.book_new();
      
      // Hoja 1: Resumen
      const wsResumen = XLSX.utils.aoa_to_sheet([
        ['RESULTADOS COMPLETOS DE ENCUESTA'],
        [],
        ['Filtro:', filtro],
        ['Fecha de exportación:', fecha],
        ['Total de preguntas:', datos.length],
        []
      ]);
      wsResumen['!cols'] = [{ wch: 25 }, { wch: 50 }];
      XLSX.utils.book_append_sheet(wb, wsResumen, 'Resumen');
      
      // Crear hoja por cada pregunta
      datos.forEach((pregunta, idx) => {
        const wsData = [
          [`PREGUNTA ${idx + 1}: ${pregunta.titulo}`],
          [],
          ['Opción', 'Respuestas', 'Porcentaje'],
          ...pregunta.opciones.map(op => [op.label, op.respuestas, `${op.porcentaje}%`]),
          ['Total', pregunta.total, '100%']
        ];
        
        const ws = XLSX.utils.aoa_to_sheet(wsData);
        ws['!cols'] = [{ wch: 30 }, { wch: 12 }, { wch: 12 }];
        
        XLSX.utils.book_append_sheet(wb, ws, `P${idx + 1}`);
      });
      
      XLSX.writeFile(wb, `resultados_completos_${Date.now()}.xlsx`);
    } catch (error) {
      console.error('Error exportando Excel:', error);
      alert('Error al exportar a Excel');
    }
  }

  function exportarTodosPDF() {
    const { filtro, fecha, datos } = obtenerDatosGlobales();
    
    if (datos.length === 0) {
      alert('No hay datos para exportar');
      return;
    }

    if (typeof jsPDF === 'undefined' || !jsPDF.jsPDF) {
      alert('Error: Librería jsPDF no cargada');
      return;
    }

    try {
      const { jsPDF } = window;
      const doc = new jsPDF();
      let paginaActual = 1;
      let yPosition = 20;
      
      // Encabezado
      doc.setFontSize(16);
      doc.setFont(undefined, 'bold');
      doc.text('RESULTADOS COMPLETOS DE ENCUESTA', 10, yPosition);
      
      yPosition += 15;
      doc.setFontSize(10);
      doc.setFont(undefined, 'normal');
      doc.text(`Filtro: ${filtro}`, 10, yPosition);
      yPosition += 7;
      doc.text(`Fecha: ${fecha}`, 10, yPosition);
      yPosition += 12;
      
      // Procesar cada pregunta
      datos.forEach((pregunta, idx) => {
        // Si nos acercamos al final de la página, crear nueva
        if (yPosition > 250) {
          doc.addPage();
          yPosition = 20;
          paginaActual++;
        }
        
        // Título de pregunta
        doc.setFontSize(12);
        doc.setFont(undefined, 'bold');
        doc.text(`Pregunta ${idx + 1}: ${pregunta.titulo.substring(0, 80)}`, 10, yPosition);
        yPosition += 8;
        
        // Encabezados de tabla
        doc.setFontSize(10);
        doc.setFont(undefined, 'bold');
        doc.text('Opción', 15, yPosition);
        doc.text('Respuestas', 100, yPosition);
        doc.text('Porcentaje', 150, yPosition);
        
        doc.setDrawColor(200);
        doc.line(10, yPosition + 2, 200, yPosition + 2);
        
        yPosition += 8;
        doc.setFont(undefined, 'normal');
        
        // Datos de la pregunta
        pregunta.opciones.forEach(op => {
          if (yPosition > 270) {
            doc.addPage();
            yPosition = 20;
            paginaActual++;
          }
          
          doc.text(op.label.substring(0, 40), 15, yPosition);
          doc.text(op.respuestas.toString(), 100, yPosition);
          doc.text(`${op.porcentaje}%`, 150, yPosition);
          yPosition += 7;
        });
        
        // Línea de total
        doc.setFont(undefined, 'bold');
        doc.text('Total', 15, yPosition);
        doc.text(pregunta.total.toString(), 100, yPosition);
        doc.text('100%', 150, yPosition);
        
        yPosition += 12;
      });
      
      doc.save(`resultados_completos_${Date.now()}.pdf`);
    } catch (error) {
      console.error('Error exportando PDF:', error);
      alert('Error al exportar a PDF');
    }
  }

  function exportarTodosPrint() {
    const { filtro, fecha, datos } = obtenerDatosGlobales();
    
    if (datos.length === 0) {
      alert('No hay datos para exportar');
      return;
    }

    const ventana = window.open('', '', 'width=900,height=700');
    ventana.document.write('<html><head><title>Imprimir Resultados Completos</title><style>');
    ventana.document.write('body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }');
    ventana.document.write('h1 { color: #7A1E2C; border-bottom: 3px solid #D4B056; padding-bottom: 10px; }');
    ventana.document.write('h2 { color: #7A1E2C; margin-top: 20px; margin-bottom: 10px; }');
    ventana.document.write('.info { background: #FFFAF3; padding: 10px; margin: 10px 0; border-left: 4px solid #D4B056; }');
    ventana.document.write('table { border-collapse: collapse; width: 100%; margin-top: 10px; background: white; page-break-inside: avoid; }');
    ventana.document.write('th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }');
    ventana.document.write('th { background-color: #7A1E2C; color: white; font-weight: bold; }');
    ventana.document.write('tr:nth-child(even) { background-color: #f9f5e4; }');
    ventana.document.write('tr:hover { background-color: #f0e6d2; }');
    ventana.document.write('@media print { body { background: white; } .page-break { page-break-after: always; } }');
    ventana.document.write('</style></head><body>');
    
    ventana.document.write('<h1>Resultados Completos de Encuesta</h1>');
    ventana.document.write(`<div class="info"><strong>Filtro aplicado:</strong> ${filtro}</div>`);
    ventana.document.write(`<div class="info"><strong>Fecha de impresión:</strong> ${fecha}</div>`);
    ventana.document.write(`<div class="info"><strong>Total de preguntas:</strong> ${datos.length}</div>`);
    
    datos.forEach((pregunta, idx) => {
      ventana.document.write(`<h2>Pregunta ${idx + 1}: ${pregunta.titulo}</h2>`);
      ventana.document.write('<table>');
      ventana.document.write('<tr><th>Opción</th><th>Respuestas</th><th>Porcentaje</th></tr>');
      
      pregunta.opciones.forEach(op => {
        ventana.document.write(`<tr><td>${op.label}</td><td>${op.respuestas}</td><td>${op.porcentaje}%</td></tr>`);
      });
      
      ventana.document.write(`<tr><td><strong>Total</strong></td><td><strong>${pregunta.total}</strong></td><td><strong>100%</strong></td></tr>`);
      ventana.document.write('</table>');
      
      if (idx < datos.length - 1) {
        ventana.document.write('<div class="page-break"></div>');
      }
    });
    
    ventana.document.write('</body></html>');
    ventana.document.close();
    ventana.print();
  }

  // ========== FIN EXPORTACIÓN GLOBAL ==========
  function toggleLegend(preguntaId) {
    const btn = document.getElementById('toggle-' + preguntaId);
    const legend = document.getElementById('legend-' + preguntaId);
    
    if (legend.classList.contains('collapsed')) {
      legend.classList.remove('collapsed');
      legend.classList.add('expanded');
      btn.classList.add('active');
      btn.innerHTML = '<span>Ocultar leyenda</span><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>';
    } else {
      legend.classList.add('collapsed');
      legend.classList.remove('expanded');
      btn.classList.remove('active');
      btn.innerHTML = '<span>Ver leyenda</span><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>';
    }
  }

  // Función para exportar gráfica
  function exportarGrafica(preguntaId, formato) {
    const canvas = document.getElementById('chart-' + preguntaId);
    if (!canvas) return;

    const opcionesExporta = {
      'csv': () => exportarCSV(preguntaId),
      'excel': () => exportarExcel(preguntaId),
      'pdf': () => exportarPDF(preguntaId),
      'print': () => imprimirGrafica(preguntaId)
    };

    if (opcionesExporta[formato]) {
      opcionesExporta[formato]();
    }
  }

  function obtenerFiltroInfo() {
    const urlParams = new URLSearchParams(window.location.search);
    const nivel = urlParams.get('nivel') || 'No especificado';
    const escuela = parseInt(urlParams.get('escuela') || '0');
    
    let filtroText = `Nivel: ${nivel}`;
    
    if (escuela > 0) {
      const selectEscuela = document.getElementById('escuela-filter');
      const nombreEscuela = selectEscuela ? selectEscuela.options[selectEscuela.selectedIndex].text : 'No especificada';
      filtroText += ` | Escuela: ${nombreEscuela}`;
    } else {
      filtroText += ` | Escuela: Todas las escuelas`;
    }
    
    return filtroText;
  }

  function exportarCSV(preguntaId) {
    const chart = window['chart_' + preguntaId];
    
    if (!chart) {
      alert('Error: No se encontraron datos de la gráfica');
      return;
    }

    const labels = chart.data.labels;
    const data = chart.data.datasets[0].data;
    const filtro = obtenerFiltroInfo();
    const fecha = new Date().toLocaleDateString('es-MX');
    
    let csv = 'RESULTADOS DE ENCUESTA\n';
    csv += `Filtro: ${filtro}\n`;
    csv += `Fecha de exportación: ${fecha}\n`;
    csv += '\nOpción,Respuestas,Porcentaje\n';
    
    const total = data.reduce((a, b) => a + b, 0);
    
    labels.forEach((label, idx) => {
      const value = data[idx];
      const porcentaje = total > 0 ? ((value / total) * 100).toFixed(2) : 0;
      csv += `"${label}",${value},${porcentaje}%\n`;
    });

    descargarArchivo(csv, `resultados_pregunta_${preguntaId}_${Date.now()}.csv`, 'text/csv');
  }

  function exportarExcel(preguntaId) {
    const chart = window['chart_' + preguntaId];
    
    if (!chart) {
      alert('Error: No se encontraron datos de la gráfica');
      return;
    }

    // Verificar si XLSX está cargado
    if (typeof XLSX === 'undefined') {
      alert('Error: Librería XLSX no cargada. Intenta descargar como CSV o PDF.');
      console.error('XLSX no está disponible');
      return;
    }

    const labels = chart.data.labels;
    const data = chart.data.datasets[0].data;
    const total = data.reduce((a, b) => a + b, 0);
    const filtro = obtenerFiltroInfo();
    const fecha = new Date().toLocaleDateString('es-MX');

    try {
      const wb = XLSX.utils.book_new();
      
      // Primera hoja con datos
      const wsData = [
        ['RESULTADOS DE ENCUESTA'],
        [],
        ['Filtro:', filtro],
        ['Fecha de exportación:', fecha],
        [],
        ['Opción', 'Respuestas', 'Porcentaje'],
        ...labels.map((label, idx) => {
          const value = data[idx];
          const porcentaje = total > 0 ? ((value / total) * 100).toFixed(2) : 0;
          return [label, value, `${porcentaje}%`];
        })
      ];
      
      const ws = XLSX.utils.aoa_to_sheet(wsData);
      ws['!cols'] = [{ wch: 25 }, { wch: 12 }, { wch: 12 }];
      
      XLSX.utils.book_append_sheet(wb, ws, 'Resultados');
      XLSX.writeFile(wb, `resultados_pregunta_${preguntaId}_${Date.now()}.xlsx`);
    } catch (error) {
      console.error('Error exportando Excel:', error);
      alert('Error al exportar a Excel. Verifica la consola.');
    }
  }

  function exportarPDF(preguntaId) {
    const canvas = document.getElementById('chart-' + preguntaId);
    const chart = window['chart_' + preguntaId];
    
    if (!chart || !canvas) {
      alert('Error: No se encontraron datos de la gráfica');
      return;
    }

    // Verificar si jsPDF está cargado
    if (typeof jsPDF === 'undefined' || !jsPDF.jsPDF) {
      alert('Error: Librería jsPDF no cargada. Intenta con otra opción.');
      console.error('jsPDF no está disponible');
      return;
    }

    try {
      const chartImage = canvas.toDataURL('image/png');
      const labels = chart.data.labels;
      const data = chart.data.datasets[0].data;
      const total = data.reduce((a, b) => a + b, 0);
      const filtro = obtenerFiltroInfo();
      const fecha = new Date().toLocaleDateString('es-MX');

      const { jsPDF } = window;
      const doc = new jsPDF();
      
      // Encabezado
      doc.setFontSize(16);
      doc.setFont(undefined, 'bold');
      doc.text('RESULTADOS DE ENCUESTA', 10, 10);
      
      // Información de filtro
      doc.setFontSize(10);
      doc.setFont(undefined, 'normal');
      doc.text(`Filtro: ${filtro}`, 10, 20);
      doc.text(`Fecha de exportación: ${fecha}`, 10, 27);
      
      // Gráfica
      doc.addImage(chartImage, 'PNG', 10, 35, 190, 100);
      
      // Tabla de datos
      doc.setFontSize(12);
      doc.setFont(undefined, 'bold');
      doc.text('Datos detallados:', 10, 145);
      
      doc.setFontSize(10);
      doc.setFont(undefined, 'normal');
      
      let yPosition = 155;
      doc.text('Opción', 15, yPosition);
      doc.text('Respuestas', 85, yPosition);
      doc.text('Porcentaje', 140, yPosition);
      
      doc.setDrawColor(200);
      doc.line(10, yPosition + 2, 200, yPosition + 2);
      
      yPosition += 8;
      
      labels.forEach((label, idx) => {
        const value = data[idx];
        const porcentaje = total > 0 ? ((value / total) * 100).toFixed(2) : 0;
        
        doc.text(label.substring(0, 60), 15, yPosition);
        doc.text(value.toString(), 85, yPosition);
        doc.text(`${porcentaje}%`, 140, yPosition);
        
        yPosition += 7;
        
        // Si nos acercamos al final, crear nueva página
        if (yPosition > 280) {
          doc.addPage();
          yPosition = 10;
        }
      });

      doc.save(`resultados_pregunta_${preguntaId}_${Date.now()}.pdf`);
    } catch (error) {
      console.error('Error exportando PDF:', error);
      alert('Error al exportar a PDF. Verifica la consola.');
    }
  }

  function imprimirGrafica(preguntaId) {
    const canvas = document.getElementById('chart-' + preguntaId);
    const chart = window['chart_' + preguntaId];
    
    if (!chart) return;

    const chartCanvas = canvas.toDataURL('image/png');
    const labels = chart.data.labels;
    const data = chart.data.datasets[0].data;
    const total = data.reduce((a, b) => a + b, 0);
    const filtro = obtenerFiltroInfo();
    const fecha = new Date().toLocaleDateString('es-MX');

    const ventana = window.open('', '', 'width=900,height=700');
    ventana.document.write('<html><head><title>Imprimir Resultados</title><style>');
    ventana.document.write('body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }');
    ventana.document.write('h1 { color: #7A1E2C; border-bottom: 3px solid #D4B056; padding-bottom: 10px; }');
    ventana.document.write('.info { background: #FFFAF3; padding: 10px; margin: 10px 0; border-left: 4px solid #D4B056; }');
    ventana.document.write('img { max-width: 800px; margin: 20px 0; border-radius: 8px; }');
    ventana.document.write('table { border-collapse: collapse; width: 100%; margin-top: 20px; background: white; }');
    ventana.document.write('th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }');
    ventana.document.write('th { background-color: #7A1E2C; color: white; font-weight: bold; }');
    ventana.document.write('tr:nth-child(even) { background-color: #f9f5e4; }');
    ventana.document.write('tr:hover { background-color: #f0e6d2; }');
    ventana.document.write('@media print { body { background: white; } .info { break-inside: avoid; } }');
    ventana.document.write('</style></head><body>');
    ventana.document.write('<h1>Resultados de Encuesta</h1>');
    ventana.document.write(`<div class="info"><strong>Filtro aplicado:</strong> ${filtro}</div>`);
    ventana.document.write(`<div class="info"><strong>Fecha de impresión:</strong> ${fecha}</div>`);
    ventana.document.write(`<img src="${chartCanvas}" />`);
    ventana.document.write('<h2>Datos detallados:</h2>');
    ventana.document.write('<table>');
    ventana.document.write('<tr><th>Opción</th><th>Respuestas</th><th>Porcentaje</th></tr>');
    
    labels.forEach((label, idx) => {
      const value = data[idx];
      const porcentaje = total > 0 ? ((value / total) * 100).toFixed(2) : 0;
      ventana.document.write(`<tr><td>${label}</td><td>${value}</td><td>${porcentaje}%</td></tr>`);
    });
    
    ventana.document.write('</table>');
    ventana.document.write('</body></html>');
    ventana.document.close();
    ventana.print();
  }

  function descargarArchivo(contenido, nombreArchivo, tipo) {
    const blob = new Blob([contenido], { type: tipo });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = nombreArchivo;
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
  }

  // Modal de respuestas
  function abrirRespuestas(preguntaId, nivel, escuela) {
    const modal = document.getElementById('modalRespuestas');
    const contenido = document.getElementById('modalContenido');
    const titulo = document.getElementById('modalTitulo');

    modal.classList.remove('hidden');
    contenido.innerHTML = '<div class="loading">Cargando respuestas...</div>';

    const url = `obtener_respuestas_texto.php?pregunta=${preguntaId}&nivel=${nivel}${escuela > 0 ? '&escuela=' + escuela : ''}`;

    fetch(url)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          titulo.textContent = `Respuestas - ${data.nombrePregunta}`;
          let html = '<div class="respuestas-tabla">';
          
          if (data.respuestas.length === 0) {
            html += '<div class="sin-respuestas">No hay respuestas para esta pregunta</div>';
          } else {
            html += '<div class="tabla-header">';
            html += '<div class="col-respuesta">Respuesta</div>';
            html += '<div class="col-info">Escuela</div>';
            html += '<div class="col-info">Fecha</div>';
            html += '<div class="col-accion">Acción</div>';
            html += '</div>';

            data.respuestas.forEach(resp => {
              html += '<div class="tabla-fila">';
              html += `<div class="col-respuesta"><p class="respuesta-texto">${escapeHtml(resp.texto)}</p></div>`;
              html += `<div class="col-info">${escapeHtml(resp.escuela)}</div>`;
              html += `<div class="col-info">${formatearFecha(resp.fecha)}</div>`;
              html += `<div class="col-accion"><button class="btn-eliminar" onclick="eliminarRespuesta(${resp.id}, ${preguntaId}, '${nivel}', ${escuela})">Eliminar</button></div>`;
              html += '</div>';
            });
          }

          html += '</div>';
          contenido.innerHTML = html;
        } else {
          contenido.innerHTML = `<div class="error-mensaje">${data.error || 'Error al cargar respuestas'}</div>`;
        }
      })
      .catch(err => {
        console.error(err);
        contenido.innerHTML = '<div class="error-mensaje">Error al cargar respuestas</div>';
      });
  }

  function cerrarRespuestas() {
    document.getElementById('modalRespuestas').classList.add('hidden');
  }

  function formatearFecha(fechaStr) {
    if (!fechaStr) return '';
    const fecha = new Date(fechaStr);
    return fecha.toLocaleDateString('es-MX');
  }

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  function eliminarRespuesta(id, preguntaId, nivel, escuela) {
    if (!confirm('¿Estás seguro de eliminar esta respuesta?')) return;

    fetch('eliminar_respuesta_texto.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `id=${id}&pregunta=${preguntaId}&nivel=${nivel}&escuela=${escuela}`
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        abrirRespuestas(preguntaId, nivel, escuela);
        alert('Respuesta eliminada');
      } else {
        alert('Error: ' + (data.error || 'No se pudo eliminar'));
      }
    })
    .catch(err => {
      console.error(err);
      alert('Error al eliminar');
    });
  }

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') cerrarRespuestas();
  });
  </script>

  <!-- Librerías para exportar -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</body>
</html>