<?php
require_once 'conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$username = trim($_POST['username'] ?? '');
$exclude_id = $_POST['exclude_id'] ?? null;

if (empty($username)) {
    echo json_encode(['exists' => false]);
    exit;
}

try {
    $query = 'SELECT COUNT(*) FROM usuarios WHERE username = ?';
    $params = [$username];

    if ($exclude_id) {
        $query .= ' AND id != ?';
        $params[] = $exclude_id;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $count = $stmt->fetchColumn();

    echo json_encode(['exists' => $count > 0]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error en la base de datos']);
}
