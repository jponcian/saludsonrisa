<?php
require_once 'conexion.php';
header('Content-Type: application/json');

$id = $_POST['id'] ?? null;
$username = $_POST['username'] ?? null;
$nombre_completo = $_POST['nombre_completo'] ?? null;
$cedula = $_POST['cedula'] ?? null;
$telefono = $_POST['telefono'] ?? null;
$rol = $_POST['rol'] ?? null;
$password = $_POST['password'] ?? null;
$foto_path = null;

if (!$id || !$username || !$nombre_completo || !$rol) {
    echo json_encode(['status' => 'error', 'message' => 'Todos los campos, excepto la contraseña, son obligatorios.']);
    exit;
}

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

        if(move_uploaded_file($fileTmpPath, $dest_path)) {
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
    $params = [$username, $nombre_completo, $cedula, $telefono, $rol];

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
?>