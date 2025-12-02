<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/conexion-db.php';
require_once __DIR__ . '/../models/Resultados.php';

class ResultadosController
{
    private mysqli $db;

    public function __construct()
    {
        global $conn;
        $this->db = $conn;
    }

    /**
     * Obtener ciclos escolares detectando automáticamente por fecha
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

            // Ciclo escolar (Agosto → Julio)
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

        // --- FILTROS ---
        $escuelaFiltro = isset($req['escuela']) ? (int)$req['escuela'] : 0;
        
        $generoFiltro = $req['genero'] ?? '';
        if (!in_array($generoFiltro, ['M', 'F', 'O', 'X'])) { 
            $generoFiltro = ''; 
        }

        $cicloFiltro = $req['ciclo'] ?? '';
        $cicloRango = null; 
        if ($cicloFiltro && strpos($cicloFiltro, '-') !== false) {
            list($inicio, $fin) = explode('-', $cicloFiltro);
            $cicloRango = [(int)$inicio, (int)$fin]; 
        }

        // --- DATOS BÁSICOS ---
        $escuelasDelNivel = Resultados::obtenerEscuelasPorNivel($this->db, $nivelId);
        
        // 1. Obtener Preguntas
        $preguntas = Resultados::obtenerPreguntas($this->db, $encuestaId);

        // --- CÁLCULO DE ESTADÍSTICAS ---
        // Separamos IDs por tipo para llamar a la función correcta del modelo
        $idsOpcion = [];
        $idsMultiple = [];
        $idsRanking = [];

        foreach ($preguntas as $p) {
            $tipo = strtolower(trim($p['tipo_pregunta']));
            $pid = (int)$p['id_pregunta'];
            
            if ($tipo === 'opcion') $idsOpcion[] = $pid;
            elseif ($tipo === 'multiple') $idsMultiple[] = $pid;
            elseif ($tipo === 'ranking') $idsRanking[] = $pid;
        }

        $stats = [];

        // Estadísticas Opción Simple
        if (!empty($idsOpcion)) {
            $s = Resultados::obtenerEstadisticasOpciones(
                $this->db, $idsOpcion, $escuelaFiltro, $generoFiltro, $cicloRango
            );
            $stats += $s;
        }

        // Estadísticas Opción Múltiple
        if (!empty($idsMultiple)) {
            $s = Resultados::obtenerEstadisticasOpciones(
                $this->db, $idsMultiple, $escuelaFiltro, $generoFiltro, $cicloRango
            );
            $stats += $s;
        }

        // Estadísticas Ranking (Aquí es donde se arregló el promedio)
        if (!empty($idsRanking)) {
            $s = Resultados::obtenerEstadisticasRanking(
                $this->db, $idsRanking, $escuelaFiltro, $generoFiltro, $cicloRango
            );
            $stats += $s;
        }

        // --- FUSIÓN DE DATOS (Texto + Estadísticas) ---
        // Obtenemos el texto de las opciones
        $allIds = array_column($preguntas, 'id_pregunta');
        $rawOptions = Resultados::obtenerOpcionesPreguntas($this->db, $allIds);

        $opcionesPorPregunta = [];
        $totalRespuestasGlobal = 0; // Para el encabezado

        foreach ($rawOptions as $pid => $opts) {
            $sumaVotosPregunta = 0;
            
            foreach ($opts as $oid => $texto) {
                // Valores por defecto
                $data = [
                    'texto' => $texto,
                    'total' => 0,
                    'promedio' => 0
                ];

                // Si hay estadísticas para esta pregunta y opción, las asignamos
                if (isset($stats[$pid][$oid])) {
                    $st = $stats[$pid][$oid];
                    // Si es un array (ranking), extraer total y promedio
                    // Si es un entero (opcion/multiple), es solo el total
                    if (is_array($st)) {
                        $data['total'] = $st['total'] ?? 0;
                        $data['promedio'] = $st['promedio_posicion'] ?? 0;
                    } else {
                        $data['total'] = (int)$st;
                        $data['promedio'] = 0;
                    }
                }

                $sumaVotosPregunta += $data['total'];
                $opcionesPorPregunta[$pid][] = $data;
            }
            
            // Actualizar el máximo global de respuestas encontradas
            if ($sumaVotosPregunta > $totalRespuestasGlobal) {
                $totalRespuestasGlobal = $sumaVotosPregunta;
            }
        }

        // Paleta de colores para gráficas
        $palette = [
            '#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6',
            '#06b6d4','#84cc16','#f97316','#e11d48','#22c55e'
        ];

        $ciclosDisponibles = $this->obtenerCiclosEscolares($encuestaId);

        return [
            'nivelNombre'         => $nivelNombre,
            'escuelaFiltro'       => $escuelaFiltro,
            'generoFiltro'        => $generoFiltro,
            'escuelasDelNivel'    => $escuelasDelNivel,
            'preguntas'           => $preguntas,
            'opcionesPorPregunta' => $opcionesPorPregunta,
            'palette'             => $palette,
            'ciclosDisponibles'   => $ciclosDisponibles,
            'cicloFiltro'         => $cicloFiltro,
            'totalRespuestas'     => $totalRespuestasGlobal
        ];
    }
}