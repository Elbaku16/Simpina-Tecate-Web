<?php
declare(strict_types=1);

require_once __DIR__ . '/../storage/DrawingStorageFactory.php';

final class DibujoHelper
{
    private const MAX_BYTES = 5 * 1024 * 1024;
    private const MAX_DIMENSION = 2048;

    /** @return array{driver:string,key:string,bytes:int} */
    public static function guardar(string $base64Data, int $idRespuesta): array
    {
        $bytes = self::decodificar($base64Data);
        $storage = DrawingStorageFactory::configured();
        $key = self::nuevaClave();
        $storage->put($key, $bytes, 'image/png');
        return ['driver' => $storage->driver(), 'key' => $key, 'bytes' => strlen($bytes)];
    }

    public static function eliminar(?string $driver, ?string $key): void
    {
        if (!$driver || !$key) return;
        DrawingStorageFactory::for($driver)->delete($key);
    }

    public static function existe(?string $driver, ?string $key): bool
    {
        return $driver && $key ? DrawingStorageFactory::for($driver)->exists($key) : false;
    }

    public static function rutaLocal(?string $driver, ?string $key): ?string
    {
        return $driver && $key ? DrawingStorageFactory::for($driver)->localPath($key) : null;
    }

    public static function urlTemporal(?string $driver, ?string $key, int $segundos = 60): ?string
    {
        return $driver && $key ? DrawingStorageFactory::for($driver)->temporaryUrl($key, $segundos) : null;
    }

    public static function nuevaClave(): string
    {
        return 'drawings/' . date('Y/m') . '/' . self::uuid() . '.png';
    }

    public static function rutaLegacy(?string $ruta): ?string
    {
        if (!$ruta) return null;
        $path = parse_url($ruta, PHP_URL_PATH) ?: $ruta;
        $base = defined('BASE_URL') ? rtrim((string) BASE_URL, '/') : '';
        if ($base !== '' && str_starts_with($path, $base . '/')) $path = substr($path, strlen($base));
        if (str_starts_with($path, '/simpinna/')) $path = substr($path, strlen('/simpinna'));
        $path = '/' . ltrim($path, '/');
        if (!preg_match('#^/uploads/dibujos/[0-9]{4}/[0-9]{2}/[A-Za-z0-9_.-]+\.png$#', $path)) return null;
        $absoluta = dirname(__DIR__, 2) . $path;
        return is_file($absoluta) ? $absoluta : null;
    }

    public static function leerLegacy(string $ruta): string
    {
        $path = self::rutaLegacy($ruta);
        $bytes = $path ? file_get_contents($path) : false;
        if (!is_string($bytes)) throw new RuntimeException('No se encontró el dibujo local.');
        return self::normalizarLegacy($bytes);
    }

    private static function decodificar(string $data): string
    {
        if (str_contains($data, ',')) [, $data] = explode(',', $data, 2);
        $estimado = (int) (strlen($data) * 0.75);
        if ($estimado > self::MAX_BYTES) throw new InvalidArgumentException('El dibujo supera 5 MB.');
        $bytes = base64_decode($data, true);
        if (!is_string($bytes)) throw new InvalidArgumentException('El dibujo no tiene base64 válido.');
        self::validarBytes($bytes);
        return $bytes;
    }

    private static function validarBytes(string $bytes): void
    {
        if (strlen($bytes) === 0 || strlen($bytes) > self::MAX_BYTES || !str_starts_with($bytes, "\x89PNG\r\n\x1a\n")) {
            throw new InvalidArgumentException('El dibujo no es un PNG válido.');
        }
        $info = getimagesizefromstring($bytes);
        if ($info === false || ($info['mime'] ?? '') !== 'image/png'
            || $info[0] > self::MAX_DIMENSION || $info[1] > self::MAX_DIMENSION) {
            throw new InvalidArgumentException('El dibujo supera 2048 px o está corrupto.');
        }
        $imagen = imagecreatefromstring($bytes);
        if ($imagen === false) {
            throw new InvalidArgumentException('El dibujo PNG está corrupto.');
        }
        unset($imagen);
    }

    private static function normalizarLegacy(string $bytes): string
    {
        if (strlen($bytes) === 0 || strlen($bytes) > self::MAX_BYTES || !str_starts_with($bytes, "\x89PNG\r\n\x1a\n")) {
            throw new InvalidArgumentException('El dibujo histórico no es un PNG válido.');
        }
        $info = getimagesizefromstring($bytes);
        if ($info === false || ($info['mime'] ?? '') !== 'image/png') {
            throw new InvalidArgumentException('El dibujo histórico está corrupto.');
        }
        $ancho = (int) $info[0];
        $alto = (int) $info[1];
        if ($ancho < 1 || $alto < 1 || $ancho > 4096 || $alto > 4096 || $ancho * $alto > 16000000) {
            throw new InvalidArgumentException('El dibujo histórico excede los límites seguros de conversión.');
        }

        $origen = imagecreatefromstring($bytes);
        if ($origen === false) {
            throw new InvalidArgumentException('El dibujo histórico no se pudo decodificar.');
        }
        $escala = min(1, self::MAX_DIMENSION / max($ancho, $alto));
        $destinoAncho = max(1, (int) round($ancho * $escala));
        $destinoAlto = max(1, (int) round($alto * $escala));
        $destino = imagecreatetruecolor($destinoAncho, $destinoAlto);
        if ($destino === false) {
            unset($origen);
            throw new RuntimeException('No se pudo preparar el dibujo histórico.');
        }
        imagealphablending($destino, false);
        imagesavealpha($destino, true);
        $transparente = imagecolorallocatealpha($destino, 0, 0, 0, 127);
        imagefilledrectangle($destino, 0, 0, $destinoAncho, $destinoAlto, $transparente);
        imagecopyresampled(
            $destino,
            $origen,
            0,
            0,
            0,
            0,
            $destinoAncho,
            $destinoAlto,
            $ancho,
            $alto
        );
        unset($origen);

        ob_start();
        $ok = imagepng($destino, null, 6);
        $normalizado = ob_get_clean();
        unset($destino);
        if (!$ok || !is_string($normalizado)) {
            throw new RuntimeException('No se pudo normalizar el dibujo histórico.');
        }
        self::validarBytes($normalizado);
        return $normalizado;
    }

    private static function uuid(): string
    {
        $b = random_bytes(16);
        $b[6] = chr((ord($b[6]) & 0x0f) | 0x40);
        $b[8] = chr((ord($b[8]) & 0x3f) | 0x80);
        $h = bin2hex($b);
        return substr($h,0,8).'-'.substr($h,8,4).'-'.substr($h,12,4).'-'.substr($h,16,4).'-'.substr($h,20);
    }
}
