import { setRespuesta } from '../utils/progreso.js';
import { escapeHtml, safeEditorialAsset, safeNumericId } from '../utils/safe-render.js';

export function plantillaTexto(p) {
    const id = safeNumericId(p.id);
    const icono = safeEditorialAsset(p.icono);
    return `
        ${icono ? `<img src="${icono}" class="img-pregunta" alt="">` : ""}
        <h3>${escapeHtml(p.texto)}</h3>
        <textarea id="texto_${id}" rows="5" placeholder="Escribe tu respuesta aquí..."></textarea>
    `;
}

export function initTextoListeners(page) {
    page.querySelectorAll('textarea[id^="texto_"]').forEach(t => {
        const id = Number(t.id.replace('texto_', ''));
        t.addEventListener('input', () => {
            setRespuesta(id, t.value.trim().length > 0);
        });
    });
}

