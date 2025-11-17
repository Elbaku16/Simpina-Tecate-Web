<?php

declare(strict_types=1);

class UsuarioAdmin
{
    private int $id;
    private string $usuario;
    private string $passwordHash;

    private function __construct(int $id, string $usuario, string $passwordHash)
    {
        $this->id = $id;
        $this->usuario = $usuario;
        $this->passwordHash = $passwordHash;
    }

    /** Factory que crea entidad desde DB */
    public static function fromArray(array $row): UsuarioAdmin
    {
        return new UsuarioAdmin(
            (int) $row['id_admin'],
            (string) $row['usuario'],
            (string) $row['password']
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
    return hash_equals($this->passwordHash, $passwordPlano);
}


    /** Getters de la entidad */
    public function getId(): int { return $this->id; }
    public function getUsuario(): string { return $this->usuario; }
}

