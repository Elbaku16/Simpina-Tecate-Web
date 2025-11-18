// front-end/scripts/encuesta.js
// ✅ VERSIÓN CORREGIDA - Recolecta TODOS los tipos de respuestas
console.log("CARGADO encuesta.js", performance.now());

import { construirPaginas } from './utils/paginacion.js';
import { renderPagina } from './utils/renderer.js';
import { actualizarProgresoRespuestas, actualizarProgresoPagina } from './utils/progreso.js';
import { respuestasRanking } from './components/pregunta-ranking.js';

let preguntas = [];
let idEncuesta = null;

let paginas = [];
let paginaActual = 0;

const contenedor = document.getElementById('contenedorPreguntas');
const nivel = contenedor.dataset.nivel;

const btnAnterior = document.getElementById('btnAnterior');
const btnSiguiente = document.getElementById('btnSiguiente');

async function cargarEncuesta() {
    const resp = await fetch(`/SIMPINNA/back-end/routes/encuestas/obtener.php?nivel=${nivel}`);
    const data = await resp.json();

    preguntas = data.preguntas;
    idEncuesta = data.id_encuesta;

    window.preguntas = preguntas; // necesario para progreso.js

    preguntas.forEach(p => {
        p.tipo = String(p.tipo).toLowerCase();
    });

    paginas = construirPaginas(preguntas);
    mostrarPagina();
}

function mostrarPagina() {
    renderPagina(paginas[paginaActual], preguntas, contenedor);
    actualizarProgresoPagina(paginaActual, paginas);
    actualizarProgresoRespuestas();
    if (paginaActual === 0) {
        btnAnterior.style.visibility = "hidden";   // o display: "none"
    } else {
        btnAnterior.style.visibility = "visible";
    }
    if (window.initCanvasPaint) {
        window.initCanvasPaint();
    }

    // 🔥 Ajustar texto del botón según página
    if (paginaActual === paginas.length - 1) {
        btnSiguiente.textContent = "Enviar encuesta";
    } else {
        btnSiguiente.textContent = "Siguiente";
    }

    // 🔥 Notificar que ya cargó
    document.dispatchEvent(new CustomEvent("encuesta:lista"));
    window.scrollTo({
    top: 0,
    behavior: "smooth"
});
}



btnSiguiente.addEventListener('click', () => {
    if (paginaActual === paginas.length - 1) {
        enviar();
    } else {
        paginaActual++;
        mostrarPagina();
    }
});

btnAnterior.addEventListener('click', () => {
    if (paginaActual > 0) {
        paginaActual--;
        mostrarPagina();
    }
});

