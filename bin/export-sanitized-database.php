#!/usr/bin/env php
<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') exit(1);
require_once __DIR__ . '/../back-end/database/Conexion.php';
require_once __DIR__ . '/../back-end/security/PasswordService.php';

$mode = $argv[1] ?? '';
$db = Conexion::getConexion();

if ($mode === 'schema') {
    echo "-- Esquema sanitizado de SIMPINNA. No contiene datos personales ni credenciales.\n";
    echo "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n";
    $result = $db->query("SHOW FULL TABLES WHERE Table_type='BASE TABLE'");
    while ($row = $result->fetch_row()) {
        $table = $row[0];
        $create = $db->query("SHOW CREATE TABLE `{$table}`")->fetch_row()[1];
        echo "DROP TABLE IF EXISTS `{$table}`;\n{$create};\n\n";
    }
    echo "SET FOREIGN_KEY_CHECKS=1;\n";
    exit(0);
}

if ($mode === 'seed') {
    echo "-- Datos de desarrollo sanitizados: catálogos y definición de encuestas.\n";
    echo "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n";
    $tables = [
        'ciclos_escolares', 'niveles_educativos', 'turnos', 'escuelas',
        'encuestas', 'preguntas', 'opciones_respuesta', 'schema_migrations',
    ];
    foreach ($tables as $table) {
        $result = $db->query("SELECT * FROM `{$table}`");
        if ($result->num_rows === 0) continue;
        $fields = array_map(static fn($f) => '`' . $f->name . '`', $result->fetch_fields());
        echo "DELETE FROM `{$table}`;\n";
        while ($row = $result->fetch_assoc()) {
            $values = [];
            foreach ($row as $value) {
                $values[] = $value === null ? 'NULL' : "'" . $db->real_escape_string((string) $value) . "'";
            }
            echo "INSERT INTO `{$table}` (" . implode(',', $fields) . ") VALUES (" . implode(',', $values) . ");\n";
        }
        echo "\n";
    }

    $hashInutilizable = PasswordService::hash(bin2hex(random_bytes(32)));
    echo "DELETE FROM `usuarios_admin`;\n";
    echo "INSERT INTO `usuarios_admin`
          (`usuario`,`password`,`nombre`,`rol`,`requiere_cambio_password`,`password_actualizada_en`)
          VALUES ('administrador','" . $db->real_escape_string($hashInutilizable) . "','Secretario Ejecutivo','secretario_ejecutivo',1,NULL);\n\n";
    echo "SET FOREIGN_KEY_CHECKS=1;\n";
    exit(0);
}

fwrite(STDERR, "Uso: php bin/export-sanitized-database.php schema|seed\n");
exit(1);
