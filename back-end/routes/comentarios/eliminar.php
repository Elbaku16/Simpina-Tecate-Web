<?php
declare(strict_types=1);


$baseBackend = __DIR__ . '/../../';

require_once $baseBackend . 'auth/verificar-sesion.php';
requerir_admin();

require_once $baseBackend . 'controllers/ComentariosController.php';



$id = (int)($_POST['id'] ?? 0);
$usuario = $_SESSION['usuario'] ?? 'Administrador'; 

if ($id > 0) {
    try {
        $controller = new ComentariosController();
        $controller->eliminar($id, $usuario);
    } catch (Exception $e) {
    }
}

header('Location: index.php');
exit;
?>