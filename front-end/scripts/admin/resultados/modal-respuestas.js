// modal-respuestas.js - Gestion del modal de respuestas de texto y dibujo
(function() {
  'use strict';

  let datosActuales = [];
  let paginaActual = 1;
  let preguntaActualId = 0;
  const RESPUESTAS_POR_PAGINA = 20;

  window.abrirRespuestas = function(idPregunta, nivel, escuela) {
    const modal = document.getElementById('modalRespuestas');
    const modalTitulo = document.getElementById('modalTitulo');
    const modalContenido = document.getElementById('modalContenido');

    preguntaActualId = idPregunta;

    // Mostrar modal
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Mostrar loading
    modalContenido.innerHTML = '<div class="loading">Cargando respuestas...</div>';

    // Construir URL con parametros
    const params = new URLSearchParams({
      accion: 'obtener',
      id_pregunta: idPregunta,
      escuela: escuela || 0
    });

    // Peticion AJAX
    fetch(`/back-end/routes/resultados/respuestas_texto.php?${params}`)
      .then(res => res.json())
      .then(data => {
        if (!data.success) {
          throw new Error(data.error || 'Error al cargar respuestas');
        }

        const tipo = data.tipo_pregunta || 'texto';
        const respuestas = data.respuestas || [];
        
        datosActuales = respuestas;
        paginaActual = 1;

        // Actualizar titulo
        modalTitulo.textContent = tipo === 'dibujo' 
          ? 'Respuestas de dibujo' 
          : 'Respuestas de texto';

        // Renderizar respuestas
        renderizarRespuestas(respuestas, tipo);
      })
      .catch(err => {
        console.error('Error al cargar respuestas:', err);
        modalContenido.innerHTML = `
          <div class="error-message">
            <p>Error al cargar respuestas: ${err.message}</p>
            <button class="btn" onclick="cerrarRespuestas()">Cerrar</button>
          </div>
        `;
      });
  };

  window.cerrarRespuestas = function() {
    const modal = document.getElementById('modalRespuestas');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
    datosActuales = [];
    paginaActual = 1;
    preguntaActualId = 0;
  };

  function renderizarRespuestas(respuestas, tipo) {
    const modalContenido = document.getElementById('modalContenido');
    
    if (respuestas.length === 0) {
      modalContenido.innerHTML = `
        <div class="empty-state">
          <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 11H3v2h6v-2z"/>
            <path d="M21 11h-6v2h6v-2z"/>
            <circle cx="12" cy="12" r="10"/>
          </svg>
          <p>No hay respuestas para mostrar</p>
        </div>
      `;
      return;
    }

    // Calcular paginacion
    const totalRespuestas = respuestas.length;
    const totalPaginas = Math.ceil(totalRespuestas / RESPUESTAS_POR_PAGINA);
    const inicio = (paginaActual - 1) * RESPUESTAS_POR_PAGINA;
    const fin = Math.min(inicio + RESPUESTAS_POR_PAGINA, totalRespuestas);
    const respuestasPagina = respuestas.slice(inicio, fin);

    // Construir HTML
    let html = `
      <div class="respuestas-container">
        <!-- Contador -->
        <div class="respuestas-contador">
          Mostrando ${inicio + 1} - ${fin} de ${totalRespuestas} respuesta${totalRespuestas !== 1 ? 's' : ''}
        </div>

        <!-- Lista de respuestas -->
        <div class="respuestas-lista">
    `;

    respuestasPagina.forEach(resp => {
      if (tipo === 'dibujo') {
        html += generarCardDibujo(resp);
      } else {
        html += generarCardTexto(resp);
      }
    });

    html += '</div>'; // Cierra respuestas-lista

    // Paginacion
    if (totalPaginas > 1) {
      html += generarPaginacion(totalPaginas);
    }

    html += '</div>'; // Cierra respuestas-container

    modalContenido.innerHTML = html;
  }

  function generarCardTexto(resp) {
    const fecha = new Date(resp.fecha);
    const fechaFormateada = formatearFecha(fecha);
    const horaFormateada = formatearHora(fecha);

    return `
      <div class="respuesta-card">
        <div class="respuesta-header">
          <div class="respuesta-info">
            <span class="respuesta-escuela">${escapeHtml(resp.escuela)}</span>
            <span class="respuesta-fecha-hora">
              ${fechaFormateada} • ${horaFormateada}
            </span>
          </div>
          <button class="respuesta-eliminar" 
                  onclick="eliminarRespuesta(${resp.id})"
                  title="Eliminar respuesta">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="3 6 5 6 21 6"></polyline>
              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
              <line x1="10" y1="11" x2="10" y2="17"></line>
              <line x1="14" y1="11" x2="14" y2="17"></line>
            </svg>
          </button>
        </div>
        <div class="respuesta-contenido">
          <p>${escapeHtml(resp.texto)}</p>
        </div>
      </div>
    `;
  }

  function generarCardDibujo(resp) {
    const fecha = new Date(resp.fecha);
    const fechaFormateada = formatearFecha(fecha);
    const horaFormateada = formatearHora(fecha);
    
    const existeArchivo = resp.existe_archivo;
    const rutaDibujo = resp.ruta_dibujo || '';
    const tamano = resp.tamano || resp.tamaño || '';

    return `
      <div class="respuesta-card respuesta-dibujo">
        <div class="respuesta-header">
          <div class="respuesta-info">
            <span class="respuesta-escuela">${escapeHtml(resp.escuela)}</span>
            <span class="respuesta-fecha-hora">
              ${fechaFormateada} • ${horaFormateada}
            </span>
          </div>
          <button class="respuesta-eliminar" 
                  onclick="eliminarRespuesta(${resp.id})"
                  title="Eliminar respuesta">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="3 6 5 6 21 6"></polyline>
              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
              <line x1="10" y1="11" x2="10" y2="17"></line>
              <line x1="14" y1="11" x2="14" y2="17"></line>
            </svg>
          </button>
        </div>
        <div class="respuesta-contenido respuesta-imagen-wrapper">
          ${existeArchivo 
            ? `<img src="${rutaDibujo}" alt="Dibujo" class="respuesta-imagen" onclick="abrirImagenCompleta('${rutaDibujo}')">
               ${tamano ? `<span class="imagen-tamano">${tamano}</span>` : ''}`
            : '<div class="imagen-no-disponible">Imagen no disponible</div>'
          }
        </div>
      </div>
    `;
  }

  function generarPaginacion(totalPaginas) {
    let html = '<div class="respuestas-paginacion">';

    // Boton anterior
    html += `
      <button class="paginacion-btn ${paginaActual === 1 ? 'disabled' : ''}" 
              onclick="cambiarPagina(${paginaActual - 1})"
              ${paginaActual === 1 ? 'disabled' : ''}>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="15 18 9 12 15 6"></polyline>
        </svg>
        Anterior
      </button>
    `;

    // Numeros de pagina
    html += '<div class="paginacion-numeros">';
    
    // Mostrar primera pagina
    if (paginaActual > 3) {
      html += `<button class="paginacion-numero" onclick="cambiarPagina(1)">1</button>`;
      if (paginaActual > 4) {
        html += '<span class="paginacion-dots">...</span>';
      }
    }

    // Mostrar paginas cercanas
    for (let i = Math.max(1, paginaActual - 2); i <= Math.min(totalPaginas, paginaActual + 2); i++) {
      html += `
        <button class="paginacion-numero ${i === paginaActual ? 'active' : ''}" 
                onclick="cambiarPagina(${i})">
          ${i}
        </button>
      `;
    }

    // Mostrar ultima pagina
    if (paginaActual < totalPaginas - 2) {
      if (paginaActual < totalPaginas - 3) {
        html += '<span class="paginacion-dots">...</span>';
      }
      html += `<button class="paginacion-numero" onclick="cambiarPagina(${totalPaginas})">${totalPaginas}</button>`;
    }

    html += '</div>';

    // Boton siguiente
    html += `
      <button class="paginacion-btn ${paginaActual === totalPaginas ? 'disabled' : ''}" 
              onclick="cambiarPagina(${paginaActual + 1})"
              ${paginaActual === totalPaginas ? 'disabled' : ''}>
        Siguiente
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="9 18 15 12 9 6"></polyline>
        </svg>
      </button>
    `;

    html += '</div>';
    return html;
  }

  window.cambiarPagina = function(nuevaPagina) {
    const totalPaginas = Math.ceil(datosActuales.length / RESPUESTAS_POR_PAGINA);
    
    if (nuevaPagina < 1 || nuevaPagina > totalPaginas) {
      return;
    }

    paginaActual = nuevaPagina;
    
    const tipo = datosActuales[0]?.es_dibujo ? 'dibujo' : 'texto';
    renderizarRespuestas(datosActuales, tipo);

    // Scroll al inicio del contenido
    const modalContenido = document.getElementById('modalContenido');
    modalContenido.scrollTop = 0;
  };

  window.eliminarRespuesta = function(idRespuesta) {
    if (!confirm('¿Estas seguro de que deseas eliminar esta respuesta?')) {
      return;
    }

    const formData = new FormData();
    formData.append('accion', 'eliminar');
    formData.append('id_respuesta', idRespuesta);

    fetch('/back-end/routes/resultados/respuestas_texto.php', {
      method: 'POST',
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // Remover de datosActuales
          datosActuales = datosActuales.filter(r => r.id !== idRespuesta);
          
          // Ajustar pagina si es necesario
          const totalPaginas = Math.ceil(datosActuales.length / RESPUESTAS_POR_PAGINA);
          if (paginaActual > totalPaginas && totalPaginas > 0) {
            paginaActual = totalPaginas;
          }
          
          // Re-renderizar
          const tipo = datosActuales[0]?.es_dibujo ? 'dibujo' : 'texto';
          renderizarRespuestas(datosActuales, tipo);
          
          // Mostrar mensaje de exito
          mostrarMensaje('Respuesta eliminada correctamente', 'success');
        } else {
          throw new Error(data.error || 'Error al eliminar respuesta');
        }
      })
      .catch(err => {
        console.error('Error:', err);
        mostrarMensaje('Error al eliminar respuesta: ' + err.message, 'error');
      });
  };

  window.abrirImagenCompleta = function(rutaImagen) {
    const overlay = document.createElement('div');
    overlay.className = 'imagen-overlay';
    overlay.innerHTML = `
      <div class="imagen-overlay-contenido">
        <button class="imagen-overlay-close" onclick="this.parentElement.parentElement.remove()">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
        <img src="${rutaImagen}" alt="Imagen completa">
      </div>
    `;
    document.body.appendChild(overlay);
    
    // Cerrar al hacer clic en el overlay
    overlay.addEventListener('click', function(e) {
      if (e.target === overlay) {
        overlay.remove();
      }
    });
  };

  // ========================================================================
  // FUNCIONES DE EXPORTACION
  // ========================================================================

  window.exportarRespuestasTexto = function(formato) {
    if (!datosActuales || datosActuales.length === 0) {
      alert('No hay respuestas para exportar');
      return;
    }

    const tipo = datosActuales[0]?.es_dibujo ? 'dibujo' : 'texto';
    
    switch(formato) {
      case 'csv':
        exportarCSVRespuestas(datosActuales, tipo);
        break;
      case 'excel':
        exportarExcelRespuestas(datosActuales, tipo);
        break;
      case 'pdf':
        exportarPDFRespuestas(datosActuales, tipo);
        break;
      case 'print':
        imprimirRespuestas(datosActuales, tipo);
        break;
    }
  };

  function exportarCSVRespuestas(respuestas, tipo) {
    let csv = 'RESPUESTAS DE ' + (tipo === 'dibujo' ? 'DIBUJO' : 'TEXTO') + '\n';
    csv += 'Pregunta ID: ' + preguntaActualId + '\n';
    csv += 'Fecha de exportacion: ' + new Date().toLocaleDateString('es-MX') + '\n';
    csv += 'Total de respuestas: ' + respuestas.length + '\n\n';
    
    if (tipo === 'dibujo') {
      csv += 'Escuela,Fecha,Hora,Ruta Imagen,Tamano\n';
      respuestas.forEach(resp => {
        const fecha = new Date(resp.fecha);
        const fechaStr = formatearFecha(fecha);
        const horaStr = formatearHora(fecha);
        const tamano = resp.tamano || resp.tamaño || 'N/A';
        const ruta = resp.ruta_dibujo || 'N/A';
        csv += `"${resp.escuela}","${fechaStr}","${horaStr}","${ruta}","${tamano}"\n`;
      });
    } else {
      csv += 'Escuela,Fecha,Hora,Respuesta\n';
      respuestas.forEach(resp => {
        const fecha = new Date(resp.fecha);
        const fechaStr = formatearFecha(fecha);
        const horaStr = formatearHora(fecha);
        const textoLimpio = (resp.texto || '').replace(/"/g, '""');
        csv += `"${resp.escuela}","${fechaStr}","${horaStr}","${textoLimpio}"\n`;
      });
    }

    descargarArchivo(csv, `respuestas_${tipo}_${Date.now()}.csv`, 'text/csv;charset=utf-8;');
  }

  function exportarExcelRespuestas(respuestas, tipo) {
    if (typeof XLSX === 'undefined') {
      alert('Error: Libreria XLSX no cargada');
      return;
    }

    const wsData = [
      ['RESPUESTAS DE ' + (tipo === 'dibujo' ? 'DIBUJO' : 'TEXTO')],
      ['Pregunta ID: ' + preguntaActualId],
      ['Fecha de exportacion: ' + new Date().toLocaleDateString('es-MX')],
      ['Total de respuestas: ' + respuestas.length],
      []
    ];

    if (tipo === 'dibujo') {
      wsData.push(['Escuela', 'Fecha', 'Hora', 'Ruta Imagen', 'Tamano']);
      respuestas.forEach(resp => {
        const fecha = new Date(resp.fecha);
        wsData.push([
          resp.escuela,
          formatearFecha(fecha),
          formatearHora(fecha),
          resp.ruta_dibujo || 'N/A',
          resp.tamano || resp.tamaño || 'N/A'
        ]);
      });
    } else {
      wsData.push(['Escuela', 'Fecha', 'Hora', 'Respuesta']);
      respuestas.forEach(resp => {
        const fecha = new Date(resp.fecha);
        wsData.push([
          resp.escuela,
          formatearFecha(fecha),
          formatearHora(fecha),
          resp.texto || ''
        ]);
      });
    }

    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(wsData);
    ws['!cols'] = [{ wch: 25 }, { wch: 12 }, { wch: 10 }, { wch: 50 }];
    XLSX.utils.book_append_sheet(wb, ws, 'Respuestas');

    XLSX.writeFile(wb, `respuestas_${tipo}_${Date.now()}.xlsx`);
  }

  function exportarPDFRespuestas(respuestas, tipo) {
    if (typeof jsPDF === 'undefined' || !jsPDF.jsPDF) {
      alert('Error: Libreria jsPDF no cargada');
      return;
    }

    const { jsPDF } = window.jsPDF;
    const doc = new jsPDF();
    let y = 20;

    // Titulo
    doc.setFontSize(16);
    doc.setFont(undefined, 'bold');
    doc.text('RESPUESTAS DE ' + (tipo === 'dibujo' ? 'DIBUJO' : 'TEXTO'), 10, y);
    y += 10;

    doc.setFontSize(10);
    doc.setFont(undefined, 'normal');
    doc.text('Pregunta ID: ' + preguntaActualId, 10, y);
    y += 7;
    doc.text('Fecha: ' + new Date().toLocaleDateString('es-MX'), 10, y);
    y += 7;
    doc.text('Total de respuestas: ' + respuestas.length, 10, y);
    y += 12;

    respuestas.forEach((resp, idx) => {
      if (y > 260) {
        doc.addPage();
        y = 20;
      }

      const fecha = new Date(resp.fecha);
      
      doc.setFontSize(11);
      doc.setFont(undefined, 'bold');
      doc.text(`Respuesta ${idx + 1}`, 10, y);
      y += 7;

      doc.setFontSize(9);
      doc.setFont(undefined, 'normal');
      doc.text(`Escuela: ${resp.escuela}`, 15, y);
      y += 6;
      doc.text(`Fecha: ${formatearFecha(fecha)} ${formatearHora(fecha)}`, 15, y);
      y += 6;

      if (tipo === 'texto') {
        const textoLineas = doc.splitTextToSize(resp.texto || '', 180);
        doc.text(textoLineas, 15, y);
        y += (textoLineas.length * 6) + 8;
      } else {
        doc.text(`Ruta: ${resp.ruta_dibujo || 'N/A'}`, 15, y);
        y += 6;
        doc.text(`Tamano: ${resp.tamano || resp.tamaño || 'N/A'}`, 15, y);
        y += 10;
      }
    });

    doc.save(`respuestas_${tipo}_${Date.now()}.pdf`);
  }

  function imprimirRespuestas(respuestas, tipo) {
    const w = window.open('', '', 'width=900,height=700');
    let html = `
      <html>
      <head>
        <title>Imprimir Respuestas</title>
        <style>
          body { font-family: Arial, sans-serif; padding: 20px; }
          h1 { color: #7A1E2C; margin-bottom: 20px; }
          .info { background: #FFFAF3; padding: 10px; margin-bottom: 20px; border-left: 4px solid #D4B056; }
          .respuesta { border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 8px; page-break-inside: avoid; }
          .respuesta-header { font-weight: bold; color: #7A1E2C; margin-bottom: 8px; }
          .respuesta-meta { color: #666; font-size: 0.9em; margin-bottom: 8px; }
          .respuesta-texto { line-height: 1.6; }
          @media print { body { padding: 10px; } }
        </style>
      </head>
      <body>
        <h1>Respuestas de ${tipo === 'dibujo' ? 'Dibujo' : 'Texto'}</h1>
        <div class="info">
          <strong>Pregunta ID:</strong> ${preguntaActualId}<br>
          <strong>Fecha:</strong> ${new Date().toLocaleDateString('es-MX')}<br>
          <strong>Total de respuestas:</strong> ${respuestas.length}
        </div>
    `;

    respuestas.forEach((resp, idx) => {
      const fecha = new Date(resp.fecha);
      html += `
        <div class="respuesta">
          <div class="respuesta-header">Respuesta ${idx + 1}</div>
          <div class="respuesta-meta">
            <strong>Escuela:</strong> ${escapeHtml(resp.escuela)}<br>
            <strong>Fecha:</strong> ${formatearFecha(fecha)} ${formatearHora(fecha)}
          </div>
      `;

      if (tipo === 'texto') {
        html += `<div class="respuesta-texto">${escapeHtml(resp.texto || '')}</div>`;
      } else {
        html += `
          <div><strong>Ruta:</strong> ${escapeHtml(resp.ruta_dibujo || 'N/A')}</div>
          <div><strong>Tamano:</strong> ${escapeHtml(resp.tamano || resp.tamaño || 'N/A')}</div>
        `;
      }

      html += '</div>';
    });

    html += '</body></html>';
    w.document.write(html);
    w.document.close();
    w.print();
  }

  // Helpers
  function formatearFecha(fecha) {
    const dia = fecha.getDate().toString().padStart(2, '0');
    const mes = (fecha.getMonth() + 1).toString().padStart(2, '0');
    const ano = fecha.getFullYear();
    return `${dia}/${mes}/${ano}`;
  }

  function formatearHora(fecha) {
    const horas = fecha.getHours().toString().padStart(2, '0');
    const minutos = fecha.getMinutes().toString().padStart(2, '0');
    return `${horas}:${minutos}`;
  }

  function escapeHtml(unsafe) {
    return String(unsafe)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function descargarArchivo(contenido, nombreArchivo, tipo) {
    const blob = new Blob([contenido], { type: tipo });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = nombreArchivo;
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    a.remove();
  }

  function mostrarMensaje(mensaje, tipo = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${tipo}`;
    toast.textContent = mensaje;
    document.body.appendChild(toast);

    setTimeout(() => {
      toast.classList.add('show');
    }, 100);

    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  // Cerrar modal con tecla ESC
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      const modal = document.getElementById('modalRespuestas');
      if (modal && !modal.classList.contains('hidden')) {
        cerrarRespuestas();
      }
    }
  });

})();