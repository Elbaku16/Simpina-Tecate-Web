#!/usr/bin/env php
<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit(1);
}
require_once __DIR__ . '/../back-end/database/Conexion.php';
require_once __DIR__ . '/../back-end/database/SurveyIntegrityRepair.php';

$stats = SurveyIntegrityRepair::ejecutar(Conexion::getConexion());
echo json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
