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
}
