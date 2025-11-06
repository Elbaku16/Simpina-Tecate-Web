<?php
/**
 * ==========================================
 * Archivo: bootstrap_session.php
 * Función: Inicializa la sesión con cookies seguras
 * ==========================================
 *
 * - Se ejecuta al inicio de cualquier script que use $_SESSION.
 * - Configura los parámetros de cookie (secure, httponly, samesite).
 * - Inicia la sesión si no está activa.
 * - Regenera el ID de sesión al inicio (protege contra fijación).
 * - Puede almacenar marcas como:
 *    __init → marca de inicio
 *    last_activity → tiempo de última acción
 * - No produce salida HTML.
 */
declare(strict_types=1);

if (session_status() === PHP_SESSION_DISABLED) {
    throw new RuntimeException('Las sesiones están deshabilitadas en el servidor.');
}

if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? null) === '443');

    $cookieParams = session_get_cookie_params();

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $cookieParams['path'] ?? '/',
        'domain' => $cookieParams['domain'] ?? '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

if (!isset($_SESSION['__init'])) {
    session_regenerate_id(true);
    $_SESSION['__init'] = time();
}

$_SESSION['last_activity'] = time();