// ✅ FUNCIÓN ENVIAR COMPLETAMENTE REESCRITA
function enviar() {
    console.log('📤 Iniciando envío de encuesta...');

    // ========================================
    // 1. RECOLECTAR RESPUESTAS DE TEXTO
    // ========================================
    const respuestasTexto = {};
    
    document.querySelectorAll('textarea[id^="texto_"]').forEach(textarea => {
        const idPregunta = textarea.id.replace('texto_', '');
        const valor = textarea.value.trim();
        
        if (valor.length > 0) {
            respuestasTexto[idPregunta] = valor;
            console.log(`✅ Texto recogido - Pregunta ${idPregunta}:`, valor);
        }
    });

    // ========================================
    // 2. RECOLECTAR RESPUESTAS DE OPCIÓN SIMPLE (RADIO)
    // ========================================
    const respuestasOpcion = {};
    
    // Obtener todos los grupos de radio buttons
    const gruposRadio = new Set();
    document.querySelectorAll('input[type="radio"]').forEach(radio => {
        gruposRadio.add(radio.name);
    });

    gruposRadio.forEach(nombreGrupo => {
        const radioSeleccionado = document.querySelector(`input[name="${nombreGrupo}"]:checked`);
        
        if (radioSeleccionado) {
            const idPregunta = nombreGrupo.replace('pregunta_', '');
            const idOpcion = radioSeleccionado.value;
            
            respuestasOpcion[idPregunta] = {
                id_opcion: parseInt(idOpcion),
                texto_opcion: radioSeleccionado.dataset.texto || ''
            };
            
            console.log(`✅ Radio recogido - Pregunta ${idPregunta}:`, respuestasOpcion[idPregunta]);
            
            // Si es "Otro", incluir el texto adicional
            const inputOtro = document.querySelector(`#otro_${idPregunta}`);
            if (inputOtro && !inputOtro.classList.contains('oculto')) {
                respuestasOpcion[idPregunta].texto_otro = inputOtro.value.trim();
            }
        }
    });

    // ========================================
    // 3. RECOLECTAR RESPUESTAS DE OPCIÓN MÚLTIPLE (CHECKBOX)
    // ========================================
    const respuestasMultiple = {};
    
    // Obtener todos los grupos de checkboxes
    const gruposCheckbox = new Set();
    document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        gruposCheckbox.add(checkbox.name);
    });

    gruposCheckbox.forEach(nombreGrupo => {
        const checkboxesSeleccionados = document.querySelectorAll(`input[name="${nombreGrupo}"]:checked`);
        
        if (checkboxesSeleccionados.length > 0) {
            const idPregunta = nombreGrupo.replace('pregunta_', '');
            respuestasMultiple[idPregunta] = [];
            
            checkboxesSeleccionados.forEach(checkbox => {
                const opcion = {
                    id_opcion: parseInt(checkbox.value),
                    texto_opcion: checkbox.dataset.texto || ''
                };
                respuestasMultiple[idPregunta].push(opcion);
            });
            
            console.log(`✅ Checkboxes recogidos - Pregunta ${idPregunta}:`, respuestasMultiple[idPregunta]);
            
            // Si es "Otro", incluir el texto adicional
            const inputOtro = document.querySelector(`#otro_${idPregunta}`);
            if (inputOtro && !inputOtro.classList.contains('oculto')) {
                respuestasMultiple[idPregunta].texto_otro = inputOtro.value.trim();
            }
        }
    });

    // ========================================
    // 4. RECOLECTAR RESPUESTAS DE RANKING
    // ========================================
    const respuestasRank = respuestasRanking || {};
    console.log('✅ Ranking recogido:', respuestasRank);

    // ========================================
    // 5. RECOLECTAR DIBUJOS
    // ========================================
    const dibujos = {};
    
    document.querySelectorAll('.canvas-paint').forEach(root => {
        const idPregunta = root.dataset.idPregunta;
        const canvas = root.querySelector('.cp-canvas');
        const filled = root.dataset.filled === '1';
        
        if (filled && canvas) {
            const base64 = canvas.toDataURL('image/png', 0.7); // 70% calidad
            dibujos[idPregunta] = base64;
            console.log(`✅ Dibujo recogido - Pregunta ${idPregunta}`);
        }
    });

    // ========================================
    // 6. CONSTRUIR PAYLOAD
    // ========================================
    const payload = {
        id_encuesta: idEncuesta,
        respuestas: {
            texto: respuestasTexto,
            opcion: respuestasOpcion,
            multiple: respuestasMultiple,
            ranking: respuestasRank
        },
        dibujos: dibujos
    };

    console.log('📦 Payload completo:', payload);

    // ========================================
    // 7. VALIDAR QUE HAY AL MENOS UNA RESPUESTA
    // ========================================
    const hayRespuestas = 
        Object.keys(respuestasTexto).length > 0 ||
        Object.keys(respuestasOpcion).length > 0 ||
        Object.keys(respuestasMultiple).length > 0 ||
        Object.keys(respuestasRank).length > 0 ||
        Object.keys(dibujos).length > 0;

    if (!hayRespuestas) {
        alert('⚠️ Debes responder al menos una pregunta antes de enviar.');
        console.warn('⚠️ No hay respuestas para enviar');
        return;
    }

    // ========================================
    // 8. ENVIAR AL SERVIDOR
    // ========================================
    console.log('🚀 Enviando al servidor...');
    
    fetch('/SIMPINNA/back-end/routes/encuestas/enviar-respuestas.php', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(response => {
        console.log('📥 Respuesta del servidor:', response);
        
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
        return response.json();
    })
    .then(data => {
        console.log('✅ Respuesta JSON:', data);
        
        if (data.success) {
            alert('✅ ¡Encuesta enviada exitosamente!');
            
            // Opcional: Redirigir a página de agradecimiento
            // window.location.href = '/SIMPINNA/front-end/frames/gracias.php';
        } else {
            alert('❌ Error al guardar: ' + (data.error || 'Error desconocido'));
            console.error('❌ Error del servidor:', data);
        }
    })
    .catch(error => {
        console.error('❌ Error en el envío:', error);
        alert('❌ Error al enviar la encuesta. Revisa la consola para más detalles.');
    });
}

// Iniciar carga de encuesta
cargarEncuesta();