<?php
require_once 'conexion.php';

header('Content-Type: application/json');

$fechaInicio = $_GET['fecha_inicio'] ?? '';
$fechaFin = $_GET['fecha_fin'] ?? '';

$response = [
    'success' => false,
    'data' => [],
    'message' => ''
];

if (empty($fechaInicio) || empty($fechaFin)) {
    $response['message'] = 'Fechas de inicio y fin son requeridas.';
    echo json_encode($response);
    exit();
}

try {
    $sql = "SELECT p.id as paciente_id, p.nombre as paciente_nombre, p.apellido as paciente_apellido, p.genero, p.telefono, p.email, ";
    $sql .= "c.fecha as fecha_consulta, e.nombre as especialista_nombre, c.diagnostico ";
    $sql .= "FROM pacientes p ";
    $sql .= "JOIN consultas c ON p.id = c.id_paciente ";
    $sql .= "JOIN especialistas e ON c.id_especialista = e.id ";
    $sql .= "WHERE c.fecha BETWEEN :fecha_inicio AND :fecha_fin ";
    $sql .= "ORDER BY c.fecha DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':fecha_inicio', $fechaInicio);
    $stmt->bindParam(':fecha_fin', $fechaFin);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response['success'] = true;
    $response['data'] = $results;

} catch (PDOException $e) {
    $response['message'] = 'Error al cargar los pacientes por periodo: ' . $e->getMessage();
}

echo json_encode($response['data']); // Only return the data array for simplicity in JS

?>