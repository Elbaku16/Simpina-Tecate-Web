#!/usr/bin/env php
<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') exit(1);
require_once __DIR__ . '/../back-end/database/Conexion.php';
require_once __DIR__ . '/../back-end/storage/DrawingStorageFactory.php';

$db = Conexion::getConexion();
$result = $db->query(
    'SELECT id,driver,object_key FROM storage_delete_queue
     WHERE procesado_en IS NULL AND intentos<10 ORDER BY id LIMIT 100'
);
$ok = $db->prepare('UPDATE storage_delete_queue SET procesado_en=NOW(),intentos=intentos+1,ultimo_error=NULL WHERE id=?');
$fail = $db->prepare('UPDATE storage_delete_queue SET intentos=intentos+1,ultimo_error=? WHERE id=?');
while ($row = $result->fetch_assoc()) {
    $id = (int) $row['id'];
    try {
        DrawingStorageFactory::for($row['driver'])->delete($row['object_key']);
        $ok->bind_param('i', $id);
        $ok->execute();
    } catch (Throwable $e) {
        $error = substr($e->getMessage(), 0, 500);
        $fail->bind_param('si', $error, $id);
        $fail->execute();
    }
}
$ok->close();
$fail->close();
echo "Cola procesada.\n";
