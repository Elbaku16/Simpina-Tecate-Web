// modal-respuestas.js - ACTUALIZADO
// Maneja el modal de respuestas de texto Y dibujos

function abrirRespuestas(idPregunta, nivel, escuela) {
    const modal = document.getElementById('modalRespuestas');
    const titulo = document.getElementById('modalTitulo');
    const contenido = document.getElementById('modalContenido');

    modal.classList.remove('hidden');
    contenido.innerHTML = '<div class="loading">Cargando respuestas...</div>';

    const url = `/SIMPINNA/back-end/routes/resultados/respuestas_texto.php?accion=obtener&id_pregunta=${idPregunta}&escuela=${escuela}`;

    fetch(url)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                contenido.innerHTML = `<p class="error">Error: ${data.error}</p>`;
                return;
            }

            const respuestas = data.respuestas || [];
            const tipoPregunta = data.tipo_pregunta || 'texto';

            // ✅ NUEVO: Actualizar título según tipo
            if (tipoPregunta === 'dibujo' || tipoPregunta === 'imagen' || tipoPregunta === 'canvas') {
                titulo.textContent = 'Respuestas de dibujo';
            } else {
                titulo.textContent = 'Respuestas de texto';
            }

            if (respuestas.length === 0) {
                contenido.innerHTML = '<p class="empty">No hay respuestas todavía.</p>';
                return;
            }

            // ✅ NUEVO: Renderizar según tipo de respuesta
            let html = '<div class="respuestas-lista">';

            respuestas.forEach(r => {
                html += `
                    <div class="respuesta-item" data-id="${r.id}">
                        <div class="respuesta-header">
                            <span class="respuesta-escuela">${r.escuela}</span>
                            <span class="respuesta-fecha">${formatearFecha(r.fecha)}</span>
                            <button class="btn-eliminar-respuesta" onclick="eliminarRespuesta(${r.id}, ${idPregunta}, '${nivel}', ${escuela})" title="Eliminar">
                                ×
                            </button>
                        </div>
                `;

                // ✅ NUEVO: Mostrar contenido según tipo
                if (r.es_dibujo) {
                    if (r.existe_archivo) {
                        html += `
                            <div class="respuesta-dibujo">
                                <img src="${r.ruta_dibujo}" alt="Dibujo del estudiante" class="dibujo-preview">
                                <div class="dibujo-info">
                                    <span class="dibujo-tamaño">${r.tamaño || 'N/A'}</span>
                                    <a href="${r.ruta_dibujo}" target="_blank" class="btn-ver-completo">Ver completo</a>
                                </div>
                            </div>
                        `;
                    } else {
                        html += `<p class="error-archivo">⚠️ Archivo no encontrado</p>`;
                    }
                } else {
                    html += `<div class="respuesta-texto">${escapeHtml(r.texto)}</div>`;
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
    if (!confirm('¿Estás seguro de eliminar esta respuesta?')) {
        return;
    }

    const formData = new FormData();
    formData.append('accion', 'eliminar');
    formData.append('id_respuesta', idRespuesta);

    fetch('/SIMPINNA/back-end/routes/resultados/respuestas_texto.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Recargar el modal
            abrirRespuestas(idPregunta, nivel, escuela);
        } else {
            alert('Error al eliminar: ' + (data.error || data.message));
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Error al eliminar la respuesta');
    });
}

function formatearFecha(fecha) {
    const d = new Date(fecha);
    const opciones = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return d.toLocaleDateString('es-MX', opciones);
}

function escapeHtml(texto) {
    const div = document.createElement('div');
    div.textContent = texto;
    return div.innerHTML;
}

// Cerrar modal con ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        cerrarRespuestas();
    }
});