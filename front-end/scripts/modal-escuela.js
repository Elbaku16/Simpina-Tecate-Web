document.addEventListener('DOMContentLoaded', async () => {
    const modal = document.getElementById('schoolModal');
    const selectEscuela = document.getElementById('selectEscuelaModal');
    const selectGenero = document.getElementById('selectGeneroModal'); // <--- NUEVO
    const btnConfirmar = document.getElementById('btnConfirmarEscuela');
    const btnCambiar = document.getElementById('btnCambiarEscuela'); // El botón de "Cambiar escuela" arriba
    const btnClose = document.getElementById('btnCloseModal');

    // 1. Validaciones básicas
    if (!modal || !selectEscuela || !selectGenero || !btnConfirmar) return;

    const container = document.getElementById('contenedorPreguntas');
    if (!container) return; 
    const nivelActual = container.dataset.nivel;

    // 2. Verificar si YA existen datos guardados
    // Ahora revisamos si hay escuela Y género
    if (localStorage.getItem('id_escuela_seleccionada') && localStorage.getItem('genero_seleccionado')) {
        // Todo listo, ocultar modal
        if(modal) modal.style.display = 'none';
    } else {
        // Falta algo, mostrar modal
        if(modal) modal.style.display = 'flex';
        cargarEscuelas();
    }

    // 3. Función para cargar escuelas del backend
    async function cargarEscuelas() {
        try {
            const res = await fetch('/back-end/routes/contacto/listar-opciones.php');
            const data = await res.json();

            if(data.ok) {
                const nivelObj = data.niveles.find(n => n.nombre_nivel.toLowerCase() === nivelActual.toLowerCase());
                
                if (nivelObj) {
                    const lista = data.escuelasPorNivel[nivelObj.id_nivel] || [];
                    
                    selectEscuela.innerHTML = '<option value="">-- Selecciona tu escuela --</option>';
                    
                    if (lista.length === 0) {
                        selectEscuela.innerHTML += '<option value="1">Escuela General</option>';
                    } else {
                        lista.forEach(esc => {
                            const opt = document.createElement('option');
                            opt.value = esc.id;
                            opt.textContent = esc.nombre;
                            selectEscuela.appendChild(opt);
                        });
                    }
                    // Intentar restaurar selección previa si existe parcialmente
                    const prevEscuela = localStorage.getItem('id_escuela_seleccionada');
                    if(prevEscuela) selectEscuela.value = prevEscuela;
                }
            }
        } catch (err) {
            console.error(err);
            selectEscuela.innerHTML = '<option>Error de conexión</option>';
        }
    }
    if (btnClose) {
        btnClose.addEventListener('click', () => {
            // Redirigir al menú de selección de encuestas
            window.location.href = '/front-end/frames/inicio/seleccion-encuesta.php';
        });
    }
    // 4. Lógica de Validación (AMBOS campos requeridos)
    function validarFormulario() {
        const escuelaValida = selectEscuela.value !== "";
        const generoValido = selectGenero.value !== "";

        if (escuelaValida && generoValido) {
            btnConfirmar.disabled = false;
            btnConfirmar.style.opacity = "1";
        } else {
            btnConfirmar.disabled = true;
            btnConfirmar.style.opacity = "0.5";
        }
    }

    // Escuchar cambios en ambos selects
    selectEscuela.addEventListener('change', validarFormulario);
    selectGenero.addEventListener('change', validarFormulario);

    // 5. Guardar y Cerrar
    btnConfirmar.addEventListener('click', () => {
        if (!selectEscuela.value || !selectGenero.value) return;

        localStorage.setItem('id_escuela_seleccionada', selectEscuela.value);
        localStorage.setItem('genero_seleccionado', selectGenero.value); // <--- GUARDAMOS GÉNERO
        
        modal.style.display = 'none';
        
        // Si existe el botón de cambiar, lo mostramos
        if(btnCambiar) btnCambiar.style.display = 'inline-block';
    });

    // 6. Lógica del botón "Cambiar Escuela" (si existe en el HTML)
    if (btnCambiar) {
        // Si ya tenemos datos, mostramos el botón, si no, lo ocultamos
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