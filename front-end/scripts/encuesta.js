let indice = 0;

const contenedor = document.getElementById("contenedorPreguntas");
const btnSiguiente = document.getElementById("btnSiguiente");
const btnAnterior = document.getElementById("btnAnterior");

function mostrarPregunta(i) {
  const pregunta = preguntas[i];
  if (!pregunta) return;

  contenedor.innerHTML = `
    <div class="pregunta">
      <h3>${pregunta.texto}</h3>
      ${pregunta.tipo === "opcion" && pregunta.opciones.length > 0
        ? pregunta.opciones.map(
            (op) => `
              <label>
                <input type="radio" name="pregunta_${pregunta.id}" value="${op.id}">
                ${op.texto}
              </label><br>
            `
          ).join("")
        : '<textarea placeholder="Escribe tu respuesta aquí..."></textarea>'
      }
    </div>
  `;

  btnAnterior.disabled = i === 0;
  btnSiguiente.disabled = i === preguntas.length - 1;
}

btnSiguiente.addEventListener("click", () => {
  if (indice < preguntas.length - 1) {
    indice++;
    mostrarPregunta(indice);
  }
});

btnAnterior.addEventListener("click", () => {
  if (indice > 0) {
    indice--;
    mostrarPregunta(indice);
  }
});

// Mostrar la primera al cargar
mostrarPregunta(indice);
