<?php
require_once 'auth_check.php';
require_once 'conexion.php';
header('Content-Type: application/json');
if (isset($rol) && $rol === 'Estandar') {
    echo json_encode(['status' => 'error', 'message' => 'No tienes permisos para eliminar usuarios.']);
    exit;
}

$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'ID de usuario no proporcionado.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Obtener el rol y el id de especialista (si aplica)
    $stmt_rol = $pdo->prepare("SELECT rol, id FROM usuarios WHERE id = ?");
    $stmt_rol->execute([$id]);
    $usuario = $stmt_rol->fetch();

    if ($usuario) {
        if ($usuario['rol'] === 'especialista') {
            // Buscar el id del especialista
            $stmt_esp = $pdo->prepare("SELECT id FROM especialistas WHERE usuario_id = ?");
            $stmt_esp->execute([$id]);
            $esp = $stmt_esp->fetch();
            if ($esp) {
                // Verificar si tiene consultas
                $stmt_consultas = $pdo->prepare("SELECT COUNT(*) as total FROM consultas WHERE especialista_id = ?");
                $stmt_consultas->execute([$esp['id']]);
                $totalConsultas = $stmt_consultas->fetchColumn();
                if ($totalConsultas > 0) {
                    $pdo->rollBack();
                    echo json_encode(['status' => 'error', 'message' => 'No se puede eliminar el usuario porque el especialista ya tiene consultas registradas.']);
                    exit;
                }
                // Eliminar especialista
                $stmt_delete_especialista = $pdo->prepare("DELETE FROM especialistas WHERE usuario_id = ?");
                $stmt_delete_especialista->execute([$id]);
            }
        }

        // Eliminar el usuario
        $stmt_delete_usuario = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt_delete_usuario->execute([$id]);

        if ($stmt_delete_usuario->rowCount()) {
            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Usuario eliminado correctamente.']);
        } else {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'El usuario no existe o ya fue eliminado.']);
        }
    } else {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado.']);
    }
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error al eliminar el usuario: ' . $e->getMessage()]);
}
