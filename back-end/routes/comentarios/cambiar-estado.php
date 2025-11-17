<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/back-end/auth/verificar-sesion.php';
requerir_admin();

require_once $_SERVER['DOCUMENT_ROOT'].'/SIMPINNA/back-end/controllers/ComentariosController.php';

$id = (int)($_POST['id'] ?? 0);
$estado = $_POST['estado'] ?? '';

$controller = new ComentariosController();
$controller->cambiarEstado($id, $estado);

header("Location: /SIMPINNA/back-end/routes/comentarios/index.php");
exit;
