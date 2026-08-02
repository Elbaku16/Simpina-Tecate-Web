// front-end/scripts/encuesta.js

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

function esOpcionOtro(texto) {
    const valor = String(texto || '').trim().toLowerCase();
    return valor.startsWith('otro') || valor.startsWith('otra');
}

function obtenerTextoOtro(idPregunta) {
    return document.getElementById(`otro_${idPregunta}`)?.value.trim() || '';
}

function actualizarCampoOtro(idPregunta, mostrar) {
    const input = document.getElementById(`otro_${idPregunta}`);
    if (!input) return;

    input.classList.toggle('oculto', !mostrar);
    input.classList.toggle('visible', mostrar);
    input.style.display = mostrar ? 'block' : 'none';
    if (!mostrar) input.value = '';
}

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
        const resp = await fetch(`${window.BASE_URL}/back-end/routes/encuestas/obtener.php?nivel=${nivel}`);
        if (!resp.ok) throw new Error(`HTTP ${resp.status}`);

        const data = await resp.json();
        preguntas = data.preguntas || [];
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

    if (target.matches('input.input-otro[id^="otro_"]')) {
        const id = target.id.replace('otro_', '');
        const textoOtro = target.value.trim();
        const opcionSimple = respuestasGlobal.opcion[id];

        if (opcionSimple && esOpcionOtro(opcionSimple.texto_opcion)) {
            opcionSimple.texto_otro = textoOtro;
        }

        const opcionesMultiples = respuestasGlobal.multiple[id];
        if (Array.isArray(opcionesMultiples)) {
            opcionesMultiples.forEach(opcion => {
                if (esOpcionOtro(opcion.texto_opcion)) {
                    opcion.texto_otro = textoOtro;
                }
            });
        }
    }
});

contenedor.addEventListener('change', (e) => {
    const target = e.target;
 if (target.matches('input[type="radio"]')) {
        const id = target.name.replace('pregunta_', '');
        
        // Simplemente guardamos el ID y el texto normal, ignorando inputs extra
        const textoOpcion = target.dataset.texto || '';
        const obj = {
            id_opcion: parseInt(target.value), 
            texto_opcion: textoOpcion,
            texto_otro: esOpcionOtro(textoOpcion) ? obtenerTextoOtro(id) : null
        };
        
        respuestasGlobal.opcion[id] = obj;
        actualizarCampoOtro(id, esOpcionOtro(textoOpcion));
    }
    if (target.matches('input[type="checkbox"]')) {
        const id = target.name.replace('pregunta_', '');
        const checked = contenedor.querySelectorAll(`input[name="${target.name}"]:checked`);
        respuestasGlobal.multiple[id] = Array.from(checked).map(c => {
            const textoOpcion = c.dataset.texto || '';
            return {
                id_opcion: parseInt(c.value),
                texto_opcion: textoOpcion,
                texto_otro: esOpcionOtro(textoOpcion) ? obtenerTextoOtro(id) : null
            };
        });
        actualizarCampoOtro(
            id,
            respuestasGlobal.multiple[id].some(opcion => esOpcionOtro(opcion.texto_opcion))
        );
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
        if (r) {
            r.checked = true;
            if (esOpcionOtro(v.texto_opcion)) {
                const inputOtro = document.getElementById(`otro_${id}`);
                if (inputOtro) {
                    inputOtro.value = v.texto_otro || '';
                    inputOtro.classList.remove('oculto');
                    inputOtro.classList.add('visible');
                    inputOtro.style.display = 'block';
                }
            }
        }
       
    });
    Object.entries(respuestasGlobal.multiple).forEach(([id, arr]) => {
        if (!Array.isArray(arr)) return;
        arr.forEach(op => {
            const c = document.querySelector(`input[name="pregunta_${id}"][value="${op.id_opcion}"]`);
            if (c) {
                c.checked = true;
                if (esOpcionOtro(op.texto_opcion)) {
                    const inputOtro = document.getElementById(`otro_${id}`);
                    if (inputOtro) {
                        inputOtro.value = op.texto_otro || '';
                        inputOtro.classList.remove('oculto');
                        inputOtro.classList.add('visible');
                        inputOtro.style.display = 'block';
                    }
                }
            }
        });
    });
}

