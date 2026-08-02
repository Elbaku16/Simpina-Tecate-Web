<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
error_reporting(E_ALL);

$archivosNuevos = [];

try {
    $baseBackend = __DIR__ . '/../../';
    require_once $baseBackend . 'core/bootstrap_session.php';
    require_once $baseBackend . 'auth/verificar-sesion.php';
    require_once $baseBackend . 'controllers/EditarController.php';
    require_once $baseBackend . 'helpers/ImagenEditorialHelper.php';

    requerir_admin();
    if (!tiene_permiso('modificar_encuesta')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Permiso denegado.']);
        exit;
    }
    requerir_post_csrf(true);

    $nivel = trim((string) ($_POST['nivel'] ?? ''));
    $idOrigen = (int) ($_POST['id_encuesta_origen'] ?? 0);
    $versionOrigen = (int) ($_POST['version_origen'] ?? 0);
    $datosPreguntas = $_POST['preguntas'] ?? [];
    if (!is_array($datosPreguntas)) {
        throw new InvalidArgumentException('El formato de preguntas no es válido.');
    }

    /** @return ?array{name:string,type:string,tmp_name:string,error:int,size:int} */
    function archivoAnidado(array $files, array $ruta): ?array
    {
        $campos = ['name', 'type', 'tmp_name', 'error', 'size'];
        $salida = [];
        foreach ($campos as $campo) {
            $valor = $files[$campo] ?? null;
            foreach ($ruta as $segmento) {
                if (!is_array($valor) || !array_key_exists($segmento, $valor)) {
                    return null;
                }
                $valor = $valor[$segmento];
            }
            $salida[$campo] = $valor;
        }
        return $salida;
    }

    $finalPreguntas = [];
    $files = isset($_FILES['preguntas']) && is_array($_FILES['preguntas'])
        ? $_FILES['preguntas']
        : [];

    foreach (array_values($datosPreguntas) as $i => $pData) {
        if (!is_array($pData)) {
            throw new InvalidArgumentException('Una pregunta tiene formato inválido.');
        }
        $pregunta = [
            'id' => (int) ($pData['id'] ?? 0),
            'texto' => (string) ($pData['texto'] ?? ''),
            'tipo' => (string) ($pData['tipo'] ?? 'texto'),
            'icono' => null,
            'icono_nuevo' => false,
            'icono_eliminado' => ($pData['eliminar_icono'] ?? '') === '1',
            'opciones' => [],
        ];

        $imagen = archivoAnidado($files, [$i, 'imagen']);
        if ($imagen !== null && (int) $imagen['error'] !== UPLOAD_ERR_NO_FILE) {
            $guardada = ImagenEditorialHelper::guardar($imagen, 'preguntas');
            $pregunta['icono'] = $guardada['ruta'];
            $pregunta['icono_nuevo'] = true;
            $archivosNuevos[] = $guardada['absoluta'];
        }

        $opciones = $pData['opciones'] ?? [];
        if (is_array($opciones)) {
            foreach (array_values($opciones) as $j => $opData) {
                if (!is_array($opData)) {
                    throw new InvalidArgumentException('Una opción tiene formato inválido.');
                }
                $opcion = [
                    'id' => (int) ($opData['id'] ?? 0),
                    'texto' => (string) ($opData['texto'] ?? ''),
                    'icono' => null,
                    'icono_nuevo' => false,
                    'icono_eliminado' => ($opData['eliminar_icono'] ?? '') === '1',
                ];
                $imagenOpcion = archivoAnidado($files, [$i, 'opciones', $j, 'imagen']);
                if ($imagenOpcion !== null && (int) $imagenOpcion['error'] !== UPLOAD_ERR_NO_FILE) {
                    $guardada = ImagenEditorialHelper::guardar($imagenOpcion, 'opciones');
                    $opcion['icono'] = $guardada['ruta'];
                    $opcion['icono_nuevo'] = true;
                    $archivosNuevos[] = $guardada['absoluta'];
                }
                $pregunta['opciones'][] = $opcion;
            }
        }
        $finalPreguntas[] = $pregunta;
    }

    $controller = new EditarController();
    $respuesta = $controller->guardarCambios($nivel, $finalPreguntas, $idOrigen, $versionOrigen);
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    foreach ($archivosNuevos as $archivo) {
        ImagenEditorialHelper::eliminarAbsoluta($archivo);
    }

    if ($e instanceof DomainException && $e->getMessage() === 'CONFLICTO_VERSION') {
        http_response_code(409);
        $mensaje = 'Otra persona publicó cambios antes. Recarga el editor e inténtalo de nuevo.';
    } elseif ($e instanceof InvalidArgumentException) {
        http_response_code(422);
        $mensaje = $e->getMessage();
    } else {
        http_response_code(500);
        $mensaje = 'No se pudo publicar la encuesta. Intenta de nuevo.';
        error_log('Error al guardar encuesta: ' . $e->getMessage());
    }
    echo json_encode(['success' => false, 'error' => $mensaje], JSON_UNESCAPED_UNICODE);
}
exit;
