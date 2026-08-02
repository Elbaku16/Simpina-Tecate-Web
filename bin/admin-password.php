#!/usr/bin/env php
<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este comando solo puede ejecutarse desde la terminal.\n");
    exit(1);
}

require_once __DIR__ . '/../back-end/database/Conexion.php';
require_once __DIR__ . '/../back-end/security/PasswordService.php';

if (($argv[1] ?? '') !== 'set' || empty($argv[2])) {
    fwrite(STDERR, "Uso: php bin/admin-password.php set <usuario>\n");
    exit(1);
}

$usuario = trim((string) $argv[2]);
$db = Conexion::getConexion();
$stmt = $db->prepare('SELECT id_admin FROM usuarios_admin WHERE usuario = ? LIMIT 1');
$stmt->bind_param('s', $usuario);
$stmt->execute();
$id = $stmt->get_result()->fetch_column();
$stmt->close();

if (!$id) {
    fwrite(STDERR, "No existe el usuario indicado.\n");
    exit(1);
}

function leerSecreto(string $prompt): string
{
    fwrite(STDOUT, $prompt);
    $ocultar = DIRECTORY_SEPARATOR === '/' && function_exists('shell_exec');
    if ($ocultar) {
        shell_exec('stty -echo');
    }
    try {
        $valor = fgets(STDIN);
    } finally {
        if ($ocultar) {
            shell_exec('stty echo');
        }
        fwrite(STDOUT, PHP_EOL);
    }
    return rtrim((string) $valor, "\r\n");
}

$password = leerSecreto('Nueva contraseña: ');
$confirmacion = leerSecreto('Confirma la contraseña: ');

if (!hash_equals($password, $confirmacion)) {
    fwrite(STDERR, "Las contraseñas no coinciden.\n");
    exit(1);
}

$error = PasswordService::validar($password);
if ($error !== null) {
    fwrite(STDERR, $error . PHP_EOL);
    exit(1);
}

$hash = PasswordService::hash($password);
$idAdmin = (int) $id;
$stmt = $db->prepare(
    'UPDATE usuarios_admin
     SET password = ?, requiere_cambio_password = 0, password_actualizada_en = NOW()
     WHERE id_admin = ?'
);
$stmt->bind_param('si', $hash, $idAdmin);
$stmt->execute();
$stmt->close();

echo "Contraseña actualizada de forma segura para {$usuario}.\n";
