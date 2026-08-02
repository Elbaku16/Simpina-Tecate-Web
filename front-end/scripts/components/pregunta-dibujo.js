import { escapeHtml, safeNumericId } from '../utils/safe-render.js';

export function plantillaDibujo(p) {
    const id = safeNumericId(p.id);
    return `
        <h3>${escapeHtml(p.texto)}</h3>
        <div class="canvas-paint" data-id-pregunta="${id}" data-default-color="#2b2b2b">
            <section class="paleta">
                <b>Color:</b> <input class="cp-color" type="color" value="#2b2b2b">
                <b>Tamaño:</b> <input class="cp-size" type="number" min="2" max="25" value="5">
                <button class="cp-clear">Limpiar</button>
            </section>
            <div class="canvas-wrap">
                <canvas class="cp-canvas" width="800" height="500"></canvas>
            </div>
            <input type="hidden" class="cp-data" name="respuestas[${id}]" value="">
        </div>
    `;
}
