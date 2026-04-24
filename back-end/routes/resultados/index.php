<?php
declare(strict_types=1);

// Debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



$pathToBackend = __DIR__ . '/../../'; 

$pathToProject = __DIR__ . '/../../../';

require_once $pathToBackend . 'auth/verificar-sesion.php';
requerir_admin(); 

// Controlador
require_once $pathToBackend . 'controllers/ResultadosController.php';

try {
    $controller = new ResultadosController();
    
    $data = $controller->resultados($_GET);

    if (is_array($data)) {
        extract($data);
    }

    $vista = $pathToProject . 'front-end/frames/panel-admin/resultados.php';

    if (!file_exists($vista)) {
        throw new Exception("Error: No encuentro el archivo de vista en: " . realpath($pathToProject) . "/front-end/frames/panel-admin/resultados.php");
    }

    require $vista;

} catch (Exception $e) {
    http_response_code(500);
    echo "<div style='font-family:sans-serif; color:#721c24; background:#f8d7da; padding:20px; border:1px solid #f5c6cb; margin:20px;'>";
    echo "<h3>Error del Sistema</h3>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";

    echo "</div>";
}
?>