<?php
require_once 'auth_check.php';
require_once 'conexion.php';
header('Content-Type: application/json');

$puedeGestionarUsuarios = in_array(4, $permisos_usuario, true);
if (!$puedeGestionarUsuarios) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado.']);
    exit;
}

$id = isset($_POST['id']) ? $_POST['id'] : null;
$username = isset($_POST['username']) ? $_POST['username'] : null;
$nombre_completo = isset($_POST['nombre_completo']) ? $_POST['nombre_completo'] : null;
$cedula = isset($_POST['cedula']) ? $_POST['cedula'] : null;
$telefono = isset($_POST['telefono']) ? $_POST['telefono'] : null;
$rol_id = isset($_POST['rol_id']) ? (int) $_POST['rol_id'] : null;
$password = isset($_POST['password']) ? $_POST['password'] : null;
$foto_path = null;

if (!$id || !$username || !$nombre_completo || !$rol_id) {
    echo json_encode(['status' => 'error', 'message' => 'Todos los campos, excepto la contraseña, son obligatorios.']);
    exit;
}

// Validar rol destino
$stmtRol = $pdo->prepare("SELECT id, nombre FROM roles WHERE id = ? LIMIT 1");
$stmtRol->execute([$rol_id]);
$rolData = $stmtRol->fetch(PDO::FETCH_ASSOC);
if (!$rolData) {
    echo json_encode(['status' => 'error', 'message' => 'Rol seleccionado no existe.']);
    exit;
}

$rol_destino_slug = rol_to_slug($rolData['nombre']);

// Manejo de la subida de la foto
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['foto']['tmp_name'];
    $fileName = $_FILES['foto']['name'];
    $fileSize = $_FILES['foto']['size'];
    $fileType = $_FILES['foto']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');
    if (in_array($fileExtension, $allowedfileExtensions)) {
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $uploadFileDir = '../uploads/';
        $dest_path = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $foto_path = $newFileName;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Hubo un error al mover el archivo de la foto.']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Tipo de archivo de foto no permitido. Solo JPG, JPEG, PNG, GIF.']);
        exit;
    }
}

try {
    $sql = "UPDATE usuarios SET username = ?, nombre_completo = ?, cedula = ?, telefono = ?, rol = ?";
    $params = [$username, $nombre_completo, $cedula, $telefono, $rolData['id']];

    if (!empty($password)) {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql .= ", password_hash = ?";
        $params[] = $password_hashed;
    }

    if ($foto_path) {
        $sql .= ", foto = ?";
        $params[] = $foto_path;
    }

    $sql .= " WHERE id = ?";
    $params[] = $id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount()) {
        echo json_encode(['status' => 'success', 'message' => 'Usuario actualizado correctamente.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se realizaron cambios o el usuario no existe.']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el usuario: ' . $e->getMessage()]);
}
