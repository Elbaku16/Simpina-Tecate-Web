<?php
/**
 * ==========================================
 * Archivo: logout.php
 * Función: Cierra la sesión de forma segura
 * ==========================================
 *
 * Flujo general:
 * 1. Elimina todas las variables de sesión.
 * 2. Invalida la cookie de sesión en el navegador.
 * 3. Destruye la sesión del servidor.
 * 4. Redirige a inicio.php con un parámetro (?out=1).
 *
 * Debe poder ejecutarse incluso si no hay sesión activa.
 */
