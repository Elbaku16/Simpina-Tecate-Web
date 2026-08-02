ALTER TABLE `encuestas`
  ADD COLUMN `version` int NOT NULL DEFAULT 1 AFTER `estado`,
  ADD COLUMN `id_encuesta_anterior` int NULL AFTER `version`,
  ADD COLUMN `publicada_en` datetime NULL AFTER `id_encuesta_anterior`,
  ADD COLUMN `acepta_respuestas_hasta` datetime NULL AFTER `publicada_en`,
  ADD INDEX `idx_encuesta_nivel_estado_version` (`id_nivel`, `estado`, `version`),
  ADD INDEX `idx_encuesta_anterior` (`id_encuesta_anterior`);

UPDATE `encuestas`
SET `publicada_en` = COALESCE(`publicada_en`, `fecha_creacion`)
WHERE `publicada_en` IS NULL;

ALTER TABLE `preguntas`
  ADD COLUMN `clave_logica` char(36) NULL AFTER `id_encuesta`;
UPDATE `preguntas` SET `clave_logica` = UUID() WHERE `clave_logica` IS NULL OR `clave_logica` = '';
ALTER TABLE `preguntas`
  MODIFY `clave_logica` char(36) NOT NULL,
  ADD INDEX `idx_pregunta_clave_logica` (`clave_logica`),
  ADD UNIQUE INDEX `uq_pregunta_encuesta_id` (`id_pregunta`, `id_encuesta`);

ALTER TABLE `opciones_respuesta`
  ADD COLUMN `clave_logica` char(36) NULL AFTER `id_pregunta`;
UPDATE `opciones_respuesta` SET `clave_logica` = UUID() WHERE `clave_logica` IS NULL OR `clave_logica` = '';
ALTER TABLE `opciones_respuesta`
  MODIFY `clave_logica` char(36) NOT NULL,
  ADD INDEX `idx_opcion_clave_logica` (`clave_logica`),
  ADD UNIQUE INDEX `uq_opcion_pregunta_id` (`id_opcion`, `id_pregunta`);

ALTER TABLE `encuestas_usuarios`
  ADD UNIQUE INDEX `uq_sesion_encuesta` (`id_usuario_encuesta`, `id_encuesta`);

ALTER TABLE `respuestas_ranking`
  ADD COLUMN `id_encuesta` int NULL AFTER `id_usuario_encuesta`;
