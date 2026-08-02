-- Alinea la tabla existente con la gestión de usuarios administrativos.
-- Los valores por defecto conservan y habilitan al administrador principal.
ALTER TABLE `usuarios_admin`
  ADD COLUMN `nombre` varchar(120) NOT NULL DEFAULT 'Secretario Ejecutivo' AFTER `password`,
  ADD COLUMN `rol` enum('secretario_ejecutivo','acompanamiento','evaluacion') NOT NULL DEFAULT 'secretario_ejecutivo' AFTER `nombre`;
