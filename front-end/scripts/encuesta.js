// front-end/scripts/encuesta.js
// 2 preguntas por página (vertical). La de ranking va sola.
// Si quieres 3 por página, cambia la constante de abajo a 3.
console.log('[encuesta.js v2025-11-05-OTRO] cargado');

const PREGUNTAS_POR_PAGINA = 2;

let paginaActual = 0;
let paginas = [];                      // array de arrays (índices de preguntas por página)
const respuestasRanking = {};          // { idPregunta: [{id_opcion, posicion}, ...] }

const contenedor   = document.getElementById('contenedorPreguntas');
const btnSiguiente = document.getElementById('btnSiguiente');
const btnAnterior  = document.getElementById('btnAnterior');

/* ========= Normalizador de tipos desde BD =========
   Salida: 'opcion' | 'multiple' | 'texto' | 'ranking'
*/
function normalizaTipo(rawTipo, opciones) {
  const s = String(rawTipo || '').toLowerCase().trim();
  const tieneOpciones = Array.isArray(opciones) && opciones.length > 0;

  const tabla = {
    ranking:  ['ranking','ordenar','prioridad','ordenamiento','drag'],
    multiple: ['multiple','múltiple','checkbox','seleccion_multiple','selección_múltiple','multi'],
    opcion:   ['opcion','opción','radio','seleccion_unica','selección_única','unica','única','single','one','si_no','sí_no'],
    texto:    ['texto','abierta','abierta_corta','abierta_larga','open','textarea']
  };

  const en = (arr) => arr.includes(s);
  if (en(tabla.ranking))  return 'ranking';
  if (en(tabla.multiple)) return 'multiple';
  if (en(tabla.opcion))   return 'opcion';
  if (en(tabla.texto))    return 'texto';

  // Heurística: si trae opciones, tratamos como opción única
  if (tieneOpciones) return 'opcion';
  return 'texto';
}

/* ========= Arma páginas con la regla ========= */
function construirPaginas(lista) {
  const pages = [];
  let buffer = [];

  for (let i = 0; i < lista.length; i++) {
    const p = lista[i];
    const esRanking = (p.tipo === 'ranking');

    if (esRanking) {
      if (buffer.length) { pages.push(buffer.slice()); buffer = []; }
      pages.push([i]); // ranking sola
      continue;
    }

    buffer.push(i);
    if (buffer.length === PREGUNTAS_POR_PAGINA) {
      pages.push(buffer.slice());
      buffer = [];
    }
  }

  if (buffer.length) pages.push(buffer.slice());
  return pages;
}

/* ========= Render de página ========= */
function mostrarPagina(k) {
  contenedor.innerHTML = '';

  const indices = paginas[k] || [];
  const page = document.createElement('div');
  page.className = 'pagina-encuesta pagina-encuesta--visible';

  indices.forEach(i => {
    const p = preguntas[i];
    const card = document.createElement('div');
    card.className = (p.tipo === 'ranking') ? 'pregunta pregunta--full' : 'pregunta';
    card.innerHTML = plantillaPregunta(p);
    page.appendChild(card);

    if (p.tipo === 'ranking') {
      setTimeout(() => {
        activarDragAndDrop(p.id);
        restaurarRanking(p.id);
      }, 0);
    }
  });

  contenedor.appendChild(page);

  /* ✅ Activador dinámico para "Otro" */
  page.querySelectorAll('input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', (e) => {
      const idPregunta = radio.name.split('_')[1];
      const inputOtro = page.querySelector(`#otro_${idPregunta}`);
      if (!inputOtro) return;

      const texto = (e.target.dataset.texto || "").trim().toLowerCase();
      const esOtro = texto.startsWith("otro") || texto.startsWith("otra");

      if (esOtro) {
        inputOtro.style.display = 'block';
        inputOtro.focus();
      } else {
        inputOtro.style.display = 'none';
        inputOtro.value = "";
      }
    });
  });

  btnAnterior.disabled = (k === 0);
  btnSiguiente.textContent = (k === paginas.length - 1) ? 'Enviar Encuesta' : 'Siguiente';
}

/* ========= Plantillas ========= */
function plantillaPregunta(p) {
  if (p.tipo === 'ranking') {
    return `
      <h3>${p.texto}</h3>
      <div class="ranking-container" id="rankingContainer_${p.id}">
        ${(p.opciones || []).map((op, idx) => `
          <div class="ranking-item" draggable="true" data-opcion-id="${op.id}" data-posicion="${idx + 1}">
            <span class="drag-handle">☰</span>
            <span class="ranking-numero">${idx + 1}</span>
            <span class="ranking-texto">${op.texto}</span>
          </div>
        `).join('')}
      </div>
    `;
  }

  if (p.tipo === 'multiple' && p.opciones?.length) {
    return `
      <h3>${p.texto}</h3>
      <p style="color:#666;font-size:14px;margin-bottom:10px;"><em>Puedes seleccionar varias opciones</em></p>
      <div class="opciones">
        ${p.opciones.map(op => `
          <label><input type="checkbox" name="pregunta_${p.id}" value="${op.id}">${op.texto}</label>
        `).join('')}
      </div>
    `;
  }

  if (p.tipo === 'opcion' && p.opciones?.length) {
    return `
      <h3>${p.texto}</h3>
      <div class="opciones">
        ${p.opciones.map(op => `
          <label class="opcion-item">
            <input type="radio" 
                  name="pregunta_${p.id}"
                  value="${op.id}"
                  data-texto="${op.texto.toLowerCase()}">
            ${op.texto}
          </label>
        `).join('')}
        <input type="text"
              class="input-otro"
              id="otro_${p.id}"
              placeholder="Especifica tu respuesta..."
              style="display:none; margin-top:10px; width:95%; padding:10px; border:1px solid #ccc; border-radius:6px;">
      </div>
    `;
  }

  return `
    <h3>${p.texto}</h3>
    <textarea id="texto_${p.id}" placeholder="Escribe tu respuesta aquí..." rows="5"></textarea>
  `;
}

