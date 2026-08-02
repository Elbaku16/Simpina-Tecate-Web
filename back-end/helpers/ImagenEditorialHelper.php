<?php
declare(strict_types=1);

final class ImagenEditorialHelper
{
    private const MAX_BYTES = 4 * 1024 * 1024;
    private const MAX_DIMENSION = 4096;

    /** @return array{ruta:string,absoluta:string} */
    public static function guardar(array $archivo, string $categoria): array
    {
        if (!in_array($categoria, ['preguntas', 'opciones'], true)) {
            throw new InvalidArgumentException('Categoría de imagen inválida.');
        }
        if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('No se recibió una imagen válida.');
        }
        $tmp = (string) ($archivo['tmp_name'] ?? '');
        $size = (int) ($archivo['size'] ?? 0);
        if ($size <= 0 || $size > self::MAX_BYTES || !is_uploaded_file($tmp)) {
            throw new InvalidArgumentException('La imagen supera 4 MB o no es una carga válida.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);
        $tipos = [
            'image/jpeg' => ['extension' => 'jpg', 'salida' => IMAGETYPE_JPEG],
            'image/png' => ['extension' => 'png', 'salida' => IMAGETYPE_PNG],
            'image/webp' => ['extension' => 'webp', 'salida' => IMAGETYPE_WEBP],
        ];
        if (!is_string($mime) || !isset($tipos[$mime])) {
            throw new InvalidArgumentException('Solo se permiten imágenes JPEG, PNG o WebP.');
        }

        $info = getimagesize($tmp);
        if ($info === false || $info[0] <= 0 || $info[1] <= 0
            || $info[0] > self::MAX_DIMENSION || $info[1] > self::MAX_DIMENSION) {
            throw new InvalidArgumentException('La imagen tiene dimensiones inválidas o supera 4096 px.');
        }

        $bytes = file_get_contents($tmp);
        $imagen = is_string($bytes) ? imagecreatefromstring($bytes) : false;
        if ($imagen === false) {
            throw new InvalidArgumentException('El contenido de la imagen está corrupto.');
        }

        $root = dirname(__DIR__, 2);
        $dir = $root . '/uploads/' . $categoria;
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            unset($imagen);
            throw new RuntimeException('No se pudo preparar el almacenamiento de imágenes.');
        }

        $extension = $tipos[$mime]['extension'];
        $nombre = bin2hex(random_bytes(24)) . '.' . $extension;
        $destino = $dir . '/' . $nombre;
        $ok = match ($tipos[$mime]['salida']) {
            IMAGETYPE_JPEG => imagejpeg($imagen, $destino, 85),
            IMAGETYPE_PNG => imagepng($imagen, $destino, 6),
            IMAGETYPE_WEBP => imagewebp($imagen, $destino, 85),
            default => false,
        };
        unset($imagen);

        if (!$ok) {
            throw new RuntimeException('No se pudo procesar la imagen.');
        }
        chmod($destino, 0644);

        return [
            'ruta' => 'uploads/' . $categoria . '/' . $nombre,
            'absoluta' => $destino,
        ];
    }

    public static function eliminarAbsoluta(string $ruta): void
    {
        if (is_file($ruta)) {
            @unlink($ruta);
        }
    }
}
