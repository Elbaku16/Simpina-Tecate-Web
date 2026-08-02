// Monta cada lienzo una sola vez y soporta mouse, lápiz y toque con Pointer Events.
(function () {
  const rootsMontados = new WeakSet();
  const canvasesObservados = new Set();

  function configurarContexto(ctx, root, color, size) {
    ctx.lineJoin = 'round';
    ctx.lineCap = 'round';
    ctx.lineWidth = Number.parseInt(root.dataset.defaultSize || size.value || '5', 10);
    ctx.strokeStyle = root.dataset.defaultColor || color.value || '#2b2b2b';
  }

  function ajustarDensidad(canvas, ctx, root, color, size, conservarContenido = true) {
    const dpr = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();
    const displayW = Math.max(1, Math.floor(rect.width));
    const displayH = Math.max(1, Math.floor(rect.height));
    const nuevoW = Math.floor(displayW * dpr);
    const nuevoH = Math.floor(displayH * dpr);

    if (canvas.width === nuevoW && canvas.height === nuevoH) return;

    let copia = null;
    if (conservarContenido && root.dataset.filled === '1' && canvas.width > 0 && canvas.height > 0) {
      copia = document.createElement('canvas');
      copia.width = canvas.width;
      copia.height = canvas.height;
      copia.getContext('2d').drawImage(canvas, 0, 0);
    }

    canvas.width = nuevoW;
    canvas.height = nuevoH;
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    configurarContexto(ctx, root, color, size);

    if (copia) {
      ctx.drawImage(copia, 0, 0, copia.width, copia.height, 0, 0, displayW, displayH);
    }
  }

  const observador = new ResizeObserver(entries => {
    entries.forEach(entry => {
      const canvas = entry.target;
      if (!canvas.isConnected) {
        observador.unobserve(canvas);
        canvasesObservados.delete(canvas);
        return;
      }

      const datos = canvas.__simpinnaCanvas;
      if (datos) {
        ajustarDensidad(canvas, datos.ctx, datos.root, datos.color, datos.size, true);
      }
    });
  });

  function limpiarCanvasesDesconectados() {
    canvasesObservados.forEach(canvas => {
      if (!canvas.isConnected) {
        observador.unobserve(canvas);
        canvasesObservados.delete(canvas);
      }
    });
  }

  function montarCanvas(root) {
    if (rootsMontados.has(root)) return;

    const canvas = root.querySelector('.cp-canvas');
    const color = root.querySelector('.cp-color');
    const size = root.querySelector('.cp-size');
    const clearBtn = root.querySelector('.cp-clear');
    const hidden = root.querySelector('.cp-data');
    if (!canvas || !color || !size || !clearBtn) return;

    rootsMontados.add(root);
    const ctx = canvas.getContext('2d');
    const idPregunta = root.dataset.idPregunta;
    canvas.style.touchAction = 'none';
    canvas.__simpinnaCanvas = { ctx, root, color, size };

    ajustarDensidad(canvas, ctx, root, color, size, false);
    observador.observe(canvas);
    canvasesObservados.add(canvas);

    let dibujando = false;
    let trazoHecho = false;

    function obtenerPunto(evento) {
      const rect = canvas.getBoundingClientRect();
      return { x: evento.clientX - rect.left, y: evento.clientY - rect.top };
    }

    function notificar(filled) {
      root.dataset.filled = filled ? '1' : '0';
      document.dispatchEvent(new CustomEvent('encuesta:dibujo-change', {
        detail: { id: Number(idPregunta), filled: Boolean(filled) }
      }));
    }

    function terminarTrazo(evento) {
      if (!dibujando) return;
      dibujando = false;

      if (canvas.hasPointerCapture?.(evento.pointerId)) {
        canvas.releasePointerCapture(evento.pointerId);
      }
      if (trazoHecho) notificar(true);
    }

    canvas.addEventListener('pointerdown', evento => {
      if (evento.pointerType === 'mouse' && evento.button !== 0) return;
      const punto = obtenerPunto(evento);
      dibujando = true;
      trazoHecho = false;
      canvas.setPointerCapture?.(evento.pointerId);
      ctx.beginPath();
      ctx.moveTo(punto.x, punto.y);
    });

    canvas.addEventListener('pointermove', evento => {
      if (!dibujando) return;
      const punto = obtenerPunto(evento);
      ctx.lineTo(punto.x, punto.y);
      ctx.stroke();
      trazoHecho = true;
    });

    canvas.addEventListener('pointerup', terminarTrazo);
    canvas.addEventListener('pointercancel', terminarTrazo);

    color.addEventListener('change', evento => {
      ctx.strokeStyle = evento.target.value;
    });
    size.addEventListener('input', evento => {
      ctx.lineWidth = Number.parseInt(evento.target.value || '5', 10);
    });

    clearBtn.addEventListener('click', () => {
      const rect = canvas.getBoundingClientRect();
      ctx.clearRect(0, 0, rect.width, rect.height);
      notificar(false);
    });

    const form = root.closest('form');
    if (form && hidden) {
      form.addEventListener('submit', () => {
        hidden.value = canvas.toDataURL('image/png');
      });
    }
  }

  function iniciarTodos() {
    limpiarCanvasesDesconectados();
    document.querySelectorAll('.canvas-paint').forEach(montarCanvas);
  }

  window.initCanvasPaint = iniciarTodos;

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', iniciarTodos, { once: true });
  } else {
    iniciarTodos();
  }
})();
