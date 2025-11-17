<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/back-end/auth/verificar-sesion.php';
requerir_admin();
// Fallback seguro para evitar warnings si el controlador no define las variables
$busqueda     = $busqueda     ?? '';
$filtroEstado = $filtroEstado ?? '';
$filtroNivel  = $filtroNivel  ?? 0;
$comentarios  = $comentarios  ?? [];
$niveles      = $niveles      ?? [];

// Variables disponibles:
// $comentarios, $niveles, $busqueda, $filtroEstado, $filtroNivel

$estadosColor = [
    'pendiente'   => '#ff9800',
    'en_revision' => '#2196f3',
    'resuelto'    => '#4caf50'
];

$estadosTexto = [
    'pendiente'   => 'Pendiente',
    'en_revision' => 'En Revisión',
    'resuelto'    => 'Resuelto'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
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
    <a href="/SIMPINNA/front-end/frames/panel/panel-admin.php" class="btn-ver">← Volver</a>
    <h1>Gestión de Reportes</h1>
    <p>Total: <strong><?= count($comentarios) ?></strong></p>
  </div>

  <!-- FILTROS -->
  <div class="filtros-bar">
      <form method="GET" action="" style="display:contents">

        <div class="filtro-group">
          <label>Buscar:</label>
          <input type="text" name="busqueda"
              value="<?= htmlspecialchars($busqueda) ?>">
        </div>

        <div class="filtro-group">
          <label>Estado:</label>
          <select name="estado">
            <option value="">Todos</option>
            <?php foreach ($estadosTexto as $key=>$txt): ?>
              <option value="<?= $key ?>" <?= $filtroEstado === $key ? 'selected':'' ?>>
                <?= $txt ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="filtro-group">
          <label>Nivel:</label>
          <select name="nivel">
            <option value="0">Todos</option>
            <?php foreach ($niveles as $n): ?>
              <option value="<?= $n['id_nivel'] ?>"
                      <?= $filtroNivel == $n['id_nivel'] ? 'selected':'' ?>>
                <?= htmlspecialchars($n['nombre_nivel']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <button type="submit" class="btn-filtrar">Filtrar</button>
        <a href="/SIMPINNA/back-end/routes/comentarios/index.php" class="btn-limpiar">Limpiar</a>
      </form>
  </div>

  <!-- TABLA -->
  <div class="tabla-comentarios">
    <?php if (empty($comentarios)): ?>
      <div class="no-datos"><p>No hay reportes</p></div>
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
        <?php foreach($comentarios as $c): ?>
        <tr>
          <td>#<?= $c['id_contacto'] ?></td>
          <td><?= htmlspecialchars($c['nombre']) ?></td>
          <td><?= htmlspecialchars($c['nombre_nivel']) ?></td>
          <td><?= htmlspecialchars($c['nombre_escuela']) ?></td>
          <td class="comentario-preview"><?= htmlspecialchars($c['comentarios']) ?></td>

          <td>
            <span class="badge"
                style="background: <?= $estadosColor[$c['estado']] ?>">
              <?= $estadosTexto[$c['estado']] ?>
            </span>
          </td>

          <td><?= date('d/m/Y H:i', strtotime($c['fecha_envio'])) ?></td>

          <td>
            <button class="btn-accion btn-ver"
                data-id="<?= $c['id_contacto'] ?>"
                data-nombre="<?= htmlspecialchars($c['nombre']) ?>"
                data-nivel="<?= htmlspecialchars($c['nombre_nivel']) ?>"
                data-escuela="<?= htmlspecialchars($c['nombre_escuela']) ?>"
                data-comentarios="<?= htmlspecialchars($c['comentarios'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?>"
                data-estado="<?= $c['estado'] ?>"
                data-fecha="<?= $c['fecha_envio'] ?>"
                onclick="verDetalle(this)">
              Ver
            </button>

            <button class="btn-accion btn-eliminar"
                data-id="<?= $c['id_contacto'] ?>"
                onclick="confirmarEliminar(this)">
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

<!-- MODAL -->
<div id="modalDetalle" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Detalles</h2>
      <span class="close" onclick="cerrarModal()">&times;</span>
    </div>
    <div class="modal-body" id="modalBody"></div>
  </div>
</div>

<footer>
  <?php include $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/front-end/includes/footer.php'; ?>
</footer>

<script src="/SIMPINNA/front-end/scripts/admin/comentarios.js"></script>

</body>
</html>
