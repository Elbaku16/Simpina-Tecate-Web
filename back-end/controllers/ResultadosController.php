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
     * ===============================================================
     *   FUNCIÓN NUEVA: Obtener ciclos escolares a partir de las fechas
     * ===============================================================
     */
    private function obtenerCiclosEscolares(int $encuestaId): array
    {
        $sql = "SELECT DISTINCT YEAR(fecha_respuesta) as anio, MONTH(fecha_respuesta) as mes
                FROM respuestas_usuario ru
                INNER JOIN preguntas p ON ru.id_pregunta = p.id_pregunta
                WHERE p.id_encuesta = ?
                ORDER BY anio DESC, mes DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $encuestaId);
        $stmt->execute();
        $result = $stmt->get_result();

        $ciclosUnicos = [];

        while ($row = $result->fetch_assoc()) {
            $anio = (int)$row['anio'];
            $mes  = (int)$row['mes'];

            // Ciclo escolar: Agosto (8) → Julio (7)
            if ($mes >= 8) {
                $cicloInicio = $anio;
                $cicloFin    = $anio + 1;
            } else {
                $cicloInicio = $anio - 1;
                $cicloFin    = $anio;
            }

            $key = "$cicloInicio-$cicloFin";

            if (!isset($ciclosUnicos[$key])) {
                $ciclosUnicos[$key] = [
                    'inicio' => $cicloInicio,
                    'fin'    => $cicloFin,
                    'label'  => "$cicloInicio - $cicloFin"
                ];
            }
        }

        // Orden descendente
        $ciclos = array_values($ciclosUnicos);
        usort($ciclos, fn($a, $b) => $b['inicio'] <=> $a['inicio']);

        return $ciclos;
    }

    /**
     * Construye los datos necesarios para la vista de resultados
     */
    public function resultados(array $req): array
    {
        $nivelNombre = strtolower(trim($req['nivel'] ?? ''));

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

        // Obtener encuesta asociada
        $encuestaId = Resultados::obtenerEncuestaId($this->db, $nivelId);
        if (!$encuestaId) {
            throw new Exception("No se encontró encuesta para este nivel");
        }

        // Filtro por escuela
        $escuelaFiltro = isset($req['escuela']) ? (int)$req['escuela'] : 0;

        /**
         * ================================
         *   NUEVO: Filtro por ciclo escolar
         * ================================
         */
        $cicloFiltro = $req['ciclo'] ?? '';
        $cicloInicio = null;
        $cicloFin    = null;

        if ($cicloFiltro && strpos($cicloFiltro, '-') !== false) {
            list($cicloInicio, $cicloFin) = explode('-', $cicloFiltro);
            $cicloInicio = (int)$cicloInicio;
            $cicloFin    = (int)$cicloFin;
        }

        // Listado de escuelas del nivel
        $escuelasDelNivel = Resultados::obtenerEscuelasPorNivel($this->db, $nivelId);

        // Preguntas
        $preguntas = Resultados::obtenerPreguntas($this->db, $encuestaId);

        // Estadísticas (con filtro por escuela)
        $estadisticas = Resultados::obtenerEstadisticasMixta(
            $this->db,
            $preguntas,
            $escuelaFiltro
        );

        // Opciones por pregunta
        $idsPreguntas = array_column($preguntas, 'id_pregunta');
        $opcionesPorPregunta = Resultados::obtenerOpciones(
            $this->db,
            $idsPreguntas,
            $estadisticas
        );

        // Paleta
        $palette = [
            '#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6',
            '#06b6d4','#84cc16','#f97316','#e11d48','#22c55e'
        ];

        /**
         * ========================================
         *   N U E V O — Lista de ciclos escolares
         * ========================================
         */
        $ciclosDisponibles = $this->obtenerCiclosEscolares($encuestaId);

        return [
            'nivelNombre'         => $nivelNombre,
            'escuelaFiltro'       => $escuelaFiltro,
            'escuelasDelNivel'    => $escuelasDelNivel,
            'preguntas'           => $preguntas,
            'opcionesPorPregunta' => $opcionesPorPregunta,
            'palette'             => $palette,

            // 🔵 NUEVO
            'ciclosDisponibles'   => $ciclosDisponibles,
            'cicloFiltro'         => $cicloFiltro
        ];
    }
}
