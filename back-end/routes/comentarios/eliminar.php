<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/back-end/auth/verificar-sesion.php';
requerir_admin();

require_once $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/back-end/controllers/ComentariosController.php';

$id = (int)($_POST['id'] ?? 0);

// Obtener el usuario de la sesión
$usuario = $_SESSION['nombre_usuario'] ?? 'Administrador';

$controller = new ComentariosController();
$controller->eliminar($id, $usuario);

header("Location: /SIMPINNA/back-end/routes/comentarios/index.php");
exit;