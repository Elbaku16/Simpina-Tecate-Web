<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/auth/verificar-sesion.php';
requerir_admin();

// RESTRICCIÓN: Solo si tiene permiso de modificar comentarios
if (!tiene_permiso('modificar_comentarios')) {
    header('Location: /back-end/routes/comentarios/index.php?error=' . urlencode('Permiso denegado para modificar estados'));
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/database/conexion-db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/controllers/ComentariosController.php';

$id     = (int)($_POST['id'] ?? 0);
$estado = $_POST['estado'] ?? '';

// Usuario que realiza la acción
$usuario = $_SESSION['usuario'] ?? 'Administrador';

$controller = new ComentariosController();
$controller->cambiarEstado($id, $estado, $usuario);


$conn->close();

header("Location: /back-end/routes/comentarios/index.php");
exit;