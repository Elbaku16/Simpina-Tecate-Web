// modal-respuestas.js - COMPATIBLE CON BACKEND NUEVO

// Función para abrir el modal de respuestas (MODIFICADA)
function abrirRespuestas(idPregunta, nivel, escuelaId, cicloEscolar) {
    const modal = document.getElementById('modalRespuestas');
    const modalContenido = document.getElementById('modalContenido');
    const modalTitulo = document.getElementById('modalTitulo');
    
    // Mostrar modal
    modal.classList.remove('hidden');
    contenido.innerHTML = '<div class="loading">Cargando respuestas...</div>';

    const url =
        `/SIMPINNA/back-end/routes/resultados/respuestas_texto.php?accion=obtener` +
        `&id_pregunta=${idPregunta}&escuela=${escuela}`;

    fetch(url)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                contenido.innerHTML = `<p class="error">Error: ${data.error}</p>`;
                return;
            }

            const respuestas = data.respuestas || [];

            // Como la vista sabe si es texto o dibujo según el tipo de la pregunta,
            // aquí no necesitamos tipo_pregunta
            titulo.textContent = "Respuestas";

            if (respuestas.length === 0) {
                contenido.innerHTML = '<p class="empty">No hay respuestas todavía.</p>';
                return;
            }

            let html = '<div class="respuestas-lista">';

            respuestas.forEach(r => {
                const isDibujo = (r.tipo === 'dibujo');
                html += `
                    <div class="respuesta-item" data-id="${r.id_respuesta}">
                        <div class="respuesta-header">
                            <span class="respuesta-escuela">${escapeHtml(r.escuela || '')}</span>
                            <span class="respuesta-fecha">${formatearFecha(r.fecha)}</span>
                            <button class="btn-eliminar-respuesta"
                                onclick="eliminarRespuesta(${r.id_respuesta}, ${idPregunta}, '${nivel}', ${escuela})"
                                title="Eliminar">×</button>
                        </div>
                `;

                if (isDibujo) {
                    if (r.dibujo_url) {
                        html += `
                            <div class="respuesta-dibujo">
                                <img src="${r.dibujo_url}" class="dibujo-preview" alt="Dibujo">
                                <a class="btn-ver-completo" href="${r.dibujo_url}" target="_blank">Ver completo</a>
                            </div>`;
                    } else {
                        html += `<p class="error-archivo">⚠️ Archivo no encontrado</p>`;
                    }
                } else {
                    html += `<div class="respuesta-texto">${escapeHtml(r.texto || '')}</div>`;
                }

                html += `</div>`;
            });

            html += '</div>';
            contenido.innerHTML = html;
        })
        .catch(err => {
            console.error('Error:', err);
            contenido.innerHTML = '<p class="error">Error al cargar las respuestas.</p>';
        });
}

function cerrarRespuestas() {
    document.getElementById('modalRespuestas').classList.add('hidden');
}

function eliminarRespuesta(idRespuesta, idPregunta, nivel, escuela) {
    if (!confirm('¿Deseas eliminar esta respuesta?')) return;

    const formData = new FormData();
    formData.append('accion', 'eliminar');
    formData.append('id_respuesta', idRespuesta);

    fetch('/SIMPINNA/back-end/routes/resultados/respuestas_texto.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            id_pregunta: idPregunta,
            nivel: nivel,
            escuela: escuelaId,
            ciclo: cicloEscolar || '' // Agregar el parámetro de ciclo escolar
        })
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                abrirRespuestas(idPregunta, nivel, escuela);
            } else {
                alert('Error al eliminar: ' + data.error);
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Error al eliminar la respuesta.');
        });
}

function formatearFecha(f) {
    const d = new Date(f);
    return d.toLocaleDateString('es-MX', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function escapeHtml(texto) {
    const div = document.createElement('div');
    div.textContent = texto || '';
    return div.innerHTML;
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        cerrarRespuestas();
    }
});
