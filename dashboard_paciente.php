<?php
// dashboard_paciente.php
// Muestra los datos del paciente y el resumen de servicios al escanear el QR
require_once __DIR__ . '/api/conexion.php';

$id = 0;
if (isset($_GET['id'])) {
    $raw = base64_decode($_GET['id']);
    $parts = explode('|', $raw);
    if (count($parts) === 2 && $parts[1] === 'saludsonrisa2025') {
        $id = (int) $parts[0];
    }
}

if (!$id) {
    die('Paciente no especificado o id inválido.');
}

$stmt = $pdo->prepare('SELECT * FROM pacientes WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paciente) {
    die('Paciente no encontrado.');
}

$stmtSus = $pdo->prepare('SELECT * FROM plan_suscripciones WHERE paciente_id = ? ORDER BY fecha_inscripcion ASC LIMIT 1');
$stmtSus->execute([$id]);
$suscripcion = $stmtSus->fetch(PDO::FETCH_ASSOC);
$fecha_emision = $suscripcion ? date('m/y', strtotime($suscripcion['fecha_inscripcion'])) : '';
$fecha_vencimiento = $suscripcion ? date('m/y', strtotime('+1 year', strtotime($suscripcion['fecha_inscripcion']))) : '';

$servicios = [];
$stmtServ = $pdo->prepare('SELECT tipo_servicio, created_at FROM servicios_consumidos WHERE paciente_id = ? ORDER BY created_at DESC');
$stmtServ->execute([$id]);
while ($row = $stmtServ->fetch(PDO::FETCH_ASSOC)) {
    $descripcion = $row['tipo_servicio'] ?? '';
    if (strpos($descripcion, '%') !== false || strpos($descripcion, '&#37;') !== false) {
        continue; // omitir descuentos
    }
    $servicios[] = [
        'servicio' => $descripcion,
        'fecha' => $row['created_at']
    ];
}

