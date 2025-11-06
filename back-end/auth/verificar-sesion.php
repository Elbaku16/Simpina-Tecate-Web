<?php
/**
 * ==========================================
 * Archivo: verificar-sesion.php
 * Función: Helpers de autenticación y roles
 * ==========================================
 *
 * Funciones esperadas:
 * - usuario_autenticado() → retorna true si hay sesión activa.
 * - rol_es($rol) → retorna true si el rol de sesión coincide.
 * - requerir_admin() → redirige a inicio.php si el usuario no es admin.
 * - generar_csrf() → crea token de formulario y lo guarda en sesión.
 * - validar_csrf($token) → verifica coincidencia del token.
 *
 * Este archivo se incluye en cualquier script que requiera control de acceso.
 * Se apoya en bootstrap_session.php para asegurar que la sesión esté activa.
 */
