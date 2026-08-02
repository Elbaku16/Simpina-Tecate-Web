ALTER TABLE `respuestas_usuario`
  ADD COLUMN `dibujo_storage` varchar(16) NULL AFTER `dibujo_ruta`,
  ADD COLUMN `dibujo_objeto` varchar(512) NULL AFTER `dibujo_storage`,
  ADD COLUMN `dibujo_bytes` int unsigned NULL AFTER `dibujo_objeto`,
  ADD UNIQUE INDEX `uq_dibujo_objeto` (`dibujo_storage`, `dibujo_objeto`);

CREATE TABLE IF NOT EXISTS `storage_delete_queue` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `driver` varchar(16) NOT NULL,
  `object_key` varchar(512) NOT NULL,
  `intentos` int unsigned NOT NULL DEFAULT 0,
  `ultimo_error` varchar(500) NULL,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `procesado_en` datetime NULL,
  PRIMARY KEY (`id`),
  KEY `idx_storage_delete_pending` (`procesado_en`, `intentos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
