<?php
/**
 * ==========================================
 * Archivo: logout.php
 * Función: Cierra la sesión de forma segura
 * ==========================================
 *
 * Flujo general:
 * 1. Elimina todas las variables de sesión.
 * 2. Invalida la cookie de sesión en el navegador.
 * 3. Destruye la sesión del servidor.
 * 4. Redirige a inicio.php con un parámetro (?out=1).
 *
 * Debe poder ejecutarse incluso si no hay sesión activa.
 */
declare(strict_types=1);

require_once __DIR__ . '/bootstrap_session.php';

if (session_status() === PHP_SESSION_ACTIVE) {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'] ?? '/',
            'domain' => $params['domain'] ?? '',
            'secure' => (bool) ($params['secure'] ?? false),
            'httponly' => true,
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }

    session_destroy();
}

header('Location: /SIMPINNA/front-end/frames/inicio/inicio.php?out=1');
exit;
