// components/pregunta-multiple.js

import { setRespuesta } from '../utils/progreso.js';

export function plantillaMultiple(p) {
    return `
        <h3>${p.texto}</h3>
        <div class="opciones">
            ${p.opciones.map(op => `
                <label>
                    <input type="checkbox" name="pregunta_${p.id}" value="${op.id}" data-texto="${op.texto}">
                    ${op.texto}
                </label>
            `).join('')}
            <input type="text" class="input-otro oculto" id="otro_${p.id}" placeholder="Especifica tu respuesta...">
        </div>
    `;
}

export function initMultipleListeners(page) {
    page.querySelectorAll('input[type="checkbox"]').forEach(ch => {
        const id = Number(ch.name.split('_')[1]);
        ch.addEventListener('change', () => toggle(id));
    });

    function toggle(id) {
        const grupo = page.querySelectorAll(`input[name="pregunta_${id}"]`);
        const input = page.querySelector(`#otro_${id}`);
        const chkOtro = [...grupo].find(x => x.dataset.texto.toLowerCase().startsWith('otro'));

        const algunChecked = [...grupo].some(x => x.checked);

        if (chkOtro?.checked) input.classList.remove('oculto');
        else input.classList.add('oculto');

        setRespuesta(id, algunChecked);
    }
}
