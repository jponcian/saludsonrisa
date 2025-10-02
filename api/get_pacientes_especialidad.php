<?php
header('Content-Type: application/json');
require 'conexion.php';

$especialidad_id = isset($_GET['especialidad_id']) ? $_GET['especialidad_id'] : '';

try {
    $sql = "SELECT p.id as paciente_id, p.nombres as paciente_nombre, p.apellidos as paciente_apellido, p.genero, p.telefono, p.email, ";
    $sql .= "esp.nombre as especialidad, c.fecha_consulta, c.diagnostico ";
    $sql .= "FROM pacientes p ";
    $sql .= "JOIN consultas c ON p.id = c.paciente_id ";
    $sql .= "JOIN especialistas e ON c.especialista_id = e.id ";
    $sql .= "JOIN especialista_especialidades ee ON e.id = ee.especialista_id ";
    $sql .= "JOIN especialidades esp ON ee.especialidad_id = esp.id ";
    if ($especialidad_id !== '' && is_numeric($especialidad_id)) {
        $sql .= "WHERE esp.id = ? ";
        $params = [$especialidad_id];
    } else {
        $params = [];
    }
    $sql .= "ORDER BY p.apellidos, p.nombres, c.fecha_consulta DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
    echo json_encode(['data' => $data]);
} catch (PDOException $e) {
    echo json_encode([
        'data' => [],
        'error' => $e->getMessage()
    ]);
}
