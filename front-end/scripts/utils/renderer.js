// utils/renderer.js
console.log("CARGADO: renderer.js", performance.now());

import { setRespuesta } from './progreso.js';
import { plantillaTexto } from '../components/pregunta-texto.js';
import { plantillaOpcion } from '../components/pregunta-opcion.js';
import { plantillaMultiple } from '../components/pregunta-multiple.js';
import { plantillaRanking, activarDragAndDrop, restaurarRanking } from '../components/pregunta-ranking.js';
import { plantillaDibujo } from '../components/pregunta-dibujo.js';

export function plantillaPregunta(p) {
    switch (p.tipo) {
        case 'ranking': return plantillaRanking(p);
        case 'multiple': return plantillaMultiple(p);
        case 'opcion': return plantillaOpcion(p);
        case 'dibujo': return plantillaDibujo(p);
        default: return plantillaTexto(p);
    }
}

export function renderPagina(indices, preguntas, contenedor) {
    
    contenedor.innerHTML = '';
    const page = document.createElement('div');
    page.className = 'pagina-encuesta pagina-encuesta--visible';

    indices.forEach(i => {
        const p = preguntas[i];
        const card = document.createElement('div');
        card.className = p.tipo === 'ranking' ? 'pregunta pregunta--full' : 'pregunta';
        card.insertAdjacentHTML("beforeend", plantillaPregunta(p));
        page.appendChild(card);

        if (p.tipo === 'ranking') {
            setTimeout(() => {
                requestAnimationFrame(() => {
                        

                activarDragAndDrop(p.id);
                restaurarRanking(p.id);
                });
            }, 50);

        }
    });

    contenedor.appendChild(page);
    page.querySelectorAll('input[type="radio"], input[type="checkbox"]').forEach(input => {
        const label = input.closest("label");
        if (!label) return;

        const texto = label.textContent.trim().toLowerCase();
        const esOtro = texto.startsWith("otro") || texto.includes("otro:")|| texto.includes("otra");

        if (!esOtro) return;

        const idPregunta = input.name.replace("pregunta_", "");
        const inputOtro = document.querySelector(`#otro_${idPregunta}`);
        if (!inputOtro) return;

        // Estado inicial
        if (!input.checked) {
            inputOtro.classList.add("oculto");
            inputOtro.style.display = "none";
        }

        // Evento para mostrar/ocultar
        input.addEventListener("change", () => {
            if (input.checked) {
                inputOtro.classList.remove("oculto");
                inputOtro.style.display = "block";
            } else if (input.type === "radio") {
                inputOtro.classList.add("oculto");
                inputOtro.style.display = "none";
                inputOtro.value = "";
            }
        });
    });
}
