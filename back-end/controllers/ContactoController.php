<?php
require_once __DIR__ . '/../database/conexion-db.php';
require_once __DIR__ . '/../models/Nivel.php';
require_once __DIR__ . '/../models/Escuela.php';
require_once __DIR__ . '/../models/Contacto.php';

class ContactoController
{
    private mysqli $db;

    public function __construct()
    {
        // Usamos la conexión global generada en conexion-db.php
        global $conn;
        $this->db = $conn;
    }

    public function obtenerDatosFormulario(): array
    {
        return [
            'niveles'  => Nivel::obtenerNiveles($this->db),
            'escuelas' => Escuela::obtenerEscuelasPorNivel($this->db)
        ];
    }

    public function procesarFormulario(array $input): array
    {
        $errores = [];

        // Honeypot
        $hp = trim($input['website'] ?? "");
        if ($hp !== "") {
            return ['success' => false, 'errores' => ['Validación fallida.']];
        }

        $nombre      = trim($input['nombre'] ?? "");
        $nivel       = trim($input['nivel'] ?? "");
        $escuela     = trim($input['escuela'] ?? "");
        $comentarios = trim($input['comentarios'] ?? "");

        if ($nivel === "0" || $nivel === "")
            $errores[] = "Selecciona un nivel educativo.";

        if ($escuela === "0" || $escuela === "")
            $errores[] = "Selecciona una escuela.";

        if ($comentarios === "")
            $errores[] = "Escribe tus comentarios.";

        if ($nombre === "")
            $nombre = "Anónimo";

        if ($errores) {
            return ['ok' => false, 'errores' => $errores];
        }

        $ok = Contacto::guardar($this->db, [
            'nombre'     => $nombre,
            'nivel'      => (int)$nivel,
            'escuela'    => (int)$escuela,
            'comentarios'=> $comentarios
        ]);

        return ['ok' => $ok];
    }
}
