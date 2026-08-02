<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/conexion-db.php';
require_once __DIR__ . '/../models/Resultados.php';

final class ResultadosController
{
    private mysqli $db;

    public function __construct()
    {
        global $conn;
        $this->db = $conn;
    }

    public function resultados(array $req): array
    {
        $nivelNombre = strtolower(trim((string) ($req['nivel'] ?? '')));
        $nivelId = Resultados::obtenerIdNivel([
            'preescolar' => 1, 'primaria' => 2, 'secundaria' => 3, 'preparatoria' => 4,
        ], $nivelNombre);
        if (!$nivelId || !Resultados::obtenerEncuestaId($this->db, $nivelId)) {
            throw new InvalidArgumentException('Nivel o encuesta activa no válidos.');
        }

        $escuela = (int) ($req['escuela'] ?? 0);
        if ($escuela === 9999) $escuela = -1;
        $genero = (string) ($req['genero'] ?? '');
        if (!in_array($genero, ['', 'M', 'F', 'O', 'X'], true)) $genero = '';

        $cicloTexto = trim((string) ($req['ciclo'] ?? ''));
        $ciclo = null;
        if ($cicloTexto !== '') {
            if (!preg_match('/^(\d{4})\s*-\s*(\d{4})$/', $cicloTexto, $m) || (int) $m[2] !== (int) $m[1] + 1) {
                throw new InvalidArgumentException('Filtro de ciclo escolar inválido.');
            }
            $ciclo = [(int) $m[1], (int) $m[2]];
            $cicloTexto = $m[1] . '-' . $m[2];
        }

        $preguntas = Resultados::obtenerPreguntasAcumuladas($this->db, $nivelId);
        $resumen = Resultados::obtenerOpcionesAcumuladas(
            $this->db, $nivelId, $preguntas, $escuela, $genero, $ciclo
        );

        return [
            'nivelNombre' => $nivelNombre,
            'escuelaFiltro' => $escuela,
            'generoFiltro' => $genero,
            'escuelasDelNivel' => Resultados::obtenerEscuelasPorNivel($this->db, $nivelId),
            'preguntas' => $preguntas,
            'opcionesPorPregunta' => $resumen['opciones'],
            'palette' => ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#84cc16','#f97316','#e11d48','#22c55e'],
            'ciclosDisponibles' => Resultados::obtenerCiclos($this->db, $nivelId),
            'cicloFiltro' => $cicloTexto,
            'totalRespuestas' => $resumen['total'],
        ];
    }
}
