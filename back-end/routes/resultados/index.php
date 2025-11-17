<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/back-end/auth/verificar-sesion.php';
requerir_admin();

require_once $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/back-end/controllers/ResultadosController.php';

try {
    $controller = new ResultadosController();
    $data = $controller->resultados($_GET);

    extract($data);

    require $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/front-end/frames/panel-admin/resultados.php';

} catch (Exception $e) {
    http_response_code(400);
    echo "Error: " . $e->getMessage();
}
