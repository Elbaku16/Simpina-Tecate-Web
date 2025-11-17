<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap_session.php';

/**
 * -----------------------------------------------------------------------------
 * Auth Helpers (procedurales pero organizados)
 * -----------------------------------------------------------------------------
 * Este archivo funciona como puente entre la sesión y el sistema de autorización.
 * No forma parte del controlador, porque se usa desde vistas y rutas.
 * -----------------------------------------------------------------------------
 */

/**
 * Indica si el usuario está autenticado.
 */
function usuario_autenticado(): bool
{
    return isset($_SESSION['uid'], $_SESSION['usuario'], $_SESSION['rol']);
}

/**
 * Verifica si el rol del usuario coincide con el proporcionado.
 */
function rol_es(string $rol): bool
{
    if (!usuario_autenticado()) {
        return false;
    }

    return hash_equals((string) $_SESSION['rol'], $rol);
}

/**
 * Requiere rol de administrador.
 */
function requerir_admin(): void
{
    // Asegurar que la sesión esté iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Crear token CSRF si no existe
    if (!isset($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    // Validar rol
    if (!rol_es('admin')) {
        header('Location: /SIMPINNA/front-end/frames/inicio/inicio.php');
        exit;
    }
}


/**
 * Genera un token CSRF para un formulario específico.
 */
function generar_csrf(string $formulario = 'default'): string
{
    $token = bin2hex(random_bytes(32));

    $_SESSION['csrf_tokens'][$formulario] = [
        'valor'   => $token,
        'creado'  => time(),
    ];

    return $token;
}

/**
 * Valida un token CSRF y lo invalida después de usarlo.
 */
function validar_csrf(?string $token, string $formulario = 'default'): bool
{
    if ($token === null) return false;

    if (!isset($_SESSION['csrf_tokens'][$formulario]['valor'])) {
        return false;
    }

    $tokenGuardado = (string) $_SESSION['csrf_tokens'][$formulario]['valor'];

    $esValido = hash_equals($tokenGuardado, (string) $token);

    if ($esValido) {
        unset($_SESSION['csrf_tokens'][$formulario]); // one-time use
    }

    return $esValido;
}
