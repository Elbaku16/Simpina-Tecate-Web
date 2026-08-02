-- Esquema sanitizado de SIMPINNA. No contiene datos personales ni credenciales.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `ciclos_escolares`;
CREATE TABLE `ciclos_escolares` (
  `id_ciclo` int NOT NULL AUTO_INCREMENT,
  `nombre_ciclo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_ciclo`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `contactos`;
CREATE TABLE `contactos` (
  `id_contacto` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Anónimo',
  `id_nivel` int NOT NULL,
  `id_escuela` int NOT NULL,
  `comentarios` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_envio` datetime DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('pendiente','en_revision','resuelto') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `notas_admin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fecha_actualizacion` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_contacto`),
  KEY `idx_fecha_envio` (`fecha_envio`),
  KEY `idx_estado` (`estado`),
  KEY `idx_nivel` (`id_nivel`),
  KEY `idx_escuela` (`id_escuela`),
  CONSTRAINT `fk_contacto_escuela` FOREIGN KEY (`id_escuela`) REFERENCES `escuelas` (`id_escuela`),
  CONSTRAINT `fk_contacto_nivel` FOREIGN KEY (`id_nivel`) REFERENCES `niveles_educativos` (`id_nivel`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `encuestas`;
CREATE TABLE `encuestas` (
  `id_encuesta` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `id_nivel` int NOT NULL,
  `id_ciclo` int NOT NULL,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('activa','inactiva') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'activa',
  `version` int NOT NULL DEFAULT '1',
  `id_encuesta_anterior` int DEFAULT NULL,
  `publicada_en` datetime DEFAULT NULL,
  `acepta_respuestas_hasta` datetime DEFAULT NULL,
  PRIMARY KEY (`id_encuesta`),
  KEY `id_nivel` (`id_nivel`),
  KEY `id_ciclo` (`id_ciclo`),
  KEY `idx_encuesta_nivel_estado_version` (`id_nivel`,`estado`,`version`),
  KEY `idx_encuesta_anterior` (`id_encuesta_anterior`),
  CONSTRAINT `encuestas_ibfk_1` FOREIGN KEY (`id_nivel`) REFERENCES `niveles_educativos` (`id_nivel`),
  CONSTRAINT `encuestas_ibfk_2` FOREIGN KEY (`id_ciclo`) REFERENCES `ciclos_escolares` (`id_ciclo`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `encuestas_usuarios`;
CREATE TABLE `encuestas_usuarios` (
  `id_usuario_encuesta` int NOT NULL AUTO_INCREMENT,
  `id_encuesta` int NOT NULL,
  `id_escuela` int DEFAULT NULL,
  `id_turno` int DEFAULT NULL,
  `id_ciclo` int DEFAULT NULL,
  `fecha_inicio` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_fin` datetime DEFAULT NULL,
  `ip` varchar(50) DEFAULT NULL,
  `dispositivo` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_usuario_encuesta`),
  UNIQUE KEY `uq_sesion_encuesta` (`id_usuario_encuesta`,`id_encuesta`),
  KEY `idx_encuesta` (`id_encuesta`),
  CONSTRAINT `fk_usuario_encuesta_encuesta` FOREIGN KEY (`id_encuesta`) REFERENCES `encuestas` (`id_encuesta`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `escuelas`;
CREATE TABLE `escuelas` (
  `id_escuela` int NOT NULL AUTO_INCREMENT,
  `id_nivel` int NOT NULL,
  `nombre_escuela` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `clave_cct` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `direccion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_turno` int DEFAULT NULL,
  PRIMARY KEY (`id_escuela`),
  KEY `id_turno` (`id_turno`),
  KEY `fk_escuela_nivel` (`id_nivel`),
  CONSTRAINT `escuelas_ibfk_1` FOREIGN KEY (`id_turno`) REFERENCES `turnos` (`id_turno`),
  CONSTRAINT `fk_escuela_nivel` FOREIGN KEY (`id_nivel`) REFERENCES `niveles_educativos` (`id_nivel`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `graficos_estadisticas`;
CREATE TABLE `graficos_estadisticas` (
  `id_estadistica` int NOT NULL AUTO_INCREMENT,
  `id_pregunta` int NOT NULL,
  `opcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `total_respuestas` int DEFAULT '0',
  `fecha_actualizacion` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_estadistica`),
  KEY `id_pregunta` (`id_pregunta`),
  CONSTRAINT `graficos_estadisticas_ibfk_1` FOREIGN KEY (`id_pregunta`) REFERENCES `preguntas` (`id_pregunta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `historial_comentarios`;
CREATE TABLE `historial_comentarios` (
  `id_historial` int NOT NULL AUTO_INCREMENT,
  `id_contacto` int NOT NULL,
  `accion` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipo de acción: cambio_estado, eliminado, creado, editado',
  `usuario` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Usuario que realizó la acción',
  `detalles` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Descripción detallada de la acción',
  `estado_anterior` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Estado antes del cambio',
  `estado_nuevo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Estado después del cambio',
  `fecha_accion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_historial`),
  KEY `idx_contacto` (`id_contacto`),
  KEY `idx_fecha` (`fecha_accion`),
  KEY `idx_accion` (`accion`),
  KEY `idx_usuario` (`usuario`),
  KEY `idx_contacto_fecha` (`id_contacto`,`fecha_accion`),
  KEY `idx_fecha_accion` (`fecha_accion`,`accion`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registra todos los cambios realizados en los comentarios';

DROP TABLE IF EXISTS `niveles_educativos`;
CREATE TABLE `niveles_educativos` (
  `id_nivel` int NOT NULL AUTO_INCREMENT,
  `nombre_nivel` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_nivel`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `opciones_respuesta`;
CREATE TABLE `opciones_respuesta` (
  `id_opcion` int NOT NULL AUTO_INCREMENT,
  `id_pregunta` int NOT NULL,
  `clave_logica` char(36) COLLATE utf8mb4_general_ci NOT NULL,
  `texto_opcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `icono` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `valor` int DEFAULT NULL,
  PRIMARY KEY (`id_opcion`),
  UNIQUE KEY `uq_opcion_pregunta_id` (`id_opcion`,`id_pregunta`),
  KEY `id_pregunta` (`id_pregunta`),
  KEY `idx_opcion_clave_logica` (`clave_logica`),
  CONSTRAINT `opciones_respuesta_ibfk_1` FOREIGN KEY (`id_pregunta`) REFERENCES `preguntas` (`id_pregunta`)
) ENGINE=InnoDB AUTO_INCREMENT=292 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `preguntas`;
CREATE TABLE `preguntas` (
  `id_pregunta` int NOT NULL AUTO_INCREMENT,
  `id_encuesta` int NOT NULL,
  `clave_logica` char(36) COLLATE utf8mb4_general_ci NOT NULL,
  `texto_pregunta` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tipo_pregunta` enum('opcion','texto','multiple','imagen','ranking','dibujo') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'opcion',
  `icono` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `orden` int DEFAULT NULL,
  `color_tema` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_pregunta`),
  UNIQUE KEY `uq_pregunta_encuesta_id` (`id_pregunta`,`id_encuesta`),
  KEY `id_encuesta` (`id_encuesta`),
  KEY `idx_pregunta_clave_logica` (`clave_logica`),
  CONSTRAINT `preguntas_ibfk_1` FOREIGN KEY (`id_encuesta`) REFERENCES `encuestas` (`id_encuesta`)
) ENGINE=InnoDB AUTO_INCREMENT=134 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `respuestas_ranking`;
CREATE TABLE `respuestas_ranking` (
  `id_respuesta` int NOT NULL AUTO_INCREMENT,
  `id_usuario_encuesta` int NOT NULL,
  `id_encuesta` int NOT NULL,
  `id_usuario` int DEFAULT NULL,
  `id_pregunta` int NOT NULL,
  `id_opcion` int NOT NULL,
  `posicion` int NOT NULL COMMENT 'Posición del 1 al 20',
  `fecha_respuesta` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_respuesta`),
  KEY `idx_usuario_pregunta` (`id_usuario`,`id_pregunta`),
  KEY `idx_pregunta` (`id_pregunta`),
  KEY `fk_ranking_opcion` (`id_opcion`),
  KEY `idx_ranking_pregunta` (`id_pregunta`),
  KEY `fk_ranking_usuario_encuesta` (`id_usuario_encuesta`),
  KEY `idx_rr_sesion_encuesta` (`id_usuario_encuesta`,`id_encuesta`),
  KEY `idx_rr_pregunta_encuesta` (`id_pregunta`,`id_encuesta`),
  KEY `idx_rr_opcion_pregunta` (`id_opcion`,`id_pregunta`),
  CONSTRAINT `fk_rr_opcion_pregunta` FOREIGN KEY (`id_opcion`, `id_pregunta`) REFERENCES `opciones_respuesta` (`id_opcion`, `id_pregunta`) ON DELETE RESTRICT,
  CONSTRAINT `fk_rr_pregunta_encuesta` FOREIGN KEY (`id_pregunta`, `id_encuesta`) REFERENCES `preguntas` (`id_pregunta`, `id_encuesta`) ON DELETE RESTRICT,
  CONSTRAINT `fk_rr_sesion_encuesta` FOREIGN KEY (`id_usuario_encuesta`, `id_encuesta`) REFERENCES `encuestas_usuarios` (`id_usuario_encuesta`, `id_encuesta`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `respuestas_usuario`;
CREATE TABLE `respuestas_usuario` (
  `id_respuesta_usuario` int NOT NULL AUTO_INCREMENT,
  `id_usuario_encuesta` int NOT NULL,
  `id_encuesta` int NOT NULL,
  `id_pregunta` int NOT NULL,
  `id_opcion` int DEFAULT NULL,
  `respuesta_texto` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `dibujo_ruta` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dibujo_storage` varchar(16) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dibujo_objeto` varchar(512) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dibujo_bytes` int unsigned DEFAULT NULL,
  `fecha_respuesta` datetime DEFAULT CURRENT_TIMESTAMP,
  `edad` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `genero` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_escuela` int DEFAULT NULL,
  `id_turno` int DEFAULT NULL,
  `id_ciclo` int DEFAULT NULL,
  PRIMARY KEY (`id_respuesta_usuario`),
  UNIQUE KEY `uq_dibujo_objeto` (`dibujo_storage`,`dibujo_objeto`),
  KEY `id_encuesta` (`id_encuesta`),
  KEY `id_pregunta` (`id_pregunta`),
  KEY `id_opcion` (`id_opcion`),
  KEY `id_escuela` (`id_escuela`),
  KEY `id_turno` (`id_turno`),
  KEY `id_ciclo` (`id_ciclo`),
  KEY `idx_dibujo_ruta` (`dibujo_ruta`),
  KEY `idx_respuestas_usuario` (`id_usuario_encuesta`),
  KEY `fk_ru_sesion_encuesta` (`id_usuario_encuesta`,`id_encuesta`),
  KEY `fk_ru_pregunta_encuesta` (`id_pregunta`,`id_encuesta`),
  KEY `fk_ru_opcion_pregunta` (`id_opcion`,`id_pregunta`),
  CONSTRAINT `fk_ru_opcion_pregunta` FOREIGN KEY (`id_opcion`, `id_pregunta`) REFERENCES `opciones_respuesta` (`id_opcion`, `id_pregunta`) ON DELETE RESTRICT,
  CONSTRAINT `fk_ru_pregunta_encuesta` FOREIGN KEY (`id_pregunta`, `id_encuesta`) REFERENCES `preguntas` (`id_pregunta`, `id_encuesta`) ON DELETE RESTRICT,
  CONSTRAINT `fk_ru_sesion_encuesta` FOREIGN KEY (`id_usuario_encuesta`, `id_encuesta`) REFERENCES `encuestas_usuarios` (`id_usuario_encuesta`, `id_encuesta`) ON DELETE RESTRICT,
  CONSTRAINT `respuestas_usuario_ibfk_4` FOREIGN KEY (`id_escuela`) REFERENCES `escuelas` (`id_escuela`),
  CONSTRAINT `respuestas_usuario_ibfk_5` FOREIGN KEY (`id_turno`) REFERENCES `turnos` (`id_turno`),
  CONSTRAINT `respuestas_usuario_ibfk_6` FOREIGN KEY (`id_ciclo`) REFERENCES `ciclos_escolares` (`id_ciclo`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `schema_migrations`;
CREATE TABLE `schema_migrations` (
  `version` varchar(190) NOT NULL,
  `aplicada_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `sesiones_admin`;
CREATE TABLE `sesiones_admin` (
  `id_sesion` int NOT NULL AUTO_INCREMENT,
  `id_admin` int NOT NULL,
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  `ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_sesion`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `sesiones_admin_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `usuarios_admin` (`id_admin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `storage_delete_queue`;
CREATE TABLE `storage_delete_queue` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `driver` varchar(16) NOT NULL,
  `object_key` varchar(512) NOT NULL,
  `intentos` int unsigned NOT NULL DEFAULT '0',
  `ultimo_error` varchar(500) DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `procesado_en` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_storage_delete_pending` (`procesado_en`,`intentos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `turnos`;
CREATE TABLE `turnos` (
  `id_turno` int NOT NULL AUTO_INCREMENT,
  `nombre_turno` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_turno`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `usuarios_admin`;
CREATE TABLE `usuarios_admin` (
  `id_admin` int NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nombre` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Secretario Ejecutivo',
  `rol` enum('secretario_ejecutivo','acompanamiento','evaluacion') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'secretario_ejecutivo',
  `requiere_cambio_password` tinyint(1) NOT NULL DEFAULT '1',
  `password_actualizada_en` datetime DEFAULT NULL,
  PRIMARY KEY (`id_admin`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS=1;
