<?php
declare(strict_types=1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/core/bootstrap_session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/auth/verificar-sesion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/back-end/controllers/EditarController.php';

if (!tiene_permiso('modificar_encuesta')) {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Permiso denegado"]);
    exit;
}

header("Content-Type: application/json; charset=utf-8");

/* ======================================================
   RECIBIR FORM-DATA
====================================================== */

$nivel = $_POST['nivel'] ?? null;
$eliminadas = json_decode($_POST['eliminadas'] ?? "[]", true);
// PHP procesa automáticamente el array 'preguntas' que viene del FormData
$datosPreguntas = $_POST['preguntas'] ?? [];

if (!$nivel) {
    echo json_encode(["success" => false, "error" => "Nivel no recibido"]);
    exit;
}

/* ======================================================
   PROCESAR PREGUNTAS + OPCIONES
====================================================== */
$finalPreguntas = [];

foreach ($datosPreguntas as $i => $pData) {

    $idPregunta = intval($pData['id'] ?? 0);

    // 1. RECUPERAR RUTA VIEJA (si existe, viene del JS como 'icono_actual')
    $rutaImagenPregunta = $pData['icono_actual'] ?? null;

    // 2. REVISAR SI HAY IMAGEN NUEVA SUBIDA
    if (isset($_FILES["preguntas"]["name"][$i]["imagen"]) &&
        $_FILES["preguntas"]["name"][$i]["imagen"] !== "" &&
        $_FILES["preguntas"]["error"][$i]["imagen"] === 0) {

        $fileTmp = $_FILES["preguntas"]["tmp_name"][$i]["imagen"];
        // Prefijo único para evitar conflictos de cache
        $fileName = uniqid() . "_P_" . basename($_FILES["preguntas"]["name"][$i]["imagen"]);

        // Aseguramos que la carpeta existe
        $destino = $_SERVER['DOCUMENT_ROOT'] . "/uploads/preguntas/";
        if (!file_exists($destino)) mkdir($destino, 0777, true);

        if (move_uploaded_file($fileTmp, $destino . $fileName)) {
            // Sobrescribimos la ruta con la nueva imagen
            $rutaImagenPregunta = "uploads/preguntas/" . $fileName;
        }
    }

    // Construir array final para el controlador
    $pregArr = [
        "id"    => $idPregunta,
        "texto" => $pData["texto"],
        "tipo"  => $pData["tipo"],
        "orden" => intval($pData["orden"]),
        // Aquí pasamos la ruta final (ya sea la vieja recuperada o la nueva subida)
        "icono" => $rutaImagenPregunta, 
        "opciones" => []
    ];

    /* ==============================================
       OPCIONES
    ============================================== */
    if (isset($pData["opciones"]) && is_array($pData["opciones"])) {

        foreach ($pData["opciones"] as $j => $opData) {

            $idOp = intval($opData["id"] ?? 0);

            // 1. RECUPERAR RUTA VIEJA OPCIÓN
            $rutaImagenOpcion = $opData['icono_actual'] ?? null;

            // 2. REVISAR SI HAY IMAGEN NUEVA OPCIÓN
            if (isset($_FILES["preguntas"]["name"][$i]["opciones"][$j]["imagen"]) &&
                $_FILES["preguntas"]["name"][$i]["opciones"][$j]["imagen"] !== "" &&
                $_FILES["preguntas"]["error"][$i]["opciones"][$j]["imagen"] === 0) {

                $fileTmp = $_FILES["preguntas"]["tmp_name"][$i]["opciones"][$j]["imagen"];
                $fileName = uniqid() . "_OP_" . basename($_FILES["preguntas"]["name"][$i]["opciones"][$j]["imagen"]);

                $destino = $_SERVER['DOCUMENT_ROOT'] . "/uploads/opciones/";
                if (!file_exists($destino)) mkdir($destino, 0777, true);

                if (move_uploaded_file($fileTmp, $destino . $fileName)) {
                    $rutaImagenOpcion = "uploads/opciones/" . $fileName;
                }
            }

            $pregArr["opciones"][] = [
                "id" => $idOp,
                "texto" => $opData["texto"] ?? "",
                "icono" => $rutaImagenOpcion
            ];
        }
    }

    $finalPreguntas[] = $pregArr;
}

/* ======================================================
   GUARDAR EN CONTROLADOR
====================================================== */

$controller = new EditarController();
// El controlador recibirá ahora las rutas correctas en el campo 'icono'
$respuesta = $controller->guardarCambios($nivel, $finalPreguntas, $eliminadas);

global $conn;
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}

echo json_encode($respuesta);
exit;