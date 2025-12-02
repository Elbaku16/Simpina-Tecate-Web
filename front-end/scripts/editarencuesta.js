/* =========================================================
   CONFIGURACIÓN Y VARIABLES
========================================================= */
const contenedor = document.getElementById("editorPreguntas");
if (!contenedor) {
  console.warn("editorPreguntas no encontrado");
}

const NIVEL = contenedor?.dataset.nivel || "primaria";

const API_OBTENER = `/back-end/routes/encuestas/obtener_editar.php?nivel=${encodeURIComponent(NIVEL)}`;
const API_GUARDAR  = `/back-end/routes/encuestas/guardar.php`;

let preguntas = [];           
const eliminadas = new Set(); 

const btnAgregar  = document.getElementById("btnAgregarPregunta");
const btnGuardar  = document.getElementById("btnGuardar");
const btnCancelar = document.getElementById("btnCancelar");

// Snapshot para cancelar cambios
let snapshotEstado = null;

function clonarEstadoActual() {
  return {
    preguntas: JSON.parse(JSON.stringify(preguntas)),
    eliminadas: Array.from(eliminadas)
  };
}

function restaurarDesdeSnapshot(snap) {
  if (!snap) return;
  preguntas = JSON.parse(JSON.stringify(snap.preguntas));
  eliminadas.clear();
  snap.eliminadas.forEach(id => eliminadas.add(id));
  renderPreguntas();
}

/* =========================================================
   CARGAR PREGUNTAS
========================================================= */
async function cargarPreguntas() {
  try {
    const res = await fetch(API_OBTENER);
    const data = await res.json();

    preguntas = (data.preguntas || []).map(p => ({
      ...p,
      id_pregunta: p.id_pregunta || p.id || 0,
      id_encuesta: p.id_encuesta || data.id_encuesta || 0,
      texto: p.texto_pregunta || p.texto || "",
      tipo: (p.tipo_pregunta || p.tipo || "texto").toLowerCase(),
      orden: p.orden || 0,
      icono: p.icono || null,     // Ruta de imagen desde BD
      archivoImagen: null,        // Archivo nuevo (si el usuario sube uno)
      opciones: Array.isArray(p.opciones)
        ? p.opciones.map(op => ({
            id: op.id_opcion || op.id || 0,
            texto: op.texto_opcion || op.texto || "",
            icono: op.icono || null, // Ruta de imagen opción desde BD
            archivoImagen: null
          }))
        : []
    }));

    eliminadas.clear();
    snapshotEstado = clonarEstadoActual();
    renderPreguntas();
  } catch (e) {
    console.error(e);
  }
}

/* =========================================================
   RENDERIZADO DE LA INTERFAZ
========================================================= */
function renderPreguntas() {
  contenedor.innerHTML = "";
  preguntas.sort((a, b) => (a.orden || 0) - (b.orden || 0));

  preguntas.forEach((p, idx) => {
    p.orden = idx + 1;
    contenedor.appendChild(crearBloquePregunta(p, idx + 1));
  });
}

