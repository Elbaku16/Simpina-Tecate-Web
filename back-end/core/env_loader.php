<?php

function cargarEnv(string $ruta): void {
    if (!file_exists($ruta)) {
        throw new Exception("El archivo .env no existe en la ruta: $ruta");
    }

    $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lineas as $linea) {
        if (strpos(trim($linea), '#') === 0) {
            continue;
        }

        if (strpos($linea, '=') !== false) {
            list($nombre, $valor) = explode('=', $linea, 2);
            $nombre = trim($nombre);
            $valor = trim($valor);

            if (!array_key_exists($nombre, $_SERVER) && !array_key_exists($nombre, $_ENV)) {
                putenv(sprintf('%s=%s', $nombre, $valor));
                $_ENV[$nombre] = $valor;
                $_SERVER[$nombre] = $valor;
            }
        }
    }
}
?>