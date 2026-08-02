<?php
declare(strict_types=1);

require_once __DIR__ . '/../../front-end/includes/config.php';

if (file_exists(__DIR__ . '/../core/bootstrap_session.php')) {
    require_once __DIR__ . '/../core/bootstrap_session.php';
} else {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function usuario_autenticado(): bool
{
    return isset($_SESSION['uid'], $_SESSION['usuario'], $_SESSION['rol']);
}

function rol_es(string $rol): bool
{
    if (!usuario_autenticado()) {
        return false;
    }

    return hash_equals((string) $_SESSION['rol'], $rol);
}

function tiene_permiso(string $permiso): bool
{
    if (!usuario_autenticado()) {
        return false;
    }
    
    $rol = (string) $_SESSION['rol'];


    if ($rol === 'secretario_ejecutivo' || $rol === 'admin') {
        return true;
    }

    // Permisos específicos por rol
    $permisosPorRol = [
        'acompanamiento' => [
            'ver_panel', 
            'ver_resultados', 
            'modificar_encuesta',
            'modificar_comentarios',
            'eliminar_respuestas'
        ],
        'evaluacion' => [
            'ver_panel', 
            'ver_resultados'
        ],
    ];

    return in_array($permiso, $permisosPorRol[$rol] ?? []);
}


function requerir_admin(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    obtener_csrf();

    // 1. Si no está logueado -> Login
    if (!usuario_autenticado()) {
        // Ajusta esta ruta si tu login está en otro lado
        header('Location: ' . FRAMES_URL . 'admin/login.php');
        exit;
    }
    
    // 2. Si está logueado pero no tiene permiso de ver panel -> Inicio
    if (!tiene_permiso('ver_panel')) {
         header('Location: ' . FRAMES_URL . 'inicio/inicio.php');
         exit;
    }
}

function generar_csrf(string $formulario = 'default'): string
{
    $token = bin2hex(random_bytes(32));

    if (!isset($_SESSION['csrf_tokens'])) {
        $_SESSION['csrf_tokens'] = [];
    }

    $_SESSION['csrf_tokens'][$formulario] = [
        'valor'   => $token,
        'creado'  => time(),
    ];

    return $token;
}

function obtener_csrf(): string
{
    if (!isset($_SESSION['csrf']) || !is_string($_SESSION['csrf']) || strlen($_SESSION['csrf']) !== 64) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

function renovar_csrf(): string
{
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_tokens'] = [];
    return $_SESSION['csrf'];
}

function validar_csrf_global(?string $token): bool
{
    return is_string($token) && hash_equals(obtener_csrf(), $token);
}

function requerir_post_csrf(bool $json = false): void
{
    $metodo = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? null;

    if ($metodo === 'POST' && validar_csrf_global(is_string($token) ? $token : null)) {
        return;
    }

    http_response_code(403);
    if ($json) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'Solicitud de seguridad inválida. Recarga la página.']);
    } else {
        echo 'Solicitud de seguridad inválida. Recarga la página.';
    }
    exit;
}

function validar_csrf(?string $token, string $formulario = 'default'): bool
{
    if ($token === null) return false;
    if (!isset($_SESSION['csrf_tokens'][$formulario]['valor'])) {
        return false;
    }

    $tokenGuardado = (string) $_SESSION['csrf_tokens'][$formulario]['valor'];

    // Comparación segura
    $esValido = hash_equals($tokenGuardado, (string) $token);

    if ($esValido) {
        // Opcional: Invalidar token tras uso (para máxima seguridad, aunque a veces molesto en UX)
        // unset($_SESSION['csrf_tokens'][$formulario]); 
    }

    return $esValido;
}
?>
