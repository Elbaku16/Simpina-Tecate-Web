<?php
/**
 * ==========================================
 * Archivo: verificar-sesion.php
 * Función: Helpers de autenticación y roles
 * ==========================================
 *
 * Funciones esperadas:
 * - usuario_autenticado() → retorna true si hay sesión activa.
 * - rol_es($rol) → retorna true si el rol de sesión coincide.
 * - requerir_admin() → redirige a inicio.php si el usuario no es admin.
 * - generar_csrf() → crea token de formulario y lo guarda en sesión.
 * - validar_csrf($token) → verifica coincidencia del token.
 *
 * Este archivo se incluye en cualquier script que requiera control de acceso.
 * Se apoya en bootstrap_session.php para asegurar que la sesión esté activa.
 */
declare(strict_types=1);

require_once __DIR__ . '/bootstrap_session.php';

if (!isset($_SESSION['csrf_tokens']) || !is_array($_SESSION['csrf_tokens'])) {
    $_SESSION['csrf_tokens'] = [];
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

    $rolSesion = (string) $_SESSION['rol'];

    return hash_equals($rolSesion, $rol);
}

function requerir_admin(): void
{
    if (!rol_es('admin')) {
        header('Location: /SIMPINNA/front-end/frames/inicio/inicio.php');
        exit;
    }
}

function generar_csrf(string $formulario = 'default'): string
{
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_tokens'][$formulario] = [
        'valor' => $token,
        'creado' => time(),
    ];

    return $token;
}

function validar_csrf(?string $token, string $formulario = 'default'): bool
{
    if ($token === null) {
        return false;
    }

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
