import { setRespuesta } from '../utils/progreso.js';
import { escapeHtml, safeEditorialAsset, safeNumericId } from '../utils/safe-render.js';

export function plantillaOpcion(p) {
    const id = safeNumericId(p.id);
    const icono = safeEditorialAsset(p.icono);
    return `
        ${icono ? `<img src="${icono}" class="img-pregunta" alt="">` : ""}
        <h3>${escapeHtml(p.texto)}</h3>
        <div class="opciones">
            ${p.opciones.map(op => {
                const opId = safeNumericId(op.id);
                const opIcono = safeEditorialAsset(op.icono);
                return `
                <label class="opcion-contenedor">
                    <input type="radio" name="pregunta_${id}" value="${opId}" data-texto="${escapeHtml(op.texto)}">
                    ${opIcono ? `<img src="${opIcono}" class="img-opcion" alt="">` : ""}
                    ${op.texto ? `<span>${escapeHtml(op.texto)}</span>` : ""}
                </label>
            `;}).join('')}
            <input type="text" class="input-otro oculto" id="otro_${id}" placeholder="Especifica tu respuesta...">
        </div>
    `;
}

export function initOpcionListeners(page) {
    page.querySelectorAll('input[type="radio"]').forEach(r => {
        const id = Number(r.name.split('_')[1]);
        r.addEventListener('change', () => toggle(id));
    });

    function toggle(id) {
        const grupo = page.querySelectorAll(`input[name="pregunta_${id}"]`);
        const input = page.querySelector(`#otro_${id}`);

        const sel = [...grupo].find(x => x.checked);
        const esOtro = sel && sel.dataset.texto.toLowerCase().startsWith('otro');

        input.classList.toggle('oculto', !esOtro);
        setRespuesta(id, esOtro || sel);
    }
}
