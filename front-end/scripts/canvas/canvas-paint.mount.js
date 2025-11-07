// Monta el canvas en cada bloque .canvas-paint, guarda Base64 al enviar y soporta mouse/touch
(function () {
  function mountCanvas(root) {
    const canvas = root.querySelector('.cp-canvas');
    const ctx = canvas.getContext('2d');
    const color = root.querySelector('.cp-color');
    const size  = root.querySelector('.cp-size');
    const clearBtn = root.querySelector('.cp-clear');
    const hidden   = root.querySelector('.cp-data');

    // Defaults
    ctx.lineWidth = parseInt(root.dataset.defaultSize || size.value || 5, 10);
    ctx.strokeStyle = root.dataset.defaultColor || color.value || '#2b2b2b';
    ctx.lineJoin = 'round';
    ctx.lineCap  = 'round';

    let drawing = false;

    // --- Helpers con corrección de escala (evita el desfase) ---
    function posFromClient(clientX, clientY) {
      const r = canvas.getBoundingClientRect();
      const scaleX = canvas.width  / r.width;
      const scaleY = canvas.height / r.height;
      return {
        x: (clientX - r.left) * scaleX,
        y: (clientY - r.top)  * scaleY
      };
    }

    const getMouse = (e) => posFromClient(e.clientX, e.clientY);

    const getTouch = (e) => {
      const t = e.touches?.[0] || e.changedTouches?.[0];
      if (!t) return { x: 0, y: 0 };
      return posFromClient(t.clientX, t.clientY);
    };

    // --- Mouse ---
    canvas.addEventListener('mousedown', (e) => {
      const { x, y } = getMouse(e);
      drawing = true;
      ctx.beginPath();
      ctx.moveTo(x, y);
    });

    canvas.addEventListener('mousemove', (e) => {
      if (!drawing) return;
      const { x, y } = getMouse(e);
      ctx.lineTo(x, y);
      ctx.stroke();
    });

    window.addEventListener('mouseup', () => { drawing = false; });

    // --- Touch ---
    canvas.addEventListener('touchstart', (e) => {
      e.preventDefault();
      const { x, y } = getTouch(e);
      drawing = true;
      ctx.beginPath();
      ctx.moveTo(x, y);
    }, { passive: false });

    canvas.addEventListener('touchmove', (e) => {
      e.preventDefault();
      if (!drawing) return;
      const { x, y } = getTouch(e);
      ctx.lineTo(x, y);
      ctx.stroke();
    }, { passive: false });

    window.addEventListener('touchend', () => { drawing = false; });

    // --- Controles ---
    color.addEventListener('change', (ev) => {
      ctx.strokeStyle = ev.target.value;
    });

    size.addEventListener('change', (ev) => {
      ctx.lineWidth = parseInt(ev.target.value || 5, 10);
    });

    clearBtn.addEventListener('click', () => {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
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
})();
