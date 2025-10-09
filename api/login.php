<?php
header('Content-Type: application/json');
require 'conexion.php';
require_once __DIR__ . '/rol_utils.php';

$response = ['status' => 'error', 'message' => 'Credenciales incorrectas.'];

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT u.*, r.nombre AS rol_nombre FROM usuarios u LEFT JOIN roles r ON u.rol = r.id WHERE u.username = ? LIMIT 1");
        $stmt->execute([$username]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password_hash'])) {
            // Iniciar sesión
            session_start();
            $rol_nombre = $usuario['rol_nombre'] ?? '';
            $rol_slug = $rol_nombre ? rol_to_slug($rol_nombre) : null;

            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['username'] = $usuario['username'];
            $_SESSION['rol'] = $rol_nombre;
            $_SESSION['rol_id'] = $usuario['rol'];
            $_SESSION['rol_slug'] = $rol_slug;
            $_SESSION['nombre_completo'] = $usuario['nombre_completo'];

            $permisos = [];
            if (!empty($usuario['rol'])) {
                $stmtPermisos = $pdo->prepare('SELECT permiso_id FROM rol_permisos WHERE rol_id = ?');
                $stmtPermisos->execute([(int) $usuario['rol']]);
                $permisos = array_map('intval', $stmtPermisos->fetchAll(PDO::FETCH_COLUMN));
            }
            $_SESSION['permisos'] = $permisos;

            $response = ['status' => 'success', 'message' => 'Inicio de sesión exitoso.'];
        }
    } catch (\PDOException $e) {
        $response['message'] = 'Error en el servidor: ' . $e->getMessage();
        http_response_code(500);
    }
}

echo json_encode($response);
