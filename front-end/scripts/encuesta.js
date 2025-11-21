// front-end/scripts/encuesta.js
console.log("CARGADO encuesta.js", performance.now());

import { construirPaginas } from './utils/paginacion.js';
import { renderPagina } from './utils/renderer.js';
import {
    actualizarProgresoRespuestas,
    actualizarProgresoPagina
} from './utils/progreso.js';
import { respuestasRanking } from './components/pregunta-ranking.js';

let preguntas = [];
let idEncuesta = null;

let paginas = [];
let paginaActual = 0;

const contenedor = document.getElementById('contenedorPreguntas');
const nivel = contenedor.dataset.nivel;

const btnAnterior = document.getElementById('btnAnterior');
const btnSiguiente = document.getElementById('btnSiguiente');

/**
 * 🧠 Estado global de respuestas (no depende del DOM de la página actual)
 */
const respuestasGlobal = {
    texto: {},    // { idPregunta: "respuesta" }
    opcion: {},   // { idPregunta: { id_opcion, texto_opcion?, texto_otro? } }
    multiple: {}, // { idPregunta: [ { id_opcion, texto_opcion }, ... ] }
    ranking: {},  // { idPregunta: [ {id_opcion, posicion}, ... ] }
    dibujo: {}    // { idPregunta: base64 }
};
/* ==========================================================
   NUEVA FUNCIÓN: GUARDAR DIBUJOS VISIBLES
   (Llamar antes de cambiar de página)
========================================================== */
function guardarDibujosPaginaActual() {
    document.querySelectorAll('.canvas-paint').forEach(root => {
        const idPregunta = root.dataset.idPregunta;
        const canvas = root.querySelector('.cp-canvas');
        // Verificamos si el usuario interactuó con el canvas (dataset.filled)
        const filled = root.dataset.filled === '1';

        if (filled && canvas) {
            // Guardamos en el estado global
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
        if (!resp.ok) {
            throw new Error(`HTTP ${resp.status}`);
        }

        const data = await resp.json();

        preguntas = data.preguntas || [];
        console.log("PREGUNTAS CRUDAS DEL BACKEND:", preguntas);

        idEncuesta = data.id_encuesta;

        // necesario para progreso.js
        window.preguntas = preguntas;

        preguntas.forEach(p => {
            p.tipo = String(p.tipo).toLowerCase();
        });

        paginas = construirPaginas(preguntas);
        paginaActual = 0;
        mostrarPagina();
    } catch (error) {
        console.error('Error al cargar encuesta:', error);
        const loader = document.getElementById('loaderEncuesta');
        if (loader) {
            loader.textContent = 'Error al cargar la encuesta. Intenta de nuevo más tarde.';
        }
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
            ctx.drawImage(
                img,
                0, 0,
                canvas.width / dpr,
                canvas.height / dpr
            );
            root.dataset.filled = '1';
        };

        img.src = base64;
    });
}

function mostrarPagina() {
    renderPagina(paginas[paginaActual], preguntas, contenedor);
    actualizarProgresoPagina(paginaActual, paginas);
    actualizarProgresoRespuestas();

    if (paginaActual === 0) {
        btnAnterior.style.visibility = "hidden";
    } else {
        btnAnterior.style.visibility = "visible";
    }

    if (window.initCanvasPaint) {
        window.initCanvasPaint();
    }
    restaurarDibujos();


    // Ajustar texto del botón según página
    if (paginaActual === paginas.length - 1) {
        btnSiguiente.textContent = "Enviar encuesta";
    } else {
        btnSiguiente.textContent = "Siguiente";
    }

    // Restaurar estado desde respuestasGlobal para esta página
    restaurarRespuestasEnDOM();

    // Notificar que ya cargó (para ocultar loader)
    document.dispatchEvent(new CustomEvent("encuesta:lista"));

    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });
}

/* ==========================================================
   NAVEGACIÓN PÁGINAS
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
   ESCUCHAR CAMBIOS EN EL CONTENEDOR (INPUT / CHANGE)
   → Actualiza respuestasGlobal al vuelo
========================================================== */

