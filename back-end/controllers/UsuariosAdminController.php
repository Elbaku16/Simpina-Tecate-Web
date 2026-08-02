<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/conexion-db.php';
require_once __DIR__ . '/../models/UsuarioAdmin.php';
require_once __DIR__ . '/../security/PasswordService.php';

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
        $usuario = trim($usuario);
        $nombre = trim($nombre);
        if ($usuario === '' || $password === '' || $nombre === '' || $rol === '') {
            return ['success' => false, 'error' => 'Datos incompletos.'];
        }

        if (preg_match('/^[A-Za-z0-9._-]{3,50}$/', $usuario) !== 1) {
            return ['success' => false, 'error' => 'El usuario debe tener de 3 a 50 caracteres y usar solo letras, números, punto, guion o guion bajo.'];
        }

        $nombreLength = function_exists('mb_strlen') ? mb_strlen($nombre, 'UTF-8') : strlen($nombre);
        if ($nombreLength > 120) {
            return ['success' => false, 'error' => 'El nombre no puede exceder 120 caracteres.'];
        }

        // CAMBIO AQUÍ: Reemplazamos 'admin' por 'secretario_ejecutivo'
        $rolesValidos = ['secretario_ejecutivo', 'acompanamiento', 'evaluacion'];
        
        if (!in_array($rol, $rolesValidos, true)) {
            return ['success' => false, 'error' => 'Rol inválido.'];
        }

        $passwordError = PasswordService::validar($password);
        if ($passwordError !== null) {
            return ['success' => false, 'error' => $passwordError];
        }

        $passwordGuardar = PasswordService::hash($password);

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
        $sql = "INSERT INTO usuarios_admin
                (usuario, password, nombre, rol, requiere_cambio_password, password_actualizada_en)
                VALUES (?, ?, ?, ?, 0, NOW())";
        $stmt = $this->db->prepare($sql);
        
        $stmt->bind_param("ssss", $usuario, $passwordGuardar, $nombre, $rol);
        $ok = $stmt->execute();
        $stmt->close();

        if (!$ok) {
            return ['success' => false, 'error' => 'No se pudo crear el usuario.'];
        }

        return ['success' => true];
    }

    /**
     * Elimina un usuario por ID.
     */
    public function eliminar(int $id, int $idActual): array
    {
        // No permitir la eliminación del ID 1 (generalmente el usuario principal/root)
        if ($id === 1) {
            return ['success' => false, 'error' => 'No se puede eliminar el usuario principal.'];
        }

        if ($id === $idActual) {
            return ['success' => false, 'error' => 'No puedes eliminar tu propia cuenta.'];
        }

        $stmtRol = $this->db->prepare('SELECT rol FROM usuarios_admin WHERE id_admin = ? LIMIT 1');
        $stmtRol->bind_param('i', $id);
        $stmtRol->execute();
        $rolObjetivo = $stmtRol->get_result()->fetch_column();
        $stmtRol->close();

        if ($rolObjetivo === 'secretario_ejecutivo') {
            $totalSecretarios = (int) $this->db
                ->query("SELECT COUNT(*) FROM usuarios_admin WHERE rol = 'secretario_ejecutivo'")
                ->fetch_column();
            if ($totalSecretarios <= 1) {
                return ['success' => false, 'error' => 'No se puede eliminar al último Secretario Ejecutivo.'];
            }
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
