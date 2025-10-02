<?php
require_once 'conexion.php';
header('Content-Type: application/json');

$id = $_POST['id'] ?? null;
$especialidades = $_POST['especialidades'] ?? [];

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'ID de especialista no proporcionado.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Eliminar las especialidades existentes
    $stmt_delete = $pdo->prepare("DELETE FROM especialista_especialidades WHERE especialista_id = ?");
    $stmt_delete->execute([$id]);

    // Insertar las nuevas especialidades
    if (!empty($especialidades)) {
        $sql_insert = "INSERT INTO especialista_especialidades (especialista_id, especialidad_id) VALUES (?, ?)";
        $stmt_insert = $pdo->prepare($sql_insert);
        foreach ($especialidades as $especialidad_id) {
            $stmt_insert->execute([$id, $especialidad_id]);
        }
    }

    $pdo->commit();

    echo json_encode(['status' => 'success', 'message' => 'Especialidades actualizadas correctamente.']);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error al actualizar las especialidades: ' . $e->getMessage()]);
}
?>