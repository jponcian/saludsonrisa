<?php
require_once 'api/auth_check.php';
$current_page = basename($_SERVER['PHP_SELF']);
// Construir la URL a cargar dentro del iframe según query params
$page = isset($_GET['page']) ? $_GET['page'] : 'index';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

function demo_url($page, $id)
{
    $base_path = 'demo/';
    switch ($page) {
        case 'participantes':
            return $base_path . 'participantes.php?id=' . $id;
        case 'generar_qr':
            return $base_path . 'generar_qr.php?id=' . $id;
        case 'asistencia':
            return $base_path . 'asistencia.php?id=' . $id;
        case 'transmitir':
            return $base_path . 'transmitir.php?id=' . $id;
        case 'qr_info':
            return $base_path . 'qr_info.php?id=' . $id;
        case 'index':
        default:
            return $base_path . 'index.php';
    }
}

$iframe_src = demo_url($page, $id);
$iframe_src .= (strpos($iframe_src, '?') !== false ? '&' : '?') . 'embed=1';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clínica SaludSonrisa | Demo QR</title>
    <link rel="stylesheet" href="css/Source Sans Pro.css">
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
    <link rel="stylesheet" href="css/custom.css">
    <style>
        .content-wrapper {
            background: #f4f6f9;
        }

        .demo-frame {
            width: 100%;
            border: none;
            flex-grow: 1;
        }

        .swal2-container {
            z-index: 99999 !important;
            position: fixed !important;
            inset: 0 !important;
        }

        .card-body {
            display: flex;
            flex-direction: column;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars"></i></a></li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($nombre_completo); ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <span class="dropdown-item dropdown-header">Opciones de Usuario</span>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item" data-toggle="modal" data-target="#modal-cambiar-contrasena">
                            <i class="fas fa-key mr-2"></i> Cambiar Contraseña
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="api/logout.php" class="dropdown-item">
                            <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                        </a>
                    </div>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <?php include 'sidebar.php'; ?>
        <?php include 'modal_cambiar_contrasena.php'; ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1><i class="fas fa-qrcode"></i> Demo - Asistencia con QR</h1>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Navegación del Módulo</h3>
                            <div class="card-tools">
                                <a class="btn btn-primary btn-sm" href="app_demo.php?page=index"><i
                                        class="fas fa-list"></i> Reuniones</a>
                                <?php if ($id > 0): ?>
                                    <a class="btn btn-info btn-sm"
                                        href="app_demo.php?page=participantes&id=<?php echo $id; ?>"><i
                                            class="fas fa-user-friends"></i> Participantes</a>
                                    <a class="btn btn-secondary btn-sm"
                                        href="app_demo.php?page=generar_qr&id=<?php echo $id; ?>"><i
                                            class="fas fa-id-card"></i> Carnets QR</a>
                                    <a class="btn btn-success btn-sm"
                                        href="app_demo.php?page=asistencia&id=<?php echo $id; ?>"><i
                                            class="fas fa-check-circle"></i> Asistencia</a>
                                    <a class="btn btn-warning btn-sm"
                                        href="app_demo.php?page=transmitir&id=<?php echo $id; ?>"><i
                                            class="fas fa-video"></i> Transmitir</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <iframe id="demoFrame" class="demo-frame"
                                src="<?php echo htmlspecialchars($iframe_src); ?>"></iframe>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <!-- /.content-wrapper -->
    </div>
    <!-- ./wrapper -->

    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="dist/js/adminlte.min.js"></script>
    <script src="plugins/sweetalert2/sweetalert2.min.js"></script>

    <script>
        // Listener para SweetAlert desde el iframe
        window.addEventListener('message', function(event) {
            // Por seguridad, podrías verificar event.origin aquí
            if (event.data && event.data.action === 'showSweetAlert') {
                const iframe = document.getElementById('demoFrame');
                Swal.fire(event.data.options).then((result) => {
                    // Opcional: devolver el resultado al iframe
                    if (iframe) {
                        iframe.contentWindow.postMessage({
                            action: 'sweetAlertResult',
                            result: result
                        }, '*');
                    }
                });
            }
        });

        (function() {
            const iframe = document.getElementById('demoFrame');
            if (!iframe) return;

            const adjustIframe = () => {
                try {
                    const doc = iframe.contentDocument || iframe.contentWindow.document;
                    if (!doc || !doc.body) return;

                    // Inyectar Estilos y Scripts
                    if (!doc.getElementById('adminlte-styles')) {
                        // Estilos
                        const adminlte_css = doc.createElement('link');
                        adminlte_css.id = 'adminlte-styles';
                        adminlte_css.rel = 'stylesheet';
                        adminlte_css.href = '../dist/css/adminlte.min.css';
                        doc.head.appendChild(adminlte_css);

                        const fontawesome_css = doc.createElement('link');
                        fontawesome_css.rel = 'stylesheet';
                        fontawesome_css.href = '../plugins/fontawesome-free/css/all.min.css';
                        doc.head.appendChild(fontawesome_css);

                        const customStyle = doc.createElement('style');
                        customStyle.textContent = `
                            body { background-color: transparent !important; }
                            #reader video {
                                width: 100% !important;
                                height: auto !important;
                                object-fit: contain;
                            }
                        `;
                        doc.head.appendChild(customStyle);

                        // Scripts (se inyectan en el body)
                        const jquery_js = doc.createElement('script');
                        jquery_js.src = '../plugins/jquery/jquery.min.js';
                        doc.body.appendChild(jquery_js);

                        jquery_js.onload = () => {
                            const bootstrap_js = doc.createElement('script');
                            bootstrap_js.src = '../plugins/bootstrap/js/bootstrap.bundle.min.js';
                            doc.body.appendChild(bootstrap_js);
                        };
                    }

                    // Ajustar altura
                    const newHeight = doc.body.scrollHeight;
                    iframe.style.height = (newHeight + 1) + 'px';

                } catch (e) {
                    console.warn('No se pudo ajustar el iframe:', e);
                }
            };

            iframe.addEventListener('load', adjustIframe);

            const observer = new MutationObserver(adjustIframe);
            iframe.addEventListener('load', () => {
                try {
                    const doc = iframe.contentDocument || iframe.contentWindow.document;
                    if (doc && doc.body) {
                        observer.observe(doc.body, {
                            childList: true,
                            subtree: true,
                            attributes: true
                        });
                    }
                } catch (e) {
                    /* ignorar */
                }
            });

            window.addEventListener('beforeunload', () => observer.disconnect());
        })();
    </script>
</body>

</html>