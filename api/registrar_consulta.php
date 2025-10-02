<?php
header('Content-Type: application/json');
require 'conexion.php';

$uploadDir = '../uploads/';
$response = ['status' => 'error', 'message' => 'Datos inválidos.']; // Inicializar al principio

// Validaciones de isset
if (!isset($_POST['paciente_id'])) {
    $response['message'] = 'Falta el ID del paciente.';
    echo json_encode($response);
    exit;
}
if (!isset($_POST['especialista_id'])) {
    $response['message'] = 'Falta el ID del especialista.';
    echo json_encode($response);
    exit;
}
if (!isset($_POST['diagnostico'])) {
    $response['message'] = 'Falta el diagnóstico.';
    echo json_encode($response);
    exit;
}
if (!isset($_POST['especialidad_id'])) {
    $response['message'] = 'Falta el tipo de atención (especialidad).';
    echo json_encode($response);
    exit;
}

$paciente_id = $_POST['paciente_id'];
$especialista_id = $_POST['especialista_id'];
$especialidades = $_POST['especialidad_id']; // array
$diagnostico = $_POST['diagnostico'];
$tratamiento = $_POST['tratamiento'] ?? '';
$observaciones = $_POST['observaciones'] ?? '';

$pdo->beginTransaction();

try {
    // Insertar la consulta (ahora con fecha_consulta = NOW())
    $sql = "INSERT INTO consultas (paciente_id, especialista_id, fecha_consulta, diagnostico, tratamiento, observaciones) VALUES (?, ?, NOW(), ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$paciente_id, $especialista_id, $diagnostico, $tratamiento, $observaciones]);
    $consulta_id = $pdo->lastInsertId();

    // Insertar especialidades asociadas
    if (is_array($especialidades)) {
        foreach ($especialidades as $esp_id) {
            $sqlEsp = "INSERT INTO consulta_especialidades (consulta_id, especialidad_id) VALUES (?, ?)";
            $stmtEsp = $pdo->prepare($sqlEsp);
            $stmtEsp->execute([$consulta_id, $esp_id]);
        }
    }

    // Manejar la subida de múltiples fotos
    if (isset($_FILES['fotos_consulta'])) {
        $files = $_FILES['fotos_consulta'];
        $fileCount = count($files['name']);

        for ($i = 0; $i < $fileCount; $i++) {
            if ($files['error'][$i] === 0) {
                $fileName = uniqid() . '-' . basename($files['name'][$i]);
                $targetFilePath = $uploadDir . $fileName;

                if (move_uploaded_file($files['tmp_name'][$i], $targetFilePath)) {
                    $sqlFoto = "INSERT INTO consulta_fotos (consulta_id, foto_url) VALUES (?, ?)";
                    $stmtFoto = $pdo->prepare($sqlFoto);
                    $stmtFoto->execute([$consulta_id, $fileName]);
                } else {
                    throw new Exception('Error al mover uno de los archivos.');
                }
            }
        }
    }

    $pdo->commit();
    $response = ['status' => 'success', 'message' => 'Consulta registrada con éxito.'];
} catch (\Exception $e) {
    $pdo->rollBack();
    $response['message'] = 'Error al registrar la consulta: ' . $e->getMessage();
    http_response_code(500);
}
echo json_encode($response);
