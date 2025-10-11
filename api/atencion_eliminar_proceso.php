<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'auth_check.php';

try {
    // Solo usuarios autorizados (ajustar rol si es necesario)
    // Solo validar que el usuario haya iniciado sesión (auth_check.php lo hace)

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
        exit;
    }

    $input = $_POST;
    $id = isset($input['id']) ? intval($input['id']) : 0;
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
        exit;
    }

    // Conexión
    $connFile = __DIR__ . '/conexion.php';
    if (!file_exists($connFile)) {
        throw new Exception('Archivo de conexión no encontrado');
    }
    require_once $connFile;
    if (!isset($pdo))
        throw new Exception('Conexión PDO no inicializada');

    // Verificar existencia
    $check = $pdo->prepare('SELECT id FROM atencion_procesos WHERE id = :id LIMIT 1');
    $check->execute([':id' => $id]);
    $exists = $check->fetchColumn();
    if (!$exists) {
        echo json_encode(['status' => 'error', 'message' => 'Proceso no encontrado']);
        exit;
    }

    // Eliminar registro
    $del = $pdo->prepare('DELETE FROM atencion_procesos WHERE id = :id');
    $del->execute([':id' => $id]);
    echo json_encode(['status' => 'ok', 'message' => 'Proceso eliminado', 'id' => $id]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error en servidor', 'detail' => $e->getMessage()]);
    exit;
}
