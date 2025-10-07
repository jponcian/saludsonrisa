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
$pacienteId = isset($_GET['paciente_id']) ? (int) $_GET['paciente_id'] : 0;

if ($pacienteId <= 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'ID de paciente inválido.'
    ]);
    exit;
}

function normalizarOpciones(?string $raw): array
{
    if ($raw === null || $raw === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $opciones = [];
        foreach ($decoded as $item) {
            if (is_array($item)) {
                $value = $item['value'] ?? $item['id'] ?? $item['clave'] ?? null;
                if ($value === null && isset($item['label'])) {
                    $value = $item['label'];
                }
                $label = $item['label'] ?? $item['nombre'] ?? $item['value'] ?? $value;
                if ($value !== null) {
                    $opciones[] = [
                        'value' => (string) $value,
                        'label' => (string) $label
                    ];
                }
            } elseif (is_scalar($item)) {
                $opciones[] = [
                    'value' => (string) $item,
                    'label' => (string) $item
                ];
            }
        }
        if (!empty($opciones)) {
            return $opciones;
        }
    }

    $parts = preg_split('/[\r\n,]+/', $raw);
    $opciones = [];
    foreach ($parts as $part) {
        $value = trim($part);
        if ($value !== '') {
            $opciones[] = [
                'value' => $value,
                'label' => $value
            ];
        }
    }
    return $opciones;
}

try {
    // Información del paciente
    $stmtPaciente = $pdo->prepare('SELECT id, nombres, apellidos, cedula, genero, fecha_nacimiento, telefono, email, direccion, foto_url FROM pacientes WHERE id = ? LIMIT 1');
    $stmtPaciente->execute([$pacienteId]);
    $paciente = $stmtPaciente->fetch();

    if (!$paciente) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Paciente no encontrado.'
        ]);
        exit;
    }

    // Estado de la historia clínica
    $stmtEstado = $pdo->prepare('SELECT estado, ultima_actualizacion, completado_por, completado_en FROM historia_paciente_estados WHERE paciente_id = ? LIMIT 1');
    $stmtEstado->execute([$pacienteId]);
    $estadoHistoria = $stmtEstado->fetch();

    $completador = null;
    if ($estadoHistoria && !empty($estadoHistoria['completado_por'])) {
        $stmtUsuario = $pdo->prepare('SELECT nombre_completo FROM usuarios WHERE id = ? LIMIT 1');
        $stmtUsuario->execute([$estadoHistoria['completado_por']]);
        $completador = $stmtUsuario->fetchColumn();
    }

    $historiaEstado = [
        'estado' => $estadoHistoria['estado'] ?? 'pendiente',
        'ultima_actualizacion' => $estadoHistoria['ultima_actualizacion'] ?? null,
        'completado_por' => $completador,
        'completado_en' => $estadoHistoria['completado_en'] ?? null
    ];

    // Definición de secciones
    $stmtSecciones = $pdo->query('SELECT id, nombre, descripcion, orden FROM historia_secciones WHERE activo = 1 ORDER BY orden ASC, id ASC');
    $seccionesRaw = $stmtSecciones->fetchAll();

    // Preguntas activas
    $stmtPreguntas = $pdo->query('SELECT id, seccion_id, pregunta, tipo, opciones, ayuda, requerida, orden FROM historia_preguntas WHERE activo = 1 ORDER BY seccion_id ASC, orden ASC, id ASC');
    $preguntasRaw = $stmtPreguntas->fetchAll();

    // Respuestas del paciente
    $stmtRespuestas = $pdo->prepare('SELECT pregunta_id, respuesta FROM historia_respuestas WHERE paciente_id = ?');
    $stmtRespuestas->execute([$pacienteId]);
    $respuestasRaw = $stmtRespuestas->fetchAll();

    $respuestas = [];
    foreach ($respuestasRaw as $row) {
        $respuestas[(int) $row['pregunta_id']] = $row['respuesta'];
    }

    $preguntasPorSeccion = [];
    foreach ($preguntasRaw as $pregunta) {
        $pid = (int) $pregunta['id'];
        $sid = (int) $pregunta['seccion_id'];
        $tipo = $pregunta['tipo'];

        $valor = $respuestas[$pid] ?? null;
        if ($valor !== null) {
            if ($tipo === 'seleccion_multiple') {
                $decoded = json_decode($valor, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $valor = $decoded;
                } else {
                    $valor = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', (string) $valor)), 'strlen'));
                }
            }
        }

        $preguntasPorSeccion[$sid][] = [
            'id' => $pid,
            'pregunta' => $pregunta['pregunta'],
            'tipo' => $tipo,
            'opciones' => normalizarOpciones($pregunta['opciones']),
            'ayuda' => $pregunta['ayuda'],
            'requerida' => (bool) $pregunta['requerida'],
            'orden' => (int) $pregunta['orden'],
            'respuesta' => $valor
        ];
    }

    $secciones = [];
    foreach ($seccionesRaw as $seccion) {
        $sid = (int) $seccion['id'];
        $secciones[] = [
            'id' => $sid,
            'nombre' => $seccion['nombre'],
            'descripcion' => $seccion['descripcion'],
            'orden' => (int) $seccion['orden'],
            'preguntas' => $preguntasPorSeccion[$sid] ?? []
        ];
    }

    echo json_encode([
        'status' => 'success',
        'paciente' => $paciente,
        'historia' => $historiaEstado,
        'secciones' => $secciones
    ]);
} catch (Throwable $e) {
    error_log('historia_obtener.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al obtener la historia clínica.'
    ]);
}
