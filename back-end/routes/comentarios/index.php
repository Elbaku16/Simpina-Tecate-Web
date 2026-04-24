<?php
declare(strict_types=1);

// Debug
ini_set('display_errors', 1);
error_reporting(E_ALL);


$baseBackend = __DIR__ . '/../../';
$baseProject = __DIR__ . '/../../../';

require_once $baseBackend . 'auth/verificar-sesion.php';
requerir_admin();

require_once $baseBackend . 'database/conexion-db.php';
require_once $baseBackend . 'controllers/ComentariosController.php';



$controller = new ComentariosController();

$busqueda     = $_GET['busqueda'] ?? '';
$filtroEstado = $_GET['estado']   ?? '';
$filtroNivel  = (int)($_GET['nivel'] ?? 0);

$comentarios = $controller->listar([
    'busqueda' => $busqueda,
    'estado'   => $filtroEstado,
    'nivel'    => $filtroNivel
]);


$niveles = [];
if (isset($conn) && $conn instanceof mysqli) {
    $res = $conn->query("SELECT id_nivel, nombre_nivel FROM niveles_educativos ORDER BY id_nivel");
    if ($res) {
        $niveles = $res->fetch_all(MYSQLI_ASSOC);
    }
    $conn->close();
}


$vista = $baseProject . 'front-end/frames/panel-admin/admin-comentarios.php';

if (!file_exists($vista)) {
    die("Error: No se encuentra la vista en $vista");
}

require $vista;
?>