<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/back-end/auth/verificar-sesion.php';
requerir_admin();

require_once $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/back-end/connect-db/conexion-db.php';

if (isset($_POST['eliminar']) && isset($_POST['id_contacto'])) {
    $idEliminar = (int)$_POST['id_contacto'];
    $stmtDel = $conn->prepare("DELETE FROM contactos WHERE id_contacto = ?");
    $stmtDel->bind_param("i", $idEliminar);
    $stmtDel->execute();
    $stmtDel->close();
    header("Location: admin-comentarios.php");
    exit;
}

if (isset($_POST['cambiar_estado']) && isset($_POST['id_contacto']) && isset($_POST['nuevo_estado'])) {
    $idContacto = (int)$_POST['id_contacto'];
    $nuevoEstado = $_POST['nuevo_estado'];
    
    // Validar que sea un estado permitido
    if (in_array($nuevoEstado, ['pendiente', 'en_revision', 'resuelto'])) {
        $stmtUpdate = $conn->prepare("UPDATE contactos SET estado = ? WHERE id_contacto = ?");
        $stmtUpdate->bind_param("si", $nuevoEstado, $idContacto);
        $stmtUpdate->execute();
        $stmtUpdate->close();
    }
    header("Location: admin-comentarios.php");
    exit;
}

$filtroEstado = isset($_GET['estado']) ? $_GET['estado'] : '';
$filtroNivel = isset($_GET['nivel']) ? (int)$_GET['nivel'] : 0;
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

//query
$sql = "SELECT 
          c.id_contacto,
          c.nombre,
          c.comentarios,
          c.fecha_envio,
          c.estado,
          n.nombre_nivel,
          e.nombre_escuela
        FROM contactos c
        INNER JOIN niveles_educativos n ON c.id_nivel = n.id_nivel
        INNER JOIN escuelas e ON c.id_escuela = e.id_escuela
        WHERE 1=1";

$params = [];
$types = "";

if ($filtroEstado !== '') {
  $sql .= " AND c.estado = ?";
  $params[] = $filtroEstado;
  $types .= "s";
}

if ($filtroNivel > 0) {
  $sql .= " AND c.id_nivel = ?";
  $params[] = $filtroNivel;
  $types .= "i";
}

if ($busqueda !== '') {
  $sql .= " AND (c.nombre LIKE ? OR c.comentarios LIKE ? OR e.nombre_escuela LIKE ?)";
  $searchTerm = "%{$busqueda}%";
  $params[] = $searchTerm;
  $params[] = $searchTerm;
  $params[] = $searchTerm;
  $types .= "sss";
}

$sql .= " ORDER BY c.fecha_envio DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$comentarios = [];
while ($row = $result->fetch_assoc()) {
  $comentarios[] = $row;
}
$stmt->close();

//filtro niveles
$niveles = [];
$sqlNiveles = "SELECT id_nivel, nombre_nivel FROM niveles_educativos ORDER BY id_nivel";
$resultNiveles = $conn->query($sqlNiveles);
if ($resultNiveles) {
  while ($row = $resultNiveles->fetch_assoc()) {
    $niveles[] = $row;
  }
}

$conn->close();

$estadosColor = [
  'pendiente' => '#ff9800',
  'en_revision' => '#2196f3',
  'resuelto' => '#4caf50'
];

