#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Verificación de extremo a extremo del bucket de Cloudflare R2.
 *
 * Sube un objeto temporal, comprueba su tamaño, genera una URL firmada, la
 * descarga, confirma que el acceso directo sin firma queda rechazado y borra
 * el objeto. No toca la base de datos ni ningún dibujo real.
 *
 * Uso: php bin/verificar-r2.php
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este comando solo puede ejecutarse desde la terminal.\n");
    exit(1);
}

require_once __DIR__ . '/../back-end/core/env_loader.php';
cargarEnv(__DIR__ . '/../.env');
require_once __DIR__ . '/../back-end/storage/DrawingStorageFactory.php';

$fallos = 0;
$paso = 0;

function informar(bool $ok, string $titulo, string $detalle = ''): void
{
    global $fallos, $paso;
    $paso++;
    $marca = $ok ? 'OK  ' : 'FALLA';
    printf("[%s] %d. %s%s\n", $marca, $paso, $titulo, $detalle === '' ? '' : " — {$detalle}");
    if (!$ok) {
        $fallos++;
    }
}

// ---------------------------------------------------------------------------
// 0. Configuración
// ---------------------------------------------------------------------------
$driver = strtolower(trim((string) (getenv('DRAWING_STORAGE_DRIVER') ?: 'local')));
if ($driver !== 'r2') {
    fwrite(STDERR, "DRAWING_STORAGE_DRIVER es '{$driver}', se esperaba 'r2'.\n");
    fwrite(STDERR, "Ajusta el .env antes de ejecutar esta verificación.\n");
    exit(1);
}

$account = trim((string) getenv('R2_ACCOUNT_ID'));
$bucket = trim((string) getenv('R2_BUCKET'));

try {
    $storage = DrawingStorageFactory::for('r2');
    informar(true, 'Configuración de R2 cargada', "bucket «{$bucket}»");
} catch (Throwable $e) {
    informar(false, 'Configuración de R2', $e->getMessage());
    exit(1);
}

// ---------------------------------------------------------------------------
// 1. PNG de prueba
// ---------------------------------------------------------------------------
$imagen = imagecreatetruecolor(8, 8);
imagesavealpha($imagen, true);
ob_start();
imagepng($imagen, null, 6);
$contenido = (string) ob_get_clean();
unset($imagen);
$bytes = strlen($contenido);
$clave = 'diagnostico/' . date('Ymd_His') . '-' . bin2hex(random_bytes(8)) . '.png';
informar($bytes > 0, 'PNG de prueba generado', "{$bytes} bytes");

// ---------------------------------------------------------------------------
// 2. Subida
// ---------------------------------------------------------------------------
try {
    $storage->put($clave, $contenido, 'image/png');
    informar(true, 'Subida del objeto', $clave);
} catch (Throwable $e) {
    informar(false, 'Subida del objeto', $e->getMessage());
    exit(1);
}

// ---------------------------------------------------------------------------
// 3. Tamaño y existencia
// ---------------------------------------------------------------------------
$tamano = $storage->size($clave);
informar($tamano === $bytes, 'Tamaño confirmado por R2', "remoto={$tamano}, local={$bytes}");
informar($storage->exists($clave), 'exists() reporta el objeto');

// ---------------------------------------------------------------------------
// 4. URL firmada
// ---------------------------------------------------------------------------
$url = $storage->temporaryUrl($clave, 60);
informar(is_string($url) && $url !== '', 'URL firmada generada');

if (is_string($url) && $url !== '') {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FAILONERROR => false,
    ]);
    $descarga = curl_exec($ch);
    $codigo = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $errorCurl = curl_error($ch);
    curl_close($ch);

    informar($codigo === 200, 'Descarga con URL firmada', "HTTP {$codigo}" . ($errorCurl !== '' ? " ({$errorCurl})" : ''));
    informar(
        is_string($descarga) && $descarga === $contenido,
        'El contenido descargado coincide byte a byte'
    );

    // La firma va en la query string: al quitarla, R2 debe rechazar.
    $sinFirma = strtok($url, '?');
    $ch = curl_init($sinFirma);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_NOBODY => true,
    ]);
    curl_exec($ch);
    $codigoSinFirma = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    informar(
        in_array($codigoSinFirma, [400, 401, 403, 404], true),
        'El bucket rechaza el acceso directo sin firma',
        "HTTP {$codigoSinFirma}"
    );
}

// ---------------------------------------------------------------------------
// 5. Borrado
// ---------------------------------------------------------------------------
try {
    $storage->delete($clave);
    informar(true, 'Borrado del objeto temporal');
} catch (Throwable $e) {
    informar(false, 'Borrado del objeto temporal', $e->getMessage());
}

informar($storage->size($clave) === null, 'El objeto ya no existe tras borrarlo');

// ---------------------------------------------------------------------------
// Resumen
// ---------------------------------------------------------------------------
echo PHP_EOL;
if ($fallos === 0) {
    echo "R2 quedó verificado: subida, tamaño, URL firmada, rechazo sin firma y borrado.\n";
    exit(0);
}

fwrite(STDERR, "Verificación incompleta: {$fallos} comprobación(es) fallaron.\n");
exit(1);
