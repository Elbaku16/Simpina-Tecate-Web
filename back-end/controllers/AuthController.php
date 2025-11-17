<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/conexion-db.php';
require_once __DIR__ . '/../models/UsuarioAdmin.php';
require_once __DIR__ . '/../auth/verificar-sesion.php';
require_once __DIR__ . '/../database/Conexion.php';


class AuthController
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    public function generarTokenCSRF(string $form): string
    {
        return generar_csrf($form);
    }

    /**
     * Maneja el login usando POST desde la ruta moderna:
     * /back-end/routes/auth/login.php
     */
    public function login(array $input): array
    {
        // 1. Validar CSRF
        if (!isset($input['csrf_token']) ||
            !validar_csrf($input['csrf_token'], 'login_admin')) {
            return ['success' => false, 'error' => 'csrf'];
        }

        // 2. Validar campos
        $usuario = trim((string) ($input['usuario'] ?? ''));
        $password = (string) ($input['password'] ?? '');

        if ($usuario === '' || $password === '') {
            return ['success' => false, 'error' => 'credenciales'];
        }

        // 3. Rate limit
        $maxIntentos    = 5;
        $tiempoBloqueo  = 180;

        $_SESSION['login_intentos']        ??= 0;
        $_SESSION['login_bloqueado_hasta'] ??= 0;

        if ($_SESSION['login_bloqueado_hasta'] > time()) {
            return ['success' => false, 'error' => 'bloqueo'];
        }

        // 4. Buscar usuario
        $usuarioEntidad = UsuarioAdmin::findByUsername($this->db, $usuario);

        if (!$usuarioEntidad || !$usuarioEntidad->verificarPassword($password)) {

            $_SESSION['login_intentos']++;

            if ($_SESSION['login_intentos'] >= $maxIntentos) {
                $_SESSION['login_bloqueado_hasta'] = time() + $tiempoBloqueo;
                $_SESSION['login_intentos'] = 0;
            }

            return ['success' => false, 'error' => 'credenciales'];
        }

        // 5. Éxito
        $_SESSION['login_intentos'] = 0;
        $_SESSION['login_bloqueado_hasta'] = 0;

        session_regenerate_id(true);

        $_SESSION['uid']          = $usuarioEntidad->getId();
        $_SESSION['usuario']      = $usuarioEntidad->getUsuario();
        $_SESSION['rol']          = 'admin';
        $_SESSION['last_activity'] = time();

        return ['success' => true];
    }

    /**
     * Cierra sesión
     */
    public function logout(): array
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];

            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();

                setcookie(session_name(), '', [
                    'expires'  => time() - 42000,
                    'path'     => $params['path'],
                    'domain'   => $params['domain'],
                    'secure'   => $params['secure'],
                    'httponly' => true,
                    'samesite' => $params['samesite'] ?? 'Lax',
                ]);
            }

            session_destroy();
        }

        return ['success' => true];
    }
}