function crearBloquePregunta(p, numero) {
  const wrapper = document.createElement("div");
  wrapper.className = "editor-pregunta";
  wrapper.dataset.idPregunta = p.id_pregunta;

  /* ---------------- HEADER ---------------- */
  const header = document.createElement("div");
  header.className = "editor-pregunta-header";

  const titulo = document.createElement("h3");
  titulo.textContent = `Pregunta #${numero} (ID: ${p.id_pregunta || 'nueva'})`;
  header.appendChild(titulo);

  const actions = document.createElement("div");
  actions.className = "editor-actions";

  const btnUp = document.createElement("button");
  btnUp.className = "btn-icon btn-updown";
  btnUp.textContent = "▲";
  btnUp.onclick = () => moverPregunta(p, -1);

  const btnDown = document.createElement("button");
  btnDown.className = "btn-icon btn-updown";
  btnDown.textContent = "▼";
  btnDown.onclick = () => moverPregunta(p, +1);

  const btnDel = document.createElement("button");
  btnDel.className = "btn-icon btn-danger";
  btnDel.textContent = "🗑";
  btnDel.onclick = () => eliminarPregunta(p);

  actions.append(btnUp, btnDown, btnDel);
  header.appendChild(actions);
  wrapper.appendChild(header);

  /* ---------------- TEXTO ---------------- */
  const txt = document.createElement("textarea");
  txt.className = "editor-texto";
  txt.value = p.texto;
  txt.placeholder = "Escribe aquí la pregunta...";
  txt.oninput = () => (p.texto = txt.value);
  wrapper.appendChild(txt);

  /* ---------------- IMAGEN DE PREGUNTA ---------------- */
  const imgWrap = document.createElement("div");
  imgWrap.className = "grupo-imagen";

  // Label visual
  const labelImg = document.createElement("label");
  labelImg.textContent = "Imagen de pregunta (opcional): ";
  imgWrap.appendChild(labelImg);

  const inputImg = document.createElement("input");
  inputImg.type = "file";
  inputImg.accept = "image/*";
  inputImg.onchange = () => {
    p.archivoImagen = inputImg.files[0] || null;
    if (p.archivoImagen) {
      const reader = new FileReader();
      reader.onload = () => {
        preview.src = reader.result;
        preview.style.display = "block";
      };
      reader.readAsDataURL(p.archivoImagen);
    }
  };

  const preview = document.createElement("img");
  preview.className = "preview-img-pregunta";
  preview.style.maxWidth = "180px";
  preview.style.marginTop = "10px";
  // Mostrar si hay imagen en BD (p.icono) o nueva
  preview.style.display = p.icono ? "block" : "none";
  if (p.icono) preview.src = "/" + p.icono;

  imgWrap.append(inputImg, preview);
  wrapper.appendChild(imgWrap);

  /* ---------------- TIPO ---------------- */
  const labelTipo = document.createElement("div");
  labelTipo.textContent = "Tipo de pregunta:";
  labelTipo.style.marginTop = "10px";
  wrapper.appendChild(labelTipo);

  const select = document.createElement("select");
  select.className = "tipo-select editor-tipo";

  [
    { val: "opcion",   label: "Opción única" },
    { val: "multiple", label: "Opción múltiple" },
    { val: "texto",    label: "Texto abierto" },
    { val: "ranking",  label: "Ranking" },
    { val: "dibujo",   label: "Dibujo / Canvas" }
  ].forEach(t => {
    const o = document.createElement("option");
    o.value = t.val;
    o.textContent = t.label;
    select.appendChild(o);
  });

  select.value = p.tipo;
  select.onchange = () => {
    p.tipo = select.value;
    renderPreguntas();
  };
  wrapper.appendChild(select);

  /* ---------------- OPCIONES ---------------- */
  const opcionesContainer = document.createElement("div");
  wrapper.appendChild(opcionesContainer);

  if (["opcion", "multiple", "ranking"].includes(p.tipo)) {
    p.opciones.forEach(op =>
      opcionesContainer.appendChild(crearFilaOpcion(p, op))
    );

    const btnAddOp = document.createElement("button");
    btnAddOp.className = "btn-add-opcion";
    btnAddOp.textContent = "+ Agregar opción";
    btnAddOp.style.marginTop = "10px";
    btnAddOp.onclick = () => {
      p.opciones.push({
        id: 0,
        texto: "",
        icono: null,
        archivoImagen: null
      });
      renderPreguntas();
    };

    opcionesContainer.appendChild(btnAddOp);
  }

  return wrapper;
}

/* =========================================================
   FILA DE OPCION
========================================================= */
function crearFilaOpcion(p, op) {
  const fila = document.createElement("div");
  fila.className = "opcion-item";
  fila.style.display = "flex";
  fila.style.alignItems = "center";
  fila.style.gap = "10px";
  fila.style.marginTop = "5px";

  const input = document.createElement("input");
  input.type = "text";
  input.className = "opcion-texto";
  input.value = op.texto;
  input.placeholder = "Texto opción";
  input.style.flex = "1";
  input.oninput = () => (op.texto = input.value);

  /* -------- IMAGEN OPCIÓN -------- */
  const inputImg = document.createElement("input");
  inputImg.type = "file";
  inputImg.accept = "image/*";
  inputImg.style.width = "200px";
  inputImg.onchange = () => {
    op.archivoImagen = inputImg.files[0] || null;

    if (op.archivoImagen) {
      const reader = new FileReader();
      reader.onload = () => {
        preview.src = reader.result;
        preview.style.display = "block";
      };
      reader.readAsDataURL(op.archivoImagen);
    }
  };

  const preview = document.createElement("img");
  preview.style.maxWidth = "50px";
  preview.style.maxHeight = "50px";
  preview.style.objectFit = "cover";
  preview.style.display = op.icono ? "block" : "none";
  if (op.icono) preview.src = "/" + op.icono;

  const btnDel = document.createElement("button");
  btnDel.className = "btn-icon btn-danger";
  btnDel.textContent = "✕";
  btnDel.title = "Eliminar opción";
  btnDel.onclick = () => {
    p.opciones = p.opciones.filter(o => o !== op);
    renderPreguntas();
  };

  fila.append(input, inputImg, preview, btnDel);
  return fila;
}