$limites = [];
$stmtLim = $pdo->prepare('SELECT codigo, nombre_limite AS nombre, usado, maximo AS max FROM vw_paciente_limites WHERE paciente_id = ? AND maximo > 0');
$stmtLim->execute([$id]);
while ($row = $stmtLim->fetch(PDO::FETCH_ASSOC)) {
    $nombreLimite = $row['nombre'] ?? '';
    if (strpos($nombreLimite, '%') !== false || strpos($nombreLimite, '&#37;') !== false) {
        continue; // omitir descuentos permanentes
    }
    $row['usado'] = (int) $row['usado'];
    $row['max'] = (int) $row['max'];
    $row['nombre'] = $nombreLimite;
    $limites[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Paciente | SomosSalud</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2f0ff 100%);
            min-height: 100vh;
            font-family: 'Open Sans', Arial, sans-serif;
        }

        .ss-card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 12px 30px rgba(15, 76, 129, 0.15);
            overflow: hidden;
        }

        .ss-card-header {
            background: linear-gradient(135deg, #0d6efd, #22c55e);
            color: #fff;
        }

        .progress {
            background-color: #e9f2ff;
        }

        .progress-bar {
            font-weight: 600;
            letter-spacing: 0.03em;
        }

        .progress-bar:not(.bg-success):not(.bg-danger) {
            background: linear-gradient(135deg, #0d6efd, #60a5fa);
        }

        .ss-section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f4c81;
        }

        .ss-empty {
            background: #fff;
            border-radius: 12px;
            border: 1px dashed #cbd5f5;
        }

        @media (max-width: 576px) {
            .ss-card {
                border-radius: 14px;
            }

            .ss-section-title {
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="container py-4 py-lg-5">
        <div class="card ss-card">
            <div class="card-header ss-card-header text-center p-4">
                <h1 class="h3 mb-2">SomosSalud<br>Clínica SaludSonrisa</h1>
                <p class="mb-0">Resumen de Cobertura del Plan</p>
            </div>
            <div class="card-body p-4 p-lg-5">
                <div class="row g-4">
                    <div class="col-12 col-md-6">
                        <div class="bg-light rounded-4 p-3 p-lg-4 h-100">
                            <div class="ss-section-title mb-3">Datos del paciente</div>
                            <p class="mb-2 text-muted small">Nombre</p>
                            <p class="fw-semibold text-dark">
                                <?php echo htmlspecialchars(($paciente['nombres'] ?? '') . ' ' . ($paciente['apellidos'] ?? '')); ?>
                            </p>
                            <p class="mb-2 text-muted small">Cédula</p>
                            <p class="fw-semibold text-dark mb-2">
                                <?php echo htmlspecialchars($paciente['cedula'] ?? ''); ?>
                            </p>
                            <p class="mb-2 text-muted small">Teléfono</p>
                            <p class="fw-semibold text-dark mb-0">
                                <?php echo htmlspecialchars($paciente['telefono'] ?? ''); ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="bg-light rounded-4 p-3 p-lg-4 h-100">
                            <div class="ss-section-title mb-3">Vigencia</div>
                            <p class="mb-2 text-muted small">Fecha de emisión</p>
                            <p class="fw-semibold text-dark">
                                <?php echo $fecha_emision ?: 'No disponible'; ?>
                            </p>
                            <p class="mb-2 text-muted small">Fecha de vencimiento</p>
                            <p class="fw-semibold text-dark mb-0">
                                <?php echo $fecha_vencimiento ?: 'No disponible'; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-5">
                    <div class="ss-section-title mb-3 d-flex align-items-center">
                        <i class="bi bi-heart-pulse-fill me-2 text-success"></i>Límites y disponibles del plan
                    </div>
                    <?php if (!empty($limites)) { ?>
                        <div class="list-group shadow-sm">
                            <?php foreach ($limites as $lim) {
                                $porcentaje = ($lim['max'] > 0) ? round(($lim['usado'] / $lim['max']) * 100) : 0;
                                if ($lim['usado'] == 0 && $lim['max'] > 0) {
                                    $barClass = 'bg-success';
                                    $porcentaje = 100;
                                } elseif ($lim['usado'] == $lim['max'] && $lim['max'] > 0) {
                                    $barClass = 'bg-danger';
                                } else {
                                    $barClass = '';
                                }
                                ?>
                                <div class="list-group-item py-4">
                                    <div class="d-flex align-items-center mb-2 flex-wrap gap-2">
                                        <span
                                            class="fw-semibold text-dark"><?php echo htmlspecialchars($lim['nombre']); ?></span>
                                    </div>
                                    <div class="progress progress-bar-striped progress-bar-animated" role="progressbar"
                                        aria-valuenow="<?php echo $porcentaje; ?>" aria-valuemin="0" aria-valuemax="100"
                                        style="height: 26px; position:relative;">
                                        <?php
                                        $textoBarra = ($lim['usado'] == 0 && $lim['max'] > 0)
                                            ? 'Consumo: 0 / ' . $lim['max']
                                            : 'Consumido: ' . $lim['usado'] . ' / ' . $lim['max'];
                                        ?>
                                        <div class="progress-bar d-flex justify-content-center align-items-center <?php echo $barClass; ?>"
                                            style="width: <?php echo $porcentaje; ?>%; font-size:1em; background-color:<?php echo ($barClass === 'bg-success') ? '#198754' : ''; ?>;">
                                            <?php if ($porcentaje >= 40) {
                                                echo $textoBarra;
                                            } ?>
                                        </div>
                                        <?php if ($porcentaje < 40) { ?>
                                            <span class="w-100 h-100 d-flex justify-content-center align-items-center"
                                                style="position:absolute; left:0; top:0; font-size:1em; font-weight:600; color:#222; z-index:2;">
                                                <?php echo $textoBarra; ?>
                                            </span>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <div class="ss-empty text-center p-4 text-muted">No hay límites registrados para este plan.
                        </div>
                    <?php } ?>
                </div>

                <div class="mt-5">
                    <div class="ss-section-title mb-3 d-flex align-items-center">
                        <i class="bi bi-check-circle-fill me-2 text-primary"></i>Servicios consumidos
                    </div>
                    <?php if (!empty($servicios)) { ?>
                        <div class="table-responsive shadow-sm rounded-4">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-primary text-primary bg-opacity-25">
                                    <tr>
                                        <th>Servicio</th>
                                        <th style="width: 160px;">Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($servicios as $serv) { ?>
                                        <tr>
                                            <td class="fw-semibold text-dark">
                                                <?php echo htmlspecialchars($serv['servicio']); ?>
                                            </td>
                                            <td class="text-muted">
                                                <?php echo $serv['fecha'] ? date('d/m/Y', strtotime($serv['fecha'])) : 'No disponible'; ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } else { ?>
                        <div class="ss-empty text-center p-4 text-muted">No hay servicios registrados.</div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>