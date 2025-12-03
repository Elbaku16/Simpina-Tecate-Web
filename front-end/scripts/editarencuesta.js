/* =========================================================
   CONFIGURACIÓN Y VARIABLES
========================================================= */
const contenedor = document.getElementById("editorPreguntas");
if (!contenedor) console.warn("editorPreguntas no encontrado");

const NIVEL = contenedor?.dataset.nivel || "primaria";
const API_OBTENER = `/back-end/routes/encuestas/obtener_editar.php?nivel=${encodeURIComponent(NIVEL)}`;
const API_GUARDAR = `/back-end/routes/encuestas/guardar.php`;

// Estado
let preguntas = [];
const eliminadas = new Set();
let snapshotEstado = null; // Para cancelar cambios

// Referencias DOM
const btnAgregar  = document.getElementById("btnAgregarPregunta");
const btnGuardar  = document.getElementById("btnGuardar");
const btnCancelar = document.getElementById("btnCancelar");

/* =========================================================
   OPTIMIZACIÓN 1: COMPRESIÓN DE IMÁGENES
   (Reduce imágenes de 5MB a ~150KB antes de subir)
========================================================= */
async function comprimirImagen(archivo) {
    // Si no es imagen, regresamos tal cual
    if (!archivo.type.startsWith('image/')) return archivo;

    return new Promise((resolve) => {
        const img = new Image();
        const reader = new FileReader();

        reader.onload = (e) => {
            img.src = e.target.result;
            img.onload = () => {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                
                // Redimensionar a máximo 1024px de ancho/alto
                const MAX_SIZE = 1024;
                let width = img.width;
                let height = img.height;

                if (width > height) {
                    if (width > MAX_SIZE) {
                        height *= MAX_SIZE / width;
                        width = MAX_SIZE;
                    }
                } else {
                    if (height > MAX_SIZE) {
                        width *= MAX_SIZE / height;
                        height = MAX_SIZE;
                    }
                }

                canvas.width = width;
                canvas.height = height;
                ctx.drawImage(img, 0, 0, width, height);

                // Convertir a Blob (JPEG calidad 0.7)
                canvas.toBlob((blob) => {
                    // Crear nuevo archivo con el mismo nombre
                    const archivoComprimido = new File([blob], archivo.name, {
                        type: 'image/jpeg',
                        lastModified: Date.now()
                    });
                    resolve(archivoComprimido);
                }, 'image/jpeg', 0.7);
            };
        };
        reader.readAsDataURL(archivo);
    });
}

/* =========================================================
   GESTIÓN DE ESTADO (Snapshots rápidos)
========================================================= */
function clonarEstadoActual() {
    // structuredClone es mucho más rápido que JSON.stringify
    return {
        preguntas: structuredClone(preguntas), 
        eliminadas: new Set(eliminadas)
    };
}

function restaurarDesdeSnapshot(snap) {
    if (!snap) return;
    preguntas = structuredClone(snap.preguntas);
    eliminadas.clear();
    snap.eliminadas.forEach(id => eliminadas.add(id));
    renderPreguntas();
}

/* =========================================================
   CARGAR DATOS
========================================================= */
async function cargarPreguntas() {
    try {
        setLoading(true);
        const res = await fetch(API_OBTENER);
        const data = await res.json();

        preguntas = (data.preguntas || []).map(p => ({
            ...p,
            id_pregunta: p.id_pregunta || p.id || 0,
            texto: p.texto_pregunta || p.texto || "",
            tipo: (p.tipo_pregunta || p.tipo || "texto").toLowerCase(),
            orden: p.orden || 0,
            icono: p.icono || null,
            archivoImagen: null, // Archivo JS puro
            previewUrl: null,    // Para visualización rápida
            opciones: Array.isArray(p.opciones)
                ? p.opciones.map(op => ({
                    id: op.id_opcion || op.id || 0,
                    texto: op.texto_opcion || op.texto || "",
                    icono: op.icono || null,
                    archivoImagen: null,
                    previewUrl: null
                  }))
                : []
        }));

        eliminadas.clear();
        snapshotEstado = clonarEstadoActual();
        renderPreguntas();
    } catch (e) {
        console.error(e);
        alert("Error cargando la encuesta.");
    } finally {
        setLoading(false);
    }
}

