<?php
require_once '../api/conexion.php';

$id_reunion = isset($_GET['reunion']) ? intval($_GET['reunion']) : 0;
$id_participante = isset($_GET['participante']) ? intval($_GET['participante']) : 0;
$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if ($id_reunion <= 0 || $id_participante <= 0) {
    http_response_code(400);
    die('Datos de QR inválidos.');
}

$stmt = $pdo->prepare("SELECT * FROM demo_reuniones WHERE id = ?");
$stmt->execute([$id_reunion]);
$reunion = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM demo_participantes WHERE id = ? AND id_reunion = ?");
$stmt->execute([$id_participante, $id_reunion]);
$participante = $stmt->fetch();

if (!$reunion || !$participante) {
    http_response_code(404);
    die('Reunión o participante no encontrado.');
}

$token_valido = ($participante['token'] && hash_equals($participante['token'], $token));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información de Carnet</title>
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../dist/css/adminlte.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .info-card {
            max-width: 500px;
            width: 100%;
        }

        .swal2-container {
            z-index: 99999 !important;
            position: fixed !important;
            inset: 0 !important;
        }
    </style>
</head>

<body>
    <div class="card info-card">
        <div class="card-body text-center">
            <img src="../logo.png" class="mb-3" alt="Logo" style="width: 80px;">

            <h2 class="card-title h4"><?= htmlspecialchars($reunion['titulo']) ?></h2>
            <p class="card-text text-muted">
                <i class="far fa-calendar-alt"></i> <?= htmlspecialchars(date('d-m-Y', strtotime($reunion['fecha']))) ?> &nbsp;
                <i class="far fa-clock"></i> <?= htmlspecialchars(date('h:i A', strtotime($reunion['hora']))) ?>
            </p>

            <hr>

            <h3 class="h5 mt-4"><i class="fas fa-user"></i> <?= htmlspecialchars($participante['nombre']) ?></h3>
            <p class="text-muted"><i>Participante Registrado</i></p>

            <?php if ($token_valido): ?>
                <div class="alert alert-success mt-4">
                    <i class="fas fa-check-circle"></i> Token de seguridad verificado.
                </div>
            <?php else: ?>
                <div class="alert alert-danger mt-4">
                    <i class="fas fa-exclamation-triangle"></i> Token de seguridad inválido o faltante.
                </div>
            <?php endif; ?>

        </div>
    </div>
</body>

</html>