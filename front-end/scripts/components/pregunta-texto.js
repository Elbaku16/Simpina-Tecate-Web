import { setRespuesta } from '../utils/progreso.js';

export function plantillaTexto(p) {
    return `
        ${p.icono ? `<img src="/${p.icono}" class="img-pregunta">` : ""}
        <h3>${p.texto}</h3>
        <textarea id="texto_${p.id}" rows="5" placeholder="Escribe tu respuesta aquí..."></textarea>
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

