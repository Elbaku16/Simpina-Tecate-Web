<?php
// front-end/frames/panel-admin/obtener_respuestas_texto.php
header('Content-Type: application/json');

require_once $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/back-end/auth/verificar-sesion.php';
requerir_admin();

require_once $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/back-end/connect-db/conexion-db.php';

// Obtener parámetros
$preguntaId = isset($_GET['pregunta']) ? (int)$_GET['pregunta'] : 0;
$nivelNombre = isset($_GET['nivel']) ? strtolower(trim($_GET['nivel'])) : '';
$escuelaId = isset($_GET['escuela']) ? (int)$_GET['escuela'] : 0;

// Validar parámetros
if ($preguntaId <= 0) {
  echo json_encode(['success' => false, 'message' => 'ID de pregunta inválido']);
  exit;
}

// Obtener nombre de la pregunta
$stmt = $conn->prepare("SELECT texto_pregunta FROM preguntas WHERE id_pregunta = ? LIMIT 1");
$stmt->bind_param("i", $preguntaId);
$stmt->execute();
$stmt->bind_result($nombrePregunta);
if (!$stmt->fetch()) {
  echo json_encode(['success' => false, 'message' => 'Pregunta no encontrada']);
  $stmt->close();
  exit;
}
$stmt->close();

// Construir query para obtener respuestas
$sql = "SELECT 
          r.id_respuesta,
          r.respuesta_texto,
          r.fecha_respuesta,
          e.nombre_escuela
        FROM respuestas_usuario r
        LEFT JOIN escuelas e ON r.id_escuela = e.id_escuela
        WHERE r.id_pregunta = ?";

$params = [$preguntaId];
$types = "i";

// Agregar filtro de escuela si existe
if ($escuelaId > 0) {
  $sql .= " AND r.id_escuela = ?";
  $params[] = $escuelaId;
  $types .= "i";
}

$sql .= " ORDER BY r.fecha_respuesta DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
  echo json_encode(['success' => false, 'message' => 'Error en la consulta: ' . $conn->error]);
  exit;
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$respuestas = [];
while ($row = $result->fetch_assoc()) {
  $respuestas[] = [
    'id' => (int)$row['id_respuesta'],
    'texto' => $row['respuesta_texto'],
    'fecha' => $row['fecha_respuesta'],
    'escuela' => $row['nombre_escuela'] ?? 'Sin escuela'
  ];
}
$stmt->close();
$conn->close();

echo json_encode([
  'success' => true,
  'nombrePregunta' => $nombrePregunta,
  'respuestas' => $respuestas
]);
?>
