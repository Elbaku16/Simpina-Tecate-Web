/**
 * MODIFICACIÓN PARA modal-respuestas.js
 * Agregar el parámetro de ciclo escolar al modal
 */

// Función para abrir el modal de respuestas (MODIFICADA)
function abrirRespuestas(idPregunta, nivel, escuelaId, cicloEscolar) {
    const modal = document.getElementById('modalRespuestas');
    const modalContenido = document.getElementById('modalContenido');
    const modalTitulo = document.getElementById('modalTitulo');
    
    // Mostrar modal
    modal.classList.remove('hidden');
    
    // Resetear contenido
    modalContenido.innerHTML = '<div class="loading">Cargando respuestas...</div>';
    modalTitulo.textContent = 'Respuestas';
    
    // Hacer petición AJAX
    fetch('/SIMPINNA/back-end/ajax/obtener-respuestas-texto.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            id_pregunta: idPregunta,
            nivel: nivel,
            escuela: escuelaId,
            ciclo: cicloEscolar || '' // Agregar el parámetro de ciclo escolar
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            modalTitulo.textContent = data.titulo || 'Respuestas';
            modalContenido.innerHTML = data.html;
        } else {
            modalContenido.innerHTML = `
                <div class="error-mensaje">
                    Error: ${data.message || 'No se pudieron cargar las respuestas'}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        modalContenido.innerHTML = `
            <div class="error-mensaje">
                Error al cargar las respuestas. Por favor, intenta de nuevo.
            </div>
        `;
    });
}

// Función para cerrar el modal
function cerrarRespuestas() {
    const modal = document.getElementById('modalRespuestas');
    modal.classList.add('hidden');
}

// Cerrar modal al presionar ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        cerrarRespuestas();
    }
});

/**
 * EJEMPLO DE ENDPOINT PHP QUE RECIBE ESTOS DATOS
 * Archivo: /SIMPINNA/back-end/ajax/obtener-respuestas-texto.php
 */

/*
<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/back-end/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/back-end/auth/verificar-sesion.php';

header('Content-Type: application/json');

// Verificar sesión de admin
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Obtener parámetros
$idPregunta = isset($_POST['id_pregunta']) ? (int)$_POST['id_pregunta'] : 0;
$escuelaId = isset($_POST['escuela']) ? (int)$_POST['escuela'] : 0;
$cicloEscolar = isset($_POST['ciclo']) ? $_POST['ciclo'] : '';

if ($idPregunta === 0) {
    echo json_encode(['success' => false, 'message' => 'ID de pregunta inválido']);
    exit;
}

// Extraer años del ciclo escolar
$cicloInicio = null;
$cicloFin = null;
if ($cicloEscolar && strpos($cicloEscolar, '-') !== false) {
    list($cicloInicio, $cicloFin) = explode('-', $cicloEscolar);
    $cicloInicio = (int)$cicloInicio;
    $cicloFin = (int)$cicloFin;
}

// Obtener información de la pregunta
$stmtPregunta = $conn->prepare("SELECT texto_pregunta, tipo_pregunta FROM preguntas WHERE id_pregunta = ?");
$stmtPregunta->bind_param("i", $idPregunta);
$stmtPregunta->execute();
$resultPregunta = $stmtPregunta->get_result();
$pregunta = $resultPregunta->fetch_assoc();

if (!$pregunta) {
    echo json_encode(['success' => false, 'message' => 'Pregunta no encontrada']);
    exit;
}

$tipoPregunta = strtolower(trim($pregunta['tipo_pregunta']));
$textoPregunta = $pregunta['texto_pregunta'];

// Construir query para obtener respuestas
$sql = "SELECT 
            ru.id_respuesta, 
            ru.respuesta_texto, 
            ru.fecha_respuesta, 
            e.nombre as escuela, 
            u.nombre_completo
        FROM respuestas_usuario ru
        INNER JOIN escuelas e ON ru.id_escuela = e.id_escuela
        INNER JOIN usuarios u ON ru.id_usuario = u.id_usuario
        WHERE ru.id_pregunta = ?";

$params = [$idPregunta];
$types = "i";

// Filtro por escuela
if ($escuelaId > 0) {
    $sql .= " AND ru.id_escuela = ?";
    $params[] = $escuelaId;
    $types .= "i";
}

// Filtro por ciclo escolar
if ($cicloInicio !== null && $cicloFin !== null) {
    $sql .= " AND (
        (YEAR(ru.fecha_respuesta) = ? AND MONTH(ru.fecha_respuesta) >= 8) OR
        (YEAR(ru.fecha_respuesta) = ? AND MONTH(ru.fecha_respuesta) <= 7)
    )";
    $params[] = $cicloInicio;
    $params[] = $cicloFin;
    $types .= "ii";
}

$sql .= " ORDER BY ru.fecha_respuesta DESC";

// Ejecutar query
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Generar HTML
$html = '';

if ($result->num_rows === 0) {
    $html = '<div class="sin-respuestas">No hay respuestas disponibles para esta pregunta con los filtros seleccionados.</div>';
} else {
    $html .= '<div class="respuestas-tabla">';
    $html .= '<div class="tabla-header">';
    $html .= '<div>Respuesta</div>';
    $html .= '<div>Estudiante</div>';
    $html .= '<div>Escuela</div>';
    $html .= '<div>Fecha</div>';
    $html .= '</div>';
    
    while ($row = $result->fetch_assoc()) {
        $html .= '<div class="tabla-fila">';
        $html .= '<div class="col-respuesta">';
        
        if ($tipoPregunta === 'texto') {
            $html .= '<p class="respuesta-texto">' . htmlspecialchars($row['respuesta_texto']) . '</p>';
        } else {
            // Para imágenes/dibujos
            $html .= '<img src="' . htmlspecialchars($row['respuesta_texto']) . '" alt="Dibujo" style="max-width: 200px; border-radius: 8px;">';
        }
        
        $html .= '</div>';
        $html .= '<div class="col-info">' . htmlspecialchars($row['nombre_completo']) . '</div>';
        $html .= '<div class="col-info">' . htmlspecialchars($row['escuela']) . '</div>';
        $html .= '<div class="col-info">' . date('d/m/Y', strtotime($row['fecha_respuesta'])) . '</div>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
}

echo json_encode([
    'success' => true,
    'titulo' => 'Respuestas: ' . $textoPregunta,
    'html' => $html
]);
?>
*/