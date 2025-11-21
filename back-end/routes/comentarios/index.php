<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/auth/verificar-sesion.php';
requerir_admin();

require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/controllers/ComentariosController.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/database/conexion-db.php';

$controller = new ComentariosController();

// --- Filtros ---
$busqueda = $_GET['busqueda'] ?? '';
$filtroEstado = $_GET['estado'] ?? '';
$filtroNivel = (int)($_GET['nivel'] ?? 0);

// --- Datos ---
$comentarios = $controller->listar([
    'busqueda' => $busqueda,
    'estado'   => $filtroEstado,
    'nivel'    => $filtroNivel
]);

// --- Niveles para el filtro ---
$niveles = $conn->query("SELECT id_nivel, nombre_nivel FROM niveles_educativos ORDER BY id_nivel")
                ->fetch_all(MYSQLI_ASSOC);

$conn->close();

// --- Pasar datos a la vista ---
require $_SERVER['DOCUMENT_ROOT'] . '/front-end/frames/panel-admin/admin-comentarios.php';