/* =========================================================
   MOVIMIENTOS
========================================================= */
function moverPregunta(p, dir) {
  let idx = preguntas.findIndex(q => q === p);
  if (idx === -1) return;
  const nuevo = idx + dir;
  if (nuevo < 0 || nuevo >= preguntas.length) return;

  const tmp = preguntas[idx];
  preguntas[idx] = preguntas[nuevo];
  preguntas[nuevo] = tmp;

  preguntas.forEach((q, i) => (q.orden = i + 1));
  renderPreguntas();
}

function eliminarPregunta(p) {
  if (p.id_pregunta > 0) eliminadas.add(p.id_pregunta);
  preguntas = preguntas.filter(x => x !== p);
  renderPreguntas();
}

/* =========================================================
   GUARDAR (CORREGIDO PARA PERSISTIR IMÁGENES)
========================================================= */
async function guardarCambios() {
    try {
        const eliminadasArray = Array.from(eliminadas);

        const formData = new FormData();
        formData.append("nivel", NIVEL);
        formData.append("eliminadas", JSON.stringify(eliminadasArray));

        preguntas.forEach((p, i) => {
            formData.append(`preguntas[${i}][id]`, p.id_pregunta || 0);
            formData.append(`preguntas[${i}][texto]`, p.texto);
            formData.append(`preguntas[${i}][tipo]`, p.tipo);
            formData.append(`preguntas[${i}][orden]`, p.orden);

            /* ----------------------------------------------------
               CORRECCIÓN: Enviar imagen NUEVA o ACTUAL
            ---------------------------------------------------- */
            // 1. Si hay una nueva imagen seleccionada, la enviamos
            if (p.archivoImagen) {
                formData.append(`preguntas[${i}][imagen]`, p.archivoImagen);
            }
            // 2. IMPORTANTE: Si NO hay nueva, pero existe una vieja en BD,
            // enviamos la ruta para que el backend sepa que debe conservarla.
            if (p.icono) {
                formData.append(`preguntas[${i}][icono_actual]`, p.icono);
            }

            // Procesar Opciones
            p.opciones.forEach((op, j) => {
                formData.append(`preguntas[${i}][opciones][${j}][id]`, op.id || 0);
                formData.append(`preguntas[${i}][opciones][${j}][texto]`, op.texto || "");

                // Misma lógica para opciones
                if (op.archivoImagen) {
                    formData.append(`preguntas[${i}][opciones][${j}][imagen]`, op.archivoImagen);
                }
                if (op.icono) {
                    formData.append(`preguntas[${i}][opciones][${j}][icono_actual]`, op.icono);
                }
            });
        });

        const res = await fetch(API_GUARDAR, {
            method: "POST",
            body: formData
        });

        const data = await res.json();

        if (data.success) {
            alert("✅ Cambios guardados correctamente.");
            eliminadas.clear();
            snapshotEstado = clonarEstadoActual();
            // Recargamos datos para actualizar las rutas y vistas previas
            await cargarPreguntas(); 
        } else {
            alert("❌ Error al guardar:\n" + (data.error || "Desconocido"));
        }

    } catch (e) {
        console.error("❌ Error guardando cambios:", e);
        alert("Error de conexión o servidor: " + e.message);
    }
}


/* =========================================================
   CANCELAR
========================================================= */
function cancelarCambios() {
  if (!snapshotEstado) return;
  if (!confirm("¿Deseas descartar los cambios no guardados?")) return;
  restaurarDesdeSnapshot(snapshotEstado);
}

/* =========================================================
   INICIALIZACIÓN
========================================================= */
document.addEventListener("DOMContentLoaded", () => {
  cargarPreguntas();

  if (btnAgregar) {
    btnAgregar.onclick = () => {
      preguntas.push({
        id_pregunta: 0,
        texto: "",
        tipo: "texto",
        orden: preguntas.length + 1,
        icono: null,
        archivoImagen: null,
        opciones: []
      });
      renderPreguntas();
    };
  }

  if (btnGuardar) btnGuardar.onclick = guardarCambios;
  if (btnCancelar) btnCancelar.onclick = cancelarCambios;
});