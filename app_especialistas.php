<?php
require_once 'api/auth_check.php';
require_once 'api/conexion.php';

$paginaRuta = basename(__FILE__);
$stmtPagina = $pdo->prepare('SELECT id FROM paginas WHERE ruta = ? LIMIT 1');
$stmtPagina->execute([$paginaRuta]);
$paginaId = $stmtPagina->fetchColumn();

if (!$paginaId || !in_array((int) $paginaId, $permisos_usuario, true)) {
  header('Location: app_inicio.php');
  exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Clínica SaludSonrisa | Gestión de Especialistas</title>

  <link rel="stylesheet" href="css/Source Sans Pro.css">
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <link rel="stylesheet" href="css/custom.css">
</head>

<body class="hold-transition sidebar-mini">
  <div class="wrapper">

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

    <?php include 'sidebar.php'; ?>
    <?php include 'modal_cambiar_contrasena.php'; ?>

    <div class="content-wrapper">
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="m-0">Gestión de Especialistas</h1>
            </div>
          </div>
        </div>
      </div>
      <div class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header modal-header-gradient-blue-green">
                  <h3 class="card-title">Listado de Especialistas</h3>
                </div>
                <div class="card-body">
                  <table id="tabla-especialistas" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>N°</th>
                        <th style="display:none;">ID</th>
                        <th>Usuario</th>
                        <th>Nombre Completo</th>
                        <th>Especialidades</th>
                        <th>Acciones</th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="modal-editar-especialista">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header modal-header-gradient">
            <h4 class="modal-title">Editar Especialidades</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form id="form-editar-especialista">
              <input type="hidden" name="id" id="edit-id">
              <div class="form-group">
                <label>Especialidades</label>
                <select class="select2" multiple="multiple" data-placeholder="Seleccione las especialidades"
                  style="width: 100%;" name="especialidades[]" id="edit-especialidades">
                </select>
              </div>
            </form>
          </div>
          <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            <button type="submit" form="form-editar-especialista" class="btn btn-primary">Guardar Cambios</button>
          </div>
        </div>
      </div>
    </div>

    <footer class="main-footer">
      <div class="float-right d-none d-sm-inline">Innovando la Gestión Médica</div><strong>Copyright &copy; 2024-2025 <a
          href="#">Clínica SaludSonrisa</a>.</strong>
    </footer>
  </div>

  <script src="plugins/jquery/jquery.min.js"></script>
  <script src="js/modal_cambiar_contrasena.js"></script>
  <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="plugins/datatables/jquery.dataTables.min.js"></script>
  <script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
  <script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
  <script src="plugins/select2/js/select2.full.min.js"></script>
  <script src="dist/js/adminlte.min.js"></script>
  <script src="plugins/sweetalert2/sweetalert2.all.min.js"></script>

  <script>
    $(function() {
      // Initialize Select2 Elements
      $('.select2').select2()

      var tablaEspecialistas = $("#tabla-especialistas").DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "ajax": {
          "url": "api/especialistas.php",
          "dataSrc": "data"
        },
        "columns": [{
            // Contador visual
            "data": null,
            "render": function(data, type, row, meta) {
              return meta.row + 1;
            }
          },
          {
            "data": "id",
            "visible": false // Oculta la columna ID pero la mantiene para acciones
          },
          {
            "data": "username"
          },
          {
            "data": "nombre_completo"
          },
          {
            "data": "especialidades",
            "render": function(data, type, row) {
              if (data && data.length > 0) {
                return data.map(function(especialidad) {
                  return especialidad.nombre;
                }).join(', ');
              } else {
                return 'Sin especialidades';
              }
            }
          },
          {
            "data": null,
            "render": function(data, type, row) {
              return `<button class="btn btn-info btn-sm btn-editar" data-id="${row.id}" data-especialidades='${JSON.stringify(row.especialidades_ids)}'><i class="fas fa-pencil-alt"></i> Editar</button>`;
            }
          }
        ],
        "language": {
          "url": "plugins/datatables/i18n/Spanish.json"
        }
      });

      $('#tabla-especialistas tbody').on('click', '.btn-editar', function() {
        var id = $(this).data('id');
        var especialidades_actuales = $(this).data('especialidades');

        $('#edit-id').val(id);

        var especialidadesSelect = $('#edit-especialidades');
        especialidadesSelect.empty();

        $.ajax({
          url: 'api/get_datos_consulta.php',
          dataType: 'json',
          success: function(response) {
            if (response.status === 'success') {
              response.data.especialidades.forEach(function(espec) {
                var option = new Option(espec.nombre, espec.id, false, false);
                especialidadesSelect.append(option);
              });
              especialidadesSelect.val(especialidades_actuales).trigger('change');
              $('#modal-editar-especialista').modal('show');
            }
          }
        });
      });

      $('#form-editar-especialista').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
          url: 'api/editar_especialista.php',
          type: 'POST',
          data: $(this).serialize(),
          dataType: 'json',
          success: function(response) {
            if (response.status === 'success') {
              $('#modal-editar-especialista').modal('hide');
              tablaEspecialistas.ajax.reload();
              Swal.fire('¡Éxito!', response.message, 'success');
            } else {
              Swal.fire('Error', response.message, 'error');
            }
          }
        });
      });

      // Botón eliminar removido

    });
  </script>
</body>

</html>