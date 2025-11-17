// /front-end/scripts/admin/resultados/modal-respuestas.js
(function (global) {
  const ns = (global.SimpinnaResultados = global.SimpinnaResultados || {});
  const helpers = ns;

  // ========== ABRIR RESPUESTAS ==========
  ns.abrirRespuestas = function (preguntaId, nivel, escuela) {
    const modal = document.getElementById("modalRespuestas");
    const contenido = document.getElementById("modalContenido");
    const titulo = document.getElementById("modalTitulo");

    if (!modal || !contenido || !titulo) return;

    modal.classList.remove("hidden");
    contenido.innerHTML = '<div class="loading">Cargando respuestas...</div>';

    let url = `/SIMPINNA/back-end/routes/resultados/respuestas_texto.php?accion=obtener&id_pregunta=${encodeURIComponent(
      preguntaId
    )}&escuela=${encodeURIComponent(escuela || 0)}`;

    fetch(url)
      .then((r) => r.json())
      .then((data) => {
        if (!data.success) {
          contenido.innerHTML = `<div class="error-mensaje">${
            data.error || "Error al cargar respuestas"
          }</div>`;
          return;
        }

        titulo.textContent = `Respuestas`;

        let html = '<div class="respuestas-tabla">';

        if (!data.respuestas.length) {
          html +=
            '<div class="sin-respuestas">No hay respuestas para esta pregunta</div>';
        } else {
          html += '<div class="tabla-header">';
          html += '<div class="col-respuesta">Respuesta</div>';
          html += '<div class="col-info">Escuela</div>';
          html += '<div class="col-info">Fecha</div>';
          html += '<div class="col-accion">Acción</div>';
          html += "</div>";

          data.respuestas.forEach((resp) => {
            html += '<div class="tabla-fila">';
            html += `<div class="col-respuesta"><p class="respuesta-texto">${helpers.escapeHtml(
              resp.texto
            )}</p></div>`;
            html += `<div class="col-info">${helpers.escapeHtml(
              resp.escuela
            )}</div>`;
            html += `<div class="col-info">${helpers.formatearFecha(
              resp.fecha
            )}</div>`;
            html += `<div class="col-accion"><button class="btn-eliminar" onclick="eliminarRespuesta(${resp.id}, ${preguntaId}, '${nivel}', ${escuela})">Eliminar</button></div>`;
            html += "</div>";
          });
        }

        html += "</div>";
        contenido.innerHTML = html;
      })
      .catch((err) => {
        console.error(err);
        contenido.innerHTML =
          '<div class="error-mensaje">Error al cargar respuestas</div>';
      });
  };

  // ========== CERRAR MODAL ==========
  ns.cerrarRespuestas = function () {
    const modal = document.getElementById("modalRespuestas");
    if (modal) modal.classList.add("hidden");
  };

  // ========== ELIMINAR RESPUESTA ==========
  ns.eliminarRespuesta = function (id, preguntaId, nivel, escuela) {
    if (!global.confirm("¿Estás seguro de eliminar esta respuesta?")) return;

    const bodyParams = new URLSearchParams();
    bodyParams.set("accion", "eliminar");
    bodyParams.set("id_respuesta", id);

    fetch("/SIMPINNA/back-end/routes/resultados/respuestas_texto.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: bodyParams.toString(),
    })
      .then((r) => r.json())
      .then((data) => {
        if (data.success) {
          ns.abrirRespuestas(preguntaId, nivel, escuela);
          alert("Respuesta eliminada");
        } else {
          alert("Error: " + (data.error || "No se pudo eliminar"));
        }
      })
      .catch((err) => {
        console.error(err);
        alert("Error al eliminar");
      });
  };

  // ========== EVENTO ESC ==========
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") ns.cerrarRespuestas();
  });

  // Exponer globalmente (para onclick)
  global.abrirRespuestas = ns.abrirRespuestas;
  global.cerrarRespuestas = ns.cerrarRespuestas;
  global.eliminarRespuesta = ns.eliminarRespuesta;
})(window);
