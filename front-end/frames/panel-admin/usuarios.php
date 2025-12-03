<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/auth/verificar-sesion.php';
requerir_admin();

// CAMBIO 1: Restricción máxima con el nuevo rol
if (!rol_es('secretario_ejecutivo')) {
    header('Location: /front-end/frames/panel/panel-admin.php');
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
    <link rel="stylesheet" href="/front-end/assets/css/global/layout.css">
    <link rel="stylesheet" href="/front-end/assets/css/admin/admin.css">
    <link rel="stylesheet" href="/front-end/assets/css/admin/usuarios.css">
</head>

<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/front-end/includes/header-admin.php'; ?>

    <main class="usuarios-container">
        <a href="/front-end/frames/panel/panel-admin.php" class="btn-back-panel">
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
                        <input type="password" id="password" name="password" required>
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
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/front-end/includes/footer.php'; ?>
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
                const response = await fetch('/back-end/routes/usuarios/gestionar.php?accion=listar');
                const data = await response.json();

                if (data.success) {
                    renderizarUsuarios(data.usuarios);
                } else {
                    lista.innerHTML = `<tr><td colspan="5" class="error-message" style="text-align:center;">Error al cargar: ${data.error}</td></tr>`;
                }
            } catch (e) {
                console.error('Error al cargar usuarios:', e);
                lista.innerHTML = `<tr><td colspan="5" class="error-message" style="text-align:center;">Error de conexión.</td></tr>`;
            }
        }

        function renderizarUsuarios(usuarios) {
            const lista = document.getElementById('listaUsuarios');
            lista.innerHTML = '';
            
            if (usuarios.length === 0) {
                lista.innerHTML = '<tr><td colspan="5" style="text-align:center;">No hay usuarios administrativos registrados.</td></tr>';
                return;
            }

            // CAMBIO 3: Diccionario de nombres de roles
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

                row.innerHTML = `
                    <td>#${user.id_admin}</td>
                    <td>${user.nombre}</td>
                    <td>${user.usuario}</td>
                    <td>${rolNombres[user.rol] || user.rol}${isSelf ? ' <strong style="color:#7A1E2C;">(Tú)</strong>' : ''}</td>
                    <td>
                        ${isPrincipal || isSelf ? 
                            '' : 
                            `<button type="button" class="btn-eliminar-usuario" onclick="eliminarUsuario(${user.id_admin})">Eliminar</button>`
                        }
                    </td>
                `;
                lista.appendChild(row);
            });
        }

        async function crearUsuario(e) {
            e.preventDefault();
            const form = e.target;
            document.getElementById('mensaje-error').style.display = 'none';

            const formData = new FormData(form);
            formData.append('accion', 'crear');

            try {
                const response = await fetch('/back-end/routes/usuarios/gestionar.php', {
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

            fetch('/back-end/routes/usuarios/gestionar.php', {
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