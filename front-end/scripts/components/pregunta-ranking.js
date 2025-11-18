// components/pregunta-ranking.js

import { setRespuesta } from '../utils/progreso.js';

export const respuestasRanking = {};

export function plantillaRanking(p) {
    return `
        <h3>${p.texto}</h3>
        <div class="ranking-container" id="rankingContainer_${p.id}">
            ${p.opciones.map((op, i) => `
                <div class="ranking-item" draggable="true"
                     data-opcion-id="${op.id}" data-posicion="${i+1}">
                    <span class="drag-handle">☰</span>
                    <span class="ranking-numero">${i+1}</span>
                    <span class="ranking-texto">${op.texto}</span>
                </div>
            `).join('')}
        </div>
    `;
}

export function activarDragAndDrop(idPregunta) {
    const c = document.getElementById(`rankingContainer_${idPregunta}`);
    if (!c) return;
    let dragged = null;

    c.querySelectorAll('.ranking-item').forEach(it => {
        it.addEventListener('dragstart', () => {
            dragged = it;
            it.classList.add('dragging');
        });
        it.addEventListener('dragend', () => {
            it.classList.remove('dragging');
            dragged = null;
            guardar(idPregunta, c);
        });
        it.addEventListener('dragover', e => {
            e.preventDefault();
            const after = [...c.querySelectorAll('.ranking-item:not(.dragging)')]
                .find(other => e.clientY < other.getBoundingClientRect().top + other.offsetHeight/2);
            after ? c.insertBefore(dragged, after) : c.appendChild(dragged);
        });
    });
}

function guardar(id, cont) {
    const arr = [];
    cont.querySelectorAll('.ranking-item').forEach((el, idx) => {
        el.querySelector('.ranking-numero').textContent = idx + 1;
        arr.push({
            id_opcion: parseInt(el.dataset.opcionId),
            posicion: idx + 1
        });
    });
    respuestasRanking[id] = arr;
    setRespuesta(id, arr.length > 0);
}

export function restaurarRanking() {}
