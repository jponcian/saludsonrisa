<?php
header('Content-Type: application/json');
require 'conexion.php';

try {
    $sql = "SELECT p.id, p.nombres, p.apellidos, p.cedula, p.fecha_nacimiento, p.genero, p.telefono, p.email, p.direccion, p.foto_url, h.estado AS historia_estado
            FROM pacientes p
            LEFT JOIN historia_paciente_estados h ON h.paciente_id = p.id
            ORDER BY p.id DESC";

    $stmt = $pdo->query($sql);
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'data' => $pacientes
    ]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al obtener los pacientes: ' . $e->getMessage()
    ]);
}
