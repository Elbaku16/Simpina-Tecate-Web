#!/usr/bin/env php
<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit(1);
}

require_once __DIR__ . '/../back-end/core/env_loader.php';
cargarEnv(__DIR__ . '/../.env');

$destino = $argv[1] ?? '';
if ($destino === '') {
    fwrite(STDERR, "Uso: php bin/backup-database.php /ruta/fuera-del-sitio/respaldo.sql\n");
    exit(1);
}

$directorio = realpath(dirname($destino));
if ($directorio === false || !is_dir($directorio) || !is_writable($directorio)) {
    fwrite(STDERR, "El directorio de destino no existe o no permite escritura.\n");
    exit(1);
}

$archivo = $directorio . DIRECTORY_SEPARATOR . basename($destino);
if (pathinfo($archivo, PATHINFO_EXTENSION) !== 'sql') {
    fwrite(STDERR, "El respaldo debe terminar en .sql.\n");
    exit(1);
}

$entorno = getenv();
$entorno['MYSQL_PWD'] = (string) getenv('DB_PASS');
$comando = [
    'mysqldump',
    '--no-defaults',
    '--host=' . (string) getenv('DB_HOST'),
    '--user=' . (string) getenv('DB_USER'),
    '--default-character-set=' . (getenv('DB_CHARSET') ?: 'utf8mb4'),
    '--single-transaction',
    '--skip-lock-tables',
    '--no-tablespaces',
    '--set-gtid-purged=OFF',
    '--triggers',
    '--result-file=' . $archivo,
    (string) getenv('DB_NAME'),
];

$descriptores = [
    0 => ['file', '/dev/null', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];
$proceso = proc_open($comando, $descriptores, $pipes, null, $entorno);
if (!is_resource($proceso)) {
    fwrite(STDERR, "No se pudo iniciar mysqldump.\n");
    exit(1);
}
fclose($pipes[1]);
$error = stream_get_contents($pipes[2]);
fclose($pipes[2]);
$codigo = proc_close($proceso);
if ($codigo !== 0 || !is_file($archivo) || filesize($archivo) === 0) {
    @unlink($archivo);
    fwrite(STDERR, "No se pudo crear el respaldo. " . trim((string) $error) . "\n");
    exit(1);
}
chmod($archivo, 0600);
echo $archivo . PHP_EOL;
