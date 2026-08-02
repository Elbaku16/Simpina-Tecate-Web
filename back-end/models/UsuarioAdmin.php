<?php

declare(strict_types=1);

require_once __DIR__ . '/../security/PasswordService.php';

class UsuarioAdmin
{
    private int $id;
    private string $usuario;
    private string $passwordHash;
    private string $nombre;
    private string $rol;
    private bool $requiereCambioPassword;

    private function __construct(
        int $id,
        string $usuario,
        string $passwordHash,
        string $nombre,
        string $rol,
        bool $requiereCambioPassword
    )
    {
        $this->id = $id;
        $this->usuario = $usuario;
        $this->passwordHash = $passwordHash;
        $this->nombre = $nombre;
        $this->rol = $rol;
        $this->requiereCambioPassword = $requiereCambioPassword;
    }

    /** Factory que crea entidad desde DB */
    public static function fromArray(array $row): UsuarioAdmin
    {
        return new UsuarioAdmin(
            (int) $row['id_admin'],
            (string) $row['usuario'],
            (string) $row['password'],
            // Opcional: Cambié el nombre por defecto visual también
            (string) ($row['nombre'] ?? 'Secretario Ejecutivo'), 
            
            // CAMBIO AQUÍ: El valor por defecto ahora es el nuevo rol
            (string) ($row['rol'] ?? 'secretario_ejecutivo'),
            (bool) ($row['requiere_cambio_password'] ?? true)
        );
    }

    /** Obtiene admin por username, o null si no existe */
    public static function findByUsername(mysqli $conn, string $usuario): ?UsuarioAdmin
    {
        $sql = "SELECT * FROM usuarios_admin WHERE usuario = ? LIMIT 1";
        $stmt = $conn->prepare($sql);

        if (!$stmt) return null;

        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        return $row ? self::fromArray($row) : null;
    }

    /** Valida el password del usuario */
    public function verificarPassword(string $passwordPlano): bool
    {
        return PasswordService::verificar($passwordPlano, $this->passwordHash);
    }

    /** Getters de la entidad */
    public function getId(): int { return $this->id; }
    public function getUsuario(): string { return $this->usuario; }
    public function getNombre(): string { return $this->nombre; }
    public function getRol(): string { return $this->rol; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function requiereCambioPassword(): bool { return $this->requiereCambioPassword; }
}
