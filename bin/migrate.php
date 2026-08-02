#!/usr/bin/env php
<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este comando solo puede ejecutarse desde la terminal.\n");
    exit(1);
}

require_once __DIR__ . '/../back-end/database/Conexion.php';
require_once __DIR__ . '/../back-end/security/PasswordService.php';
require_once __DIR__ . '/../back-end/database/SurveyIntegrityRepair.php';

$db = Conexion::getConexion();
$db->query(
    'CREATE TABLE IF NOT EXISTS schema_migrations (
        version varchar(190) NOT NULL PRIMARY KEY,
        aplicada_en datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
);

$dir = __DIR__ . '/../back-end/database/migrations';
$files = glob($dir . '/*.sql') ?: [];
sort($files, SORT_STRING);

foreach ($files as $file) {
    $version = basename($file);
    $stmt = $db->prepare('SELECT 1 FROM schema_migrations WHERE version = ?');
    $stmt->bind_param('s', $version);
    $stmt->execute();
    $aplicada = (bool) $stmt->get_result()->fetch_row();
    $stmt->close();

    if ($aplicada) {
        echo "omitida  {$version}\n";
        continue;
    }

    if ($version === '20260731_agregar_nombre_rol_usuarios_admin.sql') {
        $columnas = (int) $db->query(
            "SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='usuarios_admin'
               AND COLUMN_NAME IN ('nombre','rol')"
        )->fetch_column();
        if ($columnas === 2) {
            $stmt = $db->prepare('INSERT INTO schema_migrations (version) VALUES (?)');
            $stmt->bind_param('s', $version);
            $stmt->execute();
            $stmt->close();
            echo "registrada {$version} (estructura ya existente)\n";
            continue;
        }
    }

    if ($version === '20260801_02_integrity_constraints.sql') {
        $stats = SurveyIntegrityRepair::ejecutar($db);
        echo 'reparación ' . json_encode($stats, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }

    $sql = file_get_contents($file);
    if (!is_string($sql)) {
        throw new RuntimeException("No se pudo leer {$version}");
    }

    echo "aplicando {$version}\n";
    if (!$db->multi_query($sql)) {
        throw new RuntimeException("Falló {$version}: {$db->error}");
    }
    do {
        if ($result = $db->store_result()) {
            $result->free();
        }
    } while ($db->more_results() && $db->next_result());

    if ($version === '20260801_security_passwords.sql') {
        $result = $db->query('SELECT id_admin FROM usuarios_admin ORDER BY id_admin');
        $update = $db->prepare(
            'UPDATE usuarios_admin
             SET password = ?, requiere_cambio_password = 1, password_actualizada_en = NULL
             WHERE id_admin = ?'
        );
        while ($row = $result->fetch_assoc()) {
            $secretoAleatorio = bin2hex(random_bytes(32));
            $hash = PasswordService::hash($secretoAleatorio);
            $id = (int) $row['id_admin'];
            $update->bind_param('si', $hash, $id);
            $update->execute();
        }
        $update->close();
    }

    $stmt = $db->prepare('INSERT INTO schema_migrations (version) VALUES (?)');
    $stmt->bind_param('s', $version);
    $stmt->execute();
    $stmt->close();
    echo "lista     {$version}\n";
}

echo "Migraciones al día.\n";
