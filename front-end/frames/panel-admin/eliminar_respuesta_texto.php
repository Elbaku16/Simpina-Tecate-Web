<?php
// front-end/frames/panel-admin/eliminar_respuesta_texto.php
header('Content-Type: application/json');

require_once $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/back-end/auth/verificar-sesion.php';
requerir_admin();

require_once $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/back-end/connect-db/conexion-db.php';

// Obtener ID de respuesta
$idRespuesta = isset($_POST['id_respuesta']) ? (int)$_POST['id_respuesta'] : 0;

// Validar
if ($idRespuesta <= 0) {
  echo json_encode(['success' => false, 'message' => 'ID de respuesta inválido']);
  exit;
}

// Eliminar respuesta
$stmt = $conn->prepare("DELETE FROM respuestas_usuario WHERE id_respuesta = ? LIMIT 1");
if (!$stmt) {
  echo json_encode(['success' => false, 'message' => 'Error en la consulta: ' . $conn->error]);
  exit;
}

$stmt->bind_param("i", $idRespuesta);
$stmt->execute();

if ($stmt->affected_rows > 0) {
  echo json_encode(['success' => true, 'message' => 'Respuesta eliminada correctamente']);
} else {
  echo json_encode(['success' => false, 'message' => 'No se pudo eliminar la respuesta']);
}

$stmt->close();
$conn->close();
?>
