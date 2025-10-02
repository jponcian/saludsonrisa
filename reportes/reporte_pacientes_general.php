<?php require_once 'api/auth_check.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reporte | Listado General de Pacientes</title>
    <link rel="stylesheet" href="css/Source Sans Pro.css">
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="css/custom.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
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
                            <h1 class="m-0">Listado General de Pacientes</h1>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <input type="text" id="obuscar" class="form-control" placeholder="Filtrar pacientes...">
                            </div>
                            <table id="tablaPacientes" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Cédula</th>
                                        <th>Nombre</th>
                                        <th>Apellido</th>
                                        <th>Género</th>
                                        <th>Fecha Nacimiento</th>
                                        <th>Teléfono</th>
                                        <th>Correo</th>
                                        <th>Dirección</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    require_once 'api/conexion.php';
                                    try {
                                        $stmt = $pdo->query('SELECT id, cedula, nombres, apellidos, genero, fecha_nacimiento, telefono, email, direccion FROM pacientes ORDER BY cedula');
                                        $contador = 1;
                                        while ($row = $stmt->fetch()) {
                                            echo '<tr>';
                                            echo '<td>' . $contador++ . '</td>';
                                            echo '<td>' . htmlspecialchars($row['cedula']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['nombres']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['apellidos']) . '</td>';
                                            echo '<td>' . (isset($row['genero']) ? htmlspecialchars($row['genero']) : '') . '</td>';
                                            $fecha = $row['fecha_nacimiento'];
                                            $fecha_formateada = '';
                                            if ($fecha && $fecha !== '0000-00-00') {
                                                $fecha_formateada = date('d-m-Y', strtotime($fecha));
                                            }
                                            echo '<td>' . htmlspecialchars($fecha_formateada) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['telefono']) . '</td>';
                                            echo '<td>' . (isset($row['email']) ? htmlspecialchars($row['email']) : '') . '</td>';
                                            echo '<td>' . htmlspecialchars($row['direccion']) . '</td>';
                                            echo '</tr>';
                                        }
                                    } catch (PDOException $e) {
                                        echo '<tr><td colspan="9">Error al cargar los pacientes: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
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
    <!-- DataTables  & Plugins -->
    <script src="plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
    <script src="plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
    <script src="plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
    <script src="plugins/jszip/jszip.min.js"></script>
    <script src="plugins/pdfmake/pdfmake.min.js"></script>
    <script src="plugins/pdfmake/vfs_fonts.js"></script>
    <script src="plugins/datatables-buttons/js/buttons.html5.min.js"></script>
    <script src="plugins/datatables-buttons/js/buttons.print.min.js"></script>
    <script src="plugins/datatables-buttons/js/buttons.colVis.min.js"></script>

    <script>
        $(document).ready(function () {
            if ($("#tablaPacientes").hasClass("dataTable")) {
                $("#tablaPacientes").DataTable().destroy();
            }
            var table = $("#tablaPacientes").DataTable({
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Buscar...",
                    decimal: "",
                    emptyTable: "No hay datos disponibles",
                    info: "Mostrando _START_ al _END_ de _TOTAL_ registros",
                    infoEmpty: "Mostrando 0 al 0 de 0 entradas",
                    infoFiltered: "(filtrado desde _MAX_ total registros)",
                    infoPostFix: "",
                    thousands: ",",
                    lengthMenu: "Mostrar _MENU_ registros",
                    loadingRecords: "Cargando...",
                    processing: "",
                    search: "Buscar:",
                    zeroRecords: "No se encontraron registros",
                    paginate: {
                        first: "Primero",
                        last: "Último",
                        next: "Siguiente",
                        previous: "Anterior",
                    },
                    aria: {
                        sortAscending: ": activar el orden ascendente",
                        sortDescending: ": activar el orden descendente",
                    },
                },
                responsive: true,
                order: [],
                dom: "Brtlp",
                lengthMenu: [
                    [5, 10, 25, 50, 100, 200, 300],
                    [5, 10, 25, 50, 100, 200, 300],
                ],
                buttons: [{
                    extend: "excelHtml5",
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    titleAttr: "Exportar a Excel",
                    className: "btn btn-success",
                },
                {
                    extend: "pdfHtml5",
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    titleAttr: "Exportar a PDF",
                    className: "btn btn-danger",
                },
                {
                    extend: "print",
                    text: '<i class="fa fa-print"></i> Imprimir',
                    titleAttr: "Imprimir",
                    className: "btn btn-info",
                },
                ],
            });
            // Input de búsqueda personalizado
            $("#obuscar").on("keyup", function () {
                table.search(this.value).draw();
            });
        });
    </script>
</body>

</html>