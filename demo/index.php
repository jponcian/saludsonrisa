<?php
date_default_timezone_set('America/Caracas');
require_once '../api/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['titulo'], $_POST['fecha'], $_POST['hora'])) {
    $titulo = $_POST['titulo'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $stmt = $pdo->prepare("INSERT INTO demo_reuniones (titulo, fecha, hora) VALUES (?, ?, ?)");
    $stmt->execute([$titulo, $fecha, $hora]);
    header('Location: index.php?embed=1'); // Keep embed for navigation context
    exit;
}

$reuniones = $pdo->query("SELECT * FROM demo_reuniones ORDER BY fecha DESC, hora DESC")->fetchAll();
?>
<div class="container-fluid pt-3">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Reuniones Programadas</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" data-toggle="collapse" data-target="#collapse-form">
                    <i class="fas fa-plus mr-1"></i> Nueva Reunión
                </button>
            </div>
        </div>
        <div id="collapse-form" class="collapse">
            <div class="card-body bg-light">
                <form method="post">
                    <div class="row">
                        <div class="form-group col-md-5">
                            <input type="text" class="form-control" name="titulo" placeholder="Título de la reunión" required>
                        </div>
                        <div class="form-group col-md-3">
                            <input type="date" class="form-control" name="fecha" required>
                        </div>
                        <div class="form-group col-md-2">
                            <input type="time" class="form-control" name="hora" required>
                        </div>
                        <div class="form-group col-md-2">
                            <button class="btn btn-success btn-block" type="submit">Crear</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 40%;">Título</th>
                            <th style="width: 20%;">Fecha y Hora</th>
                            <th style="width: 40%;" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reuniones)): ?>
                            <tr>
                                <td colspan="3">
                                    <div class="text-center p-5">
                                        <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No hay reuniones creadas</h5>
                                        <p>Utiliza el botón "Nueva Reunión" para empezar.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($reuniones as $r): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($r['titulo']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars(date("d/m/Y", strtotime($r['fecha']))) ?> <span class="badge badge-secondary"><?= htmlspecialchars(date("h:i A", strtotime($r['hora']))) ?></span></td>
                                <td class="text-center">
                                    <a class="btn btn-sm btn-primary" href="participantes.php?id=<?= $r['id'] ?>&embed=1"><i class="fas fa-users"></i> Participantes</a>
                                    <a class="btn btn-sm btn-secondary" href="generar_qr.php?id=<?= $r['id'] ?>&embed=1"><i class="fas fa-qrcode"></i> Carnets</a>
                                    <a class="btn btn-sm btn-success" href="asistencia.php?id=<?= $r['id'] ?>&embed=1"><i class="fas fa-check-circle"></i> Asistencia</a>
                                    <a class="btn btn-sm btn-warning" href="transmitir.php?id=<?= $r['id'] ?>&embed=1"><i class="fas fa-video"></i> Transmitir</a>
                                    <a class="btn btn-sm btn-danger" href="#" onclick="eliminarReunion(<?= $r['id'] ?>); return false;"><i class="fas fa-trash"></i> Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="../js/iframe_helper.js"></script>
<script>
    function eliminarReunion(id) {
        const options = {
            title: '¿Eliminar reunión?',
            text: 'Se eliminarán también los participantes y asistencias asociadas.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#d33',
            reverseButtons: true
        };

        showGlobalSweetAlert(options, (result) => {
            if (result.isConfirmed) {
                fetch('eliminar_reunion.php?id=' + id)
                    .then(() => {
                        showGlobalSweetAlert({
                            title: 'Eliminado',
                            text: 'La reunión fue eliminada.',
                            icon: 'success'
                        }, () => {
                            location.reload();
                        });
                    });
            }
        });
    }
</script>