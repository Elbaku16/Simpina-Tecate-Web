// utils/renderer.js
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
        card.innerHTML = plantillaPregunta(p);
        page.appendChild(card);

        if (p.tipo === 'ranking') {
            setTimeout(() => {
                activarDragAndDrop(p.id);
                restaurarRanking(p.id);
            }, 0);
        }
    });

    contenedor.appendChild(page);
}
