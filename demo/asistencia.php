<head>
    <!-- ...existing code... -->
    <style>
        .swal2-container {
            z-index: 99999 !important;
            position: fixed !important;
            inset: 0 !important;
        }
    </style>
    <!-- ...existing code... -->
</head>

<body>
    <?php
    require_once '../api/conexion.php';
    $id_reunion = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id_reunion <= 0) die('ID de reunión inválido.');

    $stmt = $pdo->prepare("SELECT * FROM demo_reuniones WHERE id = ?");
    $stmt->execute([$id_reunion]);
    $reunion = $stmt->fetch();
    if (!$reunion) die('Reunión no encontrada.');
    ?>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="container-fluid pt-3">
        <div class="row">
            <div class="col-lg-5">
                <div class="card card-primary card-outline h-100">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-qrcode mr-2"></i>Escanear Código QR</h3>
                    </div>
                    <div class="card-body text-center">
                        <div id="reader" style="width: 100%; max-width: 400px; margin: auto;"></div>
                        <div id="result" class="mt-3 font-weight-bold"></div>
                        <div class="mb-3">
                            <button id="btn-iniciar" class="btn btn-success mr-2"><i class="fas fa-play"></i> Iniciar escaneo
                            </button>
                            <button id="btn-detener" class="btn btn-danger" disabled><i class="fas fa-stop"></i> Detener escaneo
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card card-info card-outline h-100">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-tasks mr-2"></i>Asistentes Registrados (Tiempo Real)</h3>
                    </div>
                    <div class="card-body">
                        <ul id="lista-asistentes" class="list-group"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let lastScanTime = 0;
        const scanCooldown = 3000; // 3 segundos
        let html5QrCode = null;
        let scanning = false;

        function onScanSuccess(decodedText, decodedResult) {
            const currentTime = Date.now();
            if (currentTime - lastScanTime < scanCooldown) return;
            lastScanTime = currentTime;

            document.getElementById('result').innerHTML = `<span class="text-success">QR detectado, procesando...</span>`;

            try {
                const url = new URL(decodedText);
                const params = new URLSearchParams(url.search);
                if (params.get('reunion') != "<?= $id_reunion ?>") {
                    setResultMessage('El QR no corresponde a esta reunión.', 'danger');
                    return;
                }

                fetch('registrar_asistencia.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams(params).toString()
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            setResultMessage(`Registrado: ${data.nombre}`, 'success');
                            cargarAsistentes();
                        } else {
                            setResultMessage(data.error || 'Error al registrar', 'warning');
                        }
                    });
            } catch (e) {
                setResultMessage('Código QR no válido.', 'danger');
            }
        }

        function setResultMessage(message, type) {
            const resultEl = document.getElementById('result');
            resultEl.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
            setTimeout(() => resultEl.innerHTML = '', 4000);
        }

        function cargarAsistentes() {
            fetch(`listar_asistentes.php?id=<?= $id_reunion ?>&t=${Date.now()}`)
                .then(r => r.json())
                .then(data => {
                    const ul = document.getElementById('lista-asistentes');
                    ul.innerHTML = '';
                    if (data.length === 0) {
                        ul.innerHTML = '<li class="list-group-item text-muted">Aún no hay asistentes registrados.</li>';
                    }
                    data.forEach(a => {
                        const fecha = new Date(a.fecha_hora);
                        const dia = ('0' + fecha.getDate()).slice(-2);
                        const mes = ('0' + (fecha.getMonth() + 1)).slice(-2);
                        const anio = fecha.getFullYear();
                        let horas = fecha.getHours();
                        const minutos = ('0' + fecha.getMinutes()).slice(-2);
                        const ampm = horas >= 12 ? 'PM' : 'AM';
                        horas = horas % 12;
                        horas = horas ? horas : 12;
                        const horaStr = `${horas}:${minutos} ${ampm}`;
                        const fechaStr = `${dia}-${mes}-${anio}`;
                        const li = document.createElement('li');
                        li.className = 'list-group-item d-flex justify-content-between align-items-center';
                        li.innerHTML = `<span><i class='fas fa-user-check mr-2 text-success'></i> ${a.nombre} <span class='badge badge-light ml-2'>${fechaStr} ${horaStr}</span></span> <button class='btn btn-danger btn-sm btn-eliminar-asistencia' data-nombre='${encodeURIComponent(a.nombre)}'><i class='fas fa-trash'></i></button>`;
                        ul.appendChild(li);
                    });
                    // Botón eliminar
                    document.querySelectorAll('.btn-eliminar-asistencia').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const nombre = decodeURIComponent(this.getAttribute('data-nombre'));
                            Swal.fire({
                                title: '¿Eliminar asistencia?',
                                text: `¿Eliminar el registro de asistencia de ${nombre}?`,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Sí, eliminar',
                                cancelButtonText: 'Cancelar',
                                confirmButtonColor: '#d33',
                                reverseButtons: true
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    fetch(`eliminar_asistencia.php?id_reunion=<?= $id_reunion ?>&nombre=${encodeURIComponent(nombre)}`)
                                        .then(r => r.json())
                                        .then(data => {
                                            if (data.success) {
                                                Swal.fire('Eliminado', 'La asistencia fue eliminada.', 'success');
                                                cargarAsistentes();
                                            } else {
                                                Swal.fire('Error', data.error || 'No se pudo eliminar.', 'error');
                                            }
                                        });
                                }
                            });
                        });
                    });
                });
        }

        document.addEventListener('DOMContentLoaded', () => {
            cargarAsistentes();
            html5QrCode = new Html5Qrcode("reader");
            document.getElementById('btn-iniciar').addEventListener('click', function() {
                if (scanning) return;
                html5QrCode.start({
                        facingMode: "environment"
                    }, {
                        fps: 10,
                        qrbox: {
                            width: 250,
                            height: 250
                        }
                    },
                    onScanSuccess,
                    (errorMessage) => {
                        /* ignore */
                    }
                ).then(() => {
                    scanning = true;
                    document.getElementById('btn-iniciar').disabled = true;
                    document.getElementById('btn-detener').disabled = false;
                }).catch(err => {
                    document.getElementById('reader').innerHTML = '<div class="alert alert-danger">No se pudo iniciar el escáner QR. Verifique los permisos de la cámara.</div>';
                });
            });
            document.getElementById('btn-detener').addEventListener('click', function() {
                if (!scanning) return;
                html5QrCode.stop().then(() => {
                    scanning = false;
                    document.getElementById('btn-iniciar').disabled = false;
                    document.getElementById('btn-detener').disabled = true;
                });
            });
        });
    </script>
</body>