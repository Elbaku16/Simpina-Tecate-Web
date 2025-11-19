// modal-respuestas.js - COMPATIBLE CON BACKEND NUEVO

function abrirRespuestas(idPregunta, nivel, escuelaId, cicloEscolar = '') {
    const modal = document.getElementById('modalRespuestas');
    const modalContenido = document.getElementById('modalContenido');
    const modalTitulo = document.getElementById('modalTitulo');

    // Mostrar modal
    modal.classList.remove('hidden');
    modalContenido.innerHTML = '<div class="loading">Cargando respuestas...</div>';

    const url =
        `/SIMPINNA/back-end/routes/resultados/respuestas_texto.php?accion=obtener` +
        `&id_pregunta=${idPregunta}` +
        `&escuela=${escuelaId}` +
        `&ciclo=${encodeURIComponent(cicloEscolar)}`;

    fetch(url)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                modalContenido.innerHTML = `<p class="error">Error: ${data.error}</p>`;
                return;
            }

            modalTitulo.textContent = "Respuestas";

            const respuestas = data.respuestas || [];
            if (respuestas.length === 0) {
                modalContenido.innerHTML = '<p class="empty">No hay respuestas todavía.</p>';
                return;
            }

            let html = '<div class="respuestas-lista">';

            respuestas.forEach(r => {
                const isDibujo = r.es_dibujo === true;
                html += `
                    <div class="respuesta-item" data-id="${r.id}">
                        <div class="respuesta-header">
                            <span class="respuesta-escuela">${escapeHtml(r.escuela || '')}</span>
                            <span class="respuesta-fecha">${formatearFecha(r.fecha)}</span>
                            <button class="btn-eliminar-respuesta"
                                onclick="eliminarRespuesta(${r.id}, ${idPregunta}, '${nivel}', ${escuelaId}, '${cicloEscolar}')"
                                title="Eliminar">×</button>
                        </div>
                `;

                if (isDibujo) {
                    if (r.ruta_dibujo) {
                        html += `
                            <div class="respuesta-dibujo">
                                <img src="${r.ruta_dibujo}" class="dibujo-preview" alt="Dibujo">
                                <a class="btn-ver-completo" href="${r.ruta_dibujo}" target="_blank">Ver completo</a>
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
            modalContenido.innerHTML = html;
        })
        .catch(err => {
            console.error('Error:', err);
            modalContenido.innerHTML = '<p class="error">Error al cargar las respuestas.</p>';
        });
}

function cerrarRespuestas() {
    document.getElementById('modalRespuestas').classList.add('hidden');
}

function eliminarRespuesta(idRespuesta, idPregunta, nivel, escuelaId, cicloEscolar = '') {
    if (!confirm('¿Deseas eliminar esta respuesta?')) return;

    fetch('/SIMPINNA/back-end/routes/resultados/respuestas_texto.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            accion: 'eliminar',
            id_respuesta: idRespuesta
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Recargar el modal después de eliminar
            abrirRespuestas(idPregunta, nivel, escuelaId, cicloEscolar);
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
