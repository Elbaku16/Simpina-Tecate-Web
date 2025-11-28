// /front-end/scripts/admin/resultados/helpers.js
(function (global) {
  const ns = (global.SimpinnaResultados = global.SimpinnaResultados || {});

  /* ------------------------ Filtro actual (ACTUALIZADO) ------------------------ */
  ns.obtenerFiltroInfo = function () {
    const urlParams = new URLSearchParams(window.location.search);
    const nivel = urlParams.get("nivel") || "No especificado";
    const escuela = parseInt(urlParams.get("escuela") || "0", 10);
    // Captura de los nuevos filtros
    const ciclo = urlParams.get("ciclo") || "Todos";
    const genero = urlParams.get("genero") || "Todos";

    let filtroText = `Nivel: ${ns.escapeHtml(nivel)}`;

    // Filtro Escuela
    if (escuela > 0) {
      const selectEscuela = document.getElementById("escuela-filter");
      const nombreEscuela =
        selectEscuela && selectEscuela.selectedIndex >= 0
          ? selectEscuela.options[selectEscuela.selectedIndex].text
          : "No especificada";

      filtroText += ` | Escuela: ${ns.escapeHtml(nombreEscuela)}`;
    } else {
      filtroText += " | Escuela: Todas las escuelas";
    }

    // Filtro Género (Mostrar el valor legible si no es "Todos")
    if (genero !== 'Todos' && genero !== '') {
        const selectGenero = document.getElementById("genero-filter");
        const nombreGenero =
            selectGenero && selectGenero.selectedIndex >= 0
                ? selectGenero.options[selectGenero.selectedIndex].text
                : genero;

        filtroText += ` | Género: ${ns.escapeHtml(nombreGenero)}`;
    }

    // Filtro Ciclo (Mostrar el valor si no es "Todos")
    if (ciclo !== 'Todos' && ciclo !== '') {
        filtroText += ` | Ciclo: ${ns.escapeHtml(ciclo)}`;
    }

    return filtroText;
  };

  /* ------------------------ Descargar archivo ------------------------ */
  ns.descargarArchivo = function (contenido, nombreArchivo, tipo) {
    const blob = new Blob([contenido], { type: tipo });
    const url = global.URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = nombreArchivo;
    document.body.appendChild(a);
    a.click();
    global.URL.revokeObjectURL(url);
    a.remove();
  };

  /* ------------------------ Formatear fecha ------------------------ */
  ns.formatearFecha = function (fechaStr) {
    if (!fechaStr) return "";
    const fecha = new Date(fechaStr);
    return isNaN(fecha.getTime()) ? fechaStr : fecha.toLocaleDateString("es-MX");
  };

  /* ------------------------ Escape HTML ------------------------ */
  ns.escapeHtml = function (text) {
    const div = document.createElement("div");
    div.textContent = text == null ? "" : String(text);
    return div.innerHTML;
  };

  /* ------------------------ Toggle Leyenda ------------------------ */
  ns.toggleRankingLegend = function (preguntaId) {
    const btn = document.getElementById("toggle-ranking-" + preguntaId);
    const legend = document.getElementById("ranking-restantes-" + preguntaId);

    if (!btn || !legend) return;

    const collapsed = legend.classList.contains("collapsed");

    legend.classList.toggle("collapsed", !collapsed);
    legend.classList.toggle("expanded", collapsed);
    btn.classList.toggle("active", collapsed);

    btn.innerHTML = collapsed
      ? `<span>Ocultar más posiciones</span>
         <svg xmlns="http://www.w3.org/2000/svg" fill="none" 
         viewBox="0 0 24 24" stroke="currentColor">
           <path stroke-linecap="round" stroke-linejoin="round"
           stroke-width="2" d="M5 15l7-7 7 7"/>
         </svg>`
      : `<span>Ver más posiciones</span>
         <svg xmlns="http://www.w3.org/2000/svg" fill="none" 
         viewBox="0 0 24 24" stroke="currentColor">
           <path stroke-linecap="round" stroke-linejoin="round"
           stroke-width="2" d="M19 9l-7 7-7-7"/>
         </svg>`;
  };

  /* Exponer para HTML */
  global.toggleLegend = ns.toggleLegend;
  // Exponer la nueva función para el ranking
  global.SimpinnaResultados.toggleRankingLegend = ns.toggleRankingLegend;

})(window);