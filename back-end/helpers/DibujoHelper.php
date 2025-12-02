<?php
/**
 * DibujoHelper.php
 * Helper para guardar, eliminar y gestionar archivos de dibujos
 * 
 * Ubicación: /back-end/helpers/DibujoHelper.php
 */

declare(strict_types=1);

class DibujoHelper
{
    /**
     * Directorio base para los dibujos (relativo a DOCUMENT_ROOT)
     */
    private const BASE_DIR = '/uploads/dibujos';

    /**
     * Guarda un dibujo en formato base64 como archivo PNG
     * 
     * @param string $base64Data Datos en formato base64 (con o sin prefijo data:image)
     * @param int $idRespuesta ID de la respuesta para el nombre del archivo
     * @return string Ruta relativa del archivo guardado
     * @throws Exception Si hay error al guardar
     */
    public static function guardar(string $base64Data, int $idRespuesta): string
    {
        if (empty($base64Data)) {
            throw new Exception("Datos de imagen vacíos");
        }

        // Limpiar el base64 (remover prefijo data:image/png;base64, si existe)
        if (strpos($base64Data, ',') !== false) {
            $base64Data = explode(',', $base64Data)[1];
        }

        // Decodificar
        $imageData = base64_decode($base64Data);
        if ($imageData === false || strlen($imageData) === 0) {
            throw new Exception("Datos de imagen inválidos o corruptos");
        }

        // Validar que sea una imagen PNG válida (opcional pero recomendado)
        if (!self::validarImagenPNG($imageData)) {
            throw new Exception("El archivo no es una imagen PNG válida");
        }

        // Crear estructura de carpetas por año/mes
        $year = date('Y');
        $month = date('m');
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . self::BASE_DIR . "/$year/$month";

        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                throw new Exception("No se pudo crear el directorio: $uploadDir");
            }
        }

        // Generar nombre único y seguro
        $fileName = self::generarNombreArchivo($idRespuesta);
        $filePath = $uploadDir . "/" . $fileName;

        // Guardar archivo
        if (!file_put_contents($filePath, $imageData)) {
            throw new Exception("Error al escribir el archivo en: $filePath");
        }

        // Verificar que el archivo se guardó correctamente
        if (!file_exists($filePath)) {
            throw new Exception("El archivo no existe después de guardarlo");
        }

        // Retornar ruta relativa (desde )
        return self::BASE_DIR . "/$year/$month/$fileName";
    }

    /**
     * Elimina un archivo de dibujo del sistema
     * 
     * @param string|null $ruta Ruta relativa del archivo
     * @return bool True si se eliminó o no existía, False si hubo error
     */
    public static function eliminar(?string $ruta): bool
    {
        if (empty($ruta)) {
            return true; // No hay nada que eliminar
        }

        $filePath = $_SERVER['DOCUMENT_ROOT'] . $ruta;

        if (!file_exists($filePath)) {
            return true; // Ya no existe
        }

        return @unlink($filePath);
    }

    /**
     * Verifica si un dibujo existe en el sistema
     * 
     * @param string|null $ruta Ruta relativa del archivo
     * @return bool
     */
    public static function existe(?string $ruta): bool
    {
        if (empty($ruta)) {
            return false;
        }

        $filePath = $_SERVER['DOCUMENT_ROOT'] . $ruta;
        return file_exists($filePath);
    }

    /**
     * Obtiene información del archivo
     * 
     * @param string $ruta Ruta relativa del archivo
     * @return array|null Array con info o null si no existe
     */
    public static function obtenerInfo(string $ruta): ?array
    {
        if (!self::existe($ruta)) {
            return null;
        }

        $filePath = $_SERVER['DOCUMENT_ROOT'] . $ruta;

        return [
            'ruta' => $ruta,
            'tamaño' => filesize($filePath),
            'tamaño_legible' => self::formatearTamaño(filesize($filePath)),
            'fecha_modificacion' => filemtime($filePath),
            'extension' => pathinfo($filePath, PATHINFO_EXTENSION)
        ];
    }

    /**
     * Genera un nombre único para el archivo
     * 
     * @param int $idRespuesta
     * @return string
     */
    private static function generarNombreArchivo(int $idRespuesta): string
    {
        $timestamp = time();
        $random = bin2hex(random_bytes(8)); // 16 caracteres hexadecimales
        return "resp_{$idRespuesta}_{$timestamp}_{$random}.png";
    }

    /**
     * Valida que los datos sean una imagen PNG válida
     * 
     * @param string $imageData
     * @return bool
     */
    private static function validarImagenPNG(string $imageData): bool
    {
        // Verificar firma PNG (primeros 8 bytes)
        $pngSignature = "\x89PNG\r\n\x1a\n";
        return substr($imageData, 0, 8) === $pngSignature;
    }

    /**
     * Formatea un tamaño en bytes a formato legible
     * 
     * @param int $bytes
     * @return string
     */
    private static function formatearTamaño(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Limpia dibujos huérfanos (archivos sin registro en DB)
     * Útil para mantenimiento
     * 
     * @param mysqli $db Conexión a la base de datos
     * @return array Resultado de la limpieza
     */
    public static function limpiarHuerfanos(mysqli $db): array
    {
        $baseDir = $_SERVER['DOCUMENT_ROOT'] . self::BASE_DIR;
        $archivosEliminados = 0;
        $errores = [];

        // Obtener todas las rutas de dibujos en la DB
        $stmt = $db->prepare("SELECT DISTINCT dibujo_ruta FROM respuestas_usuario WHERE dibujo_ruta IS NOT NULL");
        $stmt->execute();
        $result = $stmt->get_result();

        $rutasDB = [];
        while ($row = $result->fetch_assoc()) {
            $rutasDB[] = $row['dibujo_ruta'];
        }

        // Recorrer archivos en el sistema
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'png') {
                $rutaRelativa = str_replace($_SERVER['DOCUMENT_ROOT'], '', $file->getPathname());
                
                // Si no está en la DB, eliminar
                if (!in_array($rutaRelativa, $rutasDB)) {
                    if (@unlink($file->getPathname())) {
                        $archivosEliminados++;
                    } else {
                        $errores[] = $rutaRelativa;
                    }
                }
            }
        }

        return [
            'eliminados' => $archivosEliminados,
            'errores' => $errores
        ];
    }
}