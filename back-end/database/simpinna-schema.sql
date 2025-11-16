-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: svdm056.serverneubox.com.mx    Database: glevanco_simpina
-- ------------------------------------------------------
-- Server version	5.5.5-10.6.20-MariaDB-cll-lve

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `ciclos_escolares`
--

DROP TABLE IF EXISTS `ciclos_escolares`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ciclos_escolares` (
  `id_ciclo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_ciclo` varchar(20) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id_ciclo`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ciclos_escolares`
--

LOCK TABLES `ciclos_escolares` WRITE;
/*!40000 ALTER TABLE `ciclos_escolares` DISABLE KEYS */;
INSERT INTO `ciclos_escolares` VALUES (1,'2025-2026','2025-08-15','2026-07-10',1),(2,'2025-2026','2025-08-15','2026-07-10',1);
/*!40000 ALTER TABLE `ciclos_escolares` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contactos`
--

DROP TABLE IF EXISTS `contactos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contactos` (
  `id_contacto` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) DEFAULT 'Anónimo',
  `id_nivel` int(11) NOT NULL,
  `id_escuela` int(11) NOT NULL,
  `comentarios` text NOT NULL,
  `fecha_envio` datetime DEFAULT current_timestamp(),
  `estado` enum('pendiente','en_revision','resuelto') DEFAULT 'pendiente',
  `notas_admin` text DEFAULT NULL,
  `fecha_actualizacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_contacto`),
  KEY `idx_fecha_envio` (`fecha_envio`),
  KEY `idx_estado` (`estado`),
  KEY `idx_nivel` (`id_nivel`),
  KEY `idx_escuela` (`id_escuela`),
  CONSTRAINT `fk_contacto_escuela` FOREIGN KEY (`id_escuela`) REFERENCES `escuelas` (`id_escuela`),
  CONSTRAINT `fk_contacto_nivel` FOREIGN KEY (`id_nivel`) REFERENCES `niveles_educativos` (`id_nivel`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contactos`
--

LOCK TABLES `contactos` WRITE;
/*!40000 ALTER TABLE `contactos` DISABLE KEYS */;
INSERT INTO `contactos` VALUES (2,'Oscar',2,27,'tu situacion','2025-11-05 15:50:09','pendiente',NULL,'2025-11-06 14:22:42');
/*!40000 ALTER TABLE `contactos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `encuestas`
--

DROP TABLE IF EXISTS `encuestas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `encuestas` (
  `id_encuesta` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `id_nivel` int(11) NOT NULL,
  `id_ciclo` int(11) NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `estado` enum('activa','inactiva') DEFAULT 'activa',
  PRIMARY KEY (`id_encuesta`),
  KEY `id_nivel` (`id_nivel`),
  KEY `id_ciclo` (`id_ciclo`),
  CONSTRAINT `encuestas_ibfk_1` FOREIGN KEY (`id_nivel`) REFERENCES `niveles_educativos` (`id_nivel`),
  CONSTRAINT `encuestas_ibfk_2` FOREIGN KEY (`id_ciclo`) REFERENCES `ciclos_escolares` (`id_ciclo`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `encuestas`
--

LOCK TABLES `encuestas` WRITE;
/*!40000 ALTER TABLE `encuestas` DISABLE KEYS */;
INSERT INTO `encuestas` VALUES (4,'Censo para Primaria (6-11 años)','Encuesta para nivel primaria',2,2,'2025-10-22 14:15:02','activa'),(5,'Censo para Secundaria (12-15 años)','Encuesta para nivel secundaria',3,2,'2025-10-22 14:15:02','activa'),(6,'Censo para Preparatoria (16-18 años)','Encuesta para nivel preparatoria',4,2,'2025-10-22 14:15:02','activa');
/*!40000 ALTER TABLE `encuestas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `escuelas`
--

DROP TABLE IF EXISTS `escuelas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `escuelas` (
  `id_escuela` int(11) NOT NULL AUTO_INCREMENT,
  `id_nivel` int(11) NOT NULL,
  `nombre_escuela` varchar(150) NOT NULL,
  `clave_cct` varchar(50) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `id_turno` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_escuela`),
  KEY `id_turno` (`id_turno`),
  KEY `fk_escuela_nivel` (`id_nivel`),
  CONSTRAINT `escuelas_ibfk_1` FOREIGN KEY (`id_turno`) REFERENCES `turnos` (`id_turno`),
  CONSTRAINT `fk_escuela_nivel` FOREIGN KEY (`id_nivel`) REFERENCES `niveles_educativos` (`id_nivel`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `escuelas`
--

LOCK TABLES `escuelas` WRITE;
/*!40000 ALTER TABLE `escuelas` DISABLE KEYS */;
INSERT INTO `escuelas` VALUES (1,2,'Escuela Primaria Benito Juárez','02DPR1234A','Tecate, B.C.',1),(2,3,'Escuela Secundaria General No. 2','02DES5678B','Tecate, B.C.',1),(3,4,'Preparatoria Oficial Tecate','02DTP9012C','Tecate, B.C.',2),(5,1,'Centro De Desarrollo Infantil Montessori Anser',NULL,'Tecate, B.C.',NULL),(6,1,'Centro De Desarrollo Infantil No.4',NULL,'Tecate, B.C.',NULL),(7,1,'Carrusel Infantil',NULL,'Aldrete Y F No.10, Tecate, B.C.',NULL),(8,1,'Camara Junior De Tecate',NULL,'Tecate, B.C.',NULL),(9,1,'Mi Arca Infantil',NULL,'Tecate, B.C.',NULL),(10,1,'Mi Mundo',NULL,'Tecate, B.C.',NULL),(11,1,'Princesa Itztakat',NULL,'Tecate, B.C.',NULL),(12,1,'Principe Cuchuma',NULL,'Tecate, B.C.',NULL),(13,1,'Estancia Infantil Participativa Num. 71',NULL,'Yucatan 120 Y Oaxaca, Tecate, B.C.',NULL),(14,1,'Bertha Von Glumer',NULL,'Tecate, B.C.',NULL),(15,2,'Centro Pedagógico Torres Quintero',NULL,'Guillermo Prieto No.1499, Benito Juarez, Tecate, B.C.',NULL),(16,2,'Alvaro Obregon',NULL,'Del Rosario S/N, San Fernando, Tecate, B.C.',NULL),(17,2,'Club de Leones Num. 2',NULL,'Cuchuman S/N, Bellavista, Tecate, B.C.',NULL),(18,2,'Club de Leones Num. 3',NULL,'24 de Febrero No.1779, Primero de Mayo, Tecate, B.C.',NULL),(19,2,'Club Rotario',NULL,'Revolucion Mexicana S/N, Rincon Tecate 2A Seccion, Tecate, B.C.',NULL),(20,2,'Emiliano Zapata',NULL,'Revolucion Mexicana S/N, Rincon Tecate, Tecate, B.C.',NULL),(21,2,'Francisco Gonzalez Bocanegra',NULL,'Rio Bravo No.130, Esteban Cantu, Tecate, B.C.',NULL),(22,2,'Miguel Hidalgo Y Costilla',NULL,'Miguel Hidalgo y Costilla S/N, Colonia Baja California, Tecate, B.C.',NULL),(23,2,'Vicente Guerrero',NULL,'San Valentín S/N, Ampliación Descanso, Tecate, B.C.',NULL),(24,2,'Nicolas Bravo',NULL,'La Paz S/N, Ampliación Descanso, Tecate, B.C.',NULL),(25,2,'Joaquín Murrieta',NULL,'Miguel Hidalgo y Costilla S/N, Colonia Baja California, Tecate, B.C.',NULL),(26,2,'21 De Marzo',NULL,'Mision Sto. Domingo No.1107, Tecate, B.C.',NULL),(27,2,'Adolfo Lopez Mateos',NULL,'Tecate, B.C.',NULL),(28,2,'Melchor Ocampo',NULL,'Tecate, B.C.',NULL),(29,2,'Naciones Unidas',NULL,'Tecate, B.C.',NULL),(30,2,'Octavio Paz',NULL,'Tecate, B.C.',NULL),(31,2,'Padre Kino',NULL,'Tecate, B.C.',NULL),(32,2,'Plan De Ayala',NULL,'Tecate, B.C.',NULL),(33,2,'Rafael Ramirez',NULL,'Tecate, B.C.',NULL),(34,2,'Revolucion Mexicana',NULL,'Tecate, B.C.',NULL),(35,2,'Ricardo Flores Magon',NULL,'Tecate, B.C.',NULL),(36,2,'Rotario No. 2',NULL,'Tecate, B.C.',NULL),(37,2,'Tierra Y Libertad',NULL,'Tecate, B.C.',NULL),(38,2,'Walt Disney',NULL,'Tecate, B.C.',NULL),(39,2,'María Montessori',NULL,'Tecate, B.C.',NULL),(40,2,'Luis Donaldo Colosio Murrieta',NULL,'Tecate, B.C.',NULL),(41,3,'Escuela Secundaria General No. 4 Elias Mora Cornejo','02DES0056Q','Tecate, B.C.',NULL),(42,3,'Secundaria General Lázaro Cárdenas','02DES0027V','Tecate, B.C.',NULL),(43,3,'Escuela Secundaria Técnica No. 17 Plan de Ayala','02DST0017G','Valle de las Palmas, Tecate, B.C.',NULL),(44,3,'Rafael Ramirez Castañeda','02DST0026O','La Rumorosa, Tecate, B.C.',NULL),(45,3,'Jóvenes por el Progreso de México','02DST0031Z','Luis Echeverría Álvarez (el Hongo), Tecate, B.C.',NULL),(46,3,'Telesecundaria Num. 110','02DTV0014X','Avenida Maclovio Herrera, Maclovio Herrera, Tecate, B.C.',NULL),(47,3,'Secundaria General Estatal Num. 202 Emiliano Zapata','02EES0048G','Avenida Maclovio Herrera, Maclovio Herrera, Tecate, B.C.',NULL),(48,3,'Secundaria General Num. 217 Centenario de la Revolución','02EES0076C','Maclovio Herrera (colonia Aviación), Tecate, B.C.',NULL),(49,3,'Colegio Tecate','02PES0022E','Tecate, B.C.',NULL),(50,3,'Juan Ma Salvatierra Num. 125','02PES0181T','Tecate, B.C.',NULL),(51,3,'Fernando Montes de Oca Num. 143','02PES0198T','Tecate, B.C.',NULL),(52,3,'Secundaria General Francisco I. Madero','02DES0004K','Jose Gutierrez Duran S/N, Tecate, B.C.',NULL),(53,3,'Secundaria General Juana Inés de la Cruz','02DES0026W','Mision Loreto S/N, Tecate, B.C.',NULL),(54,3,'Escudo Nacional',NULL,'Avenida Maclovio Herrera, Tecate, B.C.',NULL),(55,4,'Colegio de Bachilleres de Baja California Plantel Tecate','02DCB0003K','Presidente Venustiano Carranza No. 10, Tecate Centro, Tecate, B.C.',NULL),(56,4,'Centro de Estudios Tecnológicos Industrial y de Servicios Num. 25',NULL,'Del Técnico Profesional Alberto Aldrete, Tecate, B.C.',NULL),(57,4,'Centro EMSAD Para Trabajadores No. 3',NULL,'Tecate, B.C.',NULL),(58,4,'Centro de Capacitación para el Trabajo Industrial Num. 191',NULL,'Manzana 6 Anexo Parque Industrial S/N, Tecate, B.C.',NULL);
/*!40000 ALTER TABLE `escuelas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `graficos_estadisticas`
--

DROP TABLE IF EXISTS `graficos_estadisticas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `graficos_estadisticas` (
  `id_estadistica` int(11) NOT NULL AUTO_INCREMENT,
  `id_pregunta` int(11) NOT NULL,
  `opcion` varchar(255) DEFAULT NULL,
  `total_respuestas` int(11) DEFAULT 0,
  `fecha_actualizacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_estadistica`),
  KEY `id_pregunta` (`id_pregunta`),
  CONSTRAINT `graficos_estadisticas_ibfk_1` FOREIGN KEY (`id_pregunta`) REFERENCES `preguntas` (`id_pregunta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `graficos_estadisticas`
--

LOCK TABLES `graficos_estadisticas` WRITE;
/*!40000 ALTER TABLE `graficos_estadisticas` DISABLE KEYS */;
/*!40000 ALTER TABLE `graficos_estadisticas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `niveles_educativos`
--

DROP TABLE IF EXISTS `niveles_educativos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `niveles_educativos` (
  `id_nivel` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_nivel` varchar(50) NOT NULL,
  PRIMARY KEY (`id_nivel`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `niveles_educativos`
--

LOCK TABLES `niveles_educativos` WRITE;
/*!40000 ALTER TABLE `niveles_educativos` DISABLE KEYS */;
INSERT INTO `niveles_educativos` VALUES (1,'Preescolar'),(2,'Primaria'),(3,'Secundaria'),(4,'Preparatoria');
/*!40000 ALTER TABLE `niveles_educativos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `opciones_respuesta`
--

DROP TABLE IF EXISTS `opciones_respuesta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opciones_respuesta` (
  `id_opcion` int(11) NOT NULL AUTO_INCREMENT,
  `id_pregunta` int(11) NOT NULL,
  `texto_opcion` varchar(255) DEFAULT NULL,
  `icono` varchar(255) DEFAULT NULL,
  `valor` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_opcion`),
  KEY `id_pregunta` (`id_pregunta`),
  CONSTRAINT `opciones_respuesta_ibfk_1` FOREIGN KEY (`id_pregunta`) REFERENCES `preguntas` (`id_pregunta`)
) ENGINE=InnoDB AUTO_INCREMENT=284 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opciones_respuesta`
--

LOCK TABLES `opciones_respuesta` WRITE;
/*!40000 ALTER TABLE `opciones_respuesta` DISABLE KEYS */;
INSERT INTO `opciones_respuesta` VALUES (9,4,'Sí',NULL,1),(10,4,'No',NULL,2),(11,4,'No estoy seguro',NULL,3),(12,6,'Escuela',NULL,1),(13,6,'Casa',NULL,2),(14,6,'Amigos',NULL,3),(15,6,'Televisión',NULL,4),(16,6,'Otro lugar',NULL,5),(17,7,'Que te brinden atención primero, antes que los adultos',NULL,1),(18,7,'Derecho a la Paz',NULL,2),(19,7,'Derecho a contar con nombre y apellidos',NULL,3),(20,7,'Derecho a vivir en familia',NULL,4),(21,7,'Derecho a no ser discriminado',NULL,5),(22,7,'Derecho a vivir sanos y saludables',NULL,6),(23,7,'Derecho a la educación',NULL,7),(24,7,'Derecho a una vida libre de violencia',NULL,8),(25,7,'Derecho a reunirte con tus amistades',NULL,9),(26,7,'Derecho a la libertad de expresión',NULL,10),(27,7,'Derecho a promover y garantizar los Derechos',NULL,11),(28,7,'Derecho a la salud, a recibir atención médica y medicamentos gratuita',NULL,12),(29,7,'Derecho a vivir en comunidades donde acepten personas con discapacidades',NULL,13),(30,7,'Derecho al descanso y actividades de juegos',NULL,14),(31,7,'Derecho a la libertad de pensamientos y decidir libremente',NULL,15),(32,7,'Derecho a participar y a ser escuchados por las autoridades',NULL,16),(33,7,'Derecho a que no se divulguen comentarios que te dañen o afecten',NULL,17),(34,7,'Derecho a contar con leyes y reglamentos que te protejan',NULL,18),(35,7,'Derecho a infantes de otros países que viven en México',NULL,19),(36,7,'Derecho al acceso de tecnologías de la información y comunicación',NULL,20),(37,8,'Casa',NULL,1),(38,8,'Escuela',NULL,2),(39,8,'Parque',NULL,3),(40,8,'Tienda',NULL,4),(41,8,'Otro lugar',NULL,5),(42,9,'Casa',NULL,1),(43,9,'Escuela',NULL,2),(44,9,'Parque',NULL,3),(45,9,'Tienda',NULL,4),(46,9,'Otro lugar',NULL,5),(47,10,'Sí',NULL,1),(48,10,'No',NULL,2),(49,10,'No estoy seguro',NULL,3),(50,12,'Si',NULL,1),(51,12,'No',NULL,2),(52,12,'A veces',NULL,3),(53,15,'Juegos',NULL,1),(54,15,'Dibujos',NULL,2),(55,15,'Deportes',NULL,3),(56,15,'Pláticas',NULL,4),(57,15,'Otra forma',NULL,5),(58,19,'8 a 11',NULL,1),(59,19,'12 a 14',NULL,2),(60,19,'15 a 17',NULL,3),(61,19,'Otra edad',NULL,4),(62,20,'Niña',NULL,1),(63,20,'Niño',NULL,2),(64,20,'Prefiero no decirlo',NULL,3),(65,20,'Otro',NULL,4),(66,21,'Si',NULL,1),(67,21,'No',NULL,2),(68,21,'No estoy seguro',NULL,3),(69,23,'Escuela',NULL,1),(70,23,'Familia',NULL,2),(71,23,'Amigos',NULL,3),(72,23,'Redes sociales',NULL,4),(73,23,'Otro',NULL,5),(74,24,'Derecho a la Paz',NULL,1),(75,24,'Derecho a vivir en familia',NULL,2),(76,24,'Derecho a la libertad de expresión',NULL,3),(77,24,'Derecho a vivir sanos y saludables',NULL,4),(78,24,'Derecho a una vida libre de violencia',NULL,5),(79,24,'Que te brinden atención primero, antes que los adultos',NULL,6),(80,24,'Derecho a contar con nombre y apellidos',NULL,7),(81,24,'Derecho a no ser discriminado',NULL,8),(82,24,'Derecho a la educación',NULL,9),(83,24,'Derecho a reunirte con tus amistades',NULL,10),(84,24,'Derecho a promover y garantizar los derechos',NULL,11),(85,24,'Derecho a la salud, recibir atención médica y medicamentos gratuitos',NULL,12),(86,24,'Derecho a vivir en comunidades donde acepten personas con discapacidades',NULL,13),(87,24,'Derecho al descanso y actividades de juegos',NULL,14),(88,24,'Derecho a la libertad de pensamientos y decidir libremente',NULL,15),(89,24,'Derecho a participar y a ser escuchados por las autoridades',NULL,16),(90,24,'Derecho a que no se divulguen comentarios que te dañen o afecten',NULL,17),(91,24,'Derecho a contar con leyes y reglamentos que te protejan',NULL,18),(92,24,'Derecho a infantes de otros países que viven en México',NULL,19),(93,24,'Derecho al acceso de tecnologías de la información y comunicación',NULL,20),(94,28,'Si',NULL,1),(95,28,'No',NULL,2),(96,28,'A veces',NULL,3),(97,29,'Física (golpes)',NULL,1),(98,29,'Gritos',NULL,2),(99,29,'Groserías',NULL,3),(100,29,'Omisión de cuidado',NULL,4),(101,29,'Otros',NULL,5),(102,31,'Juegos',NULL,1),(103,31,'Dibujos',NULL,2),(104,31,'Deportes',NULL,3),(105,31,'Pláticas',NULL,4),(106,31,'Talleres',NULL,5),(107,31,'Otra forma:',NULL,6),(108,35,'8 a 11',NULL,1),(109,35,'12 a 14',NULL,2),(110,35,'15 a 17',NULL,3),(111,35,'Otra edad',NULL,4),(112,36,'Mujer',NULL,1),(113,36,'Hombre',NULL,2),(114,36,'Prefiero no decirlo',NULL,3),(115,36,'Otro:',NULL,4),(116,37,'Si',NULL,1),(117,37,'No',NULL,2),(118,37,'No estoy seguro',NULL,3),(119,39,'Escuela',NULL,1),(120,39,'Familia',NULL,2),(121,39,'Amigos',NULL,3),(122,39,'Redes sociales',NULL,4),(123,39,'Otro:',NULL,5),(124,40,'Derecho a la Paz',NULL,1),(125,40,'Derecho a vivir en familia',NULL,2),(126,40,'Derecho a la libertad de expresión',NULL,3),(127,40,'Derecho a vivir sanos y saludables',NULL,4),(128,40,'Derecho a una vida libre de violencia',NULL,5),(129,40,'Que te brinden atención primero, antes que los adultos',NULL,6),(130,40,'Derecho a contar con nombre y apellidos',NULL,7),(131,40,'Derecho a no ser discriminado',NULL,8),(132,40,'Derecho a la educación',NULL,9),(133,40,'Derecho a reunirte con tus amistades',NULL,10),(134,40,'Derecho a promover y garantizar los Derechos',NULL,11),(135,40,'Derecho a la salud, recibir atención médica y medicamentos gratuita',NULL,12),(136,40,'Derecho a vivir en comunidades donde acepten personas con discapacidades',NULL,13),(137,40,'Derecho al descanso y actividades de juegos',NULL,14),(138,40,'Derecho a la libertad de pensamientos y decidir libremente',NULL,15),(139,40,'Derecho a participar y a ser escuchados por las autoridades',NULL,16),(140,40,'Derecho a que no se divulguen comentarios que te dañen o afecten',NULL,17),(141,40,'Derecho a contar con leyes y reglamentos que te protejan',NULL,18),(142,40,'Derecho a infantes de otros países que viven en México',NULL,19),(143,40,'Derecho al acceso de tecnologías de la información y comunicación',NULL,20),(144,45,'Si',NULL,1),(145,45,'No',NULL,2),(146,45,'A veces',NULL,3),(147,46,'Física (golpes)',NULL,1),(148,46,'Gritos',NULL,2),(149,46,'Groserías',NULL,3),(150,46,'Omisión de cuidado',NULL,4),(151,46,'Otros',NULL,5),(152,48,'Talleres',NULL,1),(153,48,'Foros',NULL,2),(154,48,'Campañas en redes',NULL,3),(155,48,'Otro:',NULL,4),(156,52,'8 a 11',NULL,1),(157,52,'12 a 14',NULL,2),(158,52,'15 a 17',NULL,3),(159,52,'Otra edad:',NULL,4),(160,53,'Mujer',NULL,1),(161,53,'Hombre',NULL,2),(162,53,'Prefiero no decirlo',NULL,3),(163,53,'Otro:',NULL,4);
/*!40000 ALTER TABLE `opciones_respuesta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `preguntas`
--

DROP TABLE IF EXISTS `preguntas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `preguntas` (
  `id_pregunta` int(11) NOT NULL AUTO_INCREMENT,
  `id_encuesta` int(11) NOT NULL,
  `texto_pregunta` text NOT NULL,
  `tipo_pregunta` enum('opcion','texto','multiple','imagen','ranking') DEFAULT 'opcion',
  `icono` varchar(255) DEFAULT NULL,
  `orden` int(11) DEFAULT NULL,
  `color_tema` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_pregunta`),
  KEY `id_encuesta` (`id_encuesta`),
  CONSTRAINT `preguntas_ibfk_1` FOREIGN KEY (`id_encuesta`) REFERENCES `encuestas` (`id_encuesta`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `preguntas`
--

LOCK TABLES `preguntas` WRITE;
/*!40000 ALTER TABLE `preguntas` DISABLE KEYS */;
INSERT INTO `preguntas` VALUES (4,4,'¿Has oído hablar de los derechos humanos? ','opcion',NULL,1,NULL),(5,4,'Escribe 1 o 2 derechos que conozcas.','texto',NULL,2,NULL),(6,4,'¿Dónde aprendiste sobre los derechos humanos?','opcion',NULL,3,NULL),(7,4,'Ordena arrastrando del 1 al 20 el derecho de mayor a menor interés.','ranking',NULL,4,NULL),(8,4,'¿Dónde te sientes más seguro con tus derechos?','opcion',NULL,5,NULL),(9,4,'¿Dónde crees que hace falta que te cuiden más?','opcion',NULL,6,NULL),(10,4,'¿Has visto o escuchado algo que te haga sentir que no lo cuidan o te incomoda(o)?','opcion',NULL,7,NULL),(11,4,'Si respondiste \"Sí\", escribe qué pasó.','texto',NULL,8,NULL),(12,4,'¿Has visto a alguien gritar o golpear en los lugares donde vas?','opcion',NULL,9,NULL),(13,4,'¿Qué necesitas para que te cuiden mejor? Escribe una idea','texto',NULL,10,NULL),(15,4,'Marca la forma en que te gustaría aprender sobre tus derechos: (elige la que más te guste)','opcion',NULL,11,NULL),(16,4,'¿Cómo te gustaría que la policía o tus papás te ayuden a estar seguro?','texto',NULL,12,NULL),(17,4,'Si pudieras hacer una regla para que estés seguro, ¿qué harias?','texto',NULL,13,NULL),(18,4,'¿Cómo se llama tu primaria y en qué grado estás?','texto',NULL,14,NULL),(19,4,'¿Cuántos años tienes?','opcion',NULL,15,NULL),(20,4,'¿Eres?','opcion',NULL,16,NULL),(21,5,'¿Has oído hablar de los derechos humanos? ','opcion',NULL,1,NULL),(22,5,'Escribe 2 o 3 derechos humanos que conozcas','texto',NULL,2,NULL),(23,5,'¿Dónde aprendiste sobre los derechos humanos? (Marca todas las que apliquen)','multiple',NULL,3,NULL),(24,5,'Ordena arrastrando del 1 al 20 el derecho de mayor a menor interés','ranking',NULL,4,NULL),(25,5,'¿En qué lugares sientes que tus derechos se respetan más? (Casa / Escuela / Parque / Tienda / Otro) y explica por qué.','texto',NULL,5,NULL),(26,5,'¿Has visto o escuchado algo que te haga pensar que los derechos de alguien (tuyos o de otros) no se respetan? (Si / No / No estoy seguro):','texto',NULL,6,NULL),(27,5,'Si tu respuesta es sí, explica qué pasó (puedes omitir si prefieres).','texto',NULL,7,NULL),(28,5,'¿Crees que hay violencia (gritos, golpes, descuido) en los lugares donde vas?','opcion',NULL,8,NULL),(29,5,'Si respondiste \"Sí\" o \"A veces\", marca los tipos que has visto: ','opcion',NULL,9,NULL),(30,5,'¿Qué crees que necesitan los niños, niñas y adolescentes de tu comunidad para que sus derechos se respeten mejor?','texto',NULL,10,NULL),(31,5,'¿Qué actividades o programas te gustaría que hubiera en tu comunidad para aprender más sobre tus derechos o protegerlos? (elige la que más te guste)','opcion',NULL,11,NULL),(32,5,'¿Cómo te gustaría que las autoridades (policía, gobierno) y la comunidad trabajen juntos para proteger los derechos de las niñas, niños y adolescentes?','texto',NULL,12,NULL),(33,5,'Si pudieras crear una regla o actividad para que se respeten los derechos humanos, ¿qué harías?','texto',NULL,13,NULL),(34,5,'¿Cómo se llama tu secundaria y en qué grado estás?','texto',NULL,14,NULL),(35,5,'¿Cuántos años tienes?','opcion',NULL,15,NULL),(36,5,'¿Eres?','opcion',NULL,16,NULL),(37,6,'¿Has oído hablar de los derechos humanos?','opcion',NULL,1,NULL),(38,6,'Escribe 3-5 derechos humanos que conozcas','texto',NULL,2,NULL),(39,6,'¿Dónde aprendiste sobre los derechos humanos?','opcion',NULL,3,NULL),(40,6,'Ordena arrastrando del 1 al 20 el derecho de mayor a menor interés','ranking',NULL,4,NULL),(41,6,'¿En qué lugares sientes que tus derechos se respetan más? (Casa / Escuela / Parque / Tienda / Otro). Explica por qué','texto',NULL,5,NULL),(42,6,'¿En qué lugares crees que hace falta que se respeten más tus derechos? (Casa / Escuela / Parque / Tienda / Otro). Explica por qué ','texto',NULL,6,NULL),(43,6,'¿Has visto o escuchado algo que te haga pensar que los derechos de alguien (tuyos o de otros) no se respetan? (Sí / No / No estoy seguro): ','texto',NULL,7,NULL),(44,6,'Si tu respuesta es sí, explica qué pasó (puedes omitir si prefieres)','texto',NULL,8,NULL),(45,6,'¿Crees que hay violencia (física, verbal, omisión de cuidados) en los lugares donde vas?','opcion',NULL,9,NULL),(46,6,'Si respondiste \"si\" o \"a veces\", marca los tipos que has visto:','multiple',NULL,10,NULL),(47,6,'¿Qué crees que necesitan los niños, niñas y adolescentes de tu comunidad para que sus derechos se respeten mejor?','texto',NULL,11,NULL),(48,6,'¿Qué actividades o programas te gustaría que hubiera en tu comunidad para aprender más sobre tus derechos o protegerlos?','opcion',NULL,12,NULL),(49,6,'¿Cómo te gustaría que las autoridades (policía, gobierno) y la comunidad trabajen juntos para proteger los derechos de las niñas, niños y adolescentes?','texto',NULL,13,NULL),(50,6,'Si pudieras diseñar una estrategia (regla, actividad o campaña) para que se respeten los derechos humanos, ¿qué harías y cómo lo harías?','texto',NULL,14,NULL),(51,6,'¿Cómo se llama tu preparatoria, en qué grupo y grado estás?','texto',NULL,15,NULL),(52,6,'¿Cuántos años tienes?','opcion',NULL,16,NULL),(53,6,'¿Eres?','opcion',NULL,17,NULL);
/*!40000 ALTER TABLE `preguntas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `respuestas_ranking`
--

DROP TABLE IF EXISTS `respuestas_ranking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `respuestas_ranking` (
  `id_respuesta` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_pregunta` int(11) NOT NULL,
  `id_opcion` int(11) NOT NULL,
  `posicion` int(11) NOT NULL COMMENT 'Posición del 1 al 20',
  `fecha_respuesta` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_respuesta`),
  KEY `idx_usuario_pregunta` (`id_usuario`,`id_pregunta`),
  KEY `idx_pregunta` (`id_pregunta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `respuestas_ranking`
--

LOCK TABLES `respuestas_ranking` WRITE;
/*!40000 ALTER TABLE `respuestas_ranking` DISABLE KEYS */;
/*!40000 ALTER TABLE `respuestas_ranking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `respuestas_usuario`
--

DROP TABLE IF EXISTS `respuestas_usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `respuestas_usuario` (
  `id_respuesta_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `id_encuesta` int(11) NOT NULL,
  `id_pregunta` int(11) NOT NULL,
  `id_opcion` int(11) DEFAULT NULL,
  `respuesta_texto` text DEFAULT NULL,
  `fecha_respuesta` datetime DEFAULT current_timestamp(),
  `edad` varchar(20) DEFAULT NULL,
  `genero` varchar(20) DEFAULT NULL,
  `id_escuela` int(11) DEFAULT NULL,
  `id_turno` int(11) DEFAULT NULL,
  `id_ciclo` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_respuesta_usuario`),
  KEY `id_encuesta` (`id_encuesta`),
  KEY `id_pregunta` (`id_pregunta`),
  KEY `id_opcion` (`id_opcion`),
  KEY `id_escuela` (`id_escuela`),
  KEY `id_turno` (`id_turno`),
  KEY `id_ciclo` (`id_ciclo`),
  CONSTRAINT `respuestas_usuario_ibfk_1` FOREIGN KEY (`id_encuesta`) REFERENCES `encuestas` (`id_encuesta`),
  CONSTRAINT `respuestas_usuario_ibfk_2` FOREIGN KEY (`id_pregunta`) REFERENCES `preguntas` (`id_pregunta`),
  CONSTRAINT `respuestas_usuario_ibfk_3` FOREIGN KEY (`id_opcion`) REFERENCES `opciones_respuesta` (`id_opcion`),
  CONSTRAINT `respuestas_usuario_ibfk_4` FOREIGN KEY (`id_escuela`) REFERENCES `escuelas` (`id_escuela`),
  CONSTRAINT `respuestas_usuario_ibfk_5` FOREIGN KEY (`id_turno`) REFERENCES `turnos` (`id_turno`),
  CONSTRAINT `respuestas_usuario_ibfk_6` FOREIGN KEY (`id_ciclo`) REFERENCES `ciclos_escolares` (`id_ciclo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `respuestas_usuario`
--

LOCK TABLES `respuestas_usuario` WRITE;
/*!40000 ALTER TABLE `respuestas_usuario` DISABLE KEYS */;
/*!40000 ALTER TABLE `respuestas_usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sesiones_admin`
--

DROP TABLE IF EXISTS `sesiones_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sesiones_admin` (
  `id_sesion` int(11) NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) NOT NULL,
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  `ip` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_sesion`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `sesiones_admin_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `usuarios_admin` (`id_admin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sesiones_admin`
--

LOCK TABLES `sesiones_admin` WRITE;
/*!40000 ALTER TABLE `sesiones_admin` DISABLE KEYS */;
/*!40000 ALTER TABLE `sesiones_admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `turnos`
--

DROP TABLE IF EXISTS `turnos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `turnos` (
  `id_turno` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_turno` varchar(50) NOT NULL,
  PRIMARY KEY (`id_turno`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `turnos`
--

LOCK TABLES `turnos` WRITE;
/*!40000 ALTER TABLE `turnos` DISABLE KEYS */;
INSERT INTO `turnos` VALUES (1,'Matutino'),(2,'Vespertino');
/*!40000 ALTER TABLE `turnos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios_admin`
--

DROP TABLE IF EXISTS `usuarios_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios_admin` (
  `id_admin` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id_admin`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios_admin`
--

LOCK TABLES `usuarios_admin` WRITE;
/*!40000 ALTER TABLE `usuarios_admin` DISABLE KEYS */;
INSERT INTO `usuarios_admin` VALUES (1,'administrador','123');
/*!40000 ALTER TABLE `usuarios_admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'glevanco_simpina'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-16  9:02:35
