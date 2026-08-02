<?php
declare(strict_types=1);

// Router seguro para el servidor integrado de PHP. Evita servir .env y
// delega los archivos existentes (incluidos los .php) al servidor.
$uri = rawurldecode((string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'));

if (str_contains($uri, "\0")
    || preg_match('#(?:^|/)(?:\.[^/]+|env\.txt)(?:/|$)#i', $uri) === 1
    || preg_match('#\.(?:sql|log|txt|bak|backup|old|ini|dist)$#i', $uri) === 1
    || preg_match('#^/(?:composer\.(?:json|lock)|README\.md)$#i', $uri) === 1
    || str_starts_with($uri, '/storage/')
    || str_starts_with($uri, '/vendor/')
    || str_starts_with($uri, '/bin/')
    || preg_match('#^/back-end/(?:auth|controllers|core|database|helpers|models|security|storage)(?:/|$)#i', $uri) === 1
    || str_starts_with($uri, '/front-end/includes/')
    || (str_starts_with($uri, '/uploads/')
        && preg_match('#\.(?:php[0-9]*|phtml|phar|cgi|pl|py|sh)$#i', $uri) === 1)) {
    http_response_code(404);
    exit('Not Found');
}

$requestedPath = realpath(__DIR__ . $uri);
$projectRoot = realpath(__DIR__);

if ($requestedPath !== false
    && $projectRoot !== false
    && ($requestedPath === $projectRoot || str_starts_with($requestedPath, $projectRoot . DIRECTORY_SEPARATOR))) {
    return false;
}

http_response_code(404);
exit('Not Found');