$estadosTexto = [
  'pendiente' => 'Pendiente',
  'en_revision' => 'En Revisión',
  'resuelto' => 'Resuelto'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Reportes | Panel Admin</title>
  <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/layout.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/admin/admin.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/admin/comentarios.css">
</head>
<body>
  <header>
    <?php include $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/front-end/includes/header-admin.php'; ?>
  </header>

  <main class="comentarios-container">
    <div class="header-section">
      <a href="../panel/panel-admin.php" class="btn-ver">← Volver al panel</a>
      <h1 style="color: var(--burgundy); margin: 0.5rem 0;">Gestión de Reportes y Comentarios</h1>
      <p style="margin: 0; opacity: 0.8;">Total de reportes: <strong><?= count($comentarios) ?></strong></p>
    </div>

    <!-- Filtros -->
    <div class="filtros-bar">
      <form method="GET" action="" style="display: contents;">
        <div class="filtro-group">
          <label>Buscar:</label>
          <input type="text" name="busqueda" value="<?= htmlspecialchars($busqueda) ?>" 
                 placeholder="Nombre, escuela...">
        </div>
        
        <div class="filtro-group">
          <label>Estado:</label>
          <select name="estado">
            <option value="">Todos</option>
            <option value="pendiente" <?= $filtroEstado === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
            <option value="en_revision" <?= $filtroEstado === 'en_revision' ? 'selected' : '' ?>>En Revisión</option>
            <option value="resuelto" <?= $filtroEstado === 'resuelto' ? 'selected' : '' ?>>Resuelto</option>
          </select>
        </div>

        <div class="filtro-group">
          <label>Nivel:</label>
          <select name="nivel">
            <option value="0">Todos</option>
            <?php foreach ($niveles as $niv): ?>
              <option value="<?= $niv['id_nivel'] ?>" <?= $filtroNivel == $niv['id_nivel'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($niv['nombre_nivel']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <button type="submit" class="btn-filtrar">Filtrar</button>
        <a href="admin-comentarios.php" class="btn-limpiar">Limpiar</a>
      </form>
    </div>

    <!-- Tabla -->
    <div class="tabla-comentarios">
      <?php if (empty($comentarios)): ?>
        <div class="no-datos">
          <p>No se encontraron reportes.</p>
        </div>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Nivel</th>
              <th>Escuela</th>
              <th>Comentario</th>
              <th>Estado</th>
              <th>Fecha</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($comentarios as $com): ?>
              <tr>
                <td><strong>#<?= $com['id_contacto'] ?></strong></td>
                <td><?= htmlspecialchars($com['nombre']) ?></td>
                <td><?= htmlspecialchars($com['nombre_nivel']) ?></td>
                <td><?= htmlspecialchars($com['nombre_escuela']) ?></td>
                <td class="comentario-preview"><?= htmlspecialchars($com['comentarios']) ?></td>
                <td>
                  <span class="badge" style="background: <?= $estadosColor[$com['estado']] ?>">
                    <?= $estadosTexto[$com['estado']] ?>
                  </span>
                </td>
                <td><?= date('d/m/Y H:i', strtotime($com['fecha_envio'])) ?></td>
                <td>
                  <button class="btn-accion btn-ver" onclick="verDetalle(<?= htmlspecialchars(json_encode($com)) ?>)">
                    Ver
                  </button>
                  <button class="btn-accion btn-eliminar" onclick="confirmarEliminar(<?= $com['id_contacto'] ?>)">
                    Eliminar
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </main>

  <!-- Modal de detalles -->
  <div id="modalDetalle" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Detalles del Reporte</h2>
        <span class="close" onclick="cerrarModal()">&times;</span>
      </div>
      <div class="modal-body" id="modalBody">
        <!-- Contenido dinámico -->
      </div>
    </div>
  </div>

  <!-- Formulario oculto para eliminar -->
  <form id="formEliminar" method="POST" style="display: none;">
    <input type="hidden" name="eliminar" value="1">
    <input type="hidden" name="id_contacto" id="idEliminar">
  </form>

  <!-- Formulario oculto para cambiar estado -->
  <form id="formCambiarEstado" method="POST" style="display: none;">
    <input type="hidden" name="cambiar_estado" value="1">
    <input type="hidden" name="id_contacto" id="idEstado">
    <input type="hidden" name="nuevo_estado" id="nuevoEstado">
  </form>

  <footer>
    <?php include $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/front-end/includes/footer.php'; ?>
  </footer>

  <script>
    function verDetalle(data) {
      const estados = <?= json_encode($estadosTexto) ?>;
      const colores = <?= json_encode($estadosColor) ?>;
      
      const fecha = new Date(data.fecha_envio);
      const fechaFormateada = fecha.toLocaleString('es-MX', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
      
      document.getElementById('modalBody').innerHTML = `
        <div class="detalle-item">
          <span class="detalle-label">ID del Reporte</span>
          <div class="detalle-valor">#${data.id_contacto}</div>
        </div>
        
        <div class="detalle-item">
          <span class="detalle-label">Nombre</span>
          <div class="detalle-valor">${data.nombre}</div>
        </div>
        
        <div class="detalle-item">
          <span class="detalle-label">Nivel Educativo</span>
          <div class="detalle-valor">${data.nombre_nivel}</div>
        </div>
        
        <div class="detalle-item">
          <span class="detalle-label">Escuela</span>
          <div class="detalle-valor">${data.nombre_escuela}</div>
        </div>
        
        <div class="detalle-item">
          <span class="detalle-label">Comentario / Situación</span>
          <div class="detalle-valor" style="white-space: pre-wrap;">${data.comentarios}</div>
        </div>
        
        <div class="detalle-item">
          <span class="detalle-label">Estado Actual</span>
          <div class="detalle-valor">
            <span class="badge" style="background: ${colores[data.estado]}">${estados[data.estado]}</span>
          </div>
        </div>
        
        <div class="detalle-item">
          <span class="detalle-label">Fecha de Envío</span>
          <div class="detalle-valor">${fechaFormateada}</div>
        </div>
        
        <div class="estado-selector">
          <label for="selectEstado">Cambiar estado del reporte:</label>
          <select id="selectEstado" onchange="cambiarEstado(${data.id_contacto}, this.value)">
            <option value="">-- Seleccionar nuevo estado --</option>
            <option value="pendiente" ${data.estado === 'pendiente' ? 'selected' : ''}>Pendiente</option>
            <option value="en_revision" ${data.estado === 'en_revision' ? 'selected' : ''}>En Revisión</option>
            <option value="resuelto" ${data.estado === 'resuelto' ? 'selected' : ''}>Resuelto</option>
          </select>
        </div>
      `;
      
      document.getElementById('modalDetalle').style.display = 'block';
    }
    
    function cambiarEstado(id, nuevoEstado) {
      if (nuevoEstado === '') return;
      
      const estados = {
        'pendiente': 'Pendiente',
        'en_revision': 'En Revisión',
        'resuelto': 'Resuelto'
      };
      
      if (confirm(`¿Cambiar el estado a "${estados[nuevoEstado]}"?`)) {
        document.getElementById('idEstado').value = id;
        document.getElementById('nuevoEstado').value = nuevoEstado;
        document.getElementById('formCambiarEstado').submit();
      }
    }
    
    function cerrarModal() {
      document.getElementById('modalDetalle').style.display = 'none';
    }
    
    function confirmarEliminar(id) {
      if (confirm('¿Estás seguro de que deseas eliminar este reporte? Esta acción no se puede deshacer.')) {
        document.getElementById('idEliminar').value = id;
        document.getElementById('formEliminar').submit();
      }
    }
    
    // Cerrar modal al hacer clic fuera
    window.onclick = function(event) {
      const modal = document.getElementById('modalDetalle');
      if (event.target == modal) {
        cerrarModal();
      }
    }
  </script>
</body>
</html>