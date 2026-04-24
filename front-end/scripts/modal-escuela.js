document.addEventListener('DOMContentLoaded', async () => {
    const modal = document.getElementById('schoolModal');
    const selectEscuela = document.getElementById('selectEscuelaModal');
    const selectGenero = document.getElementById('selectGeneroModal');
    const btnConfirmar = document.getElementById('btnConfirmarEscuela');
    const btnCambiar = document.getElementById('btnCambiarEscuela');
    const btnClose = document.getElementById('btnCloseModal');

    // 1. Validaciones básicas
    if (!modal || !selectEscuela || !selectGenero || !btnConfirmar) return;

    const container = document.getElementById('contenedorPreguntas');
    if (!container) return;
    const nivelActual = container.dataset.nivel;

    // 2. Revisar si ya existen datos guardados
    if (localStorage.getItem('id_escuela_seleccionada') && localStorage.getItem('genero_seleccionado')) {
        modal.style.display = 'none';
    } else {
        modal.style.display = 'flex';
        cargarEscuelas();
    }

    // 3. Función para cargar escuelas
    async function cargarEscuelas() {
        try {
            const res = await fetch('/simpinna/back-end/routes/contacto/listar-opciones.php');
            const data = await res.json();

            if (data.ok) {
                const nivelObj = data.niveles.find(n => n.nombre_nivel.toLowerCase() === nivelActual.toLowerCase());

                if (nivelObj) {
                    const lista = data.escuelasPorNivel[nivelObj.id_nivel] || [];

                    const ID_NO_ESTUDIO = 9999; // EL MISMO QUE INSERTASTE EN BD

                    selectEscuela.innerHTML = `
                        <option value="">-- Selecciona tu escuela --</option>
                        <option value="${ID_NO_ESTUDIO}">No estudio actualmente</option>
                    `;

                    lista.forEach(esc => {
                        const opt = document.createElement('option');
                        opt.value = esc.id;
                        opt.textContent = esc.nombre;
                        selectEscuela.appendChild(opt);
                    });

                    // Restaurar selección previa
                    const prevEscuela = localStorage.getItem('id_escuela_seleccionada');
                    if (prevEscuela) selectEscuela.value = prevEscuela;
                }
            }

        } catch (err) {
            console.error(err);
            selectEscuela.innerHTML = '<option>Error de conexión</option>';
        }
    }

    // Botón cerrar modal
    if (btnClose) {
        btnClose.addEventListener('click', () => {
            window.location.href = '/simpinna/front-end/frames/inicio/seleccion-encuesta.php';
        });
    }

    // 4. Validación
    function validarFormulario() {
        const escuelaValida = selectEscuela.value !== "";
        const generoValido = selectGenero.value !== "";

        btnConfirmar.disabled = !(escuelaValida && generoValido);
        btnConfirmar.style.opacity = escuelaValida && generoValido ? "1" : "0.5";
    }

    selectEscuela.addEventListener('change', validarFormulario);
    selectGenero.addEventListener('change', validarFormulario);

    // 5. Guardar selección
    btnConfirmar.addEventListener('click', () => {
        if (!selectEscuela.value || !selectGenero.value) return;

        localStorage.setItem('id_escuela_seleccionada', selectEscuela.value);
        localStorage.setItem('genero_seleccionado', selectGenero.value);

        modal.style.display = 'none';

        if (btnCambiar) btnCambiar.style.display = 'inline-block';
    });

    // 6. Cambiar escuela
    if (btnCambiar) {
        const hayDatos = localStorage.getItem('id_escuela_seleccionada');
        btnCambiar.style.display = hayDatos ? 'inline-block' : 'none';

        btnCambiar.addEventListener('click', () => {
            if (confirm('¿Quieres cambiar tus datos (Escuela/Género)? Se reiniciará la encuesta.')) {
                localStorage.removeItem('id_escuela_seleccionada');
                localStorage.removeItem('genero_seleccionado');
                window.location.reload();
            }
        });
    }
});
