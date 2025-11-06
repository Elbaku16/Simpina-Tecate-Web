<?php
/**
 * ==========================================
 * Archivo: login.php
 * Función: Procesa las credenciales de administrador
 * ==========================================
 *
 * Flujo general:
 * 1. Solo acepta método POST.
 * 2. Valida token CSRF incluido en el formulario.
 * 3. Lee 'email' y 'password' enviados.
 * 4. Busca el usuario en la BD (tabla usuarios).
 * 5. Si no existe o está inactivo → redirige con error.
 * 6. Verifica hash de contraseña con password_verify().
 * 7. Si es correcto:
 *     - Regenera ID de sesión.
 *     - Guarda uid, nombre, rol.
 *     - Redirige a panel-admin.php (si rol=admin).
 * 8. Si es incorrecto:
 *     - Incrementa contador de intentos.
 *     - Redirige a login con mensaje de error.
 *
 * Requiere:
 * - conexion-db.php para acceso a BD
 * - verificar-sesion.php para funciones de sesión
 */
declare(strict_types=1);

require_once __DIR__ . '/verificar-sesion.php';
require_once __DIR__ . '/../connect-db/conexion-db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /SIMPINNA/front-end/frames/admin/login.php?e=csrf');
    exit;
}

$tokenFormulario = $_POST['csrf_token'] ?? null;

if (!validar_csrf(is_string($tokenFormulario) ? $tokenFormulario : null, 'login_form')) {
    header('Location: /SIMPINNA/front-end/frames/admin/login.php?e=csrf');
    exit;
}

$usuario = trim((string) ($_POST['usuario'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($usuario === '' || $password === '') {
    header('Location: /SIMPINNA/front-end/frames/admin/login.php?e=credenciales');
    exit;
}

$maxIntentos = 5;
$tiempoBloqueo = 300;

if (!isset($_SESSION['login_intentos'])) {
    $_SESSION['login_intentos'] = 0;
}

if (!isset($_SESSION['login_bloqueado_hasta'])) {
    $_SESSION['login_bloqueado_hasta'] = 0;
}

if (is_numeric($_SESSION['login_bloqueado_hasta']) && (int) $_SESSION['login_bloqueado_hasta'] > time()) {
    header('Location: /SIMPINNA/front-end/frames/admin/login.php?e=credenciales');
    exit;
}

$consulta = $conn->prepare('SELECT id_admin, usuario, password FROM usuarios_admin WHERE usuario = ? LIMIT 1');

if ($consulta === false) {
    header('Location: /SIMPINNA/front-end/frames/admin/login.php?e=credenciales');
    exit;
}

$consulta->bind_param('s', $usuario);
$consulta->execute();
$resultado = $consulta->get_result();
$datosUsuario = $resultado ? $resultado->fetch_assoc() : null;
$consulta->close();

if (!$datosUsuario || !hash_equals((string) $datosUsuario['password'], $password)) {
    $_SESSION['login_intentos'] = (int) $_SESSION['login_intentos'] + 1;

    if ($_SESSION['login_intentos'] >= $maxIntentos) {
        $_SESSION['login_bloqueado_hasta'] = time() + $tiempoBloqueo;
        $_SESSION['login_intentos'] = 0;
    }

    header('Location: /SIMPINNA/front-end/frames/admin/login.php?e=credenciales');
    exit;
}

$_SESSION['login_intentos'] = 0;
$_SESSION['login_bloqueado_hasta'] = 0;

session_regenerate_id(true);

$_SESSION['uid'] = (int) $datosUsuario['id_admin'];
$_SESSION['usuario'] = (string) $datosUsuario['usuario'];
$_SESSION['rol'] = 'admin';
$_SESSION['last_activity'] = time();

header('Location: /SIMPINNA/front-end/frames/panel-admin/panel-admin.php');
exit;
