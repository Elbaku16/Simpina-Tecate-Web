<?php
declare(strict_types=1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/back-end/core/bootstrap_session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/SIMPINNA/back-end/database/Conexion.php';

header("Content-Type: application/json; charset=utf-8");

$db = Conexion::getConexion();

// Leer JSON crudo
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode(["success" => false, "error" => "JSON inválido"]);
    exit;
}

$nivel      = $data['nivel']      ?? null;
$preguntas  = $data['preguntas']  ?? [];
$eliminadas = $data['eliminadas'] ?? [];

if (!$nivel || !is_array($preguntas)) {
    echo json_encode(["success" => false, "error" => "Payload inválido"]);
    exit;
}

// Mapa de niveles → id_encuesta
$mapNiveles = [
    'preescolar'   => 1,
    'primaria'     => 4,
    'secundaria'   => 5,
    'preparatoria' => 6,
];

$idEncuesta = $mapNiveles[$nivel] ?? null;

if (!$idEncuesta) {
    echo json_encode(["success" => false, "error" => "Nivel no válido"]);
    exit;
}

try {
    $db->begin_transaction();

    // ---------------------------------------------------------
    // 1) ELIMINAR PREGUNTAS MARCADAS
    // ---------------------------------------------------------
    if (!empty($eliminadas)) {

        $delOpc  = $db->prepare("DELETE FROM opciones_respuesta WHERE id_pregunta=?");
        $delRU   = $db->prepare("DELETE FROM respuestas_usuario WHERE id_pregunta=?");
        $delRR   = $db->prepare("DELETE FROM respuestas_ranking WHERE id_pregunta=?");
        $delPreg = $db->prepare("DELETE FROM preguntas WHERE id_pregunta=?");

        foreach ($eliminadas as $idDel) {
            $idDel = (int)$idDel;
            if ($idDel <= 0) continue;

            $delRU->bind_param("i", $idDel);
            $delRU->execute();

            $delRR->bind_param("i", $idDel);
            $delRR->execute();

            $delOpc->bind_param("i", $idDel);
            $delOpc->execute();

            $delPreg->bind_param("i", $idDel);
            $delPreg->execute();
        }
    }

    // ---------------------------------------------------------
    // 2) GUARDAR / ACTUALIZAR PREGUNTAS
    // ---------------------------------------------------------

    $update = $db->prepare(
        "UPDATE preguntas 
         SET texto_pregunta=?, tipo_pregunta=?, orden=? 
         WHERE id_pregunta=?"
    );

    $insert = $db->prepare(
        "INSERT INTO preguntas (id_encuesta, texto_pregunta, tipo_pregunta, orden)
         VALUES (?, ?, ?, ?)"
    );

    $delOpciones = $db->prepare(
        "DELETE FROM opciones_respuesta WHERE id_pregunta=?"
    );

    $insertOpcion = $db->prepare(
        "INSERT INTO opciones_respuesta (id_pregunta, texto_opcion)
         VALUES (?, ?)"
    );

    foreach ($preguntas as $p) {

        $idPregunta = (int)($p['id_pregunta'] ?? 0);
        $texto = trim($p['texto'] ?? "");
        $tipo  = strtolower(trim($p['tipo'] ?? "texto"));
        $orden = (int)($p['orden'] ?? 0);

        if ($texto === "") continue;

        if (!in_array($tipo, ['opcion','multiple','ranking','texto','dibujo'])) {
            $tipo = 'texto';
        }

        // --- UPDATE ---
        if ($idPregunta > 0) {

            $update->bind_param("ssii", $texto, $tipo, $orden, $idPregunta);
            $update->execute();

        } else {
            // --- INSERT ---
            $insert->bind_param("issi", $idEncuesta, $texto, $tipo, $orden);
            $insert->execute();

            $idPregunta = $db->insert_id;
        }

        // --------------------------------
        // OPCIONES
        // --------------------------------
        $delOpciones->bind_param("i", $idPregunta);
        $delOpciones->execute();

        if (in_array($tipo, ['opcion','multiple','ranking'])) {

            foreach ($p['opciones'] as $op) {
                $textoOp = trim($op['texto'] ?? "");
                if ($textoOp === "") continue;

                $insertOpcion->bind_param("is", $idPregunta, $textoOp);
                $insertOpcion->execute();
            }
        }
    }

    $db->commit();
    echo json_encode(["success" => true]);

} catch (Throwable $e) {
    $db->rollback();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
