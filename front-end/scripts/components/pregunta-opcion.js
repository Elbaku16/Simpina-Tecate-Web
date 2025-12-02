import { setRespuesta } from '../utils/progreso.js';

export function plantillaOpcion(p) {
    return `
        ${p.icono ? `<img src="/${p.icono}" class="img-pregunta">` : ""}
        <h3>${p.texto}</h3>
        <div class="opciones">
            ${p.opciones.map(op => `
                <label class="opcion-contenedor">
                    <input type="radio" name="pregunta_${p.id}" value="${op.id}" data-texto="${op.texto}">
                    ${op.icono ? `<img src="/${op.icono}" class="img-opcion">` : ""}
                    ${op.texto ? `<span>${op.texto}</span>` : ""}
                </label>
            `).join('')}
            <input type="text" class="input-otro oculto" id="otro_${p.id}" placeholder="Especifica tu respuesta...">
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
