<?php
declare(strict_types=1);

class Pregunta
{
    public int $id;
    public int $id_encuesta;
    public string $texto;
    public string $tipo;          // opcion, multiple, texto, ranking, dibujo
    public ?string $icono;
    public ?int $orden;
    public ?string $color_tema;

    /** @var array<int, array{id:int, texto:string, icono:?string, valor:?int}> */
    public array $opciones = [];

    public function __construct(array $data)
    {
        $this->id         = (int)$data['id_pregunta'];
        $this->id_encuesta = (int)$data['id_encuesta'];
        $this->texto      = $data['texto_pregunta'];
        $this->tipo       = strtolower(trim($data['tipo_pregunta'] ?? 'texto'));
        $this->icono      = $data['icono'] ?? null;
        $this->orden      = isset($data['orden']) ? (int)$data['orden'] : null;
        $this->color_tema = $data['color_tema'] ?? null;
    }

    public function agregarOpcion(array $op): void
    {
        $this->opciones[] = [
            'id'    => (int)$op['id_opcion'],
            'texto' => trim((string)$op['texto_opcion']),
            'icono' => $op['icono'] ?? null,
            'valor' => isset($op['valor']) ? (int)$op['valor'] : null,
        ];
    }

    /** Normaliza tipo usando valores reales de DB */
    public function tipoNormalizado(): string
    {
        $t = $this->tipo;

        // soporte oficial para dibujo (nuevo tipo)
        if ($t === 'dibujo' || $t === 'imagen' || $t === 'canvas') {
            return 'dibujo';
        }

        return match ($t) {
            'opcion', 'radio'   => 'opcion',
            'multiple', 'multi' => 'multiple',
            'texto', 'text'     => 'texto',
            'ranking', 'orden'  => 'ranking',
            default             => 'texto'
        };
    }
    public static function eliminarCompleto(mysqli $db, int $idPregunta): void
    {
        // 1. Eliminar respuestas de usuarios (texto/dibujos)
        $stmt = $db->prepare("DELETE FROM respuestas_usuario WHERE id_pregunta = ?");
        $stmt->bind_param("i", $idPregunta);
        $stmt->execute();
        $stmt->close();

        // 2. Eliminar respuestas de ranking
        $stmt = $db->prepare("DELETE FROM respuestas_ranking WHERE id_pregunta = ?");
        $stmt->bind_param("i", $idPregunta);
        $stmt->execute();
        $stmt->close();

        // 3. Eliminar opciones
        $stmt = $db->prepare("DELETE FROM opciones_respuesta WHERE id_pregunta = ?");
        $stmt->bind_param("i", $idPregunta);
        $stmt->execute();
        $stmt->close();

        // 4. Eliminar la pregunta en sí
        $stmt = $db->prepare("DELETE FROM preguntas WHERE id_pregunta = ?");
        $stmt->bind_param("i", $idPregunta);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Crea o actualiza una pregunta
     */
    public static function guardar(mysqli $db, int $idEncuesta, array $datos): int
    {
        $id    = (int)($datos['id_pregunta'] ?? 0);
        $texto = trim($datos['texto'] ?? "");
        $tipo  = strtolower(trim($datos['tipo'] ?? "texto"));
        $orden = (int)($datos['orden'] ?? 0);

        // Validación básica de tipo
        $tiposValidos = ['opcion','multiple','ranking','texto','dibujo'];
        if (!in_array($tipo, $tiposValidos, true)) {
            $tipo = 'texto';
        }

        if ($id > 0) {
            // UPDATE
            $sql = "UPDATE preguntas SET texto_pregunta=?, tipo_pregunta=?, orden=? WHERE id_pregunta=?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("ssii", $texto, $tipo, $orden, $id);
            $stmt->execute();
            $stmt->close();
            return $id;
        } else {
            // INSERT
            $sql = "INSERT INTO preguntas (id_encuesta, texto_pregunta, tipo_pregunta, orden) VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("issi", $idEncuesta, $texto, $tipo, $orden);
            $stmt->execute();
            $newId = $stmt->insert_id;
            $stmt->close();
            return $newId;
        }
    }

    /**
     * Reemplaza las opciones de una pregunta (Borra y crea nuevas)
     */
    public static function reemplazarOpciones(mysqli $db, int $idPregunta, array $opciones): void
    {
        // 1. Borrar anteriores
        $stmt = $db->prepare("DELETE FROM opciones_respuesta WHERE id_pregunta=?");
        $stmt->bind_param("i", $idPregunta);
        $stmt->execute();
        $stmt->close();

        // 2. Insertar nuevas
        $sql = "INSERT INTO opciones_respuesta (id_pregunta, texto_opcion) VALUES (?, ?)";
        $stmt = $db->prepare($sql);

        foreach ($opciones as $op) {
            $texto = trim($op['texto'] ?? "");
            if ($texto === "") continue;
            
            $stmt->bind_param("is", $idPregunta, $texto);
            $stmt->execute();
        }
        $stmt->close();
    }
    
}