// Texto + "Otro"
contenedor.addEventListener('input', (e) => {
    const target = e.target;

    // TEXTAREA de respuesta abierta
    if (target.matches('textarea[id^="texto_"]')) {
        const idPregunta = target.id.replace('texto_', '');
        respuestasGlobal.texto[idPregunta] = target.value.trim();
        return;
    }

    // Campo "Otro"
    if (target.id && target.id.startsWith('otro_')) {
        const idPregunta = target.id.replace('otro_', '');
        const valor = target.value.trim();

        if (respuestasGlobal.opcion[idPregunta]) {
            respuestasGlobal.opcion[idPregunta].texto_otro = valor || undefined;
        }
        if (respuestasGlobal.multiple[idPregunta]) {
            respuestasGlobal.multiple[idPregunta].texto_otro = valor || undefined;
        }
    }
});

// Radios / Checkboxes
contenedor.addEventListener('change', (e) => {
    const target = e.target;

    // RADIO (opción simple)
    if (target.matches('input[type="radio"][name^="pregunta_"]')) {
        const nombreGrupo = target.name;              // pregunta_18
        const idPregunta = nombreGrupo.replace('pregunta_', '');
        const idOpcion = parseInt(target.value, 10);
        const textoOpcion = target.dataset.texto || '';

        const esOtro = textoOpcion.toLowerCase().startsWith('otro');
        const obj = {
            id_opcion: idOpcion,
            texto_opcion: textoOpcion
        };

        if (esOtro) {
            const inputOtro = document.getElementById(`otro_${idPregunta}`);
            if (inputOtro) {
                const otroTexto = inputOtro.value.trim();
                if (otroTexto !== '') {
                    obj.texto_otro = otroTexto;
                }
            }
        }

        respuestasGlobal.opcion[idPregunta] = obj;
        return;
    }

    // CHECKBOX (opción múltiple)
    if (target.matches('input[type="checkbox"][name^="pregunta_"]')) {
        const nombreGrupo = target.name;              // pregunta_19
        const idPregunta = nombreGrupo.replace('pregunta_', '');

        const seleccionados = contenedor.querySelectorAll(
            `input[type="checkbox"][name="${nombreGrupo}"]:checked`
        );

        const lista = [];
        seleccionados.forEach(chk => {
            lista.push({
                id_opcion: parseInt(chk.value, 10),
                texto_opcion: chk.dataset.texto || ''
            });
        });

        respuestasGlobal.multiple[idPregunta] = lista;
    }
});

/* ==========================================================
   RESTAURAR VALORES EN EL DOM SEGÚN respuestasGlobal
========================================================== */
function restaurarRespuestasEnDOM() {
    // TEXTO
    Object.entries(respuestasGlobal.texto).forEach(([id, valor]) => {
        const el = document.getElementById(`texto_${id}`);
        if (el) el.value = valor;
    });

    // OPCIÓN SIMPLE (RADIO)
    Object.entries(respuestasGlobal.opcion).forEach(([id, data]) => {
        if (!data || typeof data.id_opcion === 'undefined') return;

        const selector = `input[type="radio"][name="pregunta_${id}"][value="${data.id_opcion}"]`;
        const radio = document.querySelector(selector);
        if (radio) {
            radio.checked = true;
        }

        if (data.texto_otro !== undefined) {
            const otro = document.getElementById(`otro_${id}`);
            if (otro) otro.value = data.texto_otro;
        }
    });

    // OPCIÓN MÚLTIPLE (CHECKBOX)
    Object.entries(respuestasGlobal.multiple).forEach(([id, lista]) => {
        if (!Array.isArray(lista)) return;

        lista.forEach(op => {
            const selector = `input[type="checkbox"][name="pregunta_${id}"][value="${op.id_opcion}"]`;
            const chk = document.querySelector(selector);
            if (chk) {
                chk.checked = true;
            }
        });
    });

    // RANKING:
    // el módulo pregunta-ranking ya debe leer/respetar respuestasRanking,
    // aquí solo nos aseguramos de que el objeto global siga disponible.
    if (window.respuestasRanking) {
        respuestasGlobal.ranking = window.respuestasRanking;
    }
}