/* =========================================================
   RENDERIZADO (DOM)
========================================================= */
function renderPreguntas() {
    contenedor.innerHTML = "";
    preguntas.sort((a, b) => (a.orden || 0) - (b.orden || 0));

    // Fragmento para evitar reflujos excesivos en el DOM
    const fragment = document.createDocumentFragment();

    preguntas.forEach((p, idx) => {
        p.orden = idx + 1;
        fragment.appendChild(crearBloquePregunta(p, idx + 1));
    });

    contenedor.appendChild(fragment);
}

function crearBloquePregunta(p, numero) {
    const wrapper = document.createElement("div");
    wrapper.className = "editor-pregunta";
    // Optimización: No usamos innerHTML para evitar perder referencias de eventos,
    // pero usamos createElement para seguridad.

    /* --- HEADER --- */
    const header = document.createElement("div");
    header.className = "editor-pregunta-header";
    
    const titulo = document.createElement("h3");
    titulo.textContent = `Pregunta #${numero}`;
    
    const actions = document.createElement("div");
    actions.className = "editor-actions";
    
    const btnUp = crearBotonIcono("▲", "btn-updown", () => moverPregunta(p, -1));
    const btnDown = crearBotonIcono("▼", "btn-updown", () => moverPregunta(p, 1));
    const btnDel = crearBotonIcono("🗑", "btn-danger", () => eliminarPregunta(p));
    
    actions.append(btnUp, btnDown, btnDel);
    header.append(titulo, actions);
    wrapper.appendChild(header);

    /* --- TEXTO --- */
    const txt = document.createElement("textarea");
    txt.className = "editor-texto";
    txt.value = p.texto;
    txt.placeholder = "¿Qué deseas preguntar?";
    // Actualizamos el modelo sin re-renderizar todo (Rendimiento)
    txt.oninput = () => (p.texto = txt.value); 
    wrapper.appendChild(txt);

    /* --- IMAGEN (Optimización: URL.createObjectURL) --- */
    const imgWrap = crearInputImagen(p, `imgP_${p.id_pregunta}_${numero}`);
    wrapper.appendChild(imgWrap);

    /* --- TIPO --- */
    const divTipo = document.createElement("div");
    divTipo.style.marginTop = "15px";
    
    const lblTipo = document.createElement("label");
    lblTipo.textContent = "Tipo de respuesta: ";
    lblTipo.style.fontWeight = "600";
    
    const select = document.createElement("select");
    select.className = "editor-tipo";
    
    const tipos = [
        { val: "opcion",   label: "Opción única" },
        { val: "multiple", label: "Opción múltiple" },
        { val: "texto",    label: "Texto abierto" },
        { val: "ranking",  label: "Ranking (Caritas)" },
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
        // Solo aquí re-renderizamos porque cambia la estructura (opciones si/no)
        renderPreguntas();
    };
    
    divTipo.append(lblTipo, select);
    wrapper.appendChild(divTipo);

    /* --- OPCIONES --- */
    if (["opcion", "multiple", "ranking"].includes(p.tipo)) {
        const opcionesContainer = document.createElement("div");
        opcionesContainer.style.marginTop = "15px";

        p.opciones.forEach(op => {
            opcionesContainer.appendChild(crearFilaOpcion(p, op));
        });

        const btnAddOp = document.createElement("button");
        btnAddOp.className = "btn-add-opcion";
        btnAddOp.textContent = "+ Agregar opción";
        btnAddOp.onclick = () => {
            p.opciones.push({ id: 0, texto: "", icono: null, archivoImagen: null });
            renderPreguntas();
        };

        opcionesContainer.appendChild(btnAddOp);
        wrapper.appendChild(opcionesContainer);
    }

    return wrapper;
}

