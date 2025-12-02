// /front-end/scripts/admin/resultados/export-pregunta.js
(function (global) {
  const ns = (global.SimpinnaResultados = global.SimpinnaResultados || {});
  const helpers = ns;

  /* ---------------- Helper para Chart.js ---------------- */
  function getChart(preguntaId) {
    if (ns.charts && ns.charts[preguntaId]) return ns.charts[preguntaId];
    return global["chart_" + preguntaId] || null;
  }

  /* ---------------- Helper para datos del DOM ---------------- */
  function getNumeroPregunta(titulo) {
    // Busca un patrón que empiece con número y punto, como "1. "
    const match = titulo.match(/^(\d+)\./); 
    return match ? match[1] : '';
  }
  
  function getTitulo(preguntaId) {
    const card = document.getElementById("pregunta-" + preguntaId);
    return card?.querySelector('.pregunta-titulo')?.textContent || `Pregunta ${preguntaId}`;
  }

  /* ===========================================================
     Extractor de Datos Unificado para Ranking y Gráfica
     =========================================================== */
  function obtenerDatosPregunta(preguntaId) {
    const chart = getChart(preguntaId);
    const titulo = getTitulo(preguntaId);
    
    const datosBase = {
        preguntaId,
        titulo,
        numero: getNumeroPregunta(titulo),
        filtro: helpers.obtenerFiltroInfo(),
        fecha: new Date().toLocaleDateString("es-MX")
    };
    
    // --- Lógica para Gráficas (Pie/Bar) ---
    if (chart) {
        const labels = chart.data.labels;
        const data = chart.data.datasets[0].data;
        const total = data.reduce((a, b) => a + b, 0);

        return {
            ...datosBase,
            tipo: 'chart',
            opciones: labels.map((label, idx) => ({
                label,
                valor: data[idx],
                respuestas: data[idx], // Usar 'respuestas' para consistencia con Ranking
                porcentaje: (total > 0 ? ((data[idx] / total) * 100) : 0).toFixed(2),
            })),
            total,
        };
    } 
    
    // --- Lógica para Ranking (Scraping del DOM) ---
    const rankingItems = document.querySelectorAll(`#ranking-q${preguntaId} .ranking-item-bar`);
    
    if (rankingItems.length > 0) {
        let totalVotosGlobal = 0;
        const lista = [];

        rankingItems.forEach((item, index) => {
            // El título contiene el promedio y el total de votos
            const title = item.title; 
            const matchPromedio = title.match(/Promedio: ([\d.]+)/);
            const matchTotal = title.match(/Total votos: (\d+)/);
            
            const promedio = matchPromedio ? parseFloat(matchPromedio[1]) : 0;
            const totalVotos = matchTotal ? parseInt(matchTotal[1], 10) : 0;
            
            if (totalVotos > 0) {
              totalVotosGlobal += totalVotos;
              lista.push({
                  posicion: index + 1, // Posición real después del ordenamiento en PHP
                  texto: item.querySelector('.ranking-texto').textContent.trim(),
                  promedio: promedio.toFixed(2), // Formatear promedio para la exportación
                  votos: totalVotos
              });
            }
        });
        
        return {
            ...datosBase,
            tipo: 'ranking',
            lista: lista,
            totalVotosGlobal: totalVotosGlobal,
        };
    }

    return null; // No hay datos de gráfica ni de ranking
  }


  /* ===========================================================
     FUNCIÓN DE EXPORTACIÓN UNIFICADA (global.exportarGrafica)
     =========================================================== */
  global.exportarGrafica = function (preguntaId, formato) {
    const datos = obtenerDatosPregunta(preguntaId);

    if (!datos) {
      alert("No hay datos válidos para esta pregunta.");
      return;
    }
    
    // El tipo de pregunta es determinado por el extractor
    if (datos.tipo === 'ranking') {
        try {
            switch (formato) {
                case "csv":
                    exportarRankingCSV(datos);
                    break;
                case "excel":
                    exportarRankingExcel(datos);
                    break;
                case "pdf":
                    exportarRankingPDF(datos);
                    break;
                case "print":
                    exportarRankingPrint(datos);
                    break;
                default:
                    console.error("Formato de exportación no reconocido:", formato);
            }
        } catch (e) {
            console.error(`Error al generar ${formato} para ranking ${preguntaId}:`, e);
            alert(`Ocurrió un error al intentar exportar el Ranking a ${formato}.`);
        }
    } else if (datos.tipo === 'chart') {
        // Lógica para Pie/Bar
         switch (formato) {
            case "csv":
                exportarGraficaCSV(datos);
                break;
            case "excel":
                exportarGraficaExcel(datos);
                break;
            case "pdf":
                exportarGraficaPDF(datos);
                break;
            case "print":
                exportarGraficaPrint(datos);
                break;
            default:
                console.error("Formato de exportación no reconocido:", formato);
        }
    } else {
         alert(`Tipo de pregunta no soportado para exportación individual.`);
    }
  };


  /* ========================================================================
     EXPORTACIONES DE RANKING (NUEVAS FUNCIONES)
     ======================================================================== */
     
  // CSV
  function exportarRankingCSV(datos) {
    let csv = "\uFEFF"; // BOM
    csv += `RESULTADOS DE RANKING (POSICIÓN PROMEDIO)\n`;
    csv += `Pregunta ${datos.numero}: ${datos.titulo}\n`; // INCLUYE NÚMERO
    csv += `Filtro: ${datos.filtro}\n`;
    csv += `Fecha: ${datos.fecha}\n\n`;

    csv += "Posición,Opcion,Promedio de Posición,Total Votos\n";
    datos.lista.forEach((op) => {
      const labelLimpia = op.texto.replace(/"/g, '""');
      // Aseguramos que los números no se exporten con formato de texto
      csv += `${op.posicion},"${labelLimpia}",${op.promedio},${op.votos}\n`; 
    });
    csv += `Total Votos Registrados,${datos.totalVotosGlobal}\n`;

    helpers.descargarArchivo(
      csv,
      `ranking_${datos.preguntaId}_${Date.now()}.csv`,
      "text/csv;charset=utf-8;"
    );
  }

  // EXCEL
  function exportarRankingExcel(datos) {
    if (typeof XLSX === "undefined") {
      alert("Error: Librería XLSX no cargada.");
      return;
    }

    const wsData = [
      [`RESULTADOS DE RANKING (POSICIÓN PROMEDIO)`],
      [`Pregunta ${datos.numero}: ${datos.titulo}`], // INCLUYE NÚMERO
      [],
      ["Filtro:", datos.filtro],
      ["Fecha de exportación:", datos.fecha],
      [],
      ["Posición", "Opción", "Promedio de Posición", "Total Votos"],
      ...datos.lista.map((op) => [
        op.posicion,
        op.texto,
        parseFloat(op.promedio), // Convertir a número para Excel
        op.votos,
      ]),
      [],
      ["Total Votos Registrados:", datos.totalVotosGlobal]
    ];

    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(wsData);
    ws["!cols"] = [{ wch: 10 }, { wch: 40 }, { wch: 20 }, { wch: 15 }];

    XLSX.utils.book_append_sheet(wb, ws, `R${datos.numero}`); // Usar número secuencial
    XLSX.writeFile(wb, `ranking_${datos.preguntaId}_${Date.now()}.xlsx`);
  }

  // PDF
  function exportarRankingPDF(datos) {
    let PDFClass = null;
    if (typeof global.jspdf !== 'undefined' && typeof global.jspdf.jsPDF === 'function') {
        PDFClass = global.jspdf.jsPDF;
    } else if (typeof global.jsPDF === 'function') {
        PDFClass = global.jsPDF;
    } else {
        alert('Error: Librería de PDF no cargada o no inicializada.');
        return;
    }

    try {
        const doc = new PDFClass();
        let y = 20;

        const numeroPregunta = datos.numero || datos.preguntaId; 

        doc.setFontSize(16);
        doc.setFont(undefined, "bold");
        doc.text(`RANKING DE PREGUNTA ${numeroPregunta}:`, 10, y); // INCLUYE NÚMERO
        y += 10;
        
        doc.setFontSize(12);
        doc.setFont(undefined, "normal");
        const tituloLineas = doc.splitTextToSize(datos.titulo, 180);
        doc.text(tituloLineas, 10, y);
        y += (tituloLineas.length * 6) + 5;


        doc.setFontSize(10);
        doc.text(`Filtro: ${datos.filtro}`, 10, y);
        y += 7;
        doc.text(`Fecha: ${datos.fecha}`, 10, y);
        y += 12;
        
        // Encabezados de tabla
        doc.setFont(undefined, "bold");
        doc.text("Posición", 15, y);
        doc.text("Opción", 40, y);
        doc.text("Promedio", 130, y);
        doc.text("Total Votos", 160, y);
        doc.setDrawColor(200);
        doc.line(10, y + 2, 200, y + 2);
        y += 8;

        doc.setFont(undefined, 'normal');
        datos.lista.forEach((op) => {
            if (y > 270) { doc.addPage(); y = 20; }
            doc.text(String(op.posicion), 15, y);
            doc.text(op.texto.substring(0, 40), 40, y);
            doc.text(op.promedio.toString(), 130, y);
            doc.text(op.votos.toString(), 160, y);
            y += 7;
        });

        // Fila de Totales
        doc.setFont(undefined, "bold");
        doc.text("Total Votos Registrados:", 15, y);
        doc.text(String(datos.totalVotosGlobal), 160, y);
        
        doc.save(`ranking_${datos.preguntaId}_${Date.now()}.pdf`);
    } catch (err) {
        console.error("Error al exportar PDF de Ranking:", err);
        alert("Error interno al generar el PDF de Ranking. Revisa la consola.");
    }
  }

  // PRINT
  function exportarRankingPrint(datos) {
    const w = global.open("", "", "width=900,height=700");
    w.document.write(
      "<html><head><title>Imprimir Ranking</title><style>"
    );
    w.document.write(
      "body{font-family:Arial,sans-serif;padding:20px;background:white;}"
    );
    w.document.write("h1{color:#7A1E2C;padding-bottom:10px;}");
    w.document.write(".info{background:#FFFAF3;padding:10px;margin:10px 0;border-left:4px solid #D4B056;}");
    w.document.write("table{border-collapse:collapse;width:100%;margin-top:10px;background:white;}");
    w.document.write("th,td{border:1px solid #ddd;padding:10px;text-align:left;}");
    w.document.write("th{background:#7A1E2C;color:white;font-weight:bold;}");
    w.document.write("</style></head><body>");

    const numeroPregunta = getNumeroPregunta(datos.titulo) || datos.preguntaId;
    
    w.document.write(`<h1>Ranking de Pregunta ${numeroPregunta}</h1>`);
    w.document.write(`<h2>${datos.titulo}</h2>`);
    w.document.write(`<div class="info"><strong>Filtro aplicado:</strong> ${datos.filtro}</div>`);
    w.document.write(`<div class="info"><strong>Fecha de impresión:</strong> ${datos.fecha}</div>`);

    w.document.write("<table>");
    w.document.write("<tr><th>Posición</th><th>Opción</th><th>Promedio de Posición</th><th>Total Votos</th></tr>");
    datos.lista.forEach((op) => {
      w.document.write(
        `<tr><td>${op.posicion}</td><td>${op.texto}</td><td>${op.promedio}</td><td>${op.votos}</td></tr>`
      );
    });
    w.document.write(
      `<tr><td colspan="3"><strong>Total Votos Registrados:</strong></td><td><strong>${datos.totalVotosGlobal}</strong></td></tr>`
    );
    w.document.write("</table>");

    w.document.write("</body></html>");
    w.document.close();
    w.print();
  }


  /* ========================================================================
     EXPORTACIONES DE GRÁFICA (PIE/BAR) - REUSAMOS LÓGICA ANTERIOR
     ======================================================================== */
     
  function exportarGraficaCSV(datos) {
    let csv = "\uFEFF"; 
    csv += `RESULTADOS DE PREGUNTA ${datos.preguntaId}\n`;
    csv += `${datos.titulo}\n`;
    csv += `Filtro: ${datos.filtro}\n`;
    csv += `Fecha: ${datos.fecha}\n\n`;

    csv += "Opción,Respuestas,Porcentaje\n";
    datos.opciones.forEach((op) => {
      const labelLimpia = op.label.replace(/"/g, '""');
      csv += `"${labelLimpia}",${op.valor},${op.porcentaje}%\n`;
    });
    csv += `Total,${datos.total},100%\n`;

    helpers.descargarArchivo(
      csv,
      `pregunta_${datos.preguntaId}_${Date.now()}.csv`,
      "text/csv;charset=utf-8;"
    );
  }

  function exportarGraficaExcel(datos) {
    if (typeof XLSX === "undefined") {
      alert("Error: Librería XLSX no cargada.");
      return;
    }

    const wsData = [
      [`RESULTADOS DE PREGUNTA ${datos.preguntaId}`],
      [datos.titulo],
      [],
      ["Filtro:", datos.filtro],
      ["Fecha de exportación:", datos.fecha],
      [],
      ["Opción", "Respuestas", "Porcentaje"],
      ...datos.opciones.map((op) => [
        op.label,
        op.valor,
        `${op.porcentaje}%`,
      ]),
      ["Total", datos.total, "100%"],
    ];

    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(wsData);
    ws["!cols"] = [{ wch: 30 }, { wch: 12 }, { wch: 12 }];

    XLSX.utils.book_append_sheet(wb, ws, `P${datos.preguntaId}`);
    XLSX.writeFile(wb, `pregunta_${datos.preguntaId}_${Date.now()}.xlsx`);
  }
  
  function exportarGraficaPDF(datos) {
    let PDFClass = null;
    if (typeof global.jspdf !== 'undefined' && typeof global.jspdf.jsPDF === 'function') {
        PDFClass = global.jspdf.jsPDF;
    } else if (typeof global.jsPDF === 'function') {
        PDFClass = global.jsPDF;
    } else {
        alert('Error: Librería de PDF no cargada o no inicializada.');
        return;
    }

    try {
        const doc = new PDFClass();
        let y = 20;
        
        const numeroPregunta = getNumeroPregunta(datos.titulo) || datos.preguntaId; 

        doc.setFontSize(16);
        doc.setFont(undefined, "bold");
        doc.text(`PREGUNTA ${numeroPregunta}:`, 10, y);
        y += 10;
        
        doc.setFontSize(12);
        doc.setFont(undefined, "normal");
        const tituloLineas = doc.splitTextToSize(datos.titulo, 180);
        doc.text(tituloLineas, 10, y);
        y += (tituloLineas.length * 6) + 5;


        doc.setFontSize(10);
        doc.text(`Filtro: ${datos.filtro}`, 10, y);
        y += 7;
        doc.text(`Fecha: ${datos.fecha}`, 10, y);
        y += 12;

        // Encabezados de tabla
        doc.setFont(undefined, "bold");
        doc.text("Opción", 15, y);
        doc.text("Respuestas", 100, y);
        doc.text("Porcentaje", 150, y);
        doc.setDrawColor(200);
        doc.line(10, y + 2, 200, y + 2);
        y += 8;

        doc.setFont(undefined, "normal");
        datos.opciones.forEach((op) => {
            if (y > 270) {
                doc.addPage();
                y = 20;
            }
            doc.text(op.label.substring(0, 40), 15, y);
            doc.text(String(op.valor), 100, y);
            doc.text(`${op.porcentaje}%`, 150, y);
            y += 7;
        });

        // Fila de Totales
        doc.setFont(undefined, "bold");
        doc.text("Total", 15, y);
        doc.text(String(datos.total), 100, y);
        doc.text("100%", 150, y);
        
        doc.save(`pregunta_${datos.preguntaId}_${Date.now()}.pdf`);
    } catch (err) {
        console.error("Error al exportar PDF:", err);
        alert("Error interno al generar el PDF. Revisa la consola.");
    }
  }

  function exportarGraficaPrint(datos) {
    const w = global.open("", "", "width=900,height=700");
    w.document.write(
      "<html><head><title>Imprimir Resultados</title><style>"
    );
    w.document.write(
      "body{font-family:Arial,sans-serif;padding:20px;background:white;}"
    );
    w.document.write("h1{color:#7A1E2C;padding-bottom:10px;}");
    w.document.write(".info{background:#FFFAF3;padding:10px;margin:10px 0;border-left:4px solid #D4B056;}");
    w.document.write("table{border-collapse:collapse;width:100%;margin-top:10px;background:white;}");
    w.document.write("th,td{border:1px solid #ddd;padding:10px;text-align:left;}");
    w.document.write("th{background:#7A1E2C;color:white;font-weight:bold;}");
    w.document.write("</style></head><body>");

    const numeroPregunta = getNumeroPregunta(datos.titulo) || datos.preguntaId;
    
    w.document.write(`<h1>Resultados de Pregunta ${numeroPregunta}</h1>`);
    w.document.write(`<h2>${datos.titulo}</h2>`);
    w.document.write(`<div class="info"><strong>Filtro aplicado:</strong> ${datos.filtro}</div>`);
    w.document.write(`<div class="info"><strong>Fecha de impresión:</strong> ${datos.fecha}</div>`);

    w.document.write("<table><tr><th>Opción</th><th>Respuestas</th><th>Porcentaje</th></tr>");
    datos.opciones.forEach((op) => {
      w.document.write(
        `<tr><td>${op.label}</td><td>${op.valor}</td><td>${op.porcentaje}%</td></tr>`
      );
    });
    w.document.write(
      `<tr><td><strong>Total</strong></td><td><strong>${datos.total}</strong></td><td><strong>100%</strong></td></tr>`
    );
    w.document.write("</table>");

    w.document.write("</body></html>");
    w.document.close();
    w.print();
  }
  
})(window);