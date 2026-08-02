#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Vacía las respuestas y deja la encuesta lista para un ciclo nuevo.
 *
 *   php bin/limpiar-respuestas.php              (solo muestra, no borra)
 *   php bin/limpiar-respuestas.php --confirmar  (borra de verdad)
 *
 * BORRA  : respuestas, rankings, sesiones, contactos e historial.
 * CONSERVA: encuestas, preguntas, opciones, escuelas, niveles, turnos,
 *           ciclos y cuentas administrativas.
 *
 * El borrado va dentro de una transacción y en orden de dependencia, porque
 * las claves foráneas de la migración 02 usan ON DELETE RESTRICT.
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este comando solo puede ejecutarse desde la terminal.\n");
    exit(1);
}

require_once __DIR__ . '/../back-end/database/Conexion.php';

/** Orden de borrado: de las tablas más dependientes a las menos. */
const A_BORRAR = [
    'respuestas_usuario',
    'respuestas_ranking',
    'encuestas_usuarios',
    'graficos_estadisticas',
    'historial_comentarios',
    'contactos',
];

const A_CONSERVAR = [
    'encuestas', 'preguntas', 'opciones_respuesta', 'escuelas',
    'niveles_educativos', 'turnos', 'ciclos_escolares', 'usuarios_admin',
];

$confirmado = in_array('--confirmar', $argv, true);
$db = Conexion::getConexion();

function contar(mysqli $db, string $tabla): int
{
    return (int) $db->query("SELECT COUNT(*) FROM `{$tabla}`")->fetch_column();
}

$nombreBase = (string) $db->query('SELECT DATABASE()')->fetch_column();
echo "Base de datos: {$nombreBase}\n\n";

$antes = [];
echo "SE VAN A BORRAR:\n";
$totalBorrar = 0;
foreach (A_BORRAR as $tabla) {
    $antes[$tabla] = contar($db, $tabla);
    $totalBorrar += $antes[$tabla];
    printf("  %6d  %s\n", $antes[$tabla], $tabla);
}

echo "\nSE CONSERVAN:\n";
$conservarAntes = [];
foreach (A_CONSERVAR as $tabla) {
    $conservarAntes[$tabla] = contar($db, $tabla);
    printf("  %6d  %s\n", $conservarAntes[$tabla], $tabla);
}

if (!$confirmado) {
    echo "\n";
    echo "Nada se ha borrado. Para ejecutar el borrado de {$totalBorrar} fila(s):\n";
    echo "  php bin/limpiar-respuestas.php --confirmar\n";
    exit(0);
}

echo "\nBorrando...\n";
$db->begin_transaction();
try {
    foreach (A_BORRAR as $tabla) {
        $db->query("DELETE FROM `{$tabla}`");
        printf("  %s: %d fila(s) eliminada(s)\n", $tabla, $db->affected_rows);
    }
    $db->commit();
} catch (Throwable $e) {
    $db->rollback();
    fwrite(STDERR, "\nFALLÓ, no se borró nada: {$e->getMessage()}\n");
    exit(1);
}

// Fuera de la transacción: en MySQL/MariaDB el ALTER hace commit implícito.
foreach (A_BORRAR as $tabla) {
    try {
        $db->query("ALTER TABLE `{$tabla}` AUTO_INCREMENT = 1");
    } catch (Throwable $e) {
        fwrite(STDERR, "  aviso: no se pudo reiniciar el contador de {$tabla}\n");
    }
}

echo "\nVERIFICACIÓN\n";
$errores = 0;
foreach (A_BORRAR as $tabla) {
    $restantes = contar($db, $tabla);
    printf("  %-24s %d fila(s)%s\n", $tabla, $restantes, $restantes === 0 ? '' : '   <-- NO QUEDÓ VACÍA');
    if ($restantes !== 0) {
        $errores++;
    }
}
foreach (A_CONSERVAR as $tabla) {
    $ahora = contar($db, $tabla);
    $ok = $ahora === $conservarAntes[$tabla];
    printf("  %-24s %d fila(s)%s\n", $tabla, $ahora, $ok ? ' (intacta)' : '   <-- CAMBIÓ');
    if (!$ok) {
        $errores++;
    }
}

if ($errores > 0) {
    fwrite(STDERR, "\nRevisa los avisos anteriores.\n");
    exit(1);
}

echo "\nListo. La encuesta quedó lista para recibir respuestas nuevas.\n";
