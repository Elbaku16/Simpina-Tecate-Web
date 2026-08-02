#!/usr/bin/env php
<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') exit(1);
require_once __DIR__ . '/../back-end/database/Conexion.php';

$db = Conexion::getConexion();
$source = (string) getenv('DB_NAME');
$target = $argv[1] ?? 'simpinna_migration_test';
if (!preg_match('/^[a-zA-Z0-9_]+$/', $source) || !preg_match('/^simpinna_[a-zA-Z0-9_]+_test$/', $target)) {
    fwrite(STDERR, "El nombre de la base temporal no es seguro.\n");
    exit(1);
}

$tables = [];
$result = $db->query("SHOW FULL TABLES FROM `{$source}` WHERE Table_type='BASE TABLE'");
while ($row = $result->fetch_row()) $tables[] = $row[0];

$db->query("DROP DATABASE IF EXISTS `{$target}`");
$db->query("CREATE DATABASE `{$target}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$db->query('SET FOREIGN_KEY_CHECKS=0');
foreach ($tables as $table) {
    $createRow = $db->query("SHOW CREATE TABLE `{$source}`.`{$table}`")->fetch_row();
    $create = preg_replace(
        '/^CREATE TABLE `[^`]+`/',
        "CREATE TABLE `{$target}`.`{$table}`",
        (string) $createRow[1]
    );
    $db->query($create);
}
foreach ($tables as $table) {
    $db->query("INSERT INTO `{$target}`.`{$table}` SELECT * FROM `{$source}`.`{$table}`");
}
$db->query('SET FOREIGN_KEY_CHECKS=1');
echo $target . PHP_EOL;
