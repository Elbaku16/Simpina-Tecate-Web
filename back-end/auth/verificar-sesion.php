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

    // Inicializar CSRF global si no existe (por seguridad)
    if (!isset($_SESSION['csrf'])) {
        try {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        } catch (Exception $e) {
            $_SESSION['csrf'] = md5(uniqid((string)mt_rand(), true));
        }
    }

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
    try {
        $token = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        $token = md5(uniqid((string)mt_rand(), true));
    }

    if (!isset($_SESSION['csrf_tokens'])) {
        $_SESSION['csrf_tokens'] = [];
    }

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

    // Comparación segura
    $esValido = hash_equals($tokenGuardado, (string) $token);

    if ($esValido) {
        // Opcional: Invalidar token tras uso (para máxima seguridad, aunque a veces molesto en UX)
        // unset($_SESSION['csrf_tokens'][$formulario]); 
    }

    return $esValido;
}
?>