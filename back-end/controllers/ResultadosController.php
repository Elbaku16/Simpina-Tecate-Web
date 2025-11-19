<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/Conexion.php';
require_once __DIR__ . '/../models/Resultados.php';

class ResultadosController
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    /**
     * Construye todos los datos necesarios para la vista de resultados
     * - nivelNombre: nombre del nivel (preescolar, primaria, ...)
     * - escuelaFiltro: id de escuela seleccionada (0 = todas)
     * - escuelasDelNivel: listado de escuelas para el combo
     * - preguntas: preguntas de la encuesta
     * - opcionesPorPregunta: opciones + totales por pregunta
     * - palette: colores para las gráficas
     */
    public function resultados(array $req): array
    {
        $nivelNombre = strtolower(trim($req['nivel'] ?? ''));

        // Estos son IDs de la tabla niveles (id_nivel),
        // NO son id_encuesta.
        $nivelesMap = [
            'preescolar'   => 1,
            'primaria'     => 2,
            'secundaria'   => 3,
            'preparatoria' => 4
        ];

        $nivelId = Resultados::obtenerIdNivel($nivelesMap, $nivelNombre);
        if (!$nivelId) {
            throw new Exception("Nivel no válido");
        }

        // Buscar la encuesta asociada a ese nivel
        $encuestaId = Resultados::obtenerEncuestaId($this->db, $nivelId);
        if (!$encuestaId) {
            throw new Exception("No se encontró encuesta para este nivel");
        }

        // Filtro de escuela (0 = todas)
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

        // Listado de escuelas del nivel (para el select)
        $escuelasDelNivel = Resultados::obtenerEscuelasPorNivel($this->db, $nivelId);

        // Preguntas de la encuesta
        $preguntas = Resultados::obtenerPreguntas($this->db, $encuestaId);

        // Estadísticas: cuenta respuestas por pregunta/opción
        // - Para opcion/multiple: desde respuestas_usuario
        // - Para ranking: desde respuestas_ranking
        // (El filtro por escuela solo aplica a respuestas_usuario,
        //  ranking no guarda id_escuela actualmente)
        $estadisticas = Resultados::obtenerEstadisticasMixta(
            $this->db,
            $preguntas,
            $escuelaFiltro
        );

        // Opciones de respuesta por pregunta + totales
        $idsPreguntas = array_column($preguntas, 'id_pregunta');
        $opcionesPorPregunta = Resultados::obtenerOpciones(
            $this->db,
            $idsPreguntas,
            $estadisticas
        );
        // Paleta de colores para las gráficas
        $palette = [
            '#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6',
            '#06b6d4','#84cc16','#f97316','#e11d48','#22c55e'
        ];

        return [
            'nivelNombre'       => $nivelNombre,
            'escuelaFiltro'     => $escuelaFiltro,
            'escuelasDelNivel'  => $escuelasDelNivel,
            'preguntas'         => $preguntas,
            'opcionesPorPregunta' => $opcionesPorPregunta,
            'palette'           => $palette
        ];
    }
}