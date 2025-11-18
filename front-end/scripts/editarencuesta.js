// -------------------------------------------------------
// CONFIG
// -------------------------------------------------------
const contenedor = document.getElementById("editorPreguntas");
if (!contenedor) {
  console.warn("editorPreguntas no encontrado");
}

const NIVEL = contenedor?.dataset.nivel || "primaria";

const API_OBTENER = `/SIMPINNA/back-end/routes/encuestas/obtener_editar.php?nivel=${encodeURIComponent(NIVEL)}`;
const API_GUARDAR  = `/SIMPINNA/back-end/routes/encuestas/guardar.php`;

let preguntas = [];           // preguntas activas
const eliminadas = new Set(); // ids de preguntas eliminadas

const btnAgregar = document.getElementById("btnAgregarPregunta");
const btnGuardar = document.getElementById("btnGuardar");


// -------------------------------------------------------
// CARGAR DESDE BD
// -------------------------------------------------------
async function cargarPreguntas() {
  try {
    const res = await fetch(API_OBTENER);
    const data = await res.json();

    console.log("DATA RECIBIDA EDITAR:", data);

    // Normalizar preguntas
    preguntas = (data.preguntas || []).map(p => ({
      ...p,
      id_pregunta: p.id_pregunta || p.id || 0,
      texto: p.texto_pregunta || p.texto || "",
      tipo: (p.tipo_pregunta || p.tipo || "texto").toLowerCase(),
      opciones: Array.isArray(p.opciones) ? p.opciones : []
    }));

    console.log("PREGUNTAS NORMALIZADAS:", preguntas);

    eliminadas.clear();

    renderPreguntas();
  } catch (e) {
    console.error("Error cargando preguntas:", e);
    alert("Error cargando la encuesta");
  }
}


