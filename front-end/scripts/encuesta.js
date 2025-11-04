// encuesta.js - VERSIÓN CORREGIDA
let indice = 0;
const respuestasRanking = {}; // Guardará los rankings

const contenedor = document.getElementById("contenedorPreguntas");
const btnSiguiente = document.getElementById("btnSiguiente");
const btnAnterior = document.getElementById("btnAnterior");

function mostrarPregunta(i) {
  const pregunta = preguntas[i];
  if (!pregunta) return;

  console.log('📋 Mostrando pregunta:', pregunta); // DEBUG

  // ==================== PREGUNTA TIPO RANKING ====================
  if (pregunta.tipo === "ranking") {
    contenedor.innerHTML = `
      <div class="pregunta">
        <h3>${pregunta.texto}</h3>
        <div class="instrucciones">
          <strong>📋 Instrucciones:</strong> Arrastra las opciones para ordenarlas según tu preferencia. 
          <br>El <strong>#1</strong> es lo más importante para ti, el <strong>#${pregunta.opciones.length}</strong> es lo menos importante.
        </div>
        <div class="ranking-container" id="rankingContainer">
          ${pregunta.opciones.map((op, idx) => `
            <div class="ranking-item" 
                 draggable="true" 
                 data-opcion-id="${op.id}"
                 data-posicion="${idx + 1}">
              <span class="drag-handle">☰</span>
              <span class="ranking-numero">${idx + 1}</span>
              <span class="ranking-texto">${op.texto}</span>
            </div>
          `).join('')}
        </div>
      </div>
    `;
    
    // Activar drag and drop
    activarDragAndDrop(pregunta.id);
    cargarRankingGuardado(pregunta.id);
    
  // ==================== PREGUNTA TIPO MULTIPLE (CHECKBOX) ====================
  } else if (pregunta.tipo === "multiple" && pregunta.opciones.length > 0) {
    contenedor.innerHTML = `
      <div class="pregunta">
        <h3>${pregunta.texto}</h3>
        <p style="color: #666; font-size: 14px; margin-bottom: 15px;">
          <em>Puedes seleccionar varias opciones</em>
        </p>
        <div class="opciones">
          ${pregunta.opciones.map(op => `
            <label>
              <input type="checkbox" name="pregunta_${pregunta.id}" value="${op.id}">
              ${op.texto}
            </label>
          `).join("")}
        </div>
      </div>
    `;
    
  // ==================== PREGUNTA TIPO OPCION (RADIO) ====================
  } else if (pregunta.tipo === "opcion" && pregunta.opciones.length > 0) {
    contenedor.innerHTML = `
      <div class="pregunta">
        <h3>${pregunta.texto}</h3>
        <div class="opciones">
          ${pregunta.opciones.map(op => `
            <label>
              <input type="radio" name="pregunta_${pregunta.id}" value="${op.id}">
              ${op.texto}
            </label>
          `).join("")}
        </div>
      </div>
    `;
    
  // ==================== PREGUNTA TIPO TEXTO ====================
  } else if (pregunta.tipo === "texto") {
    contenedor.innerHTML = `
      <div class="pregunta">
        <h3>${pregunta.texto}</h3>
        <textarea id="texto_${pregunta.id}" placeholder="Escribe tu respuesta aquí..." rows="5"></textarea>
      </div>
    `;
    
  // ==================== PREGUNTA TIPO IMAGEN ====================
  } else if (pregunta.tipo === "imagen") {
    contenedor.innerHTML = `
      <div class="pregunta">
        <h3>${pregunta.texto}</h3>
        <p style="color: #666;">Este tipo de pregunta aún no está implementado.</p>
      </div>
    `;
  }

  btnAnterior.disabled = i === 0;
  btnSiguiente.textContent = i === preguntas.length - 1 ? "Enviar Encuesta" : "Siguiente";
}

// ==================== FUNCIONES DRAG AND DROP ====================

