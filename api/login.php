<?php
header('Content-Type: application/json');
require 'conexion.php';

$response = ['status' => 'error', 'message' => 'Credenciales incorrectas.'];

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = ?");
        $stmt->execute([$username]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password_hash'])) {
            // Iniciar sesión
            session_start();
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['username'] = $usuario['username'];
            $_SESSION['rol'] = $usuario['rol'];
            $_SESSION['nombre_completo'] = $usuario['nombre_completo'];

            $response = ['status' => 'success', 'message' => 'Inicio de sesión exitoso.'];
        }

    } catch (\PDOException $e) {
        $response['message'] = 'Error en el servidor: ' . $e->getMessage();
        http_response_code(500);
    }
}

echo json_encode($response);
?>