/* ==========================================================
   VALIDAR
========================================================== */
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
            else if (esOpcionOtro(d.texto_opcion) && !d.texto_otro?.trim()) {
                err.push(`"${label}" requiere especificar la opción "Otro".`);
            }
        }
        if (tipo === 'multiple') {
            const seleccionadas = respuestasGlobal.multiple[id] || [];
            if (seleccionadas.length === 0) {
                err.push(`"${label}" requiere al menos una opción.`);
            } else if (seleccionadas.some(op => esOpcionOtro(op.texto_opcion) && !op.texto_otro?.trim())) {
                err.push(`"${label}" requiere especificar la opción "Otro".`);
            }
        }
        
        if (tipo === 'ranking') {
            // Validar contra window.respuestasRanking que es la fuente de verdad para ranking
            const lista = window.respuestasRanking && window.respuestasRanking[id];
            if (!lista || lista.length === 0) err.push(`"${label}" requiere ordenar.`);
        }
        
        if (tipo === 'dibujo') {
            // Solo validar si NO es la pregunta opcional
            const esOpcional = label.includes('Si tu respuesta es sí') || 
                             label.includes('Sí tu respuesta es sí') ||
                             label.includes('puedes no contestar si no quieres');
            
            if (!esOpcional) {
                const b64 = respuestasGlobal.dibujo[id];
                if (!b64 || b64.length < 50) err.push(`"${label}" requiere dibujo.`);
            }
        }
    });
    return err;
}

/* ==========================================================
   ENVIAR
========================================================== */
function enviar() {
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

    const idEscuelaGuardada = localStorage.getItem('id_escuela_seleccionada');
    const generoGuardado = localStorage.getItem('genero_seleccionado');

    if (idEscuelaGuardada === null || !generoGuardado) {
        alert('Selecciona tu escuela y sexo antes de enviar la encuesta.');
        return;
    }

    const idEscuela = Number.parseInt(idEscuelaGuardada, 10);
    const genero = generoGuardado;

    if (!Number.isInteger(idEscuela) || !['M', 'F', 'O', 'X'].includes(genero)) {
        alert('Los datos de escuela o sexo no son válidos. Vuelve a seleccionarlos.');
        return;
    }

    const payload = {
        id_encuesta: idEncuesta,
        id_escuela: idEscuela,
        genero: genero,
        respuestas: {
            texto: respuestasGlobal.texto,
            opcion: respuestasGlobal.opcion,
            multiple: respuestasGlobal.multiple,
            ranking: respuestasGlobal.ranking
        },
        dibujos: respuestasGlobal.dibujo
    };

    btnSiguiente.disabled = true;

    fetch(window.BASE_URL + '/back-end/routes/encuestas/enviar-respuestas.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(async response => {
        let data;
        try {
            data = await response.json();
        } catch (_) {
            throw new Error('El servidor devolvió una respuesta inválida.');
        }

        if (!response.ok || !data.success) {
            throw new Error(data.error || 'No se pudo guardar la encuesta.');
        }

        return data;
    })
    .then(data => {
        if (data.success) {
            alert('¡Encuesta enviada exitosamente! ¡Gracias por participar!');
            
            // Limpieza de datos
            localStorage.removeItem('id_escuela_seleccionada');
            localStorage.removeItem('genero_seleccionado');
            localStorage.removeItem('nivel_encuesta_seleccionado');
            
            window.location.href = window.BASE_URL + '/front-end/frames/inicio/inicio.php';
        }
    })
    .catch(error => {
        console.error('Error al enviar la encuesta:', error);
        alert('No se pudo enviar la encuesta: ' + error.message);
        btnSiguiente.disabled = false;
    });
}

cargarEncuesta();
