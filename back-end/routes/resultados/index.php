<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/back-end/auth/verificar-sesion.php';
requerir_admin();

require_once $_SERVER['DOCUMENT_ROOT'].'/back-end/database/conexion-db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/back-end/controllers/ResultadosController.php';

try {
    $controller = new ResultadosController();
    $data = $controller->resultados($_GET);

    // Extraemos variables para la vista
    extract($data);

    // CERRAR conexión ANTES de cargar la vista (la vista no usa DB)
    $conn->close();

    require $_SERVER['DOCUMENT_ROOT'].'/front-end/frames/panel-admin/resultados.php';

} catch (Exception $e) {

    // Cerrar conexión también en errores
    if (isset($conn)) {
        $conn->close();
    }

    http_response_code(400);
    echo "Error: " . $e->getMessage();
}
