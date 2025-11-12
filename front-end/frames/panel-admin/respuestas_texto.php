<?php
// front-end/frames/panel-admin/respuestas_texto.php
// Archivo unificado para obtener y eliminar respuestas de texto
header('Content-Type: application/json; charset=utf-8');

require_once $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/back-end/auth/verificar-sesion.php';
requerir_admin();

require_once $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/back-end/connect-db/conexion-db.php';

// Determinar acción
$accion = isset($_GET['accion']) ? trim($_GET['accion']) : (isset($_POST['accion']) ? trim($_POST['accion']) : 'obtener');

if ($accion === 'obtener') {
    // Obtener respuestas de texto
    $idPregunta = isset($_GET['id_pregunta']) ? (int)$_GET['id_pregunta'] : 0;
    $escuelaFiltro = isset($_GET['escuela']) ? (int)$_GET['escuela'] : 0;

    // Validar
    if ($idPregunta <= 0) {
        echo json_encode(['data' => [], 'error' => 'ID de pregunta inválido'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Construir query
    $sql = "SELECT 
              r.id_respuesta,
              r.respuesta_texto,
              r.fecha_respuesta,
              e.nombre_escuela
            FROM respuestas_usuario r
            INNER JOIN escuelas e ON r.id_escuela = e.id_escuela
            WHERE r.id_pregunta = ?";

    $params = [$idPregunta];
    $types = 'i';

    // Aplicar filtro de escuela si existe
    if ($escuelaFiltro > 0) {
        $sql .= " AND r.id_escuela = ?";
        $params[] = $escuelaFiltro;
        $types .= 'i';
    }

    $sql .= " ORDER BY r.fecha_respuesta DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['data' => [], 'error' => 'Error en la consulta: ' . $conn->error], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $respuestas = [];
    while ($row = $result->fetch_assoc()) {
        $respuestas[] = [
            'id_respuesta' => (int)$row['id_respuesta'],
            'respuesta_texto' => $row['respuesta_texto'],
            'fecha_respuesta' => $row['fecha_respuesta'],
            'nombre_escuela' => $row['nombre_escuela']
        ];
    }

    $stmt->close();
    $conn->close();

    // Respuesta en formato DataTables
    echo json_encode(['data' => $respuestas], JSON_UNESCAPED_UNICODE);
    exit;
}

elseif ($accion === 'eliminar') {
    // Eliminar respuesta
    $idRespuesta = isset($_POST['id_respuesta']) ? (int)$_POST['id_respuesta'] : 0;

    // Validar
    if ($idRespuesta <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de respuesta inválido'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Eliminar respuesta
    $stmt = $conn->prepare("DELETE FROM respuestas_usuario WHERE id_respuesta = ? LIMIT 1");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Error en la consulta: ' . $conn->error], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt->bind_param("i", $idRespuesta);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Respuesta eliminada correctamente'], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo eliminar la respuesta'], JSON_UNESCAPED_UNICODE);
    }

    $stmt->close();
    $conn->close();
    exit;
}

// Acción no reconocida
echo json_encode(['error' => 'Acción no reconocida'], JSON_UNESCAPED_UNICODE);
$conn->close();
?>