// front-end/scripts/encuesta.js
console.log("CARGADO encuesta.js", performance.now());

import { construirPaginas } from './utils/paginacion.js';
import { renderPagina } from './utils/renderer.js';
import {
    actualizarProgresoRespuestas,
    actualizarProgresoPagina
} from './utils/progreso.js';

// 👇 CORRECCIÓN 1: Importar restaurarRanking
import { respuestasRanking, restaurarRanking } from './components/pregunta-ranking.js';

let preguntas = [];
let idEncuesta = null;

let paginas = [];
let paginaActual = 0;

const contenedor = document.getElementById('contenedorPreguntas');
const nivel = contenedor.dataset.nivel;

const btnAnterior = document.getElementById('btnAnterior');
const btnSiguiente = document.getElementById('btnSiguiente');

/**
 * 🧠 Estado global de respuestas
 */
const respuestasGlobal = {
    texto: {},
    opcion: {},
    multiple: {},
    ranking: {}, 
    dibujo: {}
};

/* ==========================================================
   GUARDAR DIBUJOS
========================================================== */
function guardarDibujosPaginaActual() {
    document.querySelectorAll('.canvas-paint').forEach(root => {
        const idPregunta = root.dataset.idPregunta;
        const canvas = root.querySelector('.cp-canvas');
        const filled = root.dataset.filled === '1';

        if (filled && canvas) {
            respuestasGlobal.dibujo[idPregunta] = canvas.toDataURL('image/png', 0.7);
        }
    });
}

/* ==========================================================
   CARGAR ENCUESTA
========================================================== */
async function cargarEncuesta() {
    try {
        const resp = await fetch(`/back-end/routes/encuestas/obtener.php?nivel=${nivel}`);
        if (!resp.ok) throw new Error(`HTTP ${resp.status}`);

        const data = await resp.json();
        preguntas = data.preguntas || [];
        console.log("PREGUNTAS CRUDAS DEL BACKEND:", preguntas);

        idEncuesta = data.id_encuesta;
        window.preguntas = preguntas;

        preguntas.forEach(p => {
            p.tipo = String(p.tipo).toLowerCase();
        });

        paginas = construirPaginas(preguntas);
        paginaActual = 0;
        mostrarPagina();
    } catch (error) {
        console.error('Error al cargar:', error);
        const loader = document.getElementById('loaderEncuesta');
        if (loader) loader.textContent = 'Error al cargar la encuesta.';
    }
}

