// front-end/scripts/encuesta.js

import { construirPaginas } from './utils/paginacion.js';
import { renderPagina } from './utils/renderer.js';
import { actualizarProgresoRespuestas, actualizarProgresoPagina } from './utils/progreso.js';
import { respuestasRanking } from './components/pregunta-ranking.js';

let preguntas = [];
let idEncuesta = null;

let paginas = [];
let paginaActual = 0;

const contenedor = document.getElementById('contenedorPreguntas');
const nivel = contenedor.dataset.nivel;

const btnAnterior  = document.getElementById('btnAnterior');
const btnSiguiente = document.getElementById('btnSiguiente');

async function cargarEncuesta() {
    const resp = await fetch(`/SIMPINNA/back-end/routes/encuestas/obtener.php?nivel=${nivel}`);
    const data = await resp.json();

    preguntas  = data.preguntas;
    idEncuesta = data.id_encuesta;

    preguntas.forEach(p => {
        p.tipo = String(p.tipo).toLowerCase();
    });

    paginas = construirPaginas(preguntas);
    mostrarPagina();
}

function mostrarPagina() {
    renderPagina(paginas[paginaActual], preguntas, contenedor);
    actualizarProgresoPagina(paginaActual, paginas);
    actualizarProgresoRespuestas();
}

btnSiguiente.addEventListener('click', () => {
    if (paginaActual === paginas.length - 1) {
        enviar();
    } else {
        paginaActual++;
        mostrarPagina();
    }
});

btnAnterior.addEventListener('click', () => {
    if (paginaActual > 0) {
        paginaActual--;
        mostrarPagina();
    }
});

function enviar() {
    const dibujos = {};

    document.querySelectorAll('.canvas-paint').forEach(root => {
        const id = root.dataset.idPregunta;
        const hidden = root.querySelector('.cp-data');
        const canvas = root.querySelector('.cp-canvas');

        hidden.value = canvas.toDataURL('image/png');
        dibujos[id] = hidden.value;
    });

    const payload = {
        respuestas: respuestasRanking,
        dibujos,
        id_encuesta: idEncuesta
    };

    fetch('/SIMPINNA/back-end/routes/encuestas/enviar-respuestas.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(() => alert("Encuesta enviada"))
    .catch(() => alert("Error al enviar la encuesta"));
}

cargarEncuesta();
