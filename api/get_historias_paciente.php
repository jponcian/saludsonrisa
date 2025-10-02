<?php
header('Content-Type: application/json');
require 'conexion.php';

$paciente_id = isset($_GET['paciente_id']) ? $_GET['paciente_id'] : '';

try {
    $sql = "SELECT c.fecha_consulta, u.nombre_completo as especialista_nombre, c.diagnostico, c.tratamiento, c.observaciones ";
    $sql .= "FROM consultas c ";
    $sql .= "JOIN especialistas e ON c.especialista_id = e.id ";
    $sql .= "JOIN usuarios u ON e.usuario_id = u.id ";
    $params = [];
    if ($paciente_id !== '' && is_numeric($paciente_id)) {
        $sql .= "WHERE c.paciente_id = ? ";
        $params[] = $paciente_id;
    }
    $sql .= "ORDER BY c.fecha_consulta DESC";
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
