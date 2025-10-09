<?php
require_once 'conexion.php';
session_start();

// Parámetros: rol y ruta de página
$rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : null;
$ruta = isset($_GET['ruta']) ? $_GET['ruta'] : null;

if (!$rol || !$ruta) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Faltan parámetros.']);
    exit;
}

$stmt = $pdo->prepare('SELECT 1 FROM roles_paginas rp JOIN paginas p ON rp.pagina_id = p.id WHERE rp.rol = ? AND p.ruta = ? AND p.activo = 1 LIMIT 1');
$stmt->execute([$rol, $ruta]);
$puede = $stmt->fetchColumn();

if ($puede) {
    echo json_encode(['status' => 'success', 'acceso' => true]);
} else {
    echo json_encode(['status' => 'success', 'acceso' => false]);
}
