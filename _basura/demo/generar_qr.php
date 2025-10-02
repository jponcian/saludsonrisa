<?php
define('SITE_URL', 'https://clinicasaludsonrisa.zz.com.ve/demo/');
require_once '../api/conexion.php';

$id_reunion = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_reunion <= 0)
    die('ID de reunión inválido.');

$stmt = $pdo->prepare("SELECT * FROM demo_reuniones WHERE id = ?");
$stmt->execute([$id_reunion]);
$reunion = $stmt->fetch();
if (!$reunion)
    die('Reunión no encontrada.');

$participantes = $pdo->prepare("SELECT id, nombre, token FROM demo_participantes WHERE id_reunion = ? ORDER BY nombre ASC");
$participantes->execute([$id_reunion]);
$result = $participantes->fetchAll();
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<style>
    .carnet-container {
        page-break-inside: avoid;
        display: flex;
        justify-content: center;
        padding: 0;
    }

    .carnet {
        border: 1px solid #dee2e6;
        transition: box-shadow .2s;
        background: #fff;
        /* Carnet más compacto: 65mm de ancho */
        width: 65mm;
        max-width: 100%;
        min-height: 38mm;
        height: 38mm;
        padding: 6px 4px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: center;
        margin: 0 auto;
        box-sizing: border-box;
        font-size: 0.85rem;
    }

    @media (max-width: 400px) {
        .carnet {
            width: 98vw;
            min-width: 0;
            height: auto;
        }
    }

    /* Grid para impresión: 3 carnets por fila */
    @media print {
        .row {
            display: flex;
            flex-wrap: wrap;
        }

        .carnet-container {
            width: 33.33% !important;
            max-width: 33.33% !important;
            flex: 0 0 33.33%;
            box-sizing: border-box;
            margin: 0 !important;
            padding: 0 !important;
        }

        .carnet {
            width: 65mm !important;
            height: 38mm !important;
            min-height: 0;
            max-width: 100%;
            padding: 6px 4px;
        }

        .no-print {
            display: none;
        }
    }

    .carnet:hover {
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15);
    }

    .carnet-check {
        transform: scale(1.5);
        margin-right: 10px;
    }

    @media print {
        .carnet-container {
            width: 50% !important;
        }

        .carnet {
            width: 85mm !important;
            height: 54mm !important;
            min-height: 0;
            max-width: 100%;
            padding: 8px 6px;
        }

        .no-print {
            display: none;
        }
    }

    .swal2-container {
        z-index: 99999 !important;
        position: fixed !important;
        inset: 0 !important;
    }
</style>

<div class="container-fluid pt-3">
    <div class="card card-primary card-outline no-print">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Carnets para:
                <?= htmlspecialchars($reunion['titulo']) ?>
            </h3>
            <div class="card-tools">
                <button class="btn btn-dark btn-sm" onclick="window.print()"><i class="fas fa-print mr-1"></i> Imprimir
                    Todo</button>
                <button class="btn btn-primary btn-sm" id="btnPrintSelected"><i class="fas fa-check-square mr-1"></i>
                    Imprimir Selección</button>
            </div>
        </div>
        <div class="card-body">
            <p class="mb-0">Marca los carnets que deseas imprimir y haz clic en <strong>Imprimir Selección</strong>. Los
                carnets se ajustarán para la impresión.</p>
        </div>
    </div>

    <div id="printableArea">
        <div class="row mt-4">
            <?php if (empty($result)): ?>
                <div class="col-12 no-print">
                    <div class="alert alert-warning">No hay participantes para generar carnets.</div>
                </div>
            <?php endif; ?>
            <?php foreach ($result as $p): ?>
                <?php $qr_url = SITE_URL . "qr_info.php?reunion={$id_reunion}&participante={$p['id']}&token=" . urlencode($p['token']); ?>
                <div class="col-12 col-sm-6 col-md-4 mb-4 carnet-container">
                    <div class="border rounded p-2 position-relative carnet h-100">
                        <div class="position-absolute no-print" style="left:10px; top:10px; z-index:2;">
                            <input type="checkbox" class="carnet-check" value="<?= $p['id'] ?>">
                        </div>
                        <div class="text-center">
                            <img src="../logo.png" alt="Logo" style="height:32px; margin-bottom: 0.5rem;">
                            <div class="mb-1 font-weight-bold" style="font-size:0.95rem;">Clínica SaludSonrisa</div>
                            <div class="small text-muted">Evento: <?= htmlspecialchars($reunion['titulo']) ?></div>
                        </div>
                        <hr class="my-2">
                        <div class="text-center">
                            <div class="font-weight-bold mb-1" style="font-size:1.05rem; color:#007bff;">Participante</div>
                            <div style="font-size:1.1rem; margin-bottom:2px;"> <?= htmlspecialchars($p['nombre']) ?> </div>
                            <div class="small text-muted mb-1">Fecha:
                                <?= htmlspecialchars(date('d-m-Y', strtotime($reunion['fecha']))) ?> | Hora:
                                <?= htmlspecialchars(date('h:i A', strtotime($reunion['hora']))) ?>
                            </div>
                            <div class="qr d-inline-block mt-1 mb-1" data-url="<?= htmlspecialchars($qr_url) ?>"></div>
                            <div class="small text-muted mt-1">Escanee este código QR para registrar su
                                asistencia.<br>Conserve este carnet durante el evento.</div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.qr').forEach(c => new QRCode(c, {
        text: c.dataset.url,
        width: 120,
        height: 120,
        correctLevel: QRCode.CorrectLevel.M
    }));

    document.getElementById('btnPrintSelected').addEventListener('click', () => {
        const checked = document.querySelectorAll('.carnet-check:checked');
        if (checked.length === 0) {
            alert('Selecciona al menos un carnet para imprimir.');
            return;
        }
        // Ocultar todos y mostrar solo los seleccionados para la impresión
        document.querySelectorAll('.carnet-container').forEach(c => c.style.display = 'none');
        checked.forEach(chk => chk.closest('.carnet-container').style.display = 'block');
        window.print();
        // Restaurar vista
        document.querySelectorAll('.carnet-container').forEach(c => c.style.display = 'block');
    });
</script>