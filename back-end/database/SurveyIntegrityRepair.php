<?php
declare(strict_types=1);

final class SurveyIntegrityRepair
{
    /** @return array{sesiones_creadas:int,respuestas_corregidas:int,rankings_corregidos:int,sesiones_vacias_eliminadas:int} */
    public static function ejecutar(mysqli $db): array
    {
        $stats = [
            'sesiones_creadas' => 0,
            'respuestas_corregidas' => 0,
            'rankings_corregidos' => 0,
            'sesiones_vacias_eliminadas' => 0,
        ];

        $db->begin_transaction();
        try {
            $pares = [];
            $sqlPares = "SELECT DISTINCT ru.id_usuario_encuesta, p.id_encuesta
                         FROM respuestas_usuario ru
                         JOIN preguntas p ON p.id_pregunta = ru.id_pregunta
                         UNION
                         SELECT DISTINCT rr.id_usuario_encuesta, p.id_encuesta
                         FROM respuestas_ranking rr
                         JOIN preguntas p ON p.id_pregunta = rr.id_pregunta";
            $result = $db->query($sqlPares);
            while ($row = $result->fetch_assoc()) {
                $sesion = (int) $row['id_usuario_encuesta'];
                $encuesta = (int) $row['id_encuesta'];
                $pares[$sesion][$encuesta] = null;
            }

            $selectSesion = $db->prepare(
                'SELECT id_encuesta, id_escuela, id_turno, id_ciclo, fecha_inicio, fecha_fin, ip, dispositivo
                 FROM encuestas_usuarios WHERE id_usuario_encuesta = ? FOR UPDATE'
            );
            $insertSesion = $db->prepare(
                'INSERT INTO encuestas_usuarios
                 (id_encuesta,id_escuela,id_turno,id_ciclo,fecha_inicio,fecha_fin,ip,dispositivo)
                 VALUES (?,?,?,?,?,?,?,?)'
            );

            foreach ($pares as $sesion => $encuestas) {
                $selectSesion->bind_param('i', $sesion);
                $selectSesion->execute();
                $origen = $selectSesion->get_result()->fetch_assoc();
                if (!$origen) {
                    throw new RuntimeException("La sesión {$sesion} no existe.");
                }

                foreach (array_keys($encuestas) as $encuesta) {
                    if ((int) $origen['id_encuesta'] === $encuesta) {
                        $pares[$sesion][$encuesta] = $sesion;
                        continue;
                    }

                    $idEscuela = $origen['id_escuela'] !== null ? (int) $origen['id_escuela'] : null;
                    $idTurno = $origen['id_turno'] !== null ? (int) $origen['id_turno'] : null;
                    $idCiclo = $origen['id_ciclo'] !== null ? (int) $origen['id_ciclo'] : null;
                    $fechaInicio = $origen['fecha_inicio'];
                    $fechaFin = $origen['fecha_fin'];
                    $ip = $origen['ip'];
                    $dispositivo = $origen['dispositivo'];
                    $insertSesion->bind_param(
                        'iiiissss',
                        $encuesta,
                        $idEscuela,
                        $idTurno,
                        $idCiclo,
                        $fechaInicio,
                        $fechaFin,
                        $ip,
                        $dispositivo
                    );
                    $insertSesion->execute();
                    $pares[$sesion][$encuesta] = $db->insert_id;
                    $stats['sesiones_creadas']++;
                }
            }
            $selectSesion->close();
            $insertSesion->close();

            $updateRu = $db->prepare(
                'UPDATE respuestas_usuario ru
                 JOIN preguntas p ON p.id_pregunta = ru.id_pregunta
                 SET ru.id_usuario_encuesta = ?, ru.id_encuesta = p.id_encuesta
                 WHERE ru.id_usuario_encuesta = ? AND p.id_encuesta = ?'
            );
            $updateRr = $db->prepare(
                'UPDATE respuestas_ranking rr
                 JOIN preguntas p ON p.id_pregunta = rr.id_pregunta
                 SET rr.id_usuario_encuesta = ?, rr.id_encuesta = p.id_encuesta
                 WHERE rr.id_usuario_encuesta = ? AND p.id_encuesta = ?'
            );
            foreach ($pares as $sesion => $encuestas) {
                foreach ($encuestas as $encuesta => $sesionDestino) {
                    $destino = (int) $sesionDestino;
                    $encuesta = (int) $encuesta;
                    $updateRu->bind_param('iii', $destino, $sesion, $encuesta);
                    $updateRu->execute();
                    $stats['respuestas_corregidas'] += $updateRu->affected_rows;
                    $updateRr->bind_param('iii', $destino, $sesion, $encuesta);
                    $updateRr->execute();
                    $stats['rankings_corregidos'] += $updateRr->affected_rows;
                }
            }
            $updateRu->close();
            $updateRr->close();

            $db->query(
                'DELETE eu FROM encuestas_usuarios eu
                 LEFT JOIN respuestas_usuario ru ON ru.id_usuario_encuesta = eu.id_usuario_encuesta
                 LEFT JOIN respuestas_ranking rr ON rr.id_usuario_encuesta = eu.id_usuario_encuesta
                 WHERE ru.id_respuesta_usuario IS NULL AND rr.id_respuesta IS NULL'
            );
            $stats['sesiones_vacias_eliminadas'] = $db->affected_rows;

            $errores = (int) $db->query(
                'SELECT COUNT(*) FROM respuestas_usuario ru
                 JOIN preguntas p ON p.id_pregunta = ru.id_pregunta
                 JOIN encuestas_usuarios eu ON eu.id_usuario_encuesta = ru.id_usuario_encuesta
                 WHERE ru.id_encuesta <> p.id_encuesta OR eu.id_encuesta <> p.id_encuesta'
            )->fetch_column();
            if ($errores !== 0) {
                throw new RuntimeException('La reparación dejó respuestas inconsistentes.');
            }

            $db->commit();
            return $stats;
        } catch (Throwable $e) {
            $db->rollback();
            throw $e;
        }
    }
}
