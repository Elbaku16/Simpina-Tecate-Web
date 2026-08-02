ALTER TABLE `respuestas_usuario`
  DROP FOREIGN KEY `fk_respuesta_usuario_encuesta`,
  DROP FOREIGN KEY `fk_respuesta_usuario_opcion`,
  DROP FOREIGN KEY `fk_respuesta_usuario_pregunta`,
  DROP FOREIGN KEY `fk_respuestas_usuario_usuario_encuesta`,
  DROP FOREIGN KEY `respuestas_usuario_ibfk_1`,
  DROP FOREIGN KEY `respuestas_usuario_ibfk_2`,
  DROP FOREIGN KEY `respuestas_usuario_ibfk_3`;

ALTER TABLE `respuestas_usuario`
  ADD CONSTRAINT `fk_ru_sesion_encuesta`
    FOREIGN KEY (`id_usuario_encuesta`, `id_encuesta`)
    REFERENCES `encuestas_usuarios` (`id_usuario_encuesta`, `id_encuesta`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_ru_pregunta_encuesta`
    FOREIGN KEY (`id_pregunta`, `id_encuesta`)
    REFERENCES `preguntas` (`id_pregunta`, `id_encuesta`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_ru_opcion_pregunta`
    FOREIGN KEY (`id_opcion`, `id_pregunta`)
    REFERENCES `opciones_respuesta` (`id_opcion`, `id_pregunta`) ON DELETE RESTRICT;

ALTER TABLE `respuestas_ranking`
  DROP FOREIGN KEY `fk_ranking_opcion`,
  DROP FOREIGN KEY `fk_ranking_pregunta`,
  DROP FOREIGN KEY `fk_ranking_usuario_encuesta`,
  MODIFY `id_encuesta` int NOT NULL;

ALTER TABLE `respuestas_ranking`
  ADD INDEX `idx_rr_sesion_encuesta` (`id_usuario_encuesta`, `id_encuesta`),
  ADD INDEX `idx_rr_pregunta_encuesta` (`id_pregunta`, `id_encuesta`),
  ADD INDEX `idx_rr_opcion_pregunta` (`id_opcion`, `id_pregunta`),
  ADD CONSTRAINT `fk_rr_sesion_encuesta`
    FOREIGN KEY (`id_usuario_encuesta`, `id_encuesta`)
    REFERENCES `encuestas_usuarios` (`id_usuario_encuesta`, `id_encuesta`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_rr_pregunta_encuesta`
    FOREIGN KEY (`id_pregunta`, `id_encuesta`)
    REFERENCES `preguntas` (`id_pregunta`, `id_encuesta`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_rr_opcion_pregunta`
    FOREIGN KEY (`id_opcion`, `id_pregunta`)
    REFERENCES `opciones_respuesta` (`id_opcion`, `id_pregunta`) ON DELETE RESTRICT;
