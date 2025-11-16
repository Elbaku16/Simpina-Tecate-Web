# SIMPINNA Backend Refactor — Contexto para Codex

Este proyecto está siendo migrado desde un backend plano en PHP hacia una estructura MVC organizada.

## Objetivos generales
- Separar frontend y backend.
- Eliminar lógica mezclada en vistas.
- Mover consultas SQL a modelos.
- Centralizar rutas en `/routes`.
- Mover lógica a `/controllers`.
- Estandarizar respuestas JSON.
- Preparar estructura para escalabilidad del sistema SIMPINNA.

## Estado actual
- El módulo AUTH ya fue reorganizado.
- Aún necesita mover login/logout al AuthController.
- Las demás áreas (contacto, encuestas, comentarios, resultados) mantienen lógica mezclada en PHP + HTML.
- La conexión a la base de datos sigue en `/database/conexion-db.php`.

## Instrucciones para Codex
- No eliminar funcionalidad.
- No romper compatibilidad con los frames existentes.
- Mantener IDs, nombres de inputs y estructura HTML.
- No cambiar estructura de base de datos (a menos que se pida).
- Toda refactorización debe ser incremental.