function crearInputImagen(objData, uniqueId) {
    const wrap = document.createElement("div");
    wrap.className = "grupo-imagen";
    wrap.style.marginTop = "10px";

    const container = document.createElement("div");
    container.className = "input-file-container";
    
    // Input oculto
    const input = document.createElement("input");
    input.type = "file";
    input.accept = "image/*";
    input.className = "input-file-hidden";
    input.id = uniqueId;

    // Botón visible
    const label = document.createElement("label");
    label.className = "btn-elegir-archivo"; // Asegúrate de tener esta clase en CSS
    label.htmlFor = uniqueId;
    label.innerHTML = '<span>📷</span> Agregar imagen';
    label.style.cursor = "pointer";
    label.style.display = "inline-block";
    label.style.marginRight = "10px";

    // Preview area
    const previewDiv = document.createElement("div");
    previewDiv.className = "preview-container";
    
    const imgPreview = document.createElement("img");
    imgPreview.className = "preview-img-pregunta"; // Clase CSS para tamaño max
    imgPreview.style.maxWidth = "100px";
    imgPreview.style.display = "none";
    imgPreview.style.marginTop = "10px";
    imgPreview.style.borderRadius = "8px";

    const btnRemove = document.createElement("button");
    btnRemove.textContent = "Quitar imagen";
    btnRemove.className = "btn-ghost"; // Estilo texto simple rojo
    btnRemove.style.color = "red";
    btnRemove.style.fontSize = "0.8rem";
    btnRemove.style.display = "none";
    
    // Función mostrar preview
    const mostrarPreview = (src) => {
        imgPreview.src = src;
        imgPreview.style.display = "block";
        btnRemove.style.display = "inline-block";
        label.textContent = "Cambiar imagen";
    };

    // Estado inicial
    if (objData.previewUrl) {
        mostrarPreview(objData.previewUrl);
    } else if (objData.icono) {
        mostrarPreview("/" + objData.icono);
    }

    // Change event
    input.onchange = async () => {
        if (input.files && input.files[0]) {
            // Optimización: Comprimir aquí antes de guardar en estado
            const original = input.files[0];
            
            // Creamos URL temporal rápida para UI inmediata
            if (objData.previewUrl) URL.revokeObjectURL(objData.previewUrl);
            objData.previewUrl = URL.createObjectURL(original);
            mostrarPreview(objData.previewUrl);

            // Guardamos el archivo (se comprimirá al guardar globalmente o aquí, 
            // pero para UI rápida usamos createObjectURL)
            objData.archivoImagen = original; 
        }
    };

    btnRemove.onclick = () => {
        objData.archivoImagen = null;
        objData.icono = null;
        objData.previewUrl = null;
        input.value = "";
        imgPreview.style.display = "none";
        btnRemove.style.display = "none";
        label.innerHTML = '<span>📷</span> Agregar imagen';
    };

    container.append(input, label, btnRemove);
    wrap.append(container, imgPreview);
    return wrap;
}

function crearFilaOpcion(p, op) {
    const row = document.createElement("div");
    row.className = "opcion-item";
    
    const input = document.createElement("input");
    input.type = "text";
    input.className = "opcion-texto";
    input.value = op.texto;
    input.placeholder = "Texto de la opción";
    input.oninput = () => (op.texto = input.value);

    // Mini input imagen opción
    const imgWrap = crearInputImagen(op, `imgOp_${p.id_pregunta}_${op.id || Math.random()}`);
    // Ajustes visuales para que se vea bien en la fila
    imgWrap.style.marginTop = "0";
    imgWrap.querySelector("img").style.maxWidth = "40px"; 
    
    const btnDel = crearBotonIcono("✕", "btn-icon", () => {
        p.opciones = p.opciones.filter(o => o !== op);
        renderPreguntas();
    });
    btnDel.style.marginLeft = "10px";

    row.append(input, imgWrap, btnDel);
    return row;
}

// Helper para botones
function crearBotonIcono(texto, clase, onclick) {
    const btn = document.createElement("button");
    btn.className = `btn-icon ${clase}`;
    btn.textContent = texto;
    btn.onclick = onclick;
    return btn;
}

