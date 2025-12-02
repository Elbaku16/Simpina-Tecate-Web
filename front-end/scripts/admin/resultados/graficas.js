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

    // Solo alternamos clases. El navegador es MUY rápido haciendo esto.
    legend.classList.toggle("collapsed"); 
    legend.classList.toggle("expanded");
    btn.classList.toggle("active");
  };

  document.addEventListener("DOMContentLoaded", initCharts);

  // Exponer para onclick del HTML
  global.toggleLegend = function (preguntaId) {
    ns.toggleLegend(preguntaId);
  };
})(window);
