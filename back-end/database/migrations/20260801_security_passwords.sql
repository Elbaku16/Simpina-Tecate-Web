-- Esta migración se acompaña de un hook del ejecutor que invalida cualquier
-- secreto anterior usando Argon2id y obliga a restablecer todas las cuentas.
ALTER TABLE `usuarios_admin`
  ADD COLUMN `requiere_cambio_password` tinyint(1) NOT NULL DEFAULT 1 AFTER `rol`,
  ADD COLUMN `password_actualizada_en` datetime NULL AFTER `requiere_cambio_password`;
