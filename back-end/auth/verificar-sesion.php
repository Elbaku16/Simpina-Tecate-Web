<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap_session.php';

/**
 * -----------------------------------------------------------------------------
 * Auth Helpers
 * -----------------------------------------------------------------------------
 */

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

/**
 * Define y verifica permisos.
 * AHORA: 'secretario_ejecutivo' tiene el control total (antes 'admin').
 */
function tiene_permiso(string $permiso): bool
{
    if (!usuario_autenticado()) {
        return false;
    }
    
    $rol = (string) $_SESSION['rol'];

    // CAMBIO AQUÍ: El rol 'secretario_ejecutivo' siempre tiene todos los permisos.
    if ($rol === 'secretario_ejecutivo') {
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

/**
 * Requiere acceso al panel.
 * (Mantenemos el nombre de la función 'requerir_admin' para no romper
 * el código en otros archivos, aunque el rol ahora sea secretario_ejecutivo).
 */
function requerir_admin(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    if (!usuario_autenticado()) {
        header('Location: /front-end/frames/admin/login.php');
        exit;
    }
    
    if (!tiene_permiso('ver_panel')) {
         header('Location: /front-end/frames/inicio/inicio.php');
         exit;
    }
}

function generar_csrf(string $formulario = 'default'): string
{
    $token = bin2hex(random_bytes(32));

    $_SESSION['csrf_tokens'][$formulario] = [
        'valor'   => $token,
        'creado'  => time(),
    ];

    return $token;
}

function validar_csrf(?string $token, string $formulario = 'default'): bool
{
    if ($token === null) return false;

    if (!isset($_SESSION['csrf_tokens'][$formulario]['valor'])) {
        return false;
    }

    $tokenGuardado = (string) $_SESSION['csrf_tokens'][$formulario]['valor'];

    $esValido = hash_equals($tokenGuardado, (string) $token);

    if ($esValido) {
        unset($_SESSION['csrf_tokens'][$formulario]); 
    }

    return $esValido;
}