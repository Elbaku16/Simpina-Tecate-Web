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
 * Define y verifica permisos:
 * - admin: Total.
 * - acompanamiento: Puede hacer todo lo del Admin, menos gestionar usuarios.
 * - evaluacion: Solo lectura.
 */
function tiene_permiso(string $permiso): bool
{
    if (!usuario_autenticado()) {
        return false;
    }
    
    $rol = (string) $_SESSION['rol'];

    // El rol 'admin' siempre tiene todos los permisos.
    if ($rol === 'admin') {
        return true;
    }

    // Permisos específicos por rol
    $permisosPorRol = [
        // Puede ver y hacer todo menos gestionar usuarios (Crear/Modificar)
        'acompanamiento' => [
            'ver_panel', 
            'ver_resultados', 
            'modificar_encuesta', // Permiso para modificar encuestas
            'modificar_comentarios', // Permiso para cambiar estado de comentarios
            'eliminar_respuestas' // Permiso para eliminar respuestas
        ],
        // Rol de solo lectura
        'evaluacion' => [
            'ver_panel', 
            'ver_resultados' // Solo puede ver resultados y el panel
        ],
    ];

    // Si el permiso requerido está en la lista de permisos del rol, es TRUE.
    return in_array($permiso, $permisosPorRol[$rol] ?? []);
}

/**
 * Requiere rol de administrador (anteriormente usado para acceso total).
 * Ahora redirige si no tiene el permiso básico de ver el panel.
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

    // Validar acceso: Ahora cualquier usuario administrativo puede entrar al panel, 
    // pero las acciones específicas requerirán 'tiene_permiso'.
    if (!usuario_autenticado()) {
        header('Location: /front-end/frames/admin/login.php');
        exit;
    }
    
    // Si no tiene permiso básico, lo forzamos al inicio (esto es para roles no definidos)
    if (!tiene_permiso('ver_panel')) {
         header('Location: /front-end/frames/inicio/inicio.php');
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