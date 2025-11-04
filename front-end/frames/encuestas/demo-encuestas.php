<?php
include($_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/back-end/connect-db/conexion-db.php');

// Obtener nivel desde la URL
$nivel = isset($_GET['nivel']) ? $_GET['nivel'] : 'primaria';

// Relacionar cada nivel con su id_encuesta correspondiente
$niveles = [
    'preescolar' => 1,
    'primaria' => 4,
    'secundaria' => 5,
    'preparatoria' => 6
];

$id_encuesta = $niveles[$nivel] ?? 2; // valor por defecto: primaria
$sql = "
SELECT p.id_pregunta, p.texto_pregunta, p.tipo_pregunta, o.id_opcion, o.texto_opcion
FROM preguntas p
LEFT JOIN opciones_respuesta o ON p.id_pregunta = o.id_pregunta
WHERE p.id_encuesta = $id_encuesta
ORDER BY p.orden ASC;
";

$result = $conn->query($sql);

$preguntas = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id_pregunta'];
        if (!isset($preguntas[$id])) {
            $preguntas[$id] = [
                'id' => $id,
                'texto' => $row['texto_pregunta'],
                'tipo' => $row['tipo_pregunta'],
                'opciones' => []
            ];
        }
        if (!empty($row['texto_opcion'])) {
            $preguntas[$id]['opciones'][] = [
                'id' => $row['id_opcion'],
                'texto' => $row['texto_opcion']
            ];
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encuesta Demo</title>
    <link rel="stylesheet" href="https://framework-gb.cdn.gob.mx/gm/v3/assets/styles/main.css">
    <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/global/inicio.css">
    <link rel="stylesheet" href="/SIMPINNA/front-end/assets/css/encuestas/encuestas.css">
</head>
<body>
    <header> 
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/header.php'); ?>
    </header>
    <main class="encuesta-container">
        <h1>Encuesta para <?php echo ucfirst($nivel); ?></h1>
        <div id="contenedorPreguntas"></div>
        <button id="btnAnterior">Anterior</button>
        <button id="btnSiguiente">Siguiente</button>
    </main>
<footer>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/front-end/includes/footer.php'); ?>
    </footer>
    <script>
        // Enviar preguntas PHP → JS
        const preguntas = <?php echo json_encode(array_values($preguntas), JSON_UNESCAPED_UNICODE); ?>;
    </script>
    <script src="/SIMPINNA/front-end/scripts/encuesta.js"></script>
</body>
</html>
