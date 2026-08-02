<?php
declare(strict_types=1);

final class LocalDrawingStorage implements DrawingStorage
{
    public function driver(): string { return 'local'; }

    public function put(string $key, string $bytes, string $contentType): void
    {
        $path = $this->path($key);
        $dir = dirname($path);
        if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) {
            throw new RuntimeException('No se pudo crear el almacenamiento privado local.');
        }
        if (file_put_contents($path, $bytes, LOCK_EX) === false) {
            throw new RuntimeException('No se pudo guardar el dibujo.');
        }
        chmod($path, 0640);
    }

    public function delete(string $key): void
    {
        $path = $this->path($key);
        if (is_file($path) && !unlink($path)) {
            throw new RuntimeException('No se pudo eliminar el dibujo local.');
        }
    }

    public function exists(string $key): bool { return is_file($this->path($key)); }
    public function size(string $key): ?int
    {
        $path = $this->path($key);
        $size = is_file($path) ? filesize($path) : false;
        return $size === false ? null : (int) $size;
    }
    public function temporaryUrl(string $key, int $seconds): ?string { return null; }
    public function localPath(string $key): ?string { return $this->path($key); }

    private function path(string $key): string
    {
        if (!preg_match('#^drawings/[0-9]{4}/[0-9]{2}/[a-f0-9-]{36}\.png$#', $key)) {
            throw new InvalidArgumentException('Clave de dibujo inválida.');
        }
        return dirname(__DIR__, 2) . '/storage/' . $key;
    }
}
