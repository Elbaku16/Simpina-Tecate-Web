<?php
require_once __DIR__ . '/../database/Conexion.php';
require_once __DIR__ . '/../models/Comentario.php';

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

    public function eliminar(int $id): bool
    {
        return Comentario::eliminar($this->db, $id);
    }

    public function cambiarEstado(int $id, string $estado): bool
    {
         if (!in_array($estado, ['pendiente','en_revision','resuelto'])) {
        return false;
    }
        return Comentario::cambiarEstado($this->db, $id, $estado);
    }
}