/* ==========================================================
   MOSTRAR PÁGINA
========================================================== */
function restaurarDibujos() {
    if (!respuestasGlobal.dibujo) return;
    document.querySelectorAll('.canvas-paint').forEach(root => {
        const id = root.dataset.idPregunta;
        const base64 = respuestasGlobal.dibujo[id];
        if (!base64) return;

        const canvas = root.querySelector('.cp-canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();
        img.onload = () => {
            const dpr = window.devicePixelRatio || 1;
            ctx.drawImage(img, 0, 0, canvas.width / dpr, canvas.height / dpr);
            root.dataset.filled = '1';
        };
        img.src = base64;
    });
}

function mostrarPagina() {
    // 1. Renderizar HTML base (esto resetea el orden visual)
    renderPagina(paginas[paginaActual], preguntas, contenedor);
    
    actualizarProgresoPagina(paginaActual, paginas);
    actualizarProgresoRespuestas();

    // Botones
    if (paginaActual === 0) {
        btnAnterior.style.visibility = "hidden";
    } else {
        btnAnterior.style.visibility = "visible";
    }

    if (window.initCanvasPaint) window.initCanvasPaint();
    
    restaurarDibujos();

    // 👇 CORRECCIÓN 2: Restaurar el orden visual del Ranking 👇
    const preguntasPagina = paginas[paginaActual];
    preguntasPagina.forEach(p => {
        if (p.tipo === 'ranking') {
            // Llama a la función del componente que reordena el DOM usando window.respuestasRanking
            restaurarRanking(p.id); 
        }
    });
    // 👆 FIN CORRECCIÓN 👆

    if (paginaActual === paginas.length - 1) {
        btnSiguiente.textContent = "Enviar encuesta";
    } else {
        btnSiguiente.textContent = "Siguiente";
    }

    restaurarRespuestasEnDOM();
    document.dispatchEvent(new CustomEvent("encuesta:lista"));
    window.scrollTo({ top: 0, behavior: "smooth" });
}

/* ==========================================================
   NAVEGACIÓN
========================================================== */
btnSiguiente.addEventListener('click', () => {
    guardarDibujosPaginaActual();
    if (paginaActual === paginas.length - 1) {
        enviar();
    } else {
        paginaActual++;
        mostrarPagina();
    }
});

btnAnterior.addEventListener('click', () => {
    guardarDibujosPaginaActual();
    if (paginaActual > 0) {
        paginaActual--;
        mostrarPagina();
    }
});

/* ==========================================================
   LISTENERS
========================================================== */
contenedor.addEventListener('input', (e) => {
    const target = e.target;
    if (target.matches('textarea[id^="texto_"]')) {
        const id = target.id.replace('texto_', '');
        respuestasGlobal.texto[id] = target.value.trim();
    }
 
});

contenedor.addEventListener('change', (e) => {
    const target = e.target;
 if (target.matches('input[type="radio"]')) {
        const id = target.name.replace('pregunta_', '');
        
        // Simplemente guardamos el ID y el texto normal, ignorando inputs extra
        const obj = { 
            id_opcion: parseInt(target.value), 
            texto_opcion: target.dataset.texto 
        };
        
        respuestasGlobal.opcion[id] = obj;
    }
    if (target.matches('input[type="checkbox"]')) {
        const id = target.name.replace('pregunta_', '');
        const checked = contenedor.querySelectorAll(`input[name="${target.name}"]:checked`);
        respuestasGlobal.multiple[id] = Array.from(checked).map(c => ({
            id_opcion: parseInt(c.value),
            texto_opcion: c.dataset.texto
        }));
    }
});

/* ==========================================================
   RESTAURAR VALORES DOM
========================================================== */
function restaurarRespuestasEnDOM() {
    Object.entries(respuestasGlobal.texto).forEach(([id, v]) => {
        const el = document.getElementById(`texto_${id}`);
        if (el) el.value = v;
    });
    Object.entries(respuestasGlobal.opcion).forEach(([id, v]) => {
        if (!v?.id_opcion) return;
        const r = document.querySelector(`input[name="pregunta_${id}"][value="${v.id_opcion}"]`);
        if (r) r.checked = true;
       
    });
    Object.entries(respuestasGlobal.multiple).forEach(([id, arr]) => {
        if (!Array.isArray(arr)) return;
        arr.forEach(op => {
            const c = document.querySelector(`input[name="pregunta_${id}"][value="${op.id_opcion}"]`);
            if (c) c.checked = true;
        });
    });
}

/* ==========================================================
   VALIDAR
========================================================== */
function validarEncuestaCompleta() {
    const err = [];
    preguntas.forEach(p => {
        const id = p.id;
        const tipo = p.tipo.toLowerCase();
        const label = (p.texto || '').trim() || `ID ${id}`;

        if (tipo === 'texto' && !respuestasGlobal.texto[id]) err.push(`"${label}" requiere texto.`);
        if (tipo === 'opcion') {
            const d = respuestasGlobal.opcion[id];
            if (!d?.id_opcion) err.push(`"${label}" requiere opción.`);
        }
        if (tipo === 'multiple' && (!respuestasGlobal.multiple[id] || respuestasGlobal.multiple[id].length === 0)) err.push(`"${label}" requiere al menos una opción.`);
        
        if (tipo === 'ranking') {
            // Validar contra window.respuestasRanking que es la fuente de verdad para ranking
            const lista = window.respuestasRanking && window.respuestasRanking[id];
            if (!lista || lista.length === 0) err.push(`"${label}" requiere ordenar.`);
        }
        
        if (tipo === 'dibujo') {
            const b64 = respuestasGlobal.dibujo[id];
            if (!b64 || b64.length < 50) err.push(`"${label}" requiere dibujo.`);
        }
    });
    return err;
}

/* ==========================================================
   ENVIAR
========================================================== */
function enviar() {
    console.log('Iniciando envío...');
    guardarDibujosPaginaActual();

    // Sincronizar Ranking final
    if (window.respuestasRanking) {
        Object.assign(respuestasGlobal.ranking, window.respuestasRanking);
    }

    const errores = validarEncuestaCompleta();
    if (errores.length > 0) {
        alert('Faltan respuestas:\n- ' + errores.join('\n- '));
        return;
    }

    let idEscuela = localStorage.getItem('id_escuela_seleccionada') || 1;
    let genero = localStorage.getItem('genero_seleccionado') || 'X';

    const payload = {
        id_encuesta: idEncuesta,
        id_escuela: parseInt(idEscuela),
        genero: genero,
        respuestas: {
            texto: respuestasGlobal.texto,
            opcion: respuestasGlobal.opcion,
            multiple: respuestasGlobal.multiple,
            ranking: respuestasGlobal.ranking
        },
        dibujos: respuestasGlobal.dibujo
    };

    console.log('Enviando:', payload);
    btnSiguiente.disabled = true;

    fetch('/back-end/routes/encuestas/enviar-respuestas.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.ok ? r.json() : Promise.reject(r.status))
    .then(data => {
        if (data.success) {
            alert('¡Encuesta enviada exitosamente! ¡Gracias por participar!');
            
            // Limpieza de datos
            localStorage.removeItem('id_escuela_seleccionada');
            localStorage.removeItem('genero_seleccionado');
            
            window.location.href = '/front-end/frames/inicio/inicio.php';
        } else {
            alert('Error: ' + (data.error || 'Desconocido'));
            btnSiguiente.disabled = false;
        }
    })
    .catch(e => {
        console.error(e);
        alert('Error de conexión.');
        btnSiguiente.disabled = false;
    });
}

cargarEncuesta();