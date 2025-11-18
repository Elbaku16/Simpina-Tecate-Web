<?php
require_once __DIR__ . '/../database/Conexion.php';
require_once __DIR__ . '/../models/Resultados.php';

class ResultadosController
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    public function resultados(array $req): array
    {
        $nivelNombre = strtolower(trim($req['nivel'] ?? ''));

        $nivelesMap = [
            'preescolar' => 1,
            'primaria'   => 2,
            'secundaria' => 3,
            'preparatoria' => 4
        ];

        $nivelId = Resultados::obtenerIdNivel($nivelesMap, $nivelNombre);
        if (!$nivelId) {
            throw new Exception("Nivel no válido");
        }

        $encuestaId = Resultados::obtenerEncuestaId($this->db, $nivelId);
        if (!$encuestaId) {
            throw new Exception("No se encontró encuesta");
        }

        $escuelaFiltro = isset($req['escuela']) ? (int)$req['escuela'] : 0;
        
        // NUEVO: Obtener filtro de ciclo escolar
        $cicloFiltro = isset($req['ciclo']) ? $req['ciclo'] : '';
        $cicloInicio = null;
        $cicloFin = null;
        
        if ($cicloFiltro && strpos($cicloFiltro, '-') !== false) {
            list($cicloInicio, $cicloFin) = explode('-', $cicloFiltro);
            $cicloInicio = (int)$cicloInicio;
            $cicloFin = (int)$cicloFin;
        }

        $escuelasDelNivel = Resultados::obtenerEscuelasPorNivel($this->db, $nivelId);
        $preguntas = Resultados::obtenerPreguntas($this->db, $encuestaId);

        $idsPreguntas = array_column($preguntas, 'id_pregunta');

        // MODIFICADO: Pasar los parámetros de ciclo escolar
        $estadisticas = Resultados::obtenerEstadisticas($this->db, $idsPreguntas, $escuelaFiltro, $cicloInicio, $cicloFin);
        $opcionesPorPregunta = Resultados::obtenerOpciones($this->db, $idsPreguntas, $estadisticas);

        $palette = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#84cc16','#f97316','#e11d48','#22c55e'];

        return [
            'nivelNombre' => $nivelNombre,
            'escuelaFiltro' => $escuelaFiltro,
            'escuelasDelNivel' => $escuelasDelNivel,
            'preguntas' => $preguntas,
            'opcionesPorPregunta' => $opcionesPorPregunta,
            'palette' => $palette,
            'conn' => $this->db  // NUEVO: Pasar la conexión para usar en la vista
        ];
    }
}