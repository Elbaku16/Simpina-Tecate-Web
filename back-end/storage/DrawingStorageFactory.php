<?php
declare(strict_types=1);

require_once __DIR__ . '/DrawingStorage.php';
require_once __DIR__ . '/LocalDrawingStorage.php';
require_once __DIR__ . '/R2DrawingStorage.php';

final class DrawingStorageFactory
{
    public static function configured(): DrawingStorage
    {
        $driver = strtolower(trim((string) (getenv('DRAWING_STORAGE_DRIVER') ?: 'local')));
        return self::for($driver);
    }

    public static function for(string $driver): DrawingStorage
    {
        return match ($driver) {
            'local' => new LocalDrawingStorage(),
            'r2' => new R2DrawingStorage(),
            default => throw new RuntimeException('Controlador de dibujos no reconocido.'),
        };
    }
}
