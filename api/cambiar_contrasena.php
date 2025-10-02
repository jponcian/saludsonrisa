<?php
require_once 'auth_check.php'; // Asegura que solo usuarios logueados puedan acceder

header('Content-Type: application/json');
require 'conexion.php';

$response = ['status' => 'error', 'message' => 'Datos inválidos.'];

if (isset($_POST['current_password']) && isset($_POST['new_password']) && isset($_POST['confirm_new_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    // 1. Validar que las nuevas contraseñas coincidan
    if ($new_password !== $confirm_new_password) {
        $response['message'] = 'La nueva contraseña y su confirmación no coinciden.';
        echo json_encode($response);
        exit;
    }

    // 2. Obtener el hash de la contraseña actual del usuario
    try {
        $stmt = $pdo->prepare("SELECT password_hash FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]); // $usuario_id viene de auth_check.php
        $usuario = $stmt->fetch();

        if (!$usuario) {
            $response['message'] = 'Usuario no encontrado.';
            echo json_encode($response);
            exit;
        }

        // 3. Verificar la contraseña actual
        if (!password_verify($current_password, $usuario['password_hash'])) {
            $response['message'] = 'La contraseña actual es incorrecta.';
            echo json_encode($response);
            exit;
        }

        // 4. Hashear la nueva contraseña
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        // 5. Actualizar la contraseña en la base de datos
        $stmtUpdate = $pdo->prepare("UPDATE usuarios SET password_hash = ? WHERE id = ?");
        $stmtUpdate->execute([$new_password_hash, $usuario_id]);

        $response = ['status' => 'success', 'message' => 'Contraseña cambiada con éxito.'];

    } catch (\PDOException $e) {
        $response['message'] = 'Error de base de datos: ' . $e->getMessage();
        http_response_code(500);
    }
}

echo json_encode($response);
?>