<?php
/**
 * ==========================================
 * Archivo: bootstrap_session.php
 * Función: Inicializa la sesión con cookies seguras
 * ==========================================
 *
 * - Se ejecuta al inicio de cualquier script que use $_SESSION.
 * - Configura los parámetros de cookie (secure, httponly, samesite).
 * - Inicia la sesión si no está activa.
 * - Regenera el ID de sesión al inicio (protege contra fijación).
 * - Puede almacenar marcas como:
 *    __init → marca de inicio
 *    last_activity → tiempo de última acción
 * - No produce salida HTML.
 */
