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

const btnAgregar  = document.getElementById("btnAgregarPregunta");
const btnGuardar  = document.getElementById("btnGuardar");
const btnCancelar = document.getElementById("btnCancelar");

// snapshot para botón cancelar
let snapshotEstado = null;

// Clonar estado actual profundamente
function clonarEstadoActual() {
  return {
    preguntas: JSON.parse(JSON.stringify(preguntas)),
    eliminadas: Array.from(eliminadas)
  };
}

// Restaurar estado desde snapshot
function restaurarDesdeSnapshot(snap) {
  if (!snap) return;
  preguntas = JSON.parse(JSON.stringify(snap.preguntas));
  eliminadas.clear();
  snap.eliminadas.forEach(id => eliminadas.add(id));
  renderPreguntas();
}

// -------------------------------------------------------
// CARGAR DESDE BD
// -------------------------------------------------------
async function cargarPreguntas() {
  try {
    const res = await fetch(API_OBTENER);
    const data = await res.json();

    console.log("DATA RECIBIDA EDITAR:", data);

    // ✅ Normalizar preguntas para asegurar que tengan id_pregunta
    preguntas = (data.preguntas || []).map(p => ({
      ...p,
      id_pregunta: p.id_pregunta || p.id || 0,
      id_encuesta: p.id_encuesta || data.id_encuesta || 0,
      texto: p.texto_pregunta || p.texto || "",
      tipo: (p.tipo_pregunta || p.tipo || "texto").toLowerCase(),
      orden: p.orden || 0,
      opciones: Array.isArray(p.opciones) ? p.opciones : []
    }));

    console.log("✅ PREGUNTAS NORMALIZADAS:", preguntas);

    eliminadas.clear();

    // tomar snapshot inicial para el botón "Cancelar"
    snapshotEstado = clonarEstadoActual();

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

  // ordenar por 'orden' y si no, por posición
  preguntas.sort((a, b) => (a.orden || 0) - (b.orden || 0));

  preguntas.forEach((p, idx) => {
    p.orden = idx + 1;
    contenedor.appendChild(crearBloquePregunta(p, idx + 1));
  });
}

function crearBloquePregunta(p, numero) {
  const wrapper = document.createElement("div");
  wrapper.className = "editor-pregunta";
  wrapper.dataset.idPregunta = p.id_pregunta || 0;

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
  btnUp.title = "Subir";
  btnUp.onclick = () => moverPregunta(p, -1);

  const btnDown = document.createElement("button");
  btnDown.className = "btn-icon btn-updown";
  btnDown.textContent = "▼";
  btnDown.title = "Bajar";
  btnDown.onclick = () => moverPregunta(p, +1);

  const btnDel = document.createElement("button");
  btnDel.className = "btn-icon btn-danger";
  btnDel.textContent = "🗑";
  btnDel.title = "Eliminar pregunta";
  btnDel.onclick = () => eliminarPregunta(p);

  actions.appendChild(btnUp);
  actions.appendChild(btnDown);
  actions.appendChild(btnDel);

  header.appendChild(actions);
  wrapper.appendChild(header);

  // TEXTO PREGUNTA
  const txt = document.createElement("textarea");
  txt.className = "editor-texto";
  txt.value = p.texto || "";
  txt.placeholder = "Escribe el texto de la pregunta...";
  txt.oninput = () => (p.texto = txt.value);
  wrapper.appendChild(txt);

  // TIPO
  const labelTipo = document.createElement("div");
  labelTipo.textContent = "Tipo de pregunta:";
  labelTipo.style.marginTop = "0.5rem";
  wrapper.appendChild(labelTipo);

  const select = document.createElement("select");
  // para estilos y para poder seleccionarlo en JS
  select.className = "tipo-select editor-tipo";

  const tipos = [
    { val: "opcion",   label: "Opción única" },
    { val: "multiple", label: "Opción múltiple" },
    { val: "texto",    label: "Texto abierto" },
    { val: "ranking",  label: "Ranking" },
    { val: "dibujo",   label: "Dibujo / Canvas" }
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
    // Normalizar opciones
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
    btnAddOp.type = "button";
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
  input.value = op.texto || "";
  input.placeholder = "Texto de opción";
  input.oninput = () => (op.texto = input.value);

  const btnDel = document.createElement("button");
  btnDel.type = "button";
  btnDel.className = "btn-icon btn-danger";
  btnDel.textContent = "✕";
  btnDel.title = "Eliminar opción";
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
function moverPregunta(p, dir) {
  // Prioridad: usar id_pregunta (para preguntas existentes)
  let idx = -1;
  if (p.id_pregunta && p.id_pregunta !== 0) {
    idx = preguntas.findIndex(q => parseInt(q.id_pregunta) === parseInt(p.id_pregunta));
  }

  // Si es nueva (id 0) o no encontró, hacemos fallback por referencia
  if (idx === -1) {
    idx = preguntas.indexOf(p);
  }

  if (idx === -1) return;

  const nuevo = idx + dir;
  if (nuevo < 0 || nuevo >= preguntas.length) return;

  // Intercambiar
  const tmp = preguntas[idx];
  preguntas[idx] = preguntas[nuevo];
  preguntas[nuevo] = tmp;

  // actualizar orden
  preguntas.forEach((q, i) => q.orden = i + 1);

  renderPreguntas();
}

function eliminarPregunta(p) {
  console.log("🗑️ ELIMINANDO PREGUNTA:", p);

  const idPregunta = parseInt(p.id_pregunta) || 0;

  if (idPregunta > 0) {
    console.log("✅ Agregando ID a eliminadas:", idPregunta);
    eliminadas.add(idPregunta);
  } else {
    console.log("⚠️ Pregunta nueva sin ID, solo se quita en memoria");
  }

  preguntas = preguntas.filter(x => x !== p);

  console.log("📋 Estado actual:");
  console.log("   - Preguntas restantes:", preguntas.length);
  console.log("   - IDs en eliminadas:", [...eliminadas]);

  renderPreguntas();
}

// -------------------------------------------------------
// SINCRONIZAR DOM -> ARRAY
// -------------------------------------------------------
function sincronizarDesdeDom() {
  const bloques = contenedor.querySelectorAll(".editor-pregunta");

  console.log("🔄 SINCRONIZANDO DESDE DOM:", bloques.length, "bloques");

  bloques.forEach((b, index) => {
    const idDom = parseInt(b.dataset.idPregunta || "0");
    let p = null;

    if (idDom > 0) {
      p = preguntas.find(q => parseInt(q.id_pregunta) === idDom);
    }
    if (!p && index < preguntas.length) {
      p = preguntas[index];
    }
    if (!p) return;

    p.texto = b.querySelector(".editor-texto")?.value.trim() || "";
    p.tipo  = b.querySelector(".editor-tipo")?.value || "texto";
    p.orden = index + 1;

    // Sincronizar opciones
    const ops = [...b.querySelectorAll(".opcion-texto")]
      .map(inp => inp.value.trim())
      .filter(t => t !== "")
      .map(t => ({ texto: t }));

    p.opciones = ops;
  });

  console.log("✅ PREGUNTAS SINCRONIZADAS:", preguntas.length);
  console.log("✅ ELIMINADAS:", [...eliminadas]);
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

    console.log("📤 ============ ENVIANDO PAYLOAD ============");
    console.log("  📊 Nivel:", payload.nivel);
    console.log("  📝 Preguntas a guardar:", payload.preguntas.length);
    console.log("  🗑️ IDs a eliminar:", eliminadasArray);
    console.log("  📦 Payload completo:");
    console.table(payload.preguntas.map(p => ({
      id: p.id_pregunta,
      texto: (p.texto || "").substring(0, 30) + '...',
      tipo: p.tipo,
      orden: p.orden
    })));

    const res = await fetch(API_GUARDAR, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });

    const txt = await res.text();
    console.log("📥 RESPUESTA RAW:", txt);

    let data;
    try {
      data = JSON.parse(txt);
    } catch {
      console.error("❌ Respuesta NO JSON:", txt);
      alert("Error: El servidor no respondió con JSON válido.\n\n" + txt);
      return;
    }

    console.log("📥 RESPUESTA PARSEADA:", data);

    if (data.success) {
      alert("✅ Cambios guardados correctamente");
      eliminadas.clear();

      // Nuevo snapshot del estado "guardado"
      snapshotEstado = clonarEstadoActual();

      console.log("🔄 Recargando preguntas desde BD...");
      await cargarPreguntas();
    } else {
      alert("❌ Error al guardar:\n" + (data.error || "Desconocido"));
    }
  } catch (e) {
    console.error("❌ Error guardando cambios:", e);
    alert("Error al guardar los cambios: " + e.message);
  }
}

// -------------------------------------------------------
// CANCELAR CAMBIOS
// -------------------------------------------------------
function cancelarCambios() {
  if (!snapshotEstado) {
    alert("No hay cambios previos que deshacer.");
    return;
  }

  if (!confirm("¿Seguro que quieres descartar los cambios no guardados?")) {
    return;
  }

  restaurarDesdeSnapshot(snapshotEstado);
  alert("Cambios descartados. Se restauró el último estado guardado/cargado.");
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

  if (btnCancelar) {
    btnCancelar.onclick = cancelarCambios;
  }
});
