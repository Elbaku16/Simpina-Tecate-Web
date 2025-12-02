<?php

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
