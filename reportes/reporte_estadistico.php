<?php require_once 'api/auth_check.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reporte | Estadístico de Pacientes</title>
    <link rel="stylesheet" href="css/Source Sans Pro.css">
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="css/custom.css">
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        <?php include 'modal_cambiar_contrasena.php'; ?>
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Reporte Estadístico</h1>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-body">
                            <?php
                            require_once 'api/conexion.php';

                            $totalPacientes = 0;
                            $totalConsultas = 0;
                            $totalEspecialidades = 0;

                            try {
                                // Total de pacientes
                                $stmt = $pdo->query('SELECT COUNT(*) AS total FROM pacientes');
                                $totalPacientes = $stmt->fetchColumn();

                                // Total de consultas
                                $stmt = $pdo->query('SELECT COUNT(*) AS total FROM consultas');
                                $totalConsultas = $stmt->fetchColumn();

                                // Total de especialidades distintas
                                $stmt = $pdo->query('SELECT COUNT(*) AS total FROM especialidades');
                                $totalEspecialidades = $stmt->fetchColumn();
                            } catch (PDOException $e) {
                                echo '<div class="alert alert-danger">Error al cargar estadísticas: ' . htmlspecialchars($e->getMessage()) . '</div>';
                            }
                            ?>
                            <div class="row">
                                <div class="col-md-4 col-sm-6 col-12">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info"><i class="far fa-user"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Pacientes</span>
                                            <span class="info-box-number"><?php echo $totalPacientes; ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6 col-12">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-success"><i
                                                class="far fa-calendar-alt"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Consultas</span>
                                            <span class="info-box-number"><?php echo $totalConsultas; ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6 col-12">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-warning"><i class="fas fa-user-md"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Especialidades Registradas</span>
                                            <span class="info-box-number"><?php echo $totalEspecialidades; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <footer class="main-footer"
            style="position:fixed;left:0;bottom:0;width:100%;z-index:1030;background:#fff;border-top:1px solid #dee2e6;">
            <strong>&copy; 2024-2025 <a href="#">Clínica SaludSonrisa</a>.</strong>
            <div class="float-right d-none d-sm-inline">Innovando la Gestión Médica</div>
        </footer>
    </div>
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="dist/js/adminlte.min.js"></script>
</body>

</html>