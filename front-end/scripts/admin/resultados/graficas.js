// /front-end/scripts/admin/resultados/graficas.js
(function (global) {
  const ns = (global.SimpinnaResultados = global.SimpinnaResultados || {});
  ns.charts = ns.charts || {};

  function initCharts() {
    const slots = document.querySelectorAll(".pie-slot[data-pregunta-id]");
    slots.forEach((slot) => {
      const preguntaId = slot.getAttribute("data-pregunta-id");
      const canvas = slot.querySelector("canvas");
      if (!canvas) return;

      try {
        const labels = JSON.parse(slot.getAttribute("data-labels") || "[]");
        const values = JSON.parse(slot.getAttribute("data-values") || "[]");
        const colors = JSON.parse(slot.getAttribute("data-colors") || "[]");

        const ctx = canvas.getContext("2d");
        const chart = new Chart(ctx, {
          type: "pie",
          data: {
            labels,
            datasets: [
              {
                data: values,
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: "#fff",
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
              legend: { display: false },
              title: { display: false },
            },
          },
        });

        ns.charts[preguntaId] = chart;
        global["chart_" + preguntaId] = chart; // compatibilidad con código existente
      } catch (err) {
        console.error("Error creando gráfica para pregunta", preguntaId, err);
      }
    });
  }

  ns.toggleLegend = function (preguntaId) {
    const btn = document.getElementById("toggle-" + preguntaId);
    const legend = document.getElementById("legend-" + preguntaId);
    if (!btn || !legend) return;

    const isCollapsed = legend.classList.contains("collapsed");

    if (isCollapsed) {
      legend.classList.remove("collapsed");
      legend.classList.add("expanded");
      btn.classList.add("active");
      btn.innerHTML =
        '<span>Ocultar leyenda</span><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>';
    } else {
      legend.classList.add("collapsed");
      legend.classList.remove("expanded");
      btn.classList.remove("active");
      btn.innerHTML =
        '<span>Ver leyenda</span><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>';
    }
  };

  document.addEventListener("DOMContentLoaded", initCharts);

  // Exponer para onclick del HTML
  global.toggleLegend = function (preguntaId) {
    ns.toggleLegend(preguntaId);
  };
})(window);
