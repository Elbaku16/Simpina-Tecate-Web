#!/usr/bin/env php
<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') exit(1);
require_once __DIR__ . '/../back-end/database/Conexion.php';
require_once __DIR__ . '/../back-end/helpers/DibujoHelper.php';

$db = Conexion::getConexion();
$storage = DrawingStorageFactory::for('r2');
$result = $db->query(
    "SELECT id_respuesta_usuario,dibujo_ruta,dibujo_objeto
     FROM respuestas_usuario
     WHERE dibujo_storage='r2' AND dibujo_objeto IS NOT NULL AND dibujo_ruta IS NOT NULL"
);
$update = $db->prepare('UPDATE respuestas_usuario SET dibujo_ruta=NULL WHERE id_respuesta_usuario=?');
$limpios = 0;
while ($row = $result->fetch_assoc()) {
    if (!$storage->exists($row['dibujo_objeto'])) {
        fwrite(STDERR, "Omitido #{$row['id_respuesta_usuario']}: falta en R2\n");
        continue;
    }
    $path = DibujoHelper::rutaLegacy($row['dibujo_ruta']);
    if ($path && !unlink($path)) {
        fwrite(STDERR, "Omitido #{$row['id_respuesta_usuario']}: no se pudo borrar local\n");
        continue;
    }
    $id = (int) $row['id_respuesta_usuario'];
    $update->bind_param('i', $id);
    $update->execute();
    $limpios++;
}
$update->close();
echo "Archivos locales retirados: {$limpios}\n";
