#!/usr/bin/env php
<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') exit(1);
require_once __DIR__ . '/../back-end/database/Conexion.php';
require_once __DIR__ . '/../back-end/helpers/DibujoHelper.php';

$dryRun = in_array('--dry-run', $argv, true);
$db = Conexion::getConexion();
$result = $db->query(
    "SELECT id_respuesta_usuario,dibujo_ruta
     FROM respuestas_usuario
     WHERE dibujo_ruta IS NOT NULL AND dibujo_ruta<>'' AND dibujo_objeto IS NULL
     ORDER BY id_respuesta_usuario"
);
$pendientes = $result->fetch_all(MYSQLI_ASSOC);
if ($dryRun) {
    $bytes = 0;
    $validos = 0;
    foreach ($pendientes as $row) {
        try {
            $contenido = DibujoHelper::leerLegacy($row['dibujo_ruta']);
            $bytes += strlen($contenido);
            $validos++;
        } catch (Throwable $e) {
            fwrite(STDERR, "No válido #{$row['id_respuesta_usuario']}: {$e->getMessage()}\n");
        }
    }
    echo json_encode(['pendientes' => count($pendientes), 'validos' => $validos, 'bytes' => $bytes], JSON_PRETTY_PRINT) . PHP_EOL;
    exit(0);
}

$storage = DrawingStorageFactory::for('r2');
$update = $db->prepare(
    "UPDATE respuestas_usuario
     SET dibujo_storage='r2',dibujo_objeto=?,dibujo_bytes=?
     WHERE id_respuesta_usuario=? AND dibujo_objeto IS NULL"
);
$migrados = 0;
$fallidos = 0;
foreach ($pendientes as $row) {
    try {
        $contenido = DibujoHelper::leerLegacy($row['dibujo_ruta']);
        $key = DibujoHelper::nuevaClave();
        $storage->put($key, $contenido, 'image/png');
        $bytes = strlen($contenido);
        if ($storage->size($key) !== $bytes) {
            try {
                $storage->delete($key);
            } catch (Throwable) {
            }
            throw new RuntimeException("R2 no confirmó el tamaño del objeto {$key}");
        }
        try {
            $id = (int) $row['id_respuesta_usuario'];
            $update->bind_param('sii', $key, $bytes, $id);
            $update->execute();
            if ($update->affected_rows === 1) $migrados++;
        } catch (Throwable $e) {
            $storage->delete($key);
            throw $e;
        }
        echo "migrado #{$row['id_respuesta_usuario']}\n";
    } catch (Throwable $e) {
        $fallidos++;
        fwrite(STDERR, "falló #{$row['id_respuesta_usuario']}: {$e->getMessage()}\n");
    }
}
$update->close();
echo "Migrados: {$migrados}; fallidos: {$fallidos}\n";
exit($fallidos === 0 ? 0 : 2);