/* =========================================================
   LÓGICA DE NEGOCIO
========================================================= */
function moverPregunta(p, dir) {
    const idx = preguntas.indexOf(p);
    if (idx === -1) return;
    const nuevo = idx + dir;
    if (nuevo < 0 || nuevo >= preguntas.length) return;

    // Swap simple
    [preguntas[idx], preguntas[nuevo]] = [preguntas[nuevo], preguntas[idx]];
    renderPreguntas();
}

function eliminarPregunta(p) {
    if(!confirm("¿Eliminar esta pregunta?")) return;
    if (p.id_pregunta > 0) eliminadas.add(p.id_pregunta);
    preguntas = preguntas.filter(x => x !== p);
    renderPreguntas();
}

/* =========================================================
   OPTIMIZACIÓN 2: GUARDADO ASÍNCRONO Y FEEDBACK
========================================================= */
async function guardarCambios() {
    try {
        setLoading(true, "Guardando y comprimiendo imágenes...");

        const eliminadasArray = Array.from(eliminadas);
        const formData = new FormData();
        
        formData.append("nivel", NIVEL);
        formData.append("eliminadas", JSON.stringify(eliminadasArray));

        // Usamos un bucle for...of para poder usar await dentro (compresión)
        for (let i = 0; i < preguntas.length; i++) {
            const p = preguntas[i];
            
            formData.append(`preguntas[${i}][id]`, p.id_pregunta || 0);
            formData.append(`preguntas[${i}][texto]`, p.texto);
            formData.append(`preguntas[${i}][tipo]`, p.tipo);
            formData.append(`preguntas[${i}][orden]`, i + 1);

            // Comprimir y adjuntar imagen Pregunta
            if (p.archivoImagen) {
                const compressed = await comprimirImagen(p.archivoImagen);
                formData.append(`preguntas[${i}][imagen]`, compressed);
            } else if (p.icono) {
                formData.append(`preguntas[${i}][icono_actual]`, p.icono);
            }

            // Opciones
            if (p.opciones && p.opciones.length > 0) {
                for (let j = 0; j < p.opciones.length; j++) {
                    const op = p.opciones[j];
                    formData.append(`preguntas[${i}][opciones][${j}][id]`, op.id || 0);
                    formData.append(`preguntas[${i}][opciones][${j}][texto]`, op.texto);
                    
                    // Comprimir y adjuntar imagen Opción
                    if (op.archivoImagen) {
                        const compressedOp = await comprimirImagen(op.archivoImagen);
                        formData.append(`preguntas[${i}][opciones][${j}][imagen]`, compressedOp);
                    } else if (op.icono) {
                        formData.append(`preguntas[${i}][opciones][${j}][icono_actual]`, op.icono);
                    }
                }
            }
        }

        const res = await fetch(API_GUARDAR, { method: "POST", body: formData });
        const data = await res.json();

        if (data.success) {
            // Éxito
            alert("Cambios guardados correctamente");
            window.location.href = "/front-end/frames/panel/panel-admin.php";
        } else {
            alert("Error al guardar: " + (data.error || "Desconocido"));
        }

    } catch (e) {
        console.error(e);
        alert("Error de conexión al guardar.");
    } finally {
        setLoading(false);
    }
}

/* =========================================================
   UTILIDADES UI
========================================================= */
function setLoading(isLoading, texto = "Cargando...") {
    if (btnGuardar) {
        btnGuardar.disabled = isLoading;
        btnGuardar.textContent = isLoading ? texto : "Guardar cambios";
        btnGuardar.style.opacity = isLoading ? "0.7" : "1";
    }
    if (btnAgregar) btnAgregar.disabled = isLoading;
}

function cancelarCambios() {
    // Preguntar antes de salir para evitar perder trabajo accidentalmente
    if (confirm("¿Estás seguro de cancelar? Se perderán los cambios no guardados.")) {
        // Redirigir al panel
        window.location.href = "/front-end/frames/panel/panel-admin.php";
    }
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
                previewUrl: null,
                opciones: []
            });
            renderPreguntas();
            // Scroll al final
            setTimeout(() => window.scrollTo(0, document.body.scrollHeight), 100);
        };
    }

    if (btnGuardar) btnGuardar.onclick = guardarCambios;
    if (btnCancelar) btnCancelar.onclick = cancelarCambios;
});