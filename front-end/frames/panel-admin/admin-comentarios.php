<?php
session_start();
if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /SIMPINNA/front-end/frames/panel-admin/login.php');
    exit;
}

// Conectar a la base de datos
require_once __DIR__ . '/../../../back-end/connect-db/conexion-db.php';

// Obtener filtros
$filtroEstado = isset($_GET['estado']) ? $_GET['estado'] : '';
$filtroNivel = isset($_GET['nivel']) ? (int)$_GET['nivel'] : 0;
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

// Construir query con filtros
$sql = "SELECT 
          c.id_contacto,
          c.nombre,
          c.comentarios,
          c.fecha_envio,
          c.estado,
          c.notas_admin,
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

$sql .= " ORDER BY c.fecha_envio DESC LIMIT 100";

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

// Obtener niveles para el filtro
$niveles = [];
$sqlNiveles = "SELECT id_nivel, nombre_nivel FROM niveles_educativos ORDER BY id_nivel";
$resultNiveles = $conn->query($sqlNiveles);
if ($resultNiveles) {
  while ($row = $resultNiveles->fetch_assoc()) {
    $niveles[] = $row;
  }
}

// Contar por estado
$contadores = ['pendiente' => 0, 'en_revision' => 0, 'resuelto' => 0, 'archivado' => 0];
$sqlCount = "SELECT estado, COUNT(*) as total FROM contactos GROUP BY estado";
$resultCount = $conn->query($sqlCount);
if ($resultCount) {
  while ($row = $resultCount->fetch_assoc()) {
    $contadores[$row['estado']] = (int)$row['total'];
  }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Comentarios | Panel Admin</title>
  <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/global.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/admin/admin.css">
  <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/admin/comentarios.css">
</head>
<body>
  <header>
    <?php include $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/front-end/includes/header-admin.php'; ?>
  </header>

  <main class="comentarios-admin">
    <div class="comentarios-header">
      <div>
        <a href="admin-encuestas.php" class="btn-volver">← Volver al panel</a>
        <h1 class="titulo-comentarios">Gestión de Reportes y Comentarios</h1>
        <p class="subtitulo-comentarios">Total de reportes: <?= array_sum($contadores) ?></p>
      </div>
    </div>

    <!-- Contadores de estado -->
    <div class="estados-grid">
      <div class="estado-card pendiente">
        <div class="estado-num"><?= $contadores['pendiente'] ?></div>
        <div class="estado-label">Pendientes</div>
      </div>
      <div class="estado-card en-revision">
        <div class="estado-num"><?= $contadores['en_revision'] ?></div>
        <div class="estado-label">En Revisión</div>
      </div>
      <div class="estado-card resuelto">
        <div class="estado-num"><?= $contadores['resuelto'] ?></div>
        <div class="estado-label">Resueltos</div>
      </div>
      <div class="estado-card archivado">
        <div class="estado-num"><?= $contadores['archivado'] ?></div>
        <div class="estado-label">Archivados</div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="filtros-comentarios">
      <form method="GET" action="" class="filtros-form">
        <div class="filtro-item">
          <label>Buscar:</label>
          <input type="text" name="busqueda" value="<?= htmlspecialchars($busqueda) ?>" 
                 placeholder="Nombre, escuela o comentario...">
        </div>
        
        <div class="filtro-item">
          <label>Estado:</label>
          <select name="estado">
            <option value="">Todos</option>
            <option value="pendiente" <?= $filtroEstado === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
            <option value="en_revision" <?= $filtroEstado === 'en_revision' ? 'selected' : '' ?>>En Revisión</option>
            <option value="resuelto" <?= $filtroEstado === 'resuelto' ? 'selected' : '' ?>>Resuelto</option>
            <option value="archivado" <?= $filtroEstado === 'archivado' ? 'selected' : '' ?>>Archivado</option>
          </select>
        </div>

        <div class="filtro-item">
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

    <!-- Lista de comentarios -->
    <div class="comentarios-lista">
      <?php if (empty($comentarios)): ?>
        <div class="no-resultados">
          <p>No se encontraron comentarios con los filtros aplicados.</p>
        </div>
      <?php else: ?>
        <?php foreach ($comentarios as $com): ?>
          <div class="comentario-card">
            <div class="comentario-header">
              <div class="comentario-info">
                <span class="comentario-id">#<?= $com['id_contacto'] ?></span>
                <span class="comentario-nombre"><?= htmlspecialchars($com['nombre']) ?></span>
                <span class="badge badge-<?= $com['estado'] ?>">
                  <?= ucfirst(str_replace('_', ' ', $com['estado'])) ?>
                </span>
              </div>
              <div class="comentario-fecha">
                <?= date('d/m/Y H:i', strtotime($com['fecha_envio'])) ?>
              </div>
            </div>

            <div class="comentario-meta">
              <span class="meta-item">
                <strong>Nivel:</strong> <?= htmlspecialchars($com['nombre_nivel']) ?>
              </span>
              <span class="meta-item">
                <strong>Escuela:</strong> <?= htmlspecialchars($com['nombre_escuela']) ?>
              </span>
            </div>

            <div class="comentario-texto">
              <?= nl2br(htmlspecialchars($com['comentarios'])) ?>
            </div>

            <?php if (!empty($com['notas_admin'])): ?>
              <div class="comentario-notas">
                <strong>Notas del administrador:</strong><br>
                <?= nl2br(htmlspecialchars($com['notas_admin'])) ?>
              </div>
            <?php endif; ?>

            <div class="comentario-acciones">
              <a href="ver-comentario.php?id=<?= $com['id_contacto'] ?>" class="btn-accion ver">
                Ver detalles
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </main>

  <footer>
    <?php include $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/front-end/includes/footer.php'; ?>
  </footer>
</body>
</html>