function activarDragAndDrop(idPregunta) {
  const container = document.getElementById('rankingContainer');
  if (!container) return;
  
  const items = container.querySelectorAll('.ranking-item');
  let draggedItem = null;
  
  items.forEach(item => {
    item.addEventListener('dragstart', (e) => {
      draggedItem = item;
      item.classList.add('dragging');
      e.dataTransfer.effectAllowed = 'move';
    });
    
    item.addEventListener('dragend', (e) => {
      item.classList.remove('dragging');
      draggedItem = null;
      actualizarNumerosRanking(container);
      guardarRankingActual(idPregunta);
    });
    
    item.addEventListener('dragover', (e) => {
      e.preventDefault();
      const afterElement = getDragAfterElement(container, e.clientY);
      if (afterElement == null) {
        container.appendChild(draggedItem);
      } else {
        container.insertBefore(draggedItem, afterElement);
      }
    });
  });
}

function getDragAfterElement(container, y) {
  const draggableElements = [...container.querySelectorAll('.ranking-item:not(.dragging)')];
  
  return draggableElements.reduce((closest, child) => {
    const box = child.getBoundingClientRect();
    const offset = y - box.top - box.height / 2;
    
    if (offset < 0 && offset > closest.offset) {
      return { offset: offset, element: child };
    } else {
      return closest;
    }
  }, { offset: Number.NEGATIVE_INFINITY }).element;
}

function actualizarNumerosRanking(container) {
  const items = container.querySelectorAll('.ranking-item');
  items.forEach((item, index) => {
    const numeroSpan = item.querySelector('.ranking-numero');
    numeroSpan.textContent = index + 1;
    item.dataset.posicion = index + 1;
  });
}

function guardarRankingActual(idPregunta) {
  const container = document.getElementById('rankingContainer');
  if (!container) return;
  
  const items = container.querySelectorAll('.ranking-item');
  respuestasRanking[idPregunta] = [];
  
  items.forEach((item, index) => {
    respuestasRanking[idPregunta].push({
      id_opcion: parseInt(item.dataset.opcionId),
      posicion: index + 1
    });
  });
  
  console.log('✅ Ranking guardado:', respuestasRanking[idPregunta]);
}

function cargarRankingGuardado(idPregunta) {
  if (!respuestasRanking[idPregunta]) return;
  
  const container = document.getElementById('rankingContainer');
  if (!container) return;
  
  const ranking = respuestasRanking[idPregunta];
  
  ranking.forEach(item => {
    const elemento = container.querySelector(`[data-opcion-id="${item.id_opcion}"]`);
    if (elemento) {
      container.appendChild(elemento);
    }
  });
  
  actualizarNumerosRanking(container);
}

// ==================== NAVEGACIÓN ====================

btnSiguiente.addEventListener("click", () => {
  const pregunta = preguntas[indice];
  
  // Guardar ranking si es tipo ranking
  if (pregunta.tipo === "ranking") {
    guardarRankingActual(pregunta.id);
  }
  
  if (indice < preguntas.length - 1) {
    indice++;
    mostrarPregunta(indice);
  } else {
    // Última pregunta - enviar encuesta
    enviarEncuesta();
  }
});

btnAnterior.addEventListener("click", () => {
  if (indice > 0) {
    indice--;
    mostrarPregunta(indice);
  }
});

// ==================== ENVIAR ENCUESTA ====================

function enviarEncuesta() {
  console.log('📤 Enviando encuesta...', respuestasRanking);
  
  // Si hay respuestas de ranking, enviarlas
  if (Object.keys(respuestasRanking).length > 0) {
    fetch('/SIMPINNA/back-end/guardar_ranking.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        respuestas: respuestasRanking,
        id_encuesta: idEncuesta // ← USA LA VARIABLE DEL PHP
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('¡Encuesta enviada exitosamente! ✅');
        // Opcional: redirigir
        // window.location.href = '/SIMPINNA/front-end/pages/gracias.php';
      } else {
        alert('❌ Error al enviar: ' + data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('❌ Error de conexión. Intenta nuevamente.');
    });
  } else {
    alert('⚠️ No hay respuestas de ranking para enviar');
  }
}

// ==================== INICIALIZAR ====================
console.log('🚀 Iniciando encuesta...');
console.log('Total de preguntas:', preguntas.length);
console.log('ID Encuesta:', idEncuesta);
mostrarPregunta(indice);




