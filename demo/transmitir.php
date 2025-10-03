<?php
require_once '../api/conexion.php';
require_once __DIR__ . '/webrtc_config.php';
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

$participantes = $pdo->prepare('SELECT p.id, p.nombre, CASE WHEN a.id IS NOT NULL THEN 1 ELSE 0 END as asistio FROM demo_participantes p LEFT JOIN demo_asistencias a ON p.id = a.id_participante AND a.id_reunion = ? WHERE p.id_reunion = ? ORDER BY p.nombre ASC');
$participantes->execute([$id_reunion, $id_reunion]);
$result = $participantes->fetchAll();
?>
<div class="container-fluid pt-3">
    <div class="row">
        <div class="col-md-7">
            <div class="card card-primary card-outline h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-video mr-2"></i>Transmisión en Vivo</h3>
                </div>
                <div class="card-body text-center">
                    <video id="videoTransmision" autoplay muted style="width: 100%; height: auto; border: 1px solid #ccc;"></video>
                    <br><br>
                    <button id="btnIniciar" class="btn btn-success btn-block"><i class="fas fa-play"></i> Iniciar Transmisión</button>
                    <button id="btnDetener" class="btn btn-danger btn-block" style="display:none;"><i class="fas fa-stop"></i> Detener Transmisión</button>
                </div>
            </div>
        </div>
        <div class="col-md-5">
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
                                    <th style="width: 40px;">Estado</th>
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
                                            <?php if ($row['asistio']): ?>
                                                <i class="fas fa-check text-success" title="Ingresado"></i>
                                            <?php else: ?>
                                                <i class="fas fa-minus-circle text-danger" title="No ingresado"></i>
                                            <?php endif; ?>
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
<script src="../plugins/peerjs.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const video = document.getElementById('videoTransmision');
        const btnIniciar = document.getElementById('btnIniciar');
        const btnDetener = document.getElementById('btnDetener');
        let stream = null;
        let peer = null;
        let calls = [];
        let frameTimer = null;

        // Fallback: captura y envía frames JPEG para viewers sin WebRTC
        function iniciarEnvioFrames() {
            if (!stream) return;
            const videoEl = video;
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            // Intervalo ~1 fps (ajustable)
            frameTimer = setInterval(() => {
                if (!videoEl.videoWidth) return;
                const targetW = 640;
                const scale = targetW / videoEl.videoWidth;
                canvas.width = targetW;
                canvas.height = Math.round(videoEl.videoHeight * scale);
                ctx.drawImage(videoEl, 0, 0, canvas.width, canvas.height);
                try {
                    const dataUrl = canvas.toDataURL('image/jpeg', 0.6);
                    fetch('upload_frame.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'frame=' + encodeURIComponent(dataUrl)
                    }).catch(() => {});
                } catch (e) {
                    console.warn('No se pudo generar frame fallback:', e);
                }
            }, 1000);
        }

        function detenerEnvioFrames() {
            if (frameTimer) {
                clearInterval(frameTimer);
                frameTimer = null;
            }
        }

        btnIniciar.addEventListener('click', async function() {
            try {
                console.log('Solicitando acceso a cámara...');
                stream = await navigator.mediaDevices.getUserMedia({
                    video: true,
                    audio: true
                });
                console.log('Stream obtenido:', stream);
                video.srcObject = stream;
                console.log('Video srcObject set');

                if (typeof Peer === 'undefined') {
                    alert('La librería PeerJS no se cargó correctamente. Verifica tu conexión a internet o descarga el archivo manualmente.');
                    return;
                }

                // Crear peer con id de reunión
                console.log('Creando peer...');
                let rawIce = <?php echo json_encode($ICE_SERVERS, JSON_UNESCAPED_SLASHES); ?>;
                let iceServers = Array.isArray(rawIce) ? rawIce.filter(s => s && s.urls) : [];
                if (!iceServers.length) {
                    console.warn('[Tx] Lista iceServers vacía, usando fallback STUN.');
                    iceServers = [{
                        urls: 'stun:stun.l.google.com:19302'
                    }];
                }
                console.log('[Tx] ICE Servers usados:', iceServers);
                peer = new Peer('transmisor-<?php echo $id_reunion; ?>', {
                    debug: 2,
                    config: {
                        iceServers
                    }
                });

                peer.on('open', function(id) {
                    console.log('Peer transmisor abierto con ID: ' + id);
                });

                // Un viewer podría intentar llamarnos directamente (fallback)
                peer.on('call', function(call) {
                    console.log('[Tx] Llamada entrante, respondiendo... peerId remoto:', call.peer);
                    call.answer(stream);
                    calls.push(call);
                    if (call.peerConnection) {
                        call.peerConnection.oniceconnectionstatechange = function() {
                            console.log('[Tx] ICE state (entrante):', call.peerConnection.iceConnectionState);
                        };
                    }
                });

                // Nuevo: cuando un viewer abre un canal de datos y solicita el stream, el transmisor inicia la llamada saliente
                peer.on('connection', function(conn) {
                    console.log('[Tx] DataConnection abierta desde', conn.peer);
                    conn.on('data', function(data) {
                        console.log('[Tx] Data recibido desde', conn.peer, ':', data);
                        if (data === 'solicitar-stream' && stream) {
                            console.log('[Tx] Iniciando llamada saliente a', conn.peer);
                            try {
                                const call = peer.call(conn.peer, stream);
                                if (call) {
                                    calls.push(call);
                                    call.on('close', () => console.log('[Tx] Llamada cerrada con', conn.peer));
                                    call.on('error', (e) => console.error('[Tx] Error en llamada saliente:', e));
                                    if (call.peerConnection) {
                                        call.peerConnection.oniceconnectionstatechange = function() {
                                            console.log('[Tx] ICE state (saliente a ' + conn.peer + '):', call.peerConnection.iceConnectionState);
                                        };
                                    }
                                } else {
                                    console.warn('[Tx] No se pudo crear la llamada hacia', conn.peer);
                                }
                            } catch (e) {
                                console.error('[Tx] Excepción creando llamada saliente:', e);
                            }
                        }
                    });
                });

                peer.on('error', function(err) {
                    console.error('Error en peer transmisor:', err);
                });
                peer.on('disconnected', function() {
                    console.warn('Peer transmisor desconectado');
                });
                peer.on('close', function() {
                    console.warn('Peer transmisor cerrado');
                });

                btnIniciar.style.display = 'none';
                btnDetener.style.display = 'block';

                // Iniciar fallback de frames
                iniciarEnvioFrames();
            } catch (error) {
                console.error('Error:', error);
                alert('Error al acceder a la cámara: ' + error.message);
            }
        });

        btnDetener.addEventListener('click', function() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                video.srcObject = null;
                stream = null;
            }
            if (peer) {
                peer.destroy();
                peer = null;
            }
            calls.forEach(call => call.close());
            calls = [];
            detenerEnvioFrames();
            btnDetener.style.display = 'none';
            btnIniciar.style.display = 'block';
        });
    });
</script>