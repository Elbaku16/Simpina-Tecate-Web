// ------------------------------
// Cargar niveles y escuelas desde el backend
// ------------------------------
async function cargarOpciones() {
  try {
    const res = await fetch('/back-end/routes/contacto/listar-opciones.php');
    const data = await res.json();

    if (!data.ok) {
      console.error("Error al obtener datos");
      return;
    }

    llenarNiveles(data.niveles);
    window.escuelasPorNivel = data.escuelasPorNivel;
  } catch (err) {
    console.error("Error de conexión:", err);
  }
}

// ------------------------------
// Llenar select NIVELES
// ------------------------------
function llenarNiveles(niveles) {
  const nivelSelect = document.getElementById('nivel');
  nivelSelect.innerHTML = '<option value="0">Selecciona un nivel</option>';

  niveles.forEach(n => {
    const option = document.createElement('option');
    option.value = n.id_nivel;
    option.textContent = n.nombre_nivel;
    nivelSelect.appendChild(option);
  });

  nivelSelect.addEventListener('change', actualizarEscuelas);
}

// ------------------------------
// Llenar select ESCUELAS depende del nivel
// ------------------------------
function actualizarEscuelas() {
  const nivelSelect = document.getElementById('nivel');
  const escuelaSelect = document.getElementById('escuela');

  const nivelId = parseInt(nivelSelect.value);

  escuelaSelect.innerHTML = '<option value="0">Selecciona una escuela</option>';

  if (nivelId > 0 && window.escuelasPorNivel[nivelId]) {
    escuelaSelect.disabled = false;

    window.escuelasPorNivel[nivelId].forEach(esc => {
      const option = document.createElement('option');
      option.value = esc.id;
      option.textContent = esc.nombre;
      escuelaSelect.appendChild(option);
    });
  } else {
    escuelaSelect.disabled = true;
    escuelaSelect.innerHTML = '<option value="0">Primero selecciona un nivel</option>';
  }
}

// ------------------------------
// Modal (como ya lo tenías)
// ------------------------------
function openContactModal() {
  document.getElementById('contactModal').style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

function closeContactModal() {
  document.getElementById('contactModal').style.display = 'none';
  document.body.style.overflow = 'auto';
}

function closeContactModalOnOverlay(event) {
  if (event.target.id === 'contactModal') {
    closeContactModal();
  }
}

document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape') {
    closeContactModal();
  }
});

// ------------------------------
// Inicializar
// ------------------------------
window.addEventListener('DOMContentLoaded', cargarOpciones);
