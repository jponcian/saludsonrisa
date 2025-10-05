<?php
require_once '../api/conexion.php';
require_once __DIR__ . '/webrtc_config.php';

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
                <hr>
                <h4 class="mt-4">Transmisión en Vivo</h4>
                <div style="position:relative;">
                    <video id="videoViewer" autoplay muted playsinline style="width: 100%; height: auto; border: 1px solid #333; background:#000; display:block;"></video>
                    <img id="fallbackImg" src="" alt="Fallback" style="display:none;width:100%;border:1px solid #333;background:#000;" />
                    <div id="overlayEstado" style="position:absolute;top:8px;left:8px;background:rgba(0,0,0,0.55);color:#fff;padding:4px 8px;border-radius:4px;font-size:12px;">Conectando...</div>
                </div>
            <?php else: ?>
                <div class="alert alert-danger mt-4">
                    <i class="fas fa-exclamation-triangle"></i> Token de seguridad inválido o faltante.
                </div>
            <?php endif; ?>

        </div>
    </div>

    <script src="../plugins/peerjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if ($token_valido): ?>
            document.addEventListener('DOMContentLoaded', function() {
                const videoViewer = document.getElementById('videoViewer');
                console.log('Iniciando conexión como viewer...');
                const rawIce = <?php echo json_encode($ICE_SERVERS, JSON_UNESCAPED_SLASHES); ?>;
                // Sanitizar en cliente por si llega algo inesperado
                let iceServers = Array.isArray(rawIce) ? rawIce.filter(s => s && s.urls) : [];
                if (!iceServers.length) {
                    console.warn('[Viewer] Lista iceServers vacía, usando STUN por defecto.');
                    iceServers = [{
                        urls: 'stun:stun.l.google.com:19302'
                    }];
                }
                console.log('[Viewer] ICE Servers usados:', iceServers);
                const peer = new Peer(undefined, {
                    debug: 2,
                    config: {
                        iceServers
                    }
                });

                // Al cargar el viewer, notificar al backend que el participante llegó (registrar asistencia)
                (async function registrarLlegada() {
                    try {
                        const resp = await fetch('registrar_asistencia.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'reunion=<?php echo $id_reunion; ?>&participante=<?php echo $id_participante; ?>&token=<?php echo rawurlencode($token); ?>'
                        });
                        const j = await resp.json();
                        if (j && j.success) {
                            console.log('[Viewer] Asistencia registrada:', j.nombre || 'ok');
                            // notificar al padre (si está embebido) para actualizar UI adicional
                            if (window.parent && window.parent !== window) {
                                window.parent.postMessage({
                                    action: 'participante_llego',
                                    participante: <?php echo $id_participante; ?>
                                }, '*');
                            }
                        } else {
                            console.log('[Viewer] Registrar asistencia respuesta:', j);
                        }
                    } catch (e) {
                        console.error('[Viewer] Error registrando asistencia:', e);
                    }
                })();

                // Escuchar llamadas entrantes (cuando el transmisor inicia la llamada tras recibir 'solicitar-stream')
                peer.on('call', function(call) {
                    console.log('[Viewer] Llamada entrante desde', call.peer, '— respondiendo...');
                    try {
                        call.answer(); // No enviamos stream, solo recibimos
                    } catch (e) {
                        console.error('[Viewer] Error al responder llamada entrante:', e);
                    }
                    call.on('stream', function(remoteStream) {
                        console.log('[Viewer] Stream recibido vía llamada entrante');
                        const videoViewer = document.getElementById('videoViewer');
                        if (videoViewer && !videoViewer.srcObject) {
                            videoViewer.srcObject = remoteStream;
                        } else {
                            // Si ya había un stream, se ignora o se podría reemplazar
                            videoViewer.srcObject = remoteStream;
                        }
                        const ov = document.getElementById('overlayEstado');
                        if (ov) {
                            ov.textContent = 'Transmisión activa';
                            ov.style.background = 'rgba(0,128,0,0.55)';
                        }
                        llamadaEstablecida = true;
                    });
                    call.on('close', function() {
                        console.log('[Viewer] Llamada entrante cerrada');
                        const ov = document.getElementById('overlayEstado');
                        if (ov) {
                            ov.textContent = 'Transmisión finalizada';
                            ov.style.background = 'rgba(128,0,0,0.55)';
                        }
                    });
                    call.on('error', function(err) {
                        console.error('[Viewer] Error en llamada entrante:', err);
                        const ov = document.getElementById('overlayEstado');
                        if (ov) {
                            ov.textContent = 'Error en transmisión';
                            ov.style.background = 'rgba(128,0,0,0.55)';
                        }
                    });
                    if (call.peerConnection) {
                        call.peerConnection.oniceconnectionstatechange = function() {
                            console.log('[Viewer] ICE state:', call.peerConnection.iceConnectionState);
                        };
                    }
                });

                let intentosSolicitud = 0;
                let maxIntentos = 6; // ~18s
                let solicitando = false;
                let intentoFallbackLlamada = 0;
                let llamadaEstablecida = false;
                let fallbackPolling = null;

                function iniciarFallbackPolling() {
                    if (fallbackPolling) return;
                    const img = document.getElementById('fallbackImg');
                    const ov = document.getElementById('overlayEstado');
                    const videoEl = document.getElementById('videoViewer');
                    if (ov) {
                        ov.textContent = 'Modo fallback (imágenes)';
                        ov.style.background = 'rgba(180,120,0,0.55)';
                    }
                    img.style.display = 'block';
                    videoEl.style.display = 'none';
                    fallbackPolling = setInterval(() => {
                        img.src = 'frames/current.jpg?ts=' + Date.now();
                    }, 1500); // 1.5s por frame
                }

                function crearStreamSilencioso() {
                    try {
                        const ctx = new(window.AudioContext || window.webkitAudioContext)();
                        const osc = ctx.createOscillator();
                        const dest = osc.connect(ctx.createMediaStreamDestination());
                        osc.start();
                        const mstream = new MediaStream([dest.stream.getAudioTracks()[0]]);
                        mstream.getAudioTracks()[0].enabled = false; // deshabilitar
                        setTimeout(() => {
                            try {
                                osc.stop();
                                ctx.close();
                            } catch (e) {}
                        }, 4000);
                        return mstream;
                    } catch (e) {
                        console.error('[Viewer] No se pudo crear stream silencioso:', e);
                        return null;
                    }
                }

                function fallbackLlamadaDirecta() {
                    if (llamadaEstablecida) return;
                    intentoFallbackLlamada++;
                    if (intentoFallbackLlamada > 4) {
                        console.warn('[Viewer][Fallback] Máximo de intentos alcanzado.');
                        return;
                    }
                    console.log('[Viewer][Fallback] Intentando llamada directa #' + intentoFallbackLlamada);
                    const dummy = crearStreamSilencioso();
                    if (!dummy) {
                        setTimeout(fallbackLlamadaDirecta, 2500);
                        return;
                    }
                    let directCall = null;
                    try {
                        directCall = peer.call('transmisor-<?php echo $id_reunion; ?>', dummy);
                    } catch (e) {
                        console.error('[Viewer][Fallback] Excepción call directa:', e);
                    }
                    if (!directCall) {
                        setTimeout(fallbackLlamadaDirecta, 2500);
                        return;
                    }
                    directCall.on('stream', function(remoteStream) {
                        if (!videoViewer.srcObject) {
                            console.log('[Viewer][Fallback] Stream recibido vía llamada directa');
                            videoViewer.srcObject = remoteStream;
                            llamadaEstablecida = true;
                            const ov = document.getElementById('overlayEstado');
                            if (ov) {
                                ov.textContent = 'Transmisión activa';
                                ov.style.background = 'rgba(0,128,0,0.55)';
                            }
                        }
                    });
                    directCall.on('error', e => console.error('[Viewer][Fallback] Error llamada directa:', e));
                    directCall.on('close', () => console.log('[Viewer][Fallback] Llamada directa cerrada'));
                }

                function solicitarStreamPeriodicamente(conn) {
                    if (solicitando) return;
                    solicitando = true;
                    const interval = setInterval(() => {
                        if (videoViewer.srcObject) {
                            clearInterval(interval);
                            return;
                        }
                        intentosSolicitud++;
                        console.log('[Viewer] Re-solicitando stream intento', intentosSolicitud);
                        try {
                            conn.send('solicitar-stream');
                        } catch (e) {
                            console.error('[Viewer] Error reenviando solicitud:', e);
                        }
                        const ov = document.getElementById('overlayEstado');
                        if (ov && !videoViewer.srcObject) {
                            ov.textContent = 'Esperando transmisión (' + intentosSolicitud + ')';
                        }
                        if (intentosSolicitud >= maxIntentos) {
                            clearInterval(interval);
                            if (!videoViewer.srcObject) {
                                let aviso = document.getElementById('estadoTransmision');
                                if (!aviso) {
                                    aviso = document.createElement('div');
                                    aviso.id = 'estadoTransmision';
                                    aviso.className = 'mt-3 text-danger small';
                                    aviso.innerText = 'No se pudo obtener la transmisión. Intente refrescar más tarde.';
                                    videoViewer.parentNode.insertBefore(aviso, videoViewer.nextSibling);
                                } else {
                                    aviso.classList.remove('text-info');
                                    aviso.classList.add('text-danger');
                                    aviso.innerText = 'No se pudo obtener la transmisión. Intente refrescar más tarde.';
                                }
                                // Lanzar fallback final (llamada directa y luego imágenes si falla)
                                fallbackLlamadaDirecta();
                                setTimeout(() => {
                                    if (!videoViewer.srcObject && !llamadaEstablecida) {
                                        iniciarFallbackPolling();
                                    }
                                }, 3000);
                            }
                        }
                    }, 3000);
                }

                function iniciarDataChannel() {
                    console.log('[Viewer] Abriendo DataConnection al transmisor...');
                    let conn;
                    try {
                        conn = peer.connect('transmisor-<?php echo $id_reunion; ?>');
                    } catch (e) {
                        console.error('[Viewer] Excepción conectando DC:', e);
                        return;
                    }
                    if (!conn) {
                        console.warn('[Viewer] No se pudo crear DataConnection');
                        return;
                    }
                    conn.on('open', function() {
                        console.log('[Viewer] DataConnection abierta, solicitando stream...');
                        conn.send('solicitar-stream');
                        solicitarStreamPeriodicamente(conn);
                    });
                    conn.on('data', function(d) {
                        console.log('[Viewer] Data recibido:', d);
                    });
                    conn.on('error', function(e) {
                        console.error('[Viewer] Error DataConnection:', e);
                    });
                }

                peer.on('open', function(id) {
                    console.log('Peer viewer abierto con ID: ' + id);
                    iniciarDataChannel();
                    const ov = document.getElementById('overlayEstado');
                    if (ov) {
                        ov.textContent = 'Conectando (peer listo)...';
                    }
                    // Intentar fallback si no llega stream entrante en 7s
                    setTimeout(() => {
                        if (!videoViewer.srcObject && !llamadaEstablecida) {
                            fallbackLlamadaDirecta();
                        }
                    }, 7000);
                    // Si tampoco funciona en 12s, activar polling de imágenes
                    setTimeout(() => {
                        if (!videoViewer.srcObject && !llamadaEstablecida) {
                            iniciarFallbackPolling();
                        }
                    }, 12000);
                });

                peer.on('error', function(err) {
                    console.error('Error en peer viewer:', err);
                });
                peer.on('disconnected', function() {
                    console.warn('Peer viewer desconectado');
                });
                peer.on('close', function() {
                    console.warn('Peer viewer cerrado');
                });
            });
        <?php endif; ?>
    </script>
</body>

</html>