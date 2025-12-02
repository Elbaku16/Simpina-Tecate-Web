// Monta el canvas en cada bloque .canvas-paint, guarda Base64 al enviar y soporta mouse/touch

(function () {
  function mountCanvas(root) {
    const canvas     = root.querySelector('.cp-canvas');
    const ctx        = canvas.getContext('2d');
    const color      = root.querySelector('.cp-color');
    const size       = root.querySelector('.cp-size');
    const clearBtn   = root.querySelector('.cp-clear');
    const hidden     = root.querySelector('.cp-data');
    const idPregunta = root.getAttribute('data-id-pregunta');

    // === Ajuste de densidad (DPR) y tamaño real ===
    function setupDensity() {
      const dpr  = window.devicePixelRatio || 1;
      const rect = canvas.getBoundingClientRect();
      const displayW = Math.max(1, Math.floor(rect.width));
      const displayH = Math.max(1, Math.floor(rect.height));
      canvas.width  = Math.floor(displayW * dpr);
      canvas.height = Math.floor(displayH * dpr);
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }
    setupDensity();
    new ResizeObserver(() => setupDensity()).observe(canvas);

    // Defaults
    ctx.lineJoin    = 'round';
    ctx.lineCap     = 'round';
    ctx.lineWidth   = parseInt(root.dataset.defaultSize || size.value || 5, 10);
    ctx.strokeStyle = root.dataset.defaultColor || color.value || '#2b2b2b';

    let drawing = false;
    let trazoHecho = false;

    const getMouse = (e) => {
      const r = canvas.getBoundingClientRect();
      return { x: e.clientX - r.left, y: e.clientY - r.top };
    };
    const getTouch = (e) => {
      const t = e.touches?.[0] || e.changedTouches?.[0];
      if (!t) return { x: 0, y: 0 };
      const r = canvas.getBoundingClientRect();
      return { x: t.clientX - r.left, y: t.clientY - r.top };
    };

    function notificar(filled) {
      root.dataset.filled = filled ? '1' : '0';
      document.dispatchEvent(new CustomEvent('encuesta:dibujo-change', {
        detail: { id: Number(idPregunta), filled: !!filled }
      }));
    }

    // --- Mouse ---
    canvas.addEventListener('mousedown', (e) => {
      const { x, y } = getMouse(e);
      drawing = true; trazoHecho = false;
      ctx.beginPath(); ctx.moveTo(x, y);
    });
    canvas.addEventListener('mousemove', (e) => {
      if (!drawing) return;
      const { x, y } = getMouse(e);
      ctx.lineTo(x, y); ctx.stroke();
      trazoHecho = true;
    });
    window.addEventListener('mouseup', () => {
      if (!drawing) return;
      drawing = false;
      if (trazoHecho) notificar(true);
    });

    // --- Touch ---
    canvas.addEventListener('touchstart', (e) => {
      e.preventDefault();
      const { x, y } = getTouch(e);
      drawing = true; trazoHecho = false;
      ctx.beginPath(); ctx.moveTo(x, y);
    }, { passive: false });
    canvas.addEventListener('touchmove', (e) => {
      e.preventDefault();
      if (!drawing) return;
      const { x, y } = getTouch(e);
      ctx.lineTo(x, y); ctx.stroke();
      trazoHecho = true;
    }, { passive: false });
    window.addEventListener('touchend', () => {
      if (!drawing) return;
      drawing = false;
      if (trazoHecho) notificar(true);
    });

    // --- Controles ---
    color.addEventListener('change', (ev) => { ctx.strokeStyle = ev.target.value; });
    size.addEventListener('change',  (ev) => { ctx.lineWidth   = parseInt(ev.target.value || 5, 10); });

    clearBtn.addEventListener('click', () => {
      const r = canvas.getBoundingClientRect();
      ctx.clearRect(0, 0, r.width, r.height);
      notificar(false);
    });

    // --- Guardar base64 antes de enviar el formulario ---
    const form = root.closest('form');
    if (form) {
      form.addEventListener('submit', () => {
        hidden.value = canvas.toDataURL('image/png'); // data:image/png;base64,...
      });
    }
  }

  function initAll() {
    document.querySelectorAll('.canvas-paint').forEach(mountCanvas);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }
   window.initCanvasPaint = function () {
      document.querySelectorAll('.canvas-paint').forEach(root => mountCanvas(root));
  };

  // Primer montaje: por si ya hay canvas presentes (normalmente no)
  if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', window.initCanvasPaint);
  } else {
      window.initCanvasPaint();
  }

})();
