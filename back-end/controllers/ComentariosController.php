<?php
require_once __DIR__ . '/../database/Conexion.php';
require_once __DIR__ . '/../models/Comentario.php';
require_once __DIR__ . '/../models/Historial.php';

class ComentariosController
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    public function listar(array $req): array
    {
        return Comentario::listar($this->db, [
            'estado' => $req['estado'] ?? '',
            'nivel' => $req['nivel'] ?? 0,
            'busqueda' => $req['busqueda'] ?? ''
        ]);
    }

    public function eliminar(int $id, string $usuario = 'Administrador'): bool
    {
        return Comentario::eliminar($this->db, $id, $usuario);
    }

    public function cambiarEstado(int $id, string $estado, string $usuario = 'Administrador'): bool
    {
        if (!in_array($estado, ['pendiente','en_revision','resuelto'])) {
            return false;
        }
        return Comentario::cambiarEstado($this->db, $id, $estado, $usuario);
    }

    /**
     * Obtiene todo el historial de cambios
     */
    public function obtenerHistorial(array $filtros = []): array
    {
        return Historial::obtenerTodo($this->db, $filtros);
    }

    /**
     * Obtiene el historial de un comentario específico
     */
    public function obtenerHistorialComentario(int $idContacto): array
    {
        return Historial::obtenerPorComentario($this->db, $idContacto);
    }

    /**
     * Obtiene estadísticas del historial
     */
    public function obtenerEstadisticasHistorial(): array
    {
        return Historial::obtenerEstadisticas($this->db);
    }

    /**
     * Cuenta el total de comentarios
     */
    public function contarTotal(array $filtros = []): int
    {
        return Comentario::contarTotal($this->db, $filtros);
    }
}
