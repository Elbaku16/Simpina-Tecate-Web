<?php

declare(strict_types=1);

class UsuarioAdmin
{
    private int $id;
    private string $usuario;
    private string $passwordHash;
    private string $nombre;
    private string $rol; // 1. Nueva propiedad para el rol

    // 2. Actualizamos el constructor para recibir el rol
    private function __construct(int $id, string $usuario, string $passwordHash, string $nombre, string $rol)
    {
        $this->id = $id;
        $this->usuario = $usuario;
        $this->passwordHash = $passwordHash;
        $this->nombre = $nombre;
        $this->rol = $rol; // Asignación del rol
    }

    /** Factory que crea entidad desde DB */
    public static function fromArray(array $row): UsuarioAdmin
    {
        return new UsuarioAdmin(
            (int) $row['id_admin'],
            (string) $row['usuario'],
            (string) $row['password'],
            (string) ($row['nombre'] ?? 'Administrador'), 
            // 3. Extraemos el rol. Por defecto 'admin' si no existe (compatibilidad).
            (string) ($row['rol'] ?? 'admin') 
        );
    }

    /** Obtiene admin por username, o null si no existe */
    public static function findByUsername(mysqli $conn, string $usuario): ?UsuarioAdmin
    {
        // Aseguramos que la consulta seleccione también el campo 'rol' (asumo que se añade en el SQL)
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
        // Nota: Idealmente deberías usar password_verify() si usas hashes reales (bcrypt/argon2)
        // Pero respeto tu lógica actual:
        return hash_equals($this->passwordHash, $passwordPlano);
    }

    /** Getters de la entidad */
    public function getId(): int { return $this->id; }
    public function getUsuario(): string { return $this->usuario; }
    public function getNombre(): string { return $this->nombre; }
    public function getRol(): string { return $this->rol; } // 4. Nuevo Getter para el Rol
}