<?php
header('Content-Type: application/json');
require_once 'conexion.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Sesión expirada. Vuelva a iniciar sesión.'
    ]);
    exit;
}

$usuarioId = (int) $_SESSION['usuario_id'];
$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Formato de datos inválido.'
    ]);
    exit;
}

$pacienteId = isset($input['paciente_id']) ? (int) $input['paciente_id'] : 0;
$respuestasEntrada = $input['respuestas'] ?? [];
$estadoSolicitado = $input['estado'] ?? 'en_progreso';

if ($pacienteId <= 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'ID de paciente inválido.'
    ]);
    exit;
}

$estadosPermitidos = ['pendiente', 'en_progreso', 'completado'];
if (!in_array($estadoSolicitado, $estadosPermitidos, true)) {
    $estadoSolicitado = 'en_progreso';
}

try {
    // Validar paciente
    $stmtPaciente = $pdo->prepare('SELECT id FROM pacientes WHERE id = ? LIMIT 1');
    $stmtPaciente->execute([$pacienteId]);
    if (!$stmtPaciente->fetchColumn()) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Paciente no encontrado.'
        ]);
        exit;
    }

    $pdo->beginTransaction();

    $respuestasNormalizadas = [];
    $preguntaIds = [];
    if (is_array($respuestasEntrada)) {
        foreach ($respuestasEntrada as $item) {
            if (!isset($item['pregunta_id'])) {
                continue;
            }
            $preguntaId = (int) $item['pregunta_id'];
            $valor = $item['valor'] ?? null;
            $preguntaIds[$preguntaId] = true;
            $respuestasNormalizadas[$preguntaId] = $valor;
        }
    }

    if (!empty($preguntaIds)) {
        $placeholders = implode(',', array_fill(0, count($preguntaIds), '?'));
        $stmtPreguntas = $pdo->prepare("SELECT id, tipo FROM historia_preguntas WHERE id IN ($placeholders)");
        $stmtPreguntas->execute(array_keys($preguntaIds));
        $preguntasValidas = $stmtPreguntas->fetchAll(PDO::FETCH_KEY_PAIR); // id => tipo
    } else {
        $preguntasValidas = [];
    }

    $stmtGuardar = $pdo->prepare('INSERT INTO historia_respuestas (paciente_id, pregunta_id, respuesta, respondido_por, respondido_en) VALUES (?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE respuesta = VALUES(respuesta), respondido_por = VALUES(respondido_por), respondido_en = NOW()');
    $stmtEliminar = $pdo->prepare('DELETE FROM historia_respuestas WHERE paciente_id = ? AND pregunta_id = ?');

    foreach ($respuestasNormalizadas as $preguntaId => $valor) {
        if (!isset($preguntasValidas[$preguntaId])) {
            continue;
        }
        $tipo = $preguntasValidas[$preguntaId];
        $valorProcesado = null;

        if (is_array($valor)) {
            $filtrado = array_values(array_filter($valor, function ($item) {
                return $item !== null && $item !== '';
            }));
            if (!empty($filtrado)) {
                $valorProcesado = json_encode($filtrado, JSON_UNESCAPED_UNICODE);
            }
        } elseif ($valor !== null) {
            if (is_string($valor)) {
                $valor = trim($valor);
            }
            if ($valor !== '' && $valor !== null) {
                $valorProcesado = (string) $valor;
            }
        }

        if ($valorProcesado === null) {
            $stmtEliminar->execute([$pacienteId, $preguntaId]);
            continue;
        }

        if ($tipo === 'numero' && !is_numeric($valorProcesado)) {
            // Evitar guardar datos no numéricos
            $stmtEliminar->execute([$pacienteId, $preguntaId]);
            continue;
        }

        $stmtGuardar->execute([$pacienteId, $preguntaId, $valorProcesado, $usuarioId]);
    }

    // Actualizar estado de la historia clínica
    $datosEstado = [
        $pacienteId,
        $estadoSolicitado,
        $estadoSolicitado === 'completado' ? $usuarioId : null
    ];

    $stmtEstado = $pdo->prepare('INSERT INTO historia_paciente_estados (paciente_id, estado, ultima_actualizacion, completado_por, completado_en) VALUES (?, ?, NOW(), ?, CASE WHEN ? IS NOT NULL THEN NOW() ELSE NULL END) ON DUPLICATE KEY UPDATE estado = VALUES(estado), ultima_actualizacion = NOW(), completado_por = VALUES(completado_por), completado_en = CASE WHEN VALUES(completado_por) IS NOT NULL THEN NOW() ELSE NULL END');
    $completador = $estadoSolicitado === 'completado' ? $usuarioId : null;
    $stmtEstado->execute([$pacienteId, $estadoSolicitado, $completador, $completador]);

    $stmtEstadoDatos = $pdo->prepare('SELECT estado, ultima_actualizacion, completado_por, completado_en FROM historia_paciente_estados WHERE paciente_id = ? LIMIT 1');
    $stmtEstadoDatos->execute([$pacienteId]);
    $estadoActual = $stmtEstadoDatos->fetch();

    $nombreCompletador = null;
    if ($estadoActual && !empty($estadoActual['completado_por'])) {
        $stmtUsuario = $pdo->prepare('SELECT nombre_completo FROM usuarios WHERE id = ? LIMIT 1');
        $stmtUsuario->execute([$estadoActual['completado_por']]);
        $nombreCompletador = $stmtUsuario->fetchColumn();
    }

    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Historia clínica guardada correctamente.',
        'estado' => $estadoActual['estado'] ?? $estadoSolicitado,
        'ultima_actualizacion' => $estadoActual['ultima_actualizacion'] ?? null,
        'completado_por' => $nombreCompletador,
        'completado_en' => $estadoActual['completado_en'] ?? null
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('historia_guardar.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al guardar la historia clínica.'
    ]);
}
