#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Gestión de cuentas administrativas desde la terminal.
 *
 *   php bin/admin-password.php listar
 *   php bin/admin-password.php set    <usuario>
 *   php bin/admin-password.php crear  <usuario> <rol>
 *
 * La contraseña nunca se pasa como argumento (quedaría en el historial del
 * shell y en el listado de procesos): siempre se pide de forma interactiva.
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este comando solo puede ejecutarse desde la terminal.\n");
    exit(1);
}

require_once __DIR__ . '/../back-end/database/Conexion.php';
require_once __DIR__ . '/../back-end/security/PasswordService.php';

const ROLES_VALIDOS = ['secretario_ejecutivo', 'acompanamiento', 'evaluacion'];

function uso(): never
{
    fwrite(STDERR, <<<TXT
    Uso:
      php bin/admin-password.php listar
      php bin/admin-password.php set    <usuario>
      php bin/admin-password.php crear  <usuario> <rol>

    Roles válidos: secretario_ejecutivo, acompanamiento, evaluacion

    TXT);
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

function leerTexto(string $prompt): string
{
    fwrite(STDOUT, $prompt);
    return trim((string) fgets(STDIN));
}

/** Pide la contraseña dos veces y la valida contra PasswordService. */
function pedirPasswordNueva(): string
{
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

    return $password;
}

/**
 * Las columnas de la migración de seguridad pueden no existir todavía si este
 * comando se usa antes de aplicar las migraciones.
 */
function columnaExiste(mysqli $db, string $tabla, string $columna): bool
{
    $stmt = $db->prepare(
        'SELECT 1 FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1'
    );
    $stmt->bind_param('ss', $tabla, $columna);
    $stmt->execute();
    $existe = (bool) $stmt->get_result()->fetch_row();
    $stmt->close();
    return $existe;
}

function etiquetaHash(string $hash): string
{
    if (str_starts_with($hash, '$argon2id$')) return 'argon2id';
    if (str_starts_with($hash, '$argon2i$')) return 'argon2i';
    if (str_starts_with($hash, '$2y$')) return 'bcrypt';
    return 'SIN CIFRAR (!)';
}

$db = Conexion::getConexion();
$tieneSeguridad = columnaExiste($db, 'usuarios_admin', 'requiere_cambio_password')
    && columnaExiste($db, 'usuarios_admin', 'password_actualizada_en');

$comando = (string) ($argv[1] ?? '');

// ---------------------------------------------------------------------------
// listar
// ---------------------------------------------------------------------------
if ($comando === 'listar') {
    $result = $db->query('SELECT id_admin, usuario, nombre, rol, password FROM usuarios_admin ORDER BY id_admin');
    printf("%-4s %-20s %-26s %-22s %s\n", 'ID', 'USUARIO', 'NOMBRE', 'ROL', 'CONTRASEÑA');
    echo str_repeat('-', 100) . PHP_EOL;

    $secretarios = 0;
    $inseguras = 0;
    $total = 0;
    while ($row = $result->fetch_assoc()) {
        $total++;
        $etiqueta = etiquetaHash((string) $row['password']);
        if ($etiqueta === 'SIN CIFRAR (!)') {
            $inseguras++;
        }
        if ($row['rol'] === 'secretario_ejecutivo') {
            $secretarios++;
        }
        printf(
            "%-4d %-20s %-26s %-22s %s\n",
            (int) $row['id_admin'],
            (string) $row['usuario'],
            (string) ($row['nombre'] ?? '(sin nombre)'),
            (string) ($row['rol'] ?? '(SIN ROL)'),
            $etiqueta
        );
    }

    echo PHP_EOL . "Cuentas: {$total}. Con rol secretario_ejecutivo: {$secretarios}.\n";
    if (!$tieneSeguridad) {
        echo "Aviso: faltan las columnas de la migración de seguridad (requiere_cambio_password).\n";
    }
    if ($secretarios === 0) {
        fwrite(STDERR, "Aviso: no hay ninguna cuenta con rol secretario_ejecutivo.\n");
    }
    if ($inseguras > 0) {
        fwrite(STDERR, "Aviso: {$inseguras} cuenta(s) con la contraseña sin cifrar. Restablécelas con 'set'.\n");
        exit(1);
    }
    exit(0);
}

// ---------------------------------------------------------------------------
// set
// ---------------------------------------------------------------------------
if ($comando === 'set') {
    $usuario = trim((string) ($argv[2] ?? ''));
    if ($usuario === '') {
        uso();
    }

    $stmt = $db->prepare('SELECT id_admin FROM usuarios_admin WHERE usuario = ? LIMIT 1');
    $stmt->bind_param('s', $usuario);
    $stmt->execute();
    $id = $stmt->get_result()->fetch_column();
    $stmt->close();

    if (!$id) {
        fwrite(STDERR, "No existe el usuario indicado.\n");
        exit(1);
    }

    $hash = PasswordService::hash(pedirPasswordNueva());
    $idAdmin = (int) $id;

    $sql = $tieneSeguridad
        ? 'UPDATE usuarios_admin
           SET password = ?, requiere_cambio_password = 0, password_actualizada_en = NOW()
           WHERE id_admin = ?'
        : 'UPDATE usuarios_admin SET password = ? WHERE id_admin = ?';

    $stmt = $db->prepare($sql);
    $stmt->bind_param('si', $hash, $idAdmin);
    $stmt->execute();
    $stmt->close();

    echo "Contraseña actualizada de forma segura para {$usuario}.\n";
    exit(0);
}

// ---------------------------------------------------------------------------
// crear
// ---------------------------------------------------------------------------
if ($comando === 'crear') {
    $usuario = trim((string) ($argv[2] ?? ''));
    $rol = trim((string) ($argv[3] ?? ''));
    if ($usuario === '' || $rol === '') {
        uso();
    }

    if (!in_array($rol, ROLES_VALIDOS, true)) {
        fwrite(STDERR, "Rol no válido. Usa uno de: " . implode(', ', ROLES_VALIDOS) . PHP_EOL);
        exit(1);
    }

    $stmt = $db->prepare('SELECT 1 FROM usuarios_admin WHERE usuario = ? LIMIT 1');
    $stmt->bind_param('s', $usuario);
    $stmt->execute();
    $existe = (bool) $stmt->get_result()->fetch_row();
    $stmt->close();

    if ($existe) {
        fwrite(STDERR, "Ya existe una cuenta con ese usuario. Usa 'set' para cambiarle la contraseña.\n");
        exit(1);
    }

    $nombre = leerTexto('Nombre para mostrar: ');
    if ($nombre === '') {
        fwrite(STDERR, "El nombre no puede quedar vacío.\n");
        exit(1);
    }

    $hash = PasswordService::hash(pedirPasswordNueva());

    if ($tieneSeguridad) {
        $stmt = $db->prepare(
            'INSERT INTO usuarios_admin
               (usuario, password, nombre, rol, requiere_cambio_password, password_actualizada_en)
             VALUES (?, ?, ?, ?, 0, NOW())'
        );
    } else {
        $stmt = $db->prepare(
            'INSERT INTO usuarios_admin (usuario, password, nombre, rol) VALUES (?, ?, ?, ?)'
        );
    }
    $stmt->bind_param('ssss', $usuario, $hash, $nombre, $rol);
    $stmt->execute();
    $nuevoId = $db->insert_id;
    $stmt->close();

    echo "Cuenta administrativa creada: {$usuario} (id {$nuevoId}, rol {$rol}).\n";
    exit(0);
}

uso();
