<?php
header('Content-Type: application/json');
require 'conexion.php';

$response = [
    'status' => 'success',
    'data' => [
        'especialistas' => [],
        'especialidades' => []
    ]
];

try {
    // Obtener especialistas con sus especialidades
    $sql = "SELECT 
                e.id, 
                u.username, 
                u.nombre_completo, 
                GROUP_CONCAT(esp.nombre SEPARATOR ', ') as especialidades,
                JSON_ARRAYAGG(esp.id) as especialidades_ids
            FROM especialistas e
            JOIN usuarios u ON e.usuario_id = u.id
            LEFT JOIN especialista_especialidades ee ON e.id = ee.especialista_id
            LEFT JOIN especialidades esp ON ee.especialidad_id = esp.id
            GROUP BY e.id, u.username, u.nombre_completo
            ORDER BY u.nombre_completo";
    $stmt = $pdo->query($sql);
    $especialistas = $stmt->fetchAll();
    foreach ($especialistas as &$especialista) {
        $especialista['especialidades_ids'] = json_decode($especialista['especialidades_ids']);
        if (is_null($especialista['especialidades_ids'][0])) {
            $especialista['especialidades_ids'] = [];
            $especialista['especialidades'] = 'Sin especialidad';
        }
    }
    $response['data']['especialistas'] = $especialistas;

    // Obtener especialidades
    $stmt = $pdo->query("SELECT id, nombre FROM especialidades ORDER BY nombre");
    $response['data']['especialidades'] = $stmt->fetchAll();
} catch (\PDOException $e) {
    $response['status'] = 'error';
    $response['message'] = 'Error al obtener datos: ' . $e->getMessage();
    http_response_code(500);
}

echo json_encode($response);
