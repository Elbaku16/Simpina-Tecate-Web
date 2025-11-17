<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/back-end/auth/verificar-sesion.php';
requerir_admin();

require_once $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/back-end/controllers/ComentariosController.php';

$id = (int)($_POST['id'] ?? 0);

$controller = new ComentariosController();
$controller->eliminar($id);

header("Location: /SIMPINNA/back-end/routes/comentarios/index.php");
exit;
