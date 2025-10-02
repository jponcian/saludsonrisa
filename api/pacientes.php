<?php
header('Content-Type: application/json');
require 'conexion.php';

try {
    $stmt = $pdo->query("SELECT id, nombres, apellidos, cedula, fecha_nacimiento, genero, telefono, direccion, foto_url FROM pacientes ORDER BY id DESC");
    $stmt = $pdo->query("SELECT id, nombres, apellidos, cedula, fecha_nacimiento, genero, telefono, email, direccion, foto_url FROM pacientes ORDER BY id DESC");
    $pacientes = $stmt->fetchAll();

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