// -------------------------------------------------------
// RENDERIZAR
// -------------------------------------------------------
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
  wrapper.dataset.idPregunta = p.id_pregunta || "";

  // HEADER
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

  actions.appendChild(btnUp);
  actions.appendChild(btnDown);
  actions.appendChild(btnDel);

  header.appendChild(actions);
  wrapper.appendChild(header);

  // TEXTO
  const txt = document.createElement("textarea");
  txt.className = "editor-texto";
  txt.value = p.texto;
  txt.oninput = () => (p.texto = txt.value);
  wrapper.appendChild(txt);

  // TIPO
  const labelTipo = document.createElement("div");
  labelTipo.textContent = "Tipo de pregunta:";
  labelTipo.style.marginTop = "0.5rem";
  wrapper.appendChild(labelTipo);

  const select = document.createElement("select");
  select.className = "editor-tipo";

  const tipos = [
    { val: "opcion", label: "Opción única" },
    { val: "multiple", label: "Opción múltiple" },
    { val: "texto", label: "Texto abierto" },
    { val: "ranking", label: "Ranking" },
    { val: "dibujo", label: "Dibujo / Canvas" }
  ];

  tipos.forEach(t => {
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

  // OPCIONES
  const opcionesContainer = document.createElement("div");
  opcionesContainer.style.marginTop = "0.5rem";
  wrapper.appendChild(opcionesContainer);

  const tiposConOpciones = ["opcion", "multiple", "ranking"];

  if (tiposConOpciones.includes(p.tipo)) {
    if (!Array.isArray(p.opciones) || p.opciones.length === 0) {
      p.opciones = [];
    } else {
      p.opciones = p.opciones.map(op => ({
        id: op.id_opcion || op.id || 0,
        texto: op.texto_opcion || op.texto || ""
      }));
    }

    p.opciones.forEach(op => {
      opcionesContainer.appendChild(crearFilaOpcion(p, op));
    });

    const btnAddOp = document.createElement("button");
    btnAddOp.className = "btn-add-opcion";
    btnAddOp.textContent = "+ Agregar opción";
    btnAddOp.onclick = () => {
      p.opciones.push({ id: 0, texto: "" });
      renderPreguntas();
    };

    opcionesContainer.appendChild(btnAddOp);
  } else {
    p.opciones = [];
  }

  return wrapper;
}


function crearFilaOpcion(p, op) {
  const fila = document.createElement("div");
  fila.className = "opcion-item";

  const input = document.createElement("input");
  input.type = "text";
  input.className = "opcion-texto";
  input.value = op.texto;
  input.oninput = () => (op.texto = input.value);

  const btnDel = document.createElement("button");
  btnDel.className = "btn-icon btn-danger";
  btnDel.textContent = "✕";
  btnDel.onclick = () => {
    p.opciones = p.opciones.filter(o => o !== op);
    renderPreguntas();
  };

  fila.appendChild(input);
  fila.appendChild(btnDel);
  return fila;
}


// -------------------------------------------------------
// OPERACIONES
// -------------------------------------------------------

// ✅ SOLO CAMBIÉ ESTA FUNCIÓN
function moverPregunta(p, dir) {
  const id = parseInt(p.id_pregunta) || 0;

  // Buscar índice por id, no por referencia
  const idx = preguntas.findIndex(x => (parseInt(x.id_pregunta) || 0) === id);

  if (idx === -1) {
    console.warn("No encontré la pregunta para mover:", p);
    return;
  }

  const nuevo = idx + dir;
  if (nuevo < 0 || nuevo >= preguntas.length) return;

  const tmp = preguntas[idx];
  preguntas[idx] = preguntas[nuevo];
  preguntas[nuevo] = tmp;

  // Reordenar
  preguntas.forEach((q, i) => q.orden = i + 1);

  renderPreguntas();
}


function eliminarPregunta(p) {
  const idPregunta = parseInt(p.id_pregunta) || 0;

  if (idPregunta > 0) eliminadas.add(idPregunta);

  preguntas = preguntas.filter(x => x !== p);

  renderPreguntas();
}


// -------------------------------------------------------
// SINCRONIZAR DOM -> ARRAY
// -------------------------------------------------------
function sincronizarDesdeDom() {
  const bloques = contenedor.querySelectorAll(".editor-pregunta");

  bloques.forEach((b, index) => {
    if (index < preguntas.length) {
      const p = preguntas[index];
      p.texto = b.querySelector(".editor-texto").value.trim();
      p.tipo = b.querySelector(".editor-tipo").value;
      p.orden = index + 1;

      const ops = [...b.querySelectorAll(".opcion-texto")]
        .map(inp => inp.value.trim())
        .filter(t => t !== "")
        .map(t => ({ texto: t }));

      p.opciones = ops;
    }
  });
}


// -------------------------------------------------------
// GUARDAR
// -------------------------------------------------------
async function guardarCambios() {
  try {
    sincronizarDesdeDom();

    const eliminadasArray = Array.from(eliminadas);

    const payload = {
      nivel: NIVEL,
      preguntas: preguntas,
      eliminadas: eliminadasArray
    };

    const res = await fetch(API_GUARDAR, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });

    const txt = await res.text();

    let data;

    try {
      data = JSON.parse(txt);
    } catch {
      alert("Error: El servidor no respondió con JSON válido.\n\n" + txt);
      return;
    }

    if (data.success) {
      alert("Cambios guardados correctamente");
      eliminadas.clear();
      await cargarPreguntas();
    } else {
      alert("Error al guardar:\n" + (data.error || "Desconocido"));
    }
  } catch (e) {
    alert("Error al guardar los cambios: " + e.message);
  }
}


// -------------------------------------------------------
// INICIO
// -------------------------------------------------------
document.addEventListener("DOMContentLoaded", () => {
  cargarPreguntas();

  if (btnAgregar) {
    btnAgregar.onclick = () => {
      preguntas.push({
        id_pregunta: 0,
        texto: "",
        tipo: "texto",
        orden: preguntas.length + 1,
        opciones: []
      });
      renderPreguntas();
    };
  }

  if (btnGuardar) {
    btnGuardar.onclick = guardarCambios;
  }
});
