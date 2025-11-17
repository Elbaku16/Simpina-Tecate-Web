function verDetalle(btn) {
    const data = btn.dataset;

    const estados = {
        pendiente: 'Pendiente',
        en_revision: 'En Revisión',
        resuelto: 'Resuelto'
    };

    const colores = {
        pendiente: '#ff9800',
        en_revision: '#2196f3',
        resuelto: '#4caf50'
    };

    const fecha = new Date(data.fecha)
        .toLocaleString('es-MX', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

    document.getElementById('modalBody').innerHTML = `
        <div class="detalle-item"><strong>ID:</strong> #${data.id}</div>
        <div class="detalle-item"><strong>Nombre:</strong> ${data.nombre}</div>
        <div class="detalle-item"><strong>Nivel:</strong> ${data.nivel}</div>
        <div class="detalle-item"><strong>Escuela:</strong> ${data.escuela}</div>
        <div class="detalle-item"><strong>Comentario:</strong> <p>${data.comentarios}</p></div>

        <div class="detalle-item">
            <strong>Estado Actual:</strong>
            <span class="badge" style="background:${colores[data.estado]}">
                ${estados[data.estado]}
            </span>
        </div>

        <label>Cambiar estado:</label>
        <select onchange="cambiarEstado(${data.id}, this.value)">
            <option value="">-- Seleccione --</option>
            <option value="pendiente">Pendiente</option>
            <option value="en_revision">En Revisión</option>
            <option value="resuelto">Resuelto</option>
        </select>

        <div class="detalle-item"><strong>Fecha:</strong> ${fecha}</div>
    `;

    document.getElementById('modalDetalle').style.display = "block";
}

function cerrarModal() {
    document.getElementById('modalDetalle').style.display = "none";
}

function confirmarEliminar(btn) {
    const id = btn.dataset.id;
    if (!confirm("¿Eliminar este reporte?")) return;

    const form = document.createElement("form");
    form.method = "POST";
    form.action = "/SIMPINNA/back-end/routes/comentarios/eliminar.php";

    form.innerHTML = `<input type="hidden" name="id" value="${id}">`;
    document.body.appendChild(form);
    form.submit();
}

function cambiarEstado(id, estado) {
    if (!estado) return;

    if (!confirm("¿Cambiar estado a: " + estado + "?")) return;

    const form = document.createElement("form");
    form.method = "POST";
    form.action = "/SIMPINNA/back-end/routes/comentarios/cambiar-estado.php";

    form.innerHTML = `
        <input type="hidden" name="id" value="${id}">
        <input type="hidden" name="estado" value="${estado}">
    `;
    document.body.appendChild(form);
    form.submit();
}
