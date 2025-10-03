<?php
require_once '../api/conexion.php';
date_default_timezone_set('America/Caracas');

$id_reunion = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_reunion <= 0) {
    die('ID de reunión inválido.');
}

$stmt = $pdo->prepare('SELECT * FROM demo_reuniones WHERE id = ?');
$stmt->execute([$id_reunion]);
$reunion = $stmt->fetch();
if (!$reunion) {
    die('Reunión no encontrada.');
}

function generar_token($len = 32)
{
    return bin2hex(random_bytes($len / 2));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombres'])) {
    $nombres_raw = trim($_POST['nombres']);
    if ($nombres_raw !== '') {
        $nombres = array_filter(array_map('trim', preg_split('/\r?\n/', $nombres_raw)));
        $insert = $pdo->prepare('INSERT INTO demo_participantes (id_reunion, nombre, token) VALUES (?, ?, ?)');
        foreach ($nombres as $nombre) {
            try {
                $insert->execute([$id_reunion, $nombre, generar_token()]);
            } catch (Exception $e) { /* Ignorar duplicados */
            }
        }
    }
    header('Location: participantes.php?id=' . $id_reunion . '&embed=1');
    exit;
}

if (isset($_GET['del'])) {
    $idp = intval($_GET['del']);
    $stmt = $pdo->prepare('DELETE FROM demo_participantes WHERE id = ? AND id_reunion = ?');
    $stmt->execute([$idp, $id_reunion]);
    header('Location: participantes.php?id=' . $id_reunion . '&embed=1');
    exit;
}

$participantes = $pdo->prepare('SELECT id, nombre FROM demo_participantes WHERE id_reunion = ? ORDER BY nombre ASC');
$participantes->execute([$id_reunion]);
$result = $participantes->fetchAll();
?>
<div class="container-fluid pt-3">
    <div class="row">
        <div class="col-md-5">
            <div class="card card-primary card-outline h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-plus mr-2"></i>Agregar Participantes</h3>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="form-group">
                            <label for="nombres">Nombres (uno por línea):</label>
                            <textarea id="nombres" class="form-control" name="nombres" rows="8" placeholder="Juan Pérez\nMaría Gómez" required></textarea>
                        </div>
                        <button class="btn btn-primary btn-block" type="submit">Agregar a la Lista</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card card-info card-outline h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list mr-2"></i>Listado de Participantes</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 10px;">#</th>
                                    <th>Nombre</th>
                                    <th style="width: 40px;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($result)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center p-4">No hay participantes registrados.</td>
                                    </tr>
                                <?php endif; ?>
                                <?php $i = 1;
                                foreach ($result as $row): ?>
                                    <tr>
                                        <td><?= $i++ ?>.</td>
                                        <td><?= htmlspecialchars($row['nombre']) ?></td>
                                        <td>
                                            <a class="btn btn-danger btn-sm" href="#" onclick="eliminarParticipante(<?= $row['id'] ?>, <?= $id_reunion ?>); return false;" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../js/iframe_helper.js"></script>
<script>
    function eliminarParticipante(id, id_reunion) {
        const options = {
            title: '¿Eliminar participante?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#d33',
            reverseButtons: true
        };

        showGlobalSweetAlert(options, (result) => {
            if (result.isConfirmed) {
                fetch(`participantes.php?id=${id_reunion}&del=${id}&embed=1`)
                    .then(() => {
                        showGlobalSweetAlert({
                            title: 'Eliminado',
                            text: 'El participante fue eliminado.',
                            icon: 'success'
                        }, () => {
                            location.reload();
                        });
                    });
            }
        });
    }
</script>