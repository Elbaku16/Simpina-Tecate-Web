<?php
require_once __DIR__ . '/../../../back-end/auth/verificar-sesion.php';
require_once __DIR__ . '/../../includes/config.php';
requerir_admin();

if (!rol_es('secretario_ejecutivo')) {
    header('Location: ' . FRAMES_URL . 'panel/panel-admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMPINNA | Gestión de Usuarios</title>

    <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>global/layout.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>admin/admin.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>admin/usuarios.css">
</head>

<body>
    <?php require_once __DIR__ . '/../../includes/header-admin.php'; ?>

    <main class="usuarios-container">
        <a href="<?php echo FRAMES_URL; ?>panel/panel-admin.php" class="btn-back-panel">
            <i class="fa-solid fa-angle-left"></i> Regresar al Panel
        </a>
        
        <div class="form-crear-usuario">
            <h2>Crear Credencial de Usuario</h2>
            <div id="mensaje-error" class="error-message" style="display:none;"></div>
            <form id="formCrearUsuario">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nombre">Nombre Completo</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="rol">Nivel de Permiso</label>
                        <select id="rol" name="rol" required>
                            <option value="acompanamiento">Acompañamiento Social</option>
                            <option value="evaluacion">Evaluación Sociocultural</option>
                            <option value="secretario_ejecutivo">Secretario Ejecutivo</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="usuario">Usuario (Login)</label>
                        <input type="text" id="usuario" name="usuario" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" minlength="12" maxlength="128" required>
                    </div>
                    <div class="full-width">
                        <button type="submit" class="btn-crear-usuario">Crear Usuario</button>
                    </div>
                </div>
            </form>
        </div>

        <h2>Usuarios Existentes</h2>
        <div class="tabla-usuarios-wrapper">
            <table class="usuarios-tabla">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="listaUsuarios">
                    <tr><td colspan="5" style="text-align:center;">Cargando usuarios...</td></tr>
                </tbody>
            </table>
        </div>
    </main>

    <footer>
        <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
    </footer>

    <script>
        const USUARIO_ACTUAL_ID = <?php echo isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : 0; ?>;

        document.addEventListener('DOMContentLoaded', () => {
            cargarUsuarios();
            document.getElementById('formCrearUsuario').addEventListener('submit', crearUsuario);
        });

        function mostrarError(mensaje) {
            const errorDiv = document.getElementById('mensaje-error');
            errorDiv.textContent = 'Error: ' + mensaje;
            errorDiv.style.display = 'block';
        }

        async function cargarUsuarios() {
            const lista = document.getElementById('listaUsuarios');
            lista.innerHTML = '<tr><td colspan="5" style="text-align:center;">Cargando usuarios...</td></tr>';

            try {
                const response = await fetch(window.BASE_URL + '/back-end/routes/usuarios/gestionar.php?accion=listar');
                const data = await response.json();

                if (data.success) {
                    renderizarUsuarios(data.usuarios);
                } else {
                    mostrarFilaMensaje(lista, 'Error al cargar: ' + (data.error || 'Desconocido'), true);
                }
            } catch (e) {
                console.error('Error al cargar usuarios:', e);
                mostrarFilaMensaje(lista, 'Error de conexión.', true);
            }
        }

        function renderizarUsuarios(usuarios) {
            const lista = document.getElementById('listaUsuarios');
            lista.innerHTML = '';
            
            if (usuarios.length === 0) {
                lista.innerHTML = '<tr><td colspan="5" style="text-align:center;">No hay usuarios administrativos registrados.</td></tr>';
                return;
            }

            const rolNombres = {
                'secretario_ejecutivo': 'Secretario Ejecutivo', // Nuevo nombre
                'admin': 'Administrador (Legacy)', // Mantenemos admin por compatibilidad si quedan en BD
                'acompanamiento': 'Acompañamiento Social',
                'evaluacion': 'Evaluación Sociocultural (Solo Lectura)'
            };

            usuarios.forEach(user => {
                const row = document.createElement('tr');
                
                const isSelf = Number(user.id_admin) === Number(USUARIO_ACTUAL_ID);
                const isPrincipal = Number(user.id_admin) === 1;

                const valores = [`#${Number(user.id_admin)}`, user.nombre, user.usuario];
                valores.forEach(valor => {
                    const td = document.createElement('td');
                    td.textContent = String(valor || '');
                    row.appendChild(td);
                });

                const rolTd = document.createElement('td');
                rolTd.textContent = rolNombres[user.rol] || String(user.rol || '');
                if (isSelf) {
                    const self = document.createElement('strong');
                    self.style.color = '#7A1E2C';
                    self.textContent = ' (Tú)';
                    rolTd.appendChild(self);
                }
                row.appendChild(rolTd);

                const acciones = document.createElement('td');
                if (!isPrincipal && !isSelf) {
                    const boton = document.createElement('button');
                    boton.type = 'button';
                    boton.className = 'btn-eliminar-usuario';
                    boton.textContent = 'Eliminar';
                    boton.addEventListener('click', () => eliminarUsuario(Number(user.id_admin)));
                    acciones.appendChild(boton);
                }
                row.appendChild(acciones);
                lista.appendChild(row);
            });
        }

        function mostrarFilaMensaje(tbody, mensaje, esError = false) {
            tbody.replaceChildren();
            const row = document.createElement('tr');
            const cell = document.createElement('td');
            cell.colSpan = 5;
            cell.style.textAlign = 'center';
            if (esError) cell.className = 'error-message';
            cell.textContent = mensaje;
            row.appendChild(cell);
            tbody.appendChild(row);
        }

        async function crearUsuario(e) {
            e.preventDefault();
            const form = e.target;
            document.getElementById('mensaje-error').style.display = 'none';

            const formData = new FormData(form);
            formData.append('accion', 'crear');
            formData.append('csrf_token', window.SIMPINNA_CSRF);

            try {
                const response = await fetch(window.BASE_URL + '/back-end/routes/usuarios/gestionar.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    alert('Usuario creado exitosamente.');
                    form.reset();
                    cargarUsuarios();
                } else {
                    mostrarError(data.error || 'Desconocido');
                }
            } catch (e) {
                console.error('Error al crear usuario:', e);
                mostrarError('Error de conexión con el servidor.');
            }
        }

        function eliminarUsuario(id) {
            if (!confirm(`¿Estás seguro de eliminar al usuario #${id}? Esta acción es permanente.`)) {
                return;
            }

            const formData = new FormData();
            formData.append('accion', 'eliminar');
            formData.append('id', id);
            formData.append('csrf_token', window.SIMPINNA_CSRF);

            fetch(window.BASE_URL + '/back-end/routes/usuarios/gestionar.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Usuario eliminado.');
                    cargarUsuarios();
                } else {
                    alert('Error al eliminar: ' + (data.error || 'Desconocido'));
                }
            })
            .catch(e => {
                console.error('Error al eliminar:', e);
                alert('Error de conexión al intentar eliminar.');
            });
        }
    </script>
</body>
</html>
