// /front-end/scripts/admin/resultados/export-global.js
(function (global) {
  const ns = (global.SimpinnaResultados = global.SimpinnaResultados || {});
  const helpers = ns;

  ns.obtenerDatosGlobales = function () {
    const datos = [];
    const filtro = helpers.obtenerFiltroInfo();
    const fecha = new Date().toLocaleDateString("es-MX");

    document.querySelectorAll('[id^="pie-q"]').forEach((element) => {
      const preguntaId = element.id.replace("pie-q", "");
      const chart =
        (ns.charts && ns.charts[preguntaId]) ||
        global["chart_" + preguntaId] ||
        null;

      if (chart) {
        const titulo = document.querySelector(
          "#pregunta-" + preguntaId + " .pregunta-titulo"
        )?.textContent;
        const labels = chart.data.labels;
        const data = chart.data.datasets[0].data;
        const total = data.reduce((a, b) => a + b, 0);

        datos.push({
          preguntaId,
          titulo,
          opciones: labels.map((label, idx) => ({
            label,
            respuestas: data[idx],
            porcentaje:
              total > 0 ? ((data[idx] / total) * 100).toFixed(2) : "0.00",
          })),
          total,
        });
      }
    });

    return { filtro, fecha, datos };
  };

  ns.exportarTodosCSV = function () {
    const { filtro, fecha, datos } = ns.obtenerDatosGlobales();
    if (!datos.length) {
      alert("No hay datos para exportar");
      return;
    }

    let csv = "RESULTADOS COMPLETOS DE ENCUESTA\n";
    csv += `Filtro: ${filtro}\n`;
    csv += `Fecha de exportación: ${fecha}\n\n`;

    datos.forEach((pregunta, idx) => {
      csv += `PREGUNTA ${idx + 1}: ${pregunta.titulo}\n`;
      csv += "Opción,Respuestas,Porcentaje\n";
      pregunta.opciones.forEach((op) => {
        csv += `"${op.label}",${op.respuestas},${op.porcentaje}%\n`;
      });
      csv += `Total,${pregunta.total},100%\n\n`;
    });

    helpers.descargarArchivo(
      csv,
      `resultados_completos_${Date.now()}.csv`,
      "text/csv"
    );
  };

  ns.exportarTodosExcel = function () {
    const { filtro, fecha, datos } = ns.obtenerDatosGlobales();
    if (!datos.length) {
      alert("No hay datos para exportar");
      return;
    }

    if (typeof XLSX === "undefined") {
      alert("Error: Librería XLSX no cargada");
      return;
    }

    try {
      const wb = XLSX.utils.book_new();

      const wsResumen = XLSX.utils.aoa_to_sheet([
        ["RESULTADOS COMPLETOS DE ENCUESTA"],
        [],
        ["Filtro:", filtro],
        ["Fecha de exportación:", fecha],
        ["Total de preguntas:", datos.length],
        [],
      ]);
      wsResumen["!cols"] = [{ wch: 25 }, { wch: 50 }];
      XLSX.utils.book_append_sheet(wb, wsResumen, "Resumen");

      datos.forEach((pregunta, idx) => {
        const wsData = [
          [`PREGUNTA ${idx + 1}: ${pregunta.titulo}`],
          [],
          ["Opción", "Respuestas", "Porcentaje"],
          ...pregunta.opciones.map((op) => [
            op.label,
            op.respuestas,
            `${op.porcentaje}%`,
          ]),
          ["Total", pregunta.total, "100%"],
        ];
        const ws = XLSX.utils.aoa_to_sheet(wsData);
        ws["!cols"] = [{ wch: 30 }, { wch: 12 }, { wch: 12 }];
        XLSX.utils.book_append_sheet(wb, ws, `P${idx + 1}`);
      });

      XLSX.writeFile(wb, `resultados_completos_${Date.now()}.xlsx`);
    } catch (err) {
      console.error("Error exportando Excel:", err);
      alert("Error al exportar a Excel");
    }
  };

  ns.exportarTodosPDF = function () {
    const { filtro, fecha, datos } = ns.obtenerDatosGlobales();
    if (!datos.length) {
      alert("No hay datos para exportar");
      return;
    }

    if (typeof jsPDF === "undefined" || !jsPDF.jsPDF) {
      alert("Error: Librería jsPDF no cargada");
      return;
    }

    try {
      const { jsPDF } = global;
      const doc = new jsPDF();
      let y = 20;

      doc.setFontSize(16);
      doc.setFont(undefined, "bold");
      doc.text("RESULTADOS COMPLETOS DE ENCUESTA", 10, y);
      y += 15;

      doc.setFontSize(10);
      doc.setFont(undefined, "normal");
      doc.text(`Filtro: ${filtro}`, 10, y);
      y += 7;
      doc.text(`Fecha: ${fecha}`, 10, y);
      y += 12;

      datos.forEach((pregunta, idx) => {
        if (y > 250) {
          doc.addPage();
          y = 20;
        }

        doc.setFontSize(12);
        doc.setFont(undefined, "bold");
        doc.text(
          `Pregunta ${idx + 1}: ${String(pregunta.titulo).substring(0, 80)}`,
          10,
          y
        );
        y += 8;

        doc.setFontSize(10);
        doc.setFont(undefined, "bold");
        doc.text("Opción", 15, y);
        doc.text("Respuestas", 100, y);
        doc.text("Porcentaje", 150, y);
        doc.setDrawColor(200);
        doc.line(10, y + 2, 200, y + 2);
        y += 8;

        doc.setFont(undefined, "normal");
        pregunta.opciones.forEach((op) => {
          if (y > 270) {
            doc.addPage();
            y = 20;
          }
          doc.text(op.label.substring(0, 40), 15, y);
          doc.text(String(op.respuestas), 100, y);
          doc.text(`${op.porcentaje}%`, 150, y);
          y += 7;
        });

        doc.setFont(undefined, "bold");
        doc.text("Total", 15, y);
        doc.text(String(pregunta.total), 100, y);
        doc.text("100%", 150, y);
        y += 12;
      });

      doc.save(`resultados_completos_${Date.now()}.pdf`);
    } catch (err) {
      console.error("Error exportando PDF:", err);
      alert("Error al exportar a PDF");
    }
  };

  ns.exportarTodosPrint = function () {
    const { filtro, fecha, datos } = ns.obtenerDatosGlobales();
    if (!datos.length) {
      alert("No hay datos para exportar");
      return;
    }

    const w = global.open("", "", "width=900,height=700");
    w.document.write(
      "<html><head><title>Imprimir Resultados Completos</title><style>"
    );
    w.document.write(
      "body{font-family:Arial,sans-serif;padding:20px;background:#f5f5f5;}"
    );
    w.document.write(
      "h1{color:#7A1E2C;border-bottom:3px solid #D4B056;padding-bottom:10px;}"
    );
    w.document.write(
      "h2{color:#7A1E2C;margin-top:20px;margin-bottom:10px;}"
    );
    w.document.write(
      ".info{background:#FFFAF3;padding:10px;margin:10px 0;border-left:4px solid #D4B056;}"
    );
    w.document.write(
      "table{border-collapse:collapse;width:100%;margin-top:10px;background:white;page-break-inside:avoid;}"
    );
    w.document.write(
      "th,td{border:1px solid #ddd;padding:10px;text-align:left;}"
    );
    w.document.write("th{background:#7A1E2C;color:white;font-weight:bold;}");
    w.document.write("tr:nth-child(even){background:#f9f5e4;}");
    w.document.write("tr:hover{background:#f0e6d2;}");
    w.document.write(
      "@media print{body{background:white;}.page-break{page-break-after:always;}}"
    );
    w.document.write("</style></head><body>");

    w.document.write("<h1>Resultados Completos de Encuesta</h1>");
    w.document.write(
      `<div class="info"><strong>Filtro aplicado:</strong> ${filtro}</div>`
    );
    w.document.write(
      `<div class="info"><strong>Fecha de impresión:</strong> ${fecha}</div>`
    );
    w.document.write(
      `<div class="info"><strong>Total de preguntas:</strong> ${datos.length}</div>`
    );

    datos.forEach((pregunta, idx) => {
      w.document.write(`<h2>Pregunta ${idx + 1}: ${pregunta.titulo}</h2>`);
      w.document.write(
        "<table><tr><th>Opción</th><th>Respuestas</th><th>Porcentaje</th></tr>"
      );
      pregunta.opciones.forEach((op) => {
        w.document.write(
          `<tr><td>${op.label}</td><td>${op.respuestas}</td><td>${op.porcentaje}%</td></tr>`
        );
      });
      w.document.write(
        `<tr><td><strong>Total</strong></td><td><strong>${pregunta.total}</strong></td><td><strong>100%</strong></td></tr>`
      );
      w.document.write("</table>");
      if (idx < datos.length - 1) {
        w.document.write('<div class="page-break"></div>');
      }
    });

    w.document.write("</body></html>");
    w.document.close();
    w.print();
  };

  // Exponer para los botones globales
  global.exportarTodosCSV = function () {
    ns.exportarTodosCSV();
  };
  global.exportarTodosExcel = function () {
    ns.exportarTodosExcel();
  };
  global.exportarTodosPDF = function () {
    ns.exportarTodosPDF();
  };
  global.exportarTodosPrint = function () {
    ns.exportarTodosPrint();
  };
})(window);