/* ========= Ranking drag & drop ========= */
function activarDragAndDrop(idPregunta) {
  const c = document.getElementById(`rankingContainer_${idPregunta}`);
  if (!c) return;
  const items = c.querySelectorAll('.ranking-item');
  let dragged = null;

  items.forEach(item => {
    item.addEventListener('dragstart', e => {
      dragged = item;
      item.classList.add('dragging');
      e.dataTransfer.effectAllowed = 'move';
    });

    item.addEventListener('dragend', () => {
      item.classList.remove('dragging');
      dragged = null;
      renumerarRanking(c);
      guardarRanking(idPregunta, c);
    });

    item.addEventListener('dragover', e => {
      e.preventDefault();
      const after = elementoDespuesDe(c, e.clientY);
      if (after == null) c.appendChild(dragged);
      else c.insertBefore(dragged, after);
    });
  });
}

function elementoDespuesDe(container, y) {
  const els = [...container.querySelectorAll('.ranking-item:not(.dragging)')];
  return els.reduce((closest, child) => {
    const box = child.getBoundingClientRect();
    const offset = y - box.top - box.height / 2;
    if (offset < 0 && offset > closest.offset) return { offset, element: child };
    return closest;
  }, { offset: Number.NEGATIVE_INFINITY }).element;
}

function renumerarRanking(container) {
  container.querySelectorAll('.ranking-item').forEach((item, idx) => {
    item.querySelector('.ranking-numero').textContent = idx + 1;
    item.dataset.posicion = idx + 1;
  });
}

function guardarRanking(idPregunta, container) {
  if (!container) container = document.getElementById(`rankingContainer_${idPregunta}`);
  if (!container) return;
  const arr = [];
  container.querySelectorAll('.ranking-item').forEach((el, idx) => {
    arr.push({ id_opcion: parseInt(el.dataset.opcionId, 10), posicion: idx + 1 });
  });
  respuestasRanking[idPregunta] = arr;
}

function restaurarRanking(idPregunta) {
  const data = respuestasRanking[idPregunta];
  if (!data) return;
  const c = document.getElementById(`rankingContainer_${idPregunta}`);
  if (!c) return;
  data.forEach(it => {
    const el = c.querySelector(`[data-opcion-id="${it.id_opcion}"]`);
    if (el) c.appendChild(el);
  });
  renumerarRanking(c);
}

function guardarRankingsVisibles() {
  document.querySelectorAll('[id^="rankingContainer_"]').forEach(c => {
    const id = parseInt(c.id.replace('rankingContainer_', ''), 10);
    guardarRanking(id, c);
  });
}

/* ========= Navegación ========= */
btnSiguiente?.addEventListener('click', () => {
  guardarRankingsVisibles();
  const esUltima = (paginaActual === paginas.length - 1);
  if (esUltima) { enviarEncuesta(); return; }
  paginaActual++; mostrarPagina(paginaActual);
});

btnAnterior?.addEventListener('click', () => {
  guardarRankingsVisibles();
  if (paginaActual > 0) { paginaActual--; mostrarPagina(paginaActual); }
});

/* ========= Envío (por ahora ranking) ========= */
function enviarEncuesta() {
  if (Object.keys(respuestasRanking).length > 0) {
    fetch('/SIMPINNA/back-end/guardar_ranking.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ respuestas: respuestasRanking, id_encuesta: idEncuesta })
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) alert('¡Encuesta enviada exitosamente! ✅');
      else alert('❌ Error al enviar: ' + data.message);
    })
    .catch(err => { console.error(err); alert('❌ Error de conexión.'); });
  } else {
    alert('Encuesta lista (sin rankings capturados).');
  }
}

/* ========= Init ========= */
(function init() {
  if (!Array.isArray(preguntas) || !preguntas.length || !contenedor) {
    console.warn('[encuesta.js] No hay preguntas para este nivel o contenedor no encontrado.');
    return;
  }

  preguntas.forEach(p => { p.tipo = normalizaTipo(p.tipo, p.opciones); });

  paginas = construirPaginas(preguntas);
  paginaActual = 0;
  mostrarPagina(paginaActual);
})();
