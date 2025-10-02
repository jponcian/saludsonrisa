<?php
header('Content-Type: application/json');
require 'conexion.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID de paciente no proporcionado.']);
    exit;
}

$paciente_id = $_GET['id'];
$response = ['status' => 'error', 'message' => 'Paciente no encontrado.'];

try {
    // 1. Obtener datos del paciente
    $stmtPaciente = $pdo->prepare("SELECT *, cedula FROM pacientes WHERE id = ?");
    $stmtPaciente->execute([$paciente_id]);
    $paciente = $stmtPaciente->fetch();

    if (!$paciente) {
        http_response_code(404);
        echo json_encode($response);
        exit;
    }

    // 2. Obtener consultas del paciente (con todas sus especialidades)
    $sqlConsultas = "
        SELECT
            c.id, c.fecha_consulta, c.diagnostico, c.tratamiento, c.observaciones,
            GROUP_CONCAT(esp.nombre SEPARATOR ', ') as especialidades_nombres,
            u.nombre_completo as especialista_nombre_completo
        FROM consultas c
        JOIN especialistas espec ON c.especialista_id = espec.id
        JOIN usuarios u ON espec.usuario_id = u.id
        LEFT JOIN consulta_especialidades ce ON c.id = ce.consulta_id
        LEFT JOIN especialidades esp ON ce.especialidad_id = esp.id
        WHERE c.paciente_id = ?
        GROUP BY c.id, c.fecha_consulta, c.diagnostico, c.tratamiento, c.observaciones, u.nombre_completo
        ORDER BY c.fecha_consulta DESC
    ";
    $stmtConsultas = $pdo->prepare($sqlConsultas);
    $stmtConsultas->execute([$paciente_id]);
    $consultas = $stmtConsultas->fetchAll();

    // 3. Para cada consulta, obtener sus fotos
    $stmtFotos = $pdo->prepare("SELECT foto_url FROM consulta_fotos WHERE consulta_id = ?");
    foreach ($consultas as $key => $consulta) {
        $stmtFotos->execute([$consulta['id']]);
        $fotos = $stmtFotos->fetchAll(PDO::FETCH_COLUMN);
        $consultas[$key]['fotos'] = $fotos;
    }

    $response = [
        'status' => 'success',
        'data' => [
            'paciente' => $paciente,
            'consultas' => $consultas
        ]
    ];
} catch (\PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
}

echo json_encode($response);
