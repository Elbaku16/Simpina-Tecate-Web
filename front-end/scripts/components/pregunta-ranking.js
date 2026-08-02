import { setRespuesta } from '../utils/progreso.js';
import { escapeHtml, safeEditorialAsset, safeNumericId } from '../utils/safe-render.js';
export const respuestasRanking = {};

export function plantillaRanking(p) {
    const id = safeNumericId(p.id);
    const icono = safeEditorialAsset(p.icono);
    return `
        ${icono ? `<img src="${icono}" class="img-pregunta" alt="">` : ""}
        <h3>${escapeHtml(p.texto)}</h3>

        <div class="ranking-container" id="rankingContainer_${id}">
            ${p.opciones.map((op, i) => {
                const opId = safeNumericId(op.id);
                const opIcono = safeEditorialAsset(op.icono);
                return `
                <div class="ranking-item" data-opcion-id="${opId}" data-posicion="${i+1}">
                    
                    ${opIcono ? `<img src="${opIcono}" class="img-opcion-ranking" alt="">` : ""}
                    
                    <span class="ranking-numero">${i+1}</span>
                    
                    <span class="ranking-texto">${escapeHtml(op.texto)}</span>

                    <div class="ranking-actions">
                        <button type="button" class="btn-rank btn-up" title="Subir">▲</button>
                        <button type="button" class="btn-rank btn-down" title="Bajar">▼</button>
                    </div>

                </div>
            `;}).join('')}
        </div>
    `;
}


export function activarDragAndDrop(idPregunta) {
    const container = document.getElementById(`rankingContainer_${idPregunta}`);
    if (!container) return;

    // Lógica de Clics (Delegación de eventos)
    container.addEventListener('click', (e) => {
        const target = e.target;
        const btn = target.closest('.btn-rank');
        if (!btn) return;

        const itemActual = btn.closest('.ranking-item');
        if (!itemActual) return;

        // Lógica para SUBIR
        if (btn.classList.contains('btn-up')) {
            const hermanoAnterior = itemActual.previousElementSibling;
            if (hermanoAnterior) {
                container.insertBefore(itemActual, hermanoAnterior);
                guardar(idPregunta, container);
            }
        }

        // Lógica para BAJAR
        if (btn.classList.contains('btn-down')) {
            const hermanoSiguiente = itemActual.nextElementSibling;
            if (hermanoSiguiente) {
                container.insertBefore(hermanoSiguiente, itemActual);
                guardar(idPregunta, container);
            }
        }
    });

    // Inicialización:
    if (!window.respuestasRanking || !window.respuestasRanking[idPregunta]) {
        guardar(idPregunta, container);
    } else {
        // Si ya existen datos, solo actualizamos la UI
        actualizarVisuales(container);
    }
}

/**
 * Actualiza SÓLO la parte visual (números y estado de botones)
 */
function actualizarVisuales(container) {
    const items = container.querySelectorAll('.ranking-item');
    
    items.forEach((el, idx) => {
        // 1. Actualizar número visual
        const numSpan = el.querySelector('.ranking-numero');
        if(numSpan) numSpan.textContent = idx + 1;
        
        // 2. Actualizar botones
        const btnUp = el.querySelector('.btn-up');
        const btnDown = el.querySelector('.btn-down');
        
        if(btnUp) btnUp.disabled = (idx === 0);
        if(btnDown) btnDown.disabled = (idx === items.length - 1);
        
        // 3. Actualizar atributo de datos
        el.dataset.posicion = idx + 1;
    });
}

/**
 * Guarda el estado actual del DOM en la memoria global
 */
function guardar(id, cont) {
    // Primero refrescamos la UI
    actualizarVisuales(cont);

    const arr = [];
    const items = cont.querySelectorAll('.ranking-item');

    items.forEach((el, idx) => {
        // Asegurarnos de que el ID sea un número válido
        const opId = parseInt(el.dataset.opcionId);
        
        if (isNaN(opId)) {
            console.error(`[Ranking Error] ID inválido en elemento:`, el);
        }

        arr.push({
            id_opcion: opId,
            posicion: idx + 1
        });
    });

    // Guardar en variable local y global (window)
    respuestasRanking[id] = arr;
    window.respuestasRanking = window.respuestasRanking || {};
    window.respuestasRanking[id] = arr;

    // Actualizar progreso
    setRespuesta(id, arr.length > 0);
    
}

/* ==========================================================
   RESTAURAR: Reordena el DOM si el usuario regresa
========================================================== */
export function restaurarRanking(idPregunta) {
    const datosGuardados = (window.respuestasRanking && window.respuestasRanking[idPregunta]) 
                           || respuestasRanking[idPregunta];
    
    if (!datosGuardados || datosGuardados.length === 0) return;

    const contenedor = document.getElementById(`rankingContainer_${idPregunta}`);
    if (!contenedor) return;

    // Obtener elementos actuales del DOM
    const items = Array.from(contenedor.querySelectorAll('.ranking-item'));
    
    // Ordenar los elementos DOM basándonos en la posición guardada
    items.sort((a, b) => {
        const idA = parseInt(a.dataset.opcionId);
        const idB = parseInt(b.dataset.opcionId);
        
        const datoA = datosGuardados.find(d => d.id_opcion === idA);
        const datoB = datosGuardados.find(d => d.id_opcion === idB);

        const posA = datoA ? datoA.posicion : 999;
        const posB = datoB ? datoB.posicion : 999;
        
        return posA - posB;
    });

    // Re-inyectar en el DOM ya ordenados
    items.forEach(item => contenedor.appendChild(item));
    
    // Solo actualizamos visuales, NO guardamos (para proteger los datos)
    actualizarVisuales(contenedor);
}
