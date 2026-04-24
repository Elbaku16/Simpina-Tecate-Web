<?php
declare(strict_types=1);

class DibujoHelper
{
    // Ruta relativa desde la raíz del proyecto
    private const RELATIVE_UPLOAD_PATH = '/uploads/dibujos';

    /**
     * Obtiene la ruta absoluta de la raíz del sistema de archivos del proyecto.
     * Calcula subir desde: /back-end/helpers/ -> /back-end/ -> / (Raíz)
     */
    private static function getProjectRoot(): string 
    {
        return dirname(__DIR__, 2); 
    }

    /**
     * Guarda un dibujo en formato base64 como archivo PNG
     */
    public static function guardar(string $base64Data, int $idRespuesta): string
    {
        if (empty($base64Data)) {
            throw new Exception("Datos de imagen vacíos");
        }

        // Limpiar el base64
        if (strpos($base64Data, ',') !== false) {
            $base64Data = explode(',', $base64Data)[1];
        }

        $imageData = base64_decode($base64Data);
        if ($imageData === false || strlen($imageData) === 0) {
            throw new Exception("Datos de imagen inválidos o corruptos");
        }

        if (!self::validarImagenPNG($imageData)) {
            throw new Exception("El archivo no es una imagen PNG válida");
        }

        // --- CAMBIO IMPORTANTE DE RUTA ---
        $year = date('Y');
        $month = date('m');
        
        // Usamos la ruta real del sistema basada en este archivo, no en la config del servidor
        $baseUploadDir = self::getProjectRoot() . self::RELATIVE_UPLOAD_PATH;
        $targetDir = "$baseUploadDir/$year/$month";

        if (!is_dir($targetDir)) {
            // 0755 es bueno, pero a veces necesitas 0777 si el usuario de PHP es distinto al dueño
            if (!mkdir($targetDir, 0755, true)) {
                throw new Exception("No se pudo crear el directorio: $targetDir");
            }
        }

        $fileName = self::generarNombreArchivo($idRespuesta);
        $filePath = $targetDir . "/" . $fileName;

        if (!file_put_contents($filePath, $imageData)) {
            throw new Exception("Error al escribir el archivo en: $filePath");
        }

        // Retornamos la ruta web (la que usará el navegador)
        // Ejemplo: /simpinna/uploads/dibujos/2024/02/archivo.png
        // IMPORTANTE: Ajusta '/simpinna' si tu proyecto cambia de nombre de carpeta
        return "/simpinna" . self::RELATIVE_UPLOAD_PATH . "/$year/$month/$fileName";
    }

    public static function eliminar(?string $ruta): bool
    {
        if (empty($ruta)) { return true; }

        // Quitamos '/simpinna' si viene en la ruta web para buscar en el disco
        $rutaLimpia = str_replace('/simpinna', '', $ruta);
        $filePath = self::getProjectRoot() . $rutaLimpia;

        if (!file_exists($filePath)) { return true; }

        return @unlink($filePath);
    }

    public static function existe(?string $ruta): bool
    {
        if (empty($ruta)) { return false; }
        
        $rutaLimpia = str_replace('/simpinna', '', $ruta);
        $filePath = self::getProjectRoot() . $rutaLimpia;
        
        return file_exists($filePath);
    }

    public static function obtenerInfo(string $ruta): ?array
    {
        if (!self::existe($ruta)) { return null; }

        $rutaLimpia = str_replace('/simpinna', '', $ruta);
        $filePath = self::getProjectRoot() . $rutaLimpia;

        return [
            'ruta' => $ruta,
            'tamaño' => filesize($filePath),
            'tamaño_legible' => self::formatearTamaño(filesize($filePath)),
            'fecha_modificacion' => filemtime($filePath),
            'extension' => pathinfo($filePath, PATHINFO_EXTENSION)
        ];
    }

    private static function generarNombreArchivo(int $idRespuesta): string
    {
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        return "resp_{$idRespuesta}_{$timestamp}_{$random}.png";
    }

    private static function validarImagenPNG(string $imageData): bool
    {
        $pngSignature = "\x89PNG\r\n\x1a\n";
        return substr($imageData, 0, 8) === $pngSignature;
    }

    private static function formatearTamaño(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}