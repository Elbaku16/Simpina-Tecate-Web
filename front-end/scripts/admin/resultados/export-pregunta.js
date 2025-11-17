// /front-end/scripts/admin/resultados/export-pregunta.js
(function (global) {
  const ns = (global.SimpinnaResultados = global.SimpinnaResultados || {});
  const helpers = ns;

  /* ---------------- Obtener Chart asociado ---------------- */
  function getChart(preguntaId) {
    if (ns.charts && ns.charts[preguntaId]) return ns.charts[preguntaId];
    return global["chart_" + preguntaId] || null;
  }

  /* ---------------- Obtener título ---------------- */
  function getTitulo(preguntaId) {
    const slot = document.getElementById("pie-q" + preguntaId);
    return slot?.dataset?.titulo || "Pregunta sin título";
  }

  /* ===========================================================
     EXPORTAR CSV
     =========================================================== */
  ns.exportarCSVPregunta = function (preguntaId) {
    const chart = getChart(preguntaId);
    if (!chart) return alert("No se encontraron datos de la gráfica");

    const titulo = getTitulo(preguntaId);
    const labels = chart.data.labels;
    const data = chart.data.datasets[0].data;
    const total = data.reduce((a, b) => a + b, 0);

    let csv = `PREGUNTA: "${titulo}"\n`;
    csv += "RESULTADOS DE ENCUESTA\n";
    csv += `Filtro: ${helpers.obtenerFiltroInfo()}\n`;
    csv += `Fecha: ${new Date().toLocaleDateString("es-MX")}\n\n`;
    csv += "Opción,Respuestas,Porcentaje\n";

    labels.forEach((label, i) => {
      const pct = total > 0 ? ((data[i] / total) * 100).toFixed(2) : 0;
      csv += `"${label}",${data[i]},${pct}%\n`;
    });

    helpers.descargarArchivo(
      csv,
      `pregunta_${preguntaId}_${Date.now()}.csv`,
      "text/csv"
    );
  };

  /* ===========================================================
     EXPORTAR EXCEL
     =========================================================== */
  ns.exportarExcelPregunta = function (preguntaId) {
    const chart = getChart(preguntaId);
    if (!chart) return alert("No se encontraron datos de la gráfica");
    if (!global.XLSX) return alert("Librería XLSX no cargada");

    const titulo = getTitulo(preguntaId);
    const labels = chart.data.labels;
    const data = chart.data.datasets[0].data;
    const total = data.reduce((a, b) => a + b, 0);

    const wsData = [
      ["PREGUNTA:", titulo],
      ["RESULTADOS DE ENCUESTA"],
      [],
      ["Filtro:", helpers.obtenerFiltroInfo()],
      ["Fecha de exportación:", new Date().toLocaleDateString("es-MX")],
      [],
      ["Opción", "Respuestas", "Porcentaje"],
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

    if (!canvas || !chart) return alert("No se encontraron datos de la gráfica");
    if (!global.jsPDF?.jsPDF) return alert("Librería jsPDF no cargada");

    const titulo = getTitulo(preguntaId);
    const { jsPDF } = global;
    const doc = new jsPDF();
    const img = canvas.toDataURL("image/png");

    doc.setFontSize(16);
    doc.text("RESULTADOS DE ENCUESTA", 10, 10);

    doc.setFontSize(12);
    doc.text(`Pregunta: ${titulo}`, 10, 17);

    doc.setFontSize(10);
    doc.text(`Filtro: ${helpers.obtenerFiltroInfo()}`, 10, 25);
    doc.text(`Fecha: ${new Date().toLocaleDateString("es-MX")}`, 10, 32);

    doc.addImage(img, "PNG", 10, 40, 190, 100);

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
    const img = canvas.toDataURL("image/png");

    const w = global.open("", "", "width=900,height=700");
    w.document.write(`
      <html>
      <head>
        <title>Imprimir Resultados</title>
        <style>
          body{font-family:Arial;padding:20px;}
          img{max-width:100%;margin:20px 0;}
        </style>
      </head>
      <body>
        <h1>Resultados de Encuesta</h1>
        <h2>${titulo}</h2>
        <p><strong>Filtro:</strong> ${helpers.obtenerFiltroInfo()}</p>
        <p><strong>Fecha:</strong> ${new Date().toLocaleDateString("es-MX")}</p>
        <img src="${img}">
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
