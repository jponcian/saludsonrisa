<?php
header('Content-Type: application/json');
require 'conexion.php';

try {

    $sql = "SELECT 
                e.id as especialista_id, 
                u.username, 
                u.nombre_completo, 
                esp.id as especialidad_id,
                esp.nombre as especialidad_nombre
            FROM especialistas e
            JOIN usuarios u ON e.usuario_id = u.id
            LEFT JOIN especialista_especialidades ee ON e.id = ee.especialista_id
            LEFT JOIN especialidades esp ON ee.especialidad_id = esp.id
            ORDER BY u.nombre_completo ASC";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll();

    $especialistas = [];
    foreach ($rows as $row) {
        $id = $row['especialista_id'];
        if (!isset($especialistas[$id])) {
            $especialistas[$id] = [
                'id' => $id,
                'username' => $row['username'],
                'nombre_completo' => $row['nombre_completo'],
                'especialidades' => []
            ];
        }
        if ($row['especialidad_id']) {
            $especialistas[$id]['especialidades'][] = [
                'id' => $row['especialidad_id'],
                'nombre' => $row['especialidad_nombre']
            ];
        }
    }
    // Si no tiene especialidades, poner mensaje
    foreach ($especialistas as &$esp) {
        if (empty($esp['especialidades'])) {
            $esp['especialidades'] = [];
        }
    }
    // Si hay filtro por id
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        if (isset($especialistas[$id])) {
            echo json_encode(['status' => 'success', 'data' => $especialistas[$id]]); // Devolver el especialista directamente
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Especialista no encontrado.']);
        }
    } else {
        echo json_encode(['data' => array_values($especialistas)]);
    }
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al obtener los especialistas: ' . $e->getMessage()
    ]);
}
