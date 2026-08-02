<?php
declare(strict_types=1);

final class PasswordService
{
    public const MIN_LENGTH = 12;
    public const MAX_LENGTH = 128;

    public static function validar(string $password): ?string
    {
        $length = function_exists('mb_strlen') ? mb_strlen($password, 'UTF-8') : strlen($password);
        if ($length < self::MIN_LENGTH) {
            return 'La contraseña debe tener al menos ' . self::MIN_LENGTH . ' caracteres.';
        }
        if ($length > self::MAX_LENGTH) {
            return 'La contraseña no puede exceder ' . self::MAX_LENGTH . ' caracteres.';
        }

        return null;
    }

    public static function hash(string $password): string
    {
        if (!defined('PASSWORD_ARGON2ID')) {
            throw new RuntimeException('El servidor no tiene soporte para Argon2id.');
        }

        $error = self::validar($password);
        if ($error !== null) {
            throw new InvalidArgumentException($error);
        }

        $hash = password_hash($password, PASSWORD_ARGON2ID);
        if (!is_string($hash)) {
            throw new RuntimeException('No se pudo proteger la contraseña.');
        }

        return $hash;
    }

    public static function verificar(string $password, string $hash): bool
    {
        $info = password_get_info($hash);
        if (($info['algoName'] ?? 'unknown') === 'unknown') {
            return false;
        }

        return password_verify($password, $hash);
    }

    public static function necesitaRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_ARGON2ID);
    }
}
