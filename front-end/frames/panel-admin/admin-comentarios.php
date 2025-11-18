<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Comentarios - SIMPINNA</title>
    <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
    <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/layout.css">
    <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/admin/comentarios.css">
</head>
<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/header-admin.php'; ?>
    <div class="comentarios-container">
        <!-- HEADER SECTION -->
        <div class="header-section">
            <div class="header-content">
                <div class="header-left">
                    <a href="/SIMPINNA/front-end/frames/panel/panel-admin.php" class="btn-back">
                        ← Volver
                    </a>
                    <div class="header-title-section">
                        <div class="title-with-badge">
                            <h1 class="header-title">Gestión de Reportes y Comentarios</h1>
                            <div class="total-badge">
                                Total: <span><?= count($comentarios) ?></span>
                            </div>
                        </div>
                        <p class="header-subtitle">Consulta, supervisa y analiza los resultados de los diferentes niveles educativos</p>
                    </div>
                </div>
                <div class="header-actions">
                    <button onclick="abrirHistorial()" class="btn-historial">
                        Ver historial de cambios
                    </button>
                </div>
            </div>
        </div>

        <!-- FILTROS SECTION -->
        <div class="filtros-section">
            <div class="filtros-header">
                <h2 class="filtros-title">Filtros de Búsqueda</h2>
            </div>
            <form method="GET" action="" class="filtros-form">
                <div class="filtros-row">
                    <div class="filtro-group">
                        <label for="busqueda">BUSCAR</label>
                        <input 
                            type="text" 
                            id="busqueda" 
                            name="busqueda" 
                            placeholder="Nombre, comentario o escuela..."
                            value="<?= htmlspecialchars($busqueda) ?>"
                        >
                    </div>

                    <div class="filtro-group">
                        <label for="estado">ESTADO</label>
                        <select id="estado" name="estado">
                            <option value="">Todos</option>
                            <option value="pendiente" <?= $filtroEstado === 'pendiente' ? 'selected' : '' ?>>
                                Pendiente
                            </option>
                            <option value="en_revision" <?= $filtroEstado === 'en_revision' ? 'selected' : '' ?>>
                                En Revisión
                            </option>
                            <option value="resuelto" <?= $filtroEstado === 'resuelto' ? 'selected' : '' ?>>
                                Resuelto
                            </option>
                        </select>
                    </div>

                    <div class="filtro-group">
                        <label for="nivel">NIVEL EDUCATIVO</label>
                        <select id="nivel" name="nivel">
                            <option value="0">Todos</option>
                            <?php foreach ($niveles as $nivel): ?>
                                <option value="<?= $nivel['id_nivel'] ?>" 
                                        <?= $filtroNivel == $nivel['id_nivel'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($nivel['nombre_nivel']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="filtros-actions">
                    <button type="submit" class="btn-filtrar">
                        Filtrar
                    </button>
                    <a href="/SIMPINNA/back-end/routes/comentarios/index.php" class="btn-limpiar">
                        Limpiar Filtros
                    </a>
                </div>
            </form>
        </div>

        <!-- TABLA DE COMENTARIOS -->
        <div class="tabla-section">
            <div class="tabla-wrapper">
                <?php if (empty($comentarios)): ?>
                    <div class="mensaje-vacio">
                        <h3>No hay comentarios</h3>
                        <p>No se encontraron comentarios con los filtros seleccionados</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>NOMBRE</th>
                                <th>NIVEL</th>
                                <th>ESCUELA</th>
                                <th>COMENTARIO</th>
                                <th>ESTADO</th>
                                <th>FECHA</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($comentarios as $c): ?>
                                <tr>
                                    <td><strong>#<?= $c['id_contacto'] ?></strong></td>
                                    <td><?= htmlspecialchars($c['nombre']) ?></td>
                                    <td><?= htmlspecialchars($c['nombre_nivel']) ?></td>
                                    <td><?= htmlspecialchars($c['nombre_escuela']) ?></td>
                                    <td>
                                        <div class="comentario-preview">
                                            <?= htmlspecialchars($c['comentarios']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $estadoClase = '';
                                        $estadoTexto = '';
                                        switch ($c['estado']) {
                                            case 'pendiente':
                                                $estadoClase = 'badge-pendiente';
                                                $estadoTexto = 'Pendiente';
                                                break;
                                            case 'en_revision':
                                                $estadoClase = 'badge-en_revision';
                                                $estadoTexto = 'En Revisión';
                                                break;
                                            case 'resuelto':
                                                $estadoClase = 'badge-resuelto';
                                                $estadoTexto = 'Resuelto';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?= $estadoClase ?>">
                                            <?= $estadoTexto ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($c['fecha_envio'])) ?></td>
                                    <td>
                                        <div class="acciones-cell">
                                            <button 
                                                onclick='verDetalle(<?= json_encode($c) ?>)' 
                                                class="btn-ver"
                                                title="Ver detalles"
                                            >
                                                Ver
                                            </button>
                                            <form method="POST" 
                                                  action="/SIMPINNA/back-end/routes/comentarios/eliminar.php" 
                                                  style="display:inline;"
                                                  onsubmit="return confirm('¿Estás seguro de eliminar este comentario?')">
                                                <input type="hidden" name="id" value="<?= $c['id_contacto'] ?>">
                                                <button type="submit" class="btn-eliminar" title="Eliminar">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- MODAL DETALLES -->
    <div id="modalDetalle" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Detalles del Comentario</h2>
                <button class="close" onclick="cerrarModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="detalle-item">
                    <span class="detalle-label">Nombre</span>
                    <div class="detalle-valor" id="detalle-nombre"></div>
                </div>

                <div class="detalle-item">
                    <span class="detalle-label">Nivel Educativo</span>
                    <div class="detalle-valor" id="detalle-nivel"></div>
                </div>

                <div class="detalle-item">
                    <span class="detalle-label">Escuela</span>
                    <div class="detalle-valor" id="detalle-escuela"></div>
                </div>

                <div class="detalle-item">
                    <span class="detalle-label">Comentario</span>
                    <div class="detalle-valor" id="detalle-comentario"></div>
                </div>

                <div class="detalle-item">
                    <span class="detalle-label">Estado Actual</span>
                    <div class="detalle-valor">
                        <span class="estado-actual" id="detalle-estado"></span>
                    </div>
                </div>

                <div class="detalle-item">
                    <span class="detalle-label">Fecha de Envío</span>
                    <div class="detalle-valor" id="detalle-fecha"></div>
                </div>

                <!-- CAMBIAR ESTADO -->
                <form method="POST" action="/SIMPINNA/back-end/routes/comentarios/cambiar-estado.php">
                    <input type="hidden" name="id" id="form-id">
                    <div class="estado-selector">
                        <div class="estado-selector-title">Cambiar Estado</div>
                        <select name="estado" id="form-estado">
                            <option value="">-- Seleccione --</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="en_revision">En Revisión</option>
                            <option value="resuelto">Resuelto</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-cambiar-estado">
                        Guardar Cambios
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL HISTORIAL -->
    <div id="modalHistorial" class="modal-historial">
        <div class="modal-historial-content">
            <div class="modal-historial-header">
                <h2>Historial de Cambios</h2>
                <button class="close" onclick="cerrarHistorial()">&times;</button>
            </div>
            <div class="modal-historial-body">
                <div id="historial-contenido">
                    <div class="historial-empty">
                        Cargando historial...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Función para ver detalle
        function verDetalle(comentario) {
            document.getElementById('detalle-nombre').textContent = comentario.nombre;
            document.getElementById('detalle-nivel').textContent = comentario.nombre_nivel;
            document.getElementById('detalle-escuela').textContent = comentario.nombre_escuela;
            document.getElementById('detalle-comentario').textContent = comentario.comentarios;
            document.getElementById('detalle-fecha').textContent = new Date(comentario.fecha_envio).toLocaleString('es-MX');

            // Estado con badge
            const estadoBadgeClasses = {
                'pendiente': 'badge badge-pendiente',
                'en_revision': 'badge badge-en_revision',
                'resuelto': 'badge badge-resuelto'
            };
            const estadoTextos = {
                'pendiente': 'Pendiente',
                'en_revision': 'En Revisión',
                'resuelto': 'Resuelto'
            };

            const estadoElement = document.getElementById('detalle-estado');
            estadoElement.className = estadoBadgeClasses[comentario.estado] || 'badge';
            estadoElement.textContent = estadoTextos[comentario.estado] || comentario.estado;

            document.getElementById('form-id').value = comentario.id_contacto;
            document.getElementById('form-estado').value = comentario.estado;

            document.getElementById('modalDetalle').style.display = 'block';
        }

        // Función para cerrar modal
        function cerrarModal() {
            document.getElementById('modalDetalle').style.display = 'none';
        }

        // Función para abrir historial
        function abrirHistorial() {
            document.getElementById('modalHistorial').style.display = 'block';
            cargarHistorial();
        }

        // Función para cerrar historial
        function cerrarHistorial() {
            document.getElementById('modalHistorial').style.display = 'none';
        }

        // Función para cargar historial via AJAX
        function cargarHistorial() {
            const contenido = document.getElementById('historial-contenido');
            contenido.innerHTML = '<div class="historial-empty">Cargando historial...</div>';

            fetch('/SIMPINNA/back-end/routes/comentarios/obtener-historial.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.historial.length > 0) {
                        let html = '<div class="historial-timeline">';
                        
                        data.historial.forEach(item => {
                            const fecha = new Date(item.fecha_accion).toLocaleString('es-MX');
                            
                            html += `
                                <div class="historial-item">
                                    <div class="historial-header-item">
                                        <div class="historial-accion">
                                            ${item.accion.replace('_', ' ')}
                                        </div>
                                        <div class="historial-fecha">${fecha}</div>
                                    </div>
                                    <div class="historial-detalles">${item.detalles || 'Sin detalles'}</div>
                                    <div class="historial-usuario">Usuario: ${item.usuario}</div>
                                </div>
                            `;
                        });
                        
                        html += '</div>';
                        contenido.innerHTML = html;
                    } else {
                        contenido.innerHTML = '<div class="historial-empty">No hay registros en el historial</div>';
                    }
                })
                .catch(error => {
                    console.error('Error al cargar historial:', error);
                    contenido.innerHTML = '<div class="historial-empty">Error al cargar el historial</div>';
                });
        }

        // Cerrar modales al hacer clic fuera
        window.onclick = function(event) {
            const modalDetalle = document.getElementById('modalDetalle');
            const modalHistorial = document.getElementById('modalHistorial');
            
            if (event.target === modalDetalle) {
                cerrarModal();
            }
            if (event.target === modalHistorial) {
                cerrarHistorial();
            }
        }

        // Cerrar modales con tecla ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                cerrarModal();
                cerrarHistorial();
            }
        });
    </script>
  <footer>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/footer.php'; ?>
  </footer>
</body>
</html>