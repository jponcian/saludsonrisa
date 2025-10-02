<?php
header('Content-Type: application/json');
require 'conexion.php';

$response = ['status' => 'error', 'message' => 'Datos inválidos.'];

if (isset($_POST['nombres']) && isset($_POST['apellidos']) && isset($_POST['especialidad_id'])) {
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $especialidad_id = $_POST['especialidad_id'];

    try {
        $sql = "INSERT INTO especialistas (nombres, apellidos, especialidad_id) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombres, $apellidos, $especialidad_id]);

        $response = ['status' => 'success', 'message' => 'Especialista registrado con éxito.'];

    } catch (\PDOException $e) {
        $response['message'] = 'Error al registrar el especialista: ' . $e->getMessage();
        http_response_code(500);
    }
}

echo json_encode($response);
?>