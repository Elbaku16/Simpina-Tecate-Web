<?php
declare(strict_types=1);

header("Content-Type: application/json; charset=utf-8");

// Debug
ini_set('display_errors', '0'); 
error_reporting(E_ALL);

try {
   
    $baseBackend = __DIR__ . '/../../';
    $baseProject = __DIR__ . '/../../../';

    require_once $baseBackend . 'core/bootstrap_session.php';
    require_once $baseBackend . 'auth/verificar-sesion.php';
    
    if (!file_exists($baseBackend . 'controllers/EditarController.php')) {
        throw new Exception("No encuentro EditarController.php");
    }
    require_once $baseBackend . 'controllers/EditarController.php';

    
    requerir_admin(); // Asegura sesión iniciada

    if (!tiene_permiso('modificar_encuesta')) {
        throw new Exception("Permiso denegado", 403);
    }

    $nivel = $_POST['nivel'] ?? null;
    if (!$nivel) {
        throw new Exception("Nivel no recibido");
    }

    $eliminadas = json_decode($_POST['eliminadas'] ?? "[]", true);
    $datosPreguntas = $_POST['preguntas'] ?? []; 


    $finalPreguntas = [];

    $uploadsDir = $baseProject . 'uploads/';

    foreach ($datosPreguntas as $i => $pData) {
        $idPregunta = intval($pData['id'] ?? 0);
        $rutaImagenPregunta = $pData['icono_actual'] ?? null;

        if (isset($_FILES['preguntas']['name'][$i]['imagen']) && 
            $_FILES['preguntas']['error'][$i]['imagen'] === 0) {
            
            $fileName = uniqid() . "_P_" . basename($_FILES['preguntas']['name'][$i]['imagen']);
            $destino = $uploadsDir . 'preguntas/';
            
            if (!file_exists($destino)) mkdir($destino, 0755, true);

            if (move_uploaded_file($_FILES['preguntas']['tmp_name'][$i]['imagen'], $destino . $fileName)) {
                $rutaImagenPregunta = "uploads/preguntas/" . $fileName;
            }
        }

        $pregArr = [
            "id"       => $idPregunta,
            "texto"    => $pData["texto"],
            "tipo"     => $pData["tipo"],
            "orden"    => intval($pData["orden"]),
            "icono"    => $rutaImagenPregunta, 
            "opciones" => []
        ];

        if (isset($pData["opciones"]) && is_array($pData["opciones"])) {
            foreach ($pData["opciones"] as $j => $opData) {
                $idOp = intval($opData["id"] ?? 0);
                $rutaImagenOpcion = $opData['icono_actual'] ?? null;

           
                $fileOp = $_FILES['preguntas']['name'][$i]['opciones'][$j]['imagen'] ?? null;
                $errorOp = $_FILES['preguntas']['error'][$i]['opciones'][$j]['imagen'] ?? 4; // 4 = no file

                if ($fileOp && $errorOp === 0) {
                    $fileNameOp = uniqid() . "_OP_" . basename($fileOp);
                    $destinoOp = $uploadsDir . 'opciones/';
                    
                    if (!file_exists($destinoOp)) mkdir($destinoOp, 0755, true);

                    $tmpOp = $_FILES['preguntas']['tmp_name'][$i]['opciones'][$j]['imagen'];
                    if (move_uploaded_file($tmpOp, $destinoOp . $fileNameOp)) {
                        $rutaImagenOpcion = "uploads/opciones/" . $fileNameOp;
                    }
                }

                $pregArr["opciones"][] = [
                    "id"    => $idOp,
                    "texto" => $opData["texto"] ?? "",
                    "icono" => $rutaImagenOpcion
                ];
            }
        }
        $finalPreguntas[] = $pregArr;
    }

 
    $controller = new EditarController();
    $respuesta = $controller->guardarCambios($nivel, $finalPreguntas, $eliminadas);

    echo json_encode($respuesta);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false, 
        "error" => $e->getMessage()
    ]);
}
exit;
?>