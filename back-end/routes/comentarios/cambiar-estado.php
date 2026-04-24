<?php
declare(strict_types=1);

ini_set('display_errors', 1);
error_reporting(E_ALL);


$baseBackend = __DIR__ . '/../../';


require_once $baseBackend . 'auth/verificar-sesion.php';
requerir_admin();

if (!tiene_permiso('modificar_comentarios')) {
    // Redirigimos al index que está en ESTA misma carpeta
    header('Location: index.php?error=' . urlencode('Permiso denegado para modificar estados'));
    exit;
}

require_once $baseBackend . 'controllers/ComentariosController.php';

// Validamos datos de entrada
$id = (int)($_POST['id'] ?? 0);
$estado = $_POST['estado'] ?? '';

$usuario = $_SESSION['usuario'] ?? 'Secretario Ejecutivo';

if ($id > 0 && !empty($estado)) {
    try {
        $controller = new ComentariosController();
        $controller->cambiarEstado($id, $estado, $usuario);
    } catch (Exception $e) {
        header('Location: index.php?error=' . urlencode('Error al cambiar estado: ' . $e->getMessage()));
        exit;
    }
}


header('Location: index.php');
exit;
?>