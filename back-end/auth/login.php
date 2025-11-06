<?php
/**
 * ==========================================
 * Archivo: login.php
 * Función: Procesa las credenciales de administrador
 * ==========================================
 *
 * Flujo general:
 * 1. Solo acepta método POST.
 * 2. Valida token CSRF incluido en el formulario.
 * 3. Lee 'email' y 'password' enviados.
 * 4. Busca el usuario en la BD (tabla usuarios).
 * 5. Si no existe o está inactivo → redirige con error.
 * 6. Verifica hash de contraseña con password_verify().
 * 7. Si es correcto:
 *     - Regenera ID de sesión.
 *     - Guarda uid, nombre, rol.
 *     - Redirige a panel-admin.php (si rol=admin).
 * 8. Si es incorrecto:
 *     - Incrementa contador de intentos.
 *     - Redirige a login con mensaje de error.
 *
 * Requiere:
 * - conexion-db.php para acceso a BD
 * - verificar-sesion.php para funciones de sesión
 */