/* ==========================================================
   VALIDAR ENCUESTA COMPLETA (opcional)
========================================================== */
function validarEncuestaCompleta() {
    const errores = [];

    preguntas.forEach(p => {
        const id = p.id;
        const tipo = String(p.tipo).toLowerCase();
        const etiqueta = (p.texto || '').trim() || `ID ${id}`;

        if (tipo === 'texto') {
            const respuesta = respuestasGlobal.texto[id];
            if (!respuesta || respuesta.trim() === '') {
                errores.push(`La pregunta "${etiqueta}" requiere una respuesta de texto.`);
            }
        }

        if (tipo === 'opcion') {
            const data = respuestasGlobal.opcion[id];
            if (!data || !data.id_opcion) {
                errores.push(`La pregunta "${etiqueta}" requiere seleccionar una opción.`);
            } else {
                const textoOpcion = (data.texto_opcion || '').toLowerCase();
                const esOtro = textoOpcion.startsWith('otro');
                if (esOtro) {
                    const t = (data.texto_otro || '').trim();
                    if (!t) {
                        errores.push(`La pregunta "${etiqueta}" requiere escribir en "Otro".`);
                    }
                }
            }
        }

        if (tipo === 'multiple') {
            const lista = respuestasGlobal.multiple[id] || [];
            if (!Array.isArray(lista) || lista.length === 0) {
                errores.push(`La pregunta "${etiqueta}" requiere seleccionar al menos una opción.`);
            }
        }

        if (tipo === 'ranking') {
            const lista = (respuestasGlobal.ranking && respuestasGlobal.ranking[id]) || [];
            if (!lista || lista.length === 0) {
                errores.push(`La pregunta "${etiqueta}" requiere ordenar los elementos.`);
            }
        }

        if (tipo === 'dibujo') {
            const base64 = respuestasGlobal.dibujo[id];
            if (!base64 || base64.length < 50) {
                errores.push(`La pregunta "${etiqueta}" requiere realizar un dibujo.`);
            }
        }
    });

    return errores;
}

/* ==========================================================
   ENVIAR ENCUESTA
========================================================== */
function enviar() {
    console.log(' Iniciando envío de encuesta...');
    guardarDibujosPaginaActual();
    // Sincronizar ranking desde módulo global, por si no lo hemos hecho
    if (window.respuestasRanking) {
        respuestasGlobal.ranking = window.respuestasRanking;
    }



    // Validación opcional (si quieres obligar todo contestado)
    const errores = validarEncuestaCompleta();
    if (errores.length > 0) {
        alert(' Hay preguntas sin responder:\n\n- ' + errores.join('\n- '));
        return;
    }
// --- OBTENER DATOS DE SESIÓN ---
    let idEscuela = localStorage.getItem('id_escuela_seleccionada');
    let genero = localStorage.getItem('genero_seleccionado'); // <--- NUEVO

    if (!idEscuela) idEscuela = 1; 
    if (!genero) genero = 'X'; // Valor por defecto si falla algo

    // --- CONSTRUIR PAYLOAD ---
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

    console.log('Payload completo:', payload);

    const hayRespuestas =
        Object.keys(respuestasGlobal.texto).length > 0 ||
        Object.keys(respuestasGlobal.opcion).length > 0 ||
        Object.keys(respuestasGlobal.multiple).length > 0 ||
        Object.keys(respuestasGlobal.ranking).length > 0 ||
        Object.keys(respuestasGlobal.dibujo).length > 0;

    if (!hayRespuestas) {
        alert(' Debes responder al menos una pregunta antes de enviar.');
        console.warn('No hay respuestas para enviar');
        return;
    }

    console.log('Enviando al servidor...', payload);

    btnSiguiente.disabled = true;

    fetch('/back-end/routes/encuestas/enviar-respuestas.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(response => {
        if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);
        return response.json();
    })
    .then(data => {
        console.log('Respuesta JSON:', data);
        if (data.success) {
            alert('¡Encuesta enviada exitosamente! ¡Gracias por participar!');
            // Redirigir o limpiar
            localStorage.removeItem('id_escuela_seleccionada');
            localStorage.removeItem('genero_seleccionado');
            window.location.href = '/front-end/frames/inicio/inicio.php';
        } else {
            alert(' Error al guardar: ' + (data.error || 'Error desconocido'));
            btnSiguiente.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error en el envío:', error);
        alert(' Error al enviar. Revisa la consola.');
        btnSiguiente.disabled = false;
    });
}

/* ==========================================================
   INICIO
========================================================== */
cargarEncuesta();
