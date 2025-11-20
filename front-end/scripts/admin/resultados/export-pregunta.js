// /front-end/scripts/admin/resultados/export-pregunta.js
(function (global) {
  const ns = (global.SimpinnaResultados = global.SimpinnaResultados || {});
  const helpers = ns;

  /* ---------------- Obtener Chart asociado ---------------- */
  function getChart(preguntaId) {
    if (ns.charts && ns.charts[preguntaId]) return ns.charts[preguntaId];
    return global["chart_" + preguntaId] || null;
  }

  /* ---------------- Obtener titulo ---------------- */
  function getTitulo(preguntaId) {
    const slot = document.getElementById("pie-q" + preguntaId);
    return slot?.dataset?.titulo || "Pregunta sin titulo";
  }

  /* ---------------- Obtener numero de pregunta ---------------- */
  function getNumeroPregunta(preguntaId) {
    const card = document.getElementById("pregunta-" + preguntaId);
    const titulo = card?.querySelector('.pregunta-titulo')?.textContent || '';
    const match = titulo.match(/^(\d+)\./);
    return match ? match[1] : '';
  }

  /* ===========================================================
     EXPORTAR CSV
     =========================================================== */
  ns.exportarCSVPregunta = function (preguntaId) {
    const chart = getChart(preguntaId);
    if (!chart) return alert("No se encontraron datos de la grafica");

    const titulo = getTitulo(preguntaId);
    const labels = chart.data.labels;
    const data = chart.data.datasets[0].data;
    const total = data.reduce((a, b) => a + b, 0);

    let csv = `PREGUNTA: "${titulo}"\n`;
    csv += "RESULTADOS DE ENCUESTA\n";
    csv += `Filtro: ${helpers.obtenerFiltroInfo()}\n`;
    csv += `Fecha: ${new Date().toLocaleDateString("es-MX")}\n\n`;
    csv += "Opcion,Respuestas,Porcentaje\n";

    labels.forEach((label, i) => {
      const pct = total > 0 ? ((data[i] / total) * 100).toFixed(2) : 0;
      csv += `"${label}",${data[i]},${pct}%\n`;
    });

    helpers.descargarArchivo(
      csv,
      `pregunta_${preguntaId}_${Date.now()}.csv`,
      "text/csv;charset=utf-8;"
    );
  };

  /* ===========================================================
     EXPORTAR EXCEL
     =========================================================== */
  ns.exportarExcelPregunta = function (preguntaId) {
    const chart = getChart(preguntaId);
    if (!chart) return alert("No se encontraron datos de la grafica");
    if (!global.XLSX) return alert("Libreria XLSX no cargada");

    const titulo = getTitulo(preguntaId);
    const labels = chart.data.labels;
    const data = chart.data.datasets[0].data;
    const total = data.reduce((a, b) => a + b, 0);

    const wsData = [
      ["PREGUNTA:", titulo],
      ["RESULTADOS DE ENCUESTA"],
      [],
      ["Filtro:", helpers.obtenerFiltroInfo()],
      ["Fecha de exportacion:", new Date().toLocaleDateString("es-MX")],
      [],
      ["Opcion", "Respuestas", "Porcentaje"],
      ...labels.map((label, i) => [
        label,
        data[i],
        total > 0 ? ((data[i] / total) * 100).toFixed(2) + "%" : "0%",
      ]),
    ];

    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(wsData);
    ws["!cols"] = [{ wch: 40 }, { wch: 12 }, { wch: 12 }];
    XLSX.utils.book_append_sheet(wb, ws, "Pregunta");

    XLSX.writeFile(wb, `pregunta_${preguntaId}_${Date.now()}.xlsx`);
  };

  /* ===========================================================
     EXPORTAR PDF
     =========================================================== */
  ns.exportarPDFPregunta = function (preguntaId) {
    const canvas = document.getElementById("chart-" + preguntaId);
    const chart = getChart(preguntaId);

    if (!canvas || !chart) return alert("No se encontraron datos de la grafica");
    if (typeof jsPDF === 'undefined' || !jsPDF.jsPDF) {
      return alert("Libreria jsPDF no cargada");
    }

    const titulo = getTitulo(preguntaId);
    const numeroPregunta = getNumeroPregunta(preguntaId);
    const { jsPDF } = global.jsPDF;
    const doc = new jsPDF();
    const img = canvas.toDataURL("image/png");

    doc.setFontSize(16);
    doc.text("RESULTADOS DE ENCUESTA", 10, 10);

    doc.setFontSize(12);
    if (numeroPregunta) {
      doc.text(`Pregunta ${numeroPregunta}: ${titulo}`, 10, 17);
    } else {
      doc.text(`Pregunta: ${titulo}`, 10, 17);
    }

    doc.setFontSize(10);
    doc.text(`Filtro: ${helpers.obtenerFiltroInfo()}`, 10, 25);
    doc.text(`Fecha: ${new Date().toLocaleDateString("es-MX")}`, 10, 32);

    doc.addImage(img, "PNG", 10, 40, 190, 100);

    // Agregar leyenda debajo de la grafica
    const labels = chart.data.labels;
    const data = chart.data.datasets[0].data;
    const total = data.reduce((a, b) => a + b, 0);

    let y = 150;
    doc.setFontSize(11);
    doc.setFont(undefined, 'bold');
    doc.text("Detalle de respuestas:", 10, y);
    y += 8;

    doc.setFontSize(9);
    doc.setFont(undefined, 'normal');
    
    labels.forEach((label, i) => {
      if (y > 270) {
        doc.addPage();
        y = 20;
      }
      const pct = total > 0 ? ((data[i] / total) * 100).toFixed(2) : 0;
      doc.text(`${label}: ${data[i]} (${pct}%)`, 15, y);
      y += 6;
    });

    doc.save(`pregunta_${preguntaId}_${Date.now()}.pdf`);
  };

  /* ===========================================================
     IMPRIMIR
     =========================================================== */
  ns.imprimirGraficaPregunta = function (preguntaId) {
    const canvas = document.getElementById("chart-" + preguntaId);
    const chart = getChart(preguntaId);
    if (!canvas || !chart) return;

    const titulo = getTitulo(preguntaId);
    const numeroPregunta = getNumeroPregunta(preguntaId);
    const img = canvas.toDataURL("image/png");

    // Obtener datos de la leyenda
    const labels = chart.data.labels;
    const data = chart.data.datasets[0].data;
    const total = data.reduce((a, b) => a + b, 0);
    const colors = chart.data.datasets[0].backgroundColor;

    let leyendaHTML = '';
    labels.forEach((label, i) => {
      const pct = total > 0 ? ((data[i] / total) * 100).toFixed(2) : 0;
      leyendaHTML += `
        <div style="display: flex; align-items: center; margin: 8px 0;">
          <div style="width: 20px; height: 20px; background: ${colors[i]}; margin-right: 10px; border-radius: 4px;"></div>
          <div style="flex: 1;">
            <strong>${label}:</strong> ${data[i]} respuestas (${pct}%)
          </div>
        </div>
      `;
    });

    const w = global.open("", "", "width=900,height=700");
    w.document.write(`
      <html>
      <head>
        <title>Imprimir Resultados</title>
        <style>
          body {
            font-family: Arial, sans-serif;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
          }
          h1 {
            color: #7A1E2C;
            margin-bottom: 10px;
            font-size: 24px;
          }
          h2 {
            color: #2D1B1F;
            margin: 20px 0 10px 0;
            font-size: 18px;
          }
          .info {
            background: #FFFAF3;
            padding: 12px;
            margin-bottom: 20px;
            border-left: 4px solid #D4B056;
            border-radius: 4px;
          }
          .info p {
            margin: 5px 0;
          }
          img {
            max-width: 100%;
            margin: 20px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            background: white;
          }
          .leyenda {
            margin-top: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
            border: 1px solid #ddd;
          }
          @media print {
            body {
              padding: 10px;
            }
            .no-print {
              display: none;
            }
          }
        </style>
      </head>
      <body>
        <h1>Resultados de Encuesta</h1>
        ${numeroPregunta ? `<h2>Pregunta ${numeroPregunta}: ${titulo}</h2>` : `<h2>${titulo}</h2>`}
        <div class="info">
          <p><strong>Filtro:</strong> ${helpers.obtenerFiltroInfo()}</p>
          <p><strong>Fecha:</strong> ${new Date().toLocaleDateString("es-MX")}</p>
          <p><strong>Total de respuestas:</strong> ${total}</p>
        </div>
        <img src="${img}">
        <div class="leyenda">
          <h3 style="margin-top: 0; color: #7A1E2C;">Detalle de respuestas:</h3>
          ${leyendaHTML}
        </div>
      </body>
      </html>
    `);
    w.document.close();
    w.print();
  };

  /* ===========================================================
     PUENTE PARA EL HTML
     =========================================================== */
  ns.exportarGrafica = function (preguntaId, formato) {
    const actions = {
      csv: ns.exportarCSVPregunta,
      excel: ns.exportarExcelPregunta,
      pdf: ns.exportarPDFPregunta,
      print: ns.imprimirGraficaPregunta,
    };
    if (actions[formato]) actions[formato](preguntaId);
  };

  global.exportarGrafica = ns.exportarGrafica;
})(window);