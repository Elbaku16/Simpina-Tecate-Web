<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/conexion-db.php';
require_once __DIR__ . '/../models/Pregunta.php';

class EditarController
{
    private mysqli $db;

    public function __construct()
    {
        // Usamos la conexión global que viene desde conexion-db.php
        global $conn;
        $this->db = $conn;
    }

    /* ============================================================
       OBTENER TODO PARA MOSTRAR EN editar.php
    ============================================================ */
    public function obtenerPreguntas(int $id_encuesta): array
    {
        $sql = "
            SELECT 
                p.id_pregunta,
                p.id_encuesta,
                p.texto_pregunta,
                p.tipo_pregunta,
                p.orden,
                o.id_opcion,
                o.texto_opcion
            FROM preguntas p
            LEFT JOIN opciones_respuesta o ON p.id_pregunta = o.id_pregunta
            WHERE p.id_encuesta = ?
            ORDER BY p.orden ASC, o.id_opcion ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id_encuesta);
        $stmt->execute();
        $res = $stmt->get_result();

        $preguntas = [];

        while ($row = $res->fetch_assoc()) {
            $pid = (int)$row["id_pregunta"];
            if (!isset($preguntas[$pid])) {
                $preguntas[$pid] = new Pregunta($row);
            }
            if ($row["id_opcion"] !== null) {
                $preguntas[$pid]->agregarOpcion($row);
            }
        }

        return array_values($preguntas);
    }

    /* ============================================================
       ACTUALIZAR TEXTO DE PREGUNTA
    ============================================================ */
    public function actualizarPregunta(int $id_pregunta, string $texto): bool
    {
        $sql = "UPDATE preguntas SET texto_pregunta = ? WHERE id_pregunta = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $texto, $id_pregunta);
        return $stmt->execute();
    }

    /* ============================================================
       CAMBIAR TIPO DE PREGUNTA
    ============================================================ */
    public function actualizarTipo(int $id_pregunta, string $tipo): bool
    {
        $sql = "UPDATE preguntas SET tipo_pregunta = ? WHERE id_pregunta = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $tipo, $id_pregunta);
        return $stmt->execute();
    }

    /* ============================================================
       ELIMINAR PREGUNTA
    ============================================================ */
    public function eliminarPregunta(int $id_pregunta): bool
    {
        $this->db->query("DELETE FROM opciones_respuesta WHERE id_pregunta = $id_pregunta");
        return $this->db->query("DELETE FROM preguntas WHERE id_pregunta = $id_pregunta");
    }

    /* ============================================================
       CREAR NUEVA PREGUNTA
    ============================================================ */
    public function crearPregunta(int $id_encuesta): int
    {
        $sql = "
            INSERT INTO preguntas (id_encuesta, texto_pregunta, tipo_pregunta, orden)
            VALUES (?, 'Nueva pregunta', 'texto', 999)
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id_encuesta);
        $stmt->execute();
        return $stmt->insert_id;
    }

    /* ============================================================
       OPCIONES DE PREGUNTA
    ============================================================ */
    public function crearOpcion(int $id_pregunta): int
    {
        $sql = "INSERT INTO opciones_respuesta (id_pregunta, texto_opcion) 
                VALUES (?, 'Nueva opción')";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id_pregunta);
        $stmt->execute();
        return $stmt->insert_id;
    }

    public function actualizarOpcion(int $id_opcion, string $texto): bool
    {
        $sql = "UPDATE opciones_respuesta SET texto_opcion = ? WHERE id_opcion = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $texto, $id_opcion);
        return $stmt->execute();
    }

    public function eliminarOpcion(int $id_opcion): bool
    {
        return $this->db->query("DELETE FROM opciones_respuesta WHERE id_opcion = $id_opcion");
    }
}
