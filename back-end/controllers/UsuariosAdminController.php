<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/conexion-db.php';
require_once __DIR__ . '/../models/UsuarioAdmin.php';

class UsuariosAdminController
{
    private mysqli $db;

    public function __construct()
    {
        global $conn;
        $this->db = $conn;
    }

    /**
     * Lista todos los usuarios administradores.
     */
    public function listar(): array
    {
        $sql = "SELECT id_admin, usuario, nombre, rol FROM usuarios_admin ORDER BY id_admin ASC";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Crea un nuevo usuario administrador.
     */
    public function crear(string $usuario, string $password, string $nombre, string $rol): array
    {
        if (empty($usuario) || empty($password) || empty($rol)) {
            return ['success' => false, 'error' => 'Datos incompletos.'];
        }

        // Validación de rol (solo si es uno de los permitidos para ser creados)
        $rolesValidos = ['admin', 'acompanamiento', 'evaluacion'];
        if (!in_array($rol, $rolesValidos, true)) {
            return ['success' => false, 'error' => 'Rol inválido.'];
        }

        // Asumo que el campo 'password' en la BD guarda el hash del password plano.
        // **IMPORTANTE**: En un sistema real, deberías usar `password_hash()` aquí.
        // Manteniendo la consistencia de tu código (que parece usar el password plano o una encriptación simple):
        $passwordGuardar = $password;

        // Verificar si el usuario ya existe
        $stmt = $this->db->prepare("SELECT id_admin FROM usuarios_admin WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close();
            return ['success' => false, 'error' => 'El nombre de usuario ya existe.'];
        }
        $stmt->close();

        // Insertar usuario
        $sql = "INSERT INTO usuarios_admin (usuario, password, nombre, rol) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        $stmt->bind_param("ssss", $usuario, $passwordGuardar, $nombre, $rol);
        $ok = $stmt->execute();
        $stmt->close();

        if (!$ok) {
            return ['success' => false, 'error' => 'Error al crear el usuario: ' . $this->db->error];
        }

        return ['success' => true];
    }

    /**
     * Elimina un usuario por ID.
     */
    public function eliminar(int $id): array
    {
        // No permitir la eliminación del ID 1 (admin principal)
        if ($id === 1) {
            return ['success' => false, 'error' => 'No se puede eliminar el usuario principal.'];
        }

        $sql = "DELETE FROM usuarios_admin WHERE id_admin = ? AND id_admin != 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $afectadas = $stmt->affected_rows;
        $stmt->close();

        if ($afectadas === 0) {
            return ['success' => false, 'error' => 'Usuario no encontrado o no autorizado para eliminar.'];
        }

        return ['success' => true];
    }
}