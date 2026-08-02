#!/usr/bin/env php
<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') exit(1);
require_once __DIR__ . '/../back-end/database/Conexion.php';
require_once __DIR__ . '/../back-end/storage/DrawingStorageFactory.php';
require_once __DIR__ . '/../back-end/helpers/DibujoHelper.php';

/**
 * Clave determinista: el mismo dibujo produce siempre la misma clave, de modo
 * que una interrupción a mitad de la migración no genere objetos duplicados al
 * reintentar. Incluye el ID de respuesta para que dos dibujos idénticos de
 * respuestas distintas no compartan el mismo objeto.
 */
function claveDeterminista(int $id, string $contenido, ?string $rutaLegacy): string
{
    $periodo = 'legacy';
    if (is_string($rutaLegacy) && preg_match('#/([0-9]{4})/([0-9]{2})/#', $rutaLegacy, $m) === 1) {
        $periodo = $m[1] . '/' . $m[2];
    }
    $hash = substr(hash('sha256', $contenido), 0, 32);
    return "drawings/{$periodo}/{$id}-{$hash}.png";
}

$dryRun = in_array('--dry-run', $argv, true);
$db = Conexion::getConexion();
$result = $db->query(
    "SELECT id_respuesta_usuario,dibujo_ruta
     FROM respuestas_usuario
     WHERE dibujo_ruta IS NOT NULL AND dibujo_ruta<>'' AND dibujo_objeto IS NULL
     ORDER BY id_respuesta_usuario"
);
$pendientes = $result->fetch_all(MYSQLI_ASSOC);

$validos = 0;
$invalidos = 0;
$migrados = 0;
$reutilizados = 0;
$fallidos = 0;
$bytesTotales = 0;

/** Imprime el resumen final con todos los totales solicitados. */
$resumen = static function () use (
    &$pendientes, &$validos, &$invalidos, &$migrados, &$reutilizados, &$fallidos, &$bytesTotales, $dryRun
): void {
    $datos = [
        'pendientes' => count($pendientes),
        'validos' => $validos,
        'invalidos' => $invalidos,
        'migrados' => $migrados,
        'reutilizados' => $reutilizados,
        'fallidos' => $fallidos,
        'bytes' => $bytesTotales,
        'modo' => $dryRun ? 'dry-run' : 'real',
    ];
    echo json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
};

if ($dryRun) {
    foreach ($pendientes as $row) {
        $id = (int) $row['id_respuesta_usuario'];
        try {
            $contenido = DibujoHelper::leerLegacy($row['dibujo_ruta']);
            $bytesTotales += strlen($contenido);
            $validos++;
            echo 'ok       #' . $id . ' -> ' . claveDeterminista($id, $contenido, $row['dibujo_ruta']) . PHP_EOL;
        } catch (Throwable $e) {
            $invalidos++;
            fwrite(STDERR, "no válido #{$id}: {$e->getMessage()}\n");
        }
    }
    $resumen();
    // Termina distinto de cero si hay dibujos inválidos o ausentes, para que la
    // verificación previa no pase por alto datos que no se podrán migrar.
    exit($invalidos === 0 ? 0 : 1);
}

$storage = DrawingStorageFactory::for('r2');
$update = $db->prepare(
    "UPDATE respuestas_usuario
     SET dibujo_storage='r2',dibujo_objeto=?,dibujo_bytes=?
     WHERE id_respuesta_usuario=? AND dibujo_objeto IS NULL"
);

foreach ($pendientes as $row) {
    $id = (int) $row['id_respuesta_usuario'];
    try {
        $contenido = DibujoHelper::leerLegacy($row['dibujo_ruta']);
    } catch (Throwable $e) {
        $invalidos++;
        fwrite(STDERR, "no válido #{$id}: {$e->getMessage()}\n");
        continue;
    }

    $validos++;
    $bytes = strlen($contenido);
    $bytesTotales += $bytes;
    $key = claveDeterminista($id, $contenido, $row['dibujo_ruta']);
    $estado = null;

    try {
        $tamanoRemoto = $storage->size($key);

        if ($tamanoRemoto === null) {
            $storage->put($key, $contenido, 'image/png');
            if ($storage->size($key) !== $bytes) {
                throw new RuntimeException("R2 no confirmó el tamaño del objeto {$key}");
            }
            $migrados++;
            $estado = 'migrado';
        } elseif ($tamanoRemoto === $bytes) {
            // Subida previa interrumpida antes de actualizar la fila: se reutiliza
            // el objeto existente en lugar de crear un duplicado.
            $reutilizados++;
            $estado = 'reusado';
        } else {
            // Misma clave con contenido distinto: indica corrupción o colisión.
            // Se detiene la migración completa sin tocar el objeto remoto.
            fwrite(STDERR, "ABORTADO en #{$id}: {$key} ya existe con tamaño {$tamanoRemoto}, se esperaba {$bytes}.\n");
            $update->close();
            $resumen();
            exit(3);
        }

        // El objeto ya está confirmado en R2; si la fila no se puede actualizar
        // NO se elimina el objeto, porque un reintento posterior lo reutilizará.
        $update->bind_param('sii', $key, $bytes, $id);
        $update->execute();
        if ($update->affected_rows !== 1) {
            throw new RuntimeException('La fila no se actualizó (¿ya fue migrada por otro proceso?).');
        }

        echo "{$estado} #{$id} -> {$key}\n";
    } catch (Throwable $e) {
        $fallidos++;
        if ($estado === 'migrado') {
            $migrados--;
        } elseif ($estado === 'reusado') {
            $reutilizados--;
        }
        fwrite(STDERR, "falló #{$id}: {$e->getMessage()}\n");
    }
}

$update->close();
$resumen();
exit($fallidos === 0 && $invalidos === 0 ? 0 : 2);
