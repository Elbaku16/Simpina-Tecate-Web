// utils/progreso.js

export const estadoRespuestas = {}; // { [idPregunta]: boolean }

export function setRespuesta(id, flag) {
    estadoRespuestas[id] = !!flag;
    actualizarProgresoRespuestas();
}

export function actualizarProgresoRespuestas() {
    const el = document.getElementById('encuestaProgresoResp');
    const total = window.preguntas.length || 0;
    const contestadas = Object.values(estadoRespuestas).filter(Boolean).length;
    if (el) el.textContent = `${contestadas} de ${total}`;
}

export function actualizarProgresoPagina(paginaActual, paginas) {
    const el = document.getElementById('encuestaProgresoPag');
    if (!el) return;
    const total = paginas.length || 1;
    const actual = Math.min(paginaActual + 1, total);
    el.textContent = `Página ${actual} de ${total}`;
}
