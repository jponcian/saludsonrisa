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
// Obtener el id de especialista si el usuario es especialista
$especialista_id = null;
if ($rol === 'especialista') {
  $stmt = $pdo->prepare('SELECT id FROM especialistas WHERE usuario_id = ? LIMIT 1');
  $stmt->execute([$usuario_id]);
  $row = $stmt->fetch();
  if ($row) {
    $especialista_id = $row['id'];
  }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Clínica SaludSonrisa | Pacientes</title>

  <link rel="stylesheet" href="css/Source Sans Pro.css">
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="plugins/ekko-lightbox/ekko-lightbox.css">
  <link rel="stylesheet" href="plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <link rel="stylesheet" href="css/custom.css">
  <style>
    .tooltip-inner {
      background: linear-gradient(90deg, #2196f3 0%, #43e97b 100%);
      color: #fff;
    }

    .tooltip .tooltip-arrow {
      border-top-color: #2196f3;
    }

    .btn-circle {
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
      border: none;
    }

    .btn-circle:hover {
      transform: scale(1.05);
    }

    .btn.btn-info:hover {
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2), 0 0 20px rgba(23, 162, 184, 0.5);
      transform: scale(1.05);
    }

    .btn.btn-warning:hover {
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2), 0 0 20px rgba(255, 193, 7, 0.5);
      transform: scale(1.05);
    }

    .btn.btn-danger:hover {
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2), 0 0 20px rgba(220, 53, 69, 0.5);
      transform: scale(1.05);
    }
  </style>
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
              <h1 class="m-0">Gestión de Pacientes</h1>
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
                  <h3 class="card-title">Listado de Pacientes Registrados</h3>
                  <div class="card-tools"><button type="button" class="btn btn-primary" data-toggle="modal"
                      data-target="#modal-registrar-paciente"><i class="fas fa-plus"></i> Registrar Nuevo
                      Paciente</button></div>
                </div>
                <div class="card-body">
                  <table id="tabla-pacientes" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Cédula</th>
                        <th>Foto</th>
                        <th>Nombres</th>
                        <th>Apellidos</th>
                        <th>Fec. Nacimiento</th>
                        <th>Género</th>
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

    <!-- MODAL REGISTRAR PACIENTE -->
    <div class="modal fade" id="modal-registrar-paciente">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header modal-header-gradient-blue-green">
            <h4 class="modal-title">Registrar Nuevo Paciente</h4><button type="button" class="close"
              data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          </div>
          <div class="modal-body">
            <form id="form-registrar-paciente" method="post" enctype="multipart/form-data" autocomplete="off">
              <div class="alert alert-danger" id="error-message-paciente" style="display:none;"></div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group"><label>Cédula</label><input type="text" class="form-control" name="cedula"
                      required></div>
                </div>
                <div class="col-md-4">
                  <div class="form-group"><label>Nombres</label><input type="text" class="form-control" name="nombres"
                      required></div>
                </div>
                <div class="col-md-4">
                  <div class="form-group"><label>Apellidos</label><input type="text" class="form-control"
                      name="apellidos" required></div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group"><label>Género</label><select class="form-control" name="genero" required>
                      <option value="">Seleccione</option>
                      <option value="Masculino">Masculino</option>
                      <option value="Femenino">Femenino</option>
                    </select></div>
                </div>
                <div class="col-md-3">
                  <div class="form-group"><label>Fecha de Nacimiento</label><input type="date" class="form-control"
                      name="fecha_nacimiento" required></div>
                </div>
                <div class="col-md-3">
                  <div class="form-group"><label>Teléfono</label><input type="tel" class="form-control" name="telefono">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group"><label>Email</label><input type="email" class="form-control" name="email">
                  </div>
                </div>
              </div>
              <div class="form-group"><label>Dirección</label><textarea class="form-control" name="direccion"
                  rows="2"></textarea></div>
              <div class="form-group"><label>Fotos del Paciente</label>
                <div class="input-group">
                  <div class="custom-file">
                    <input type="file" class="custom-file-input" name="foto" accept="image/*">
                    <label class="custom-file-label">Elegir archivo</label>
                  </div>
                  <div class="input-group-append">
                    <button type="button" class="btn btn-primary" id="btn-usar-camara">
                      <i class="fas fa-camera"></i> Usar cámara
                    </button>
                  </div>
                </div>
                <!-- Vista previa de la foto capturada -->
                <div id="preview-camara" style="display:none; margin-top:10px; text-align:center;">
                  <video id="video-camara" width="220" height="180" autoplay style="border-radius:8px;"></video>
                  <br>
                  <button type="button" class="btn btn-success btn-sm mt-2" id="btn-capturar-foto">Capturar</button>
                  <canvas id="canvas-camara" width="220" height="180" style="display:none;"></canvas>
                  <img id="img-capturada" src="" alt="Foto capturada"
                    style="display:none; margin-top:10px; max-width:220px; border-radius:8px;">
                  <button type="button" class="btn btn-danger btn-sm mt-2" id="btn-cerrar-camara">Cerrar cámara</button>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer justify-content-between"><button type="button" class="btn btn-default"
              data-dismiss="modal">Cancelar</button><button type="submit" form="form-registrar-paciente"
              class="btn btn-primary">Guardar Paciente</button></div>
        </div>
      </div>
    </div>

    <!-- MODAL CREAR CONSULTA -->
    <div class="modal fade" id="modal-crear-consulta">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header modal-header-gradient-blue-green">
            <h4 class="modal-title">Registrar Nueva Consulta</h4><button type="button" class="close"
              data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          </div>
          <div class="modal-body">
            <form id="form-crear-consulta" enctype="multipart/form-data">
              <input type="hidden" id="paciente_id_consulta" name="paciente_id">
              <div class="alert alert-danger" id="error-message-consulta" style="display:none;"></div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group"><label>Paciente</label>
                    <p class="form-control-static" id="nombre-paciente-consulta"></p>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group"><label>Especialista que atiende</label><select class="form-control"
                      name="especialista_id" required></select></div>
                </div>
                <div class="col-md-6">
                  <div class="form-group"><label>Tipo de Atención (Especialidad)</label><select
                      class="form-control select2" name="especialidad_id[]" id="select-especialidad" multiple="multiple"
                      data-placeholder="Seleccione las especialidades" style="width: 100%;" required></select></div>
                </div>
              </div>
              <div class="form-group"><label>Diagnóstico</label><textarea class="form-control" name="diagnostico"
                  rows="3" required></textarea></div>
              <div class="form-group"><label>Tratamiento</label><textarea class="form-control" name="tratamiento"
                  rows="3"></textarea></div>
              <div class="form-group"><label>Observaciones</label><textarea class="form-control" name="observaciones"
                  rows="2"></textarea></div>
              <div class="form-group"><label>Fotos de la Consulta</label>
                <div class="input-group">
                  <div class="custom-file"><input type="file" class="custom-file-input" name="fotos_consulta[]"
                      accept="image/*" multiple><label class="custom-file-label">Elegir archivos</label></div>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer justify-content-between"><button type="button" class="btn btn-default"
              data-dismiss="modal">Cancelar</button><button type="submit" form="form-crear-consulta"
              class="btn btn-success">Guardar Consulta</button></div>
        </div>
      </div>
    </div>


    <!-- MODAL VER PACIENTE -->
    <div class="modal fade" id="modal-ver-paciente">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header modal-header-gradient-blue-green">
            <h4 class="modal-title">Historial del Paciente</h4><button type="button" class="close" data-dismiss="modal"
              aria-label="Close"><span aria-hidden="true">&times;</span></button>
          </div>
          <div class="modal-body" id="historial-paciente-body"></div>
          <div class="modal-footer"> <button type="button" class="btn btn-success" id="btn-generar-carnet"
              style="display:none;">
              <i class="fas fa-id-card"></i> Generar Carnet
            </button>
            <button type="button" class="btn btn-info" data-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL CAMBIAR CONTRASEÑA -->

    <footer class="main-footer"
      style="position:fixed;left:0;bottom:0;width:100%;z-index:1030;background:#fff;border-top:1px solid #dee2e6;">
      <strong>&copy; 2024-2025 <a href="#">Clínica SaludSonrisa</a>.</strong>
      <div class="float-right d-none d-sm-inline">Innovando la Gestión Médica</div>
    </footer>

    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
    <script src="plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="plugins/ekko-lightbox/ekko-lightbox.min.js"></script>
    <script src="plugins/select2/js/select2.full.min.js"></script>
    <script src="dist/js/adminlte.min.js"></script>
    <script src="plugins/sweetalert2/sweetalert2.all.min.js"></script>

    <script>
      var tablaPacientes; // Declarar tablaPacientes en un ámbito superior
      // Variables globales de sesión para JS
      var usuarioRol = '<?php echo $rol; ?>';
      var usuarioId = '<?php echo $usuario_id; ?>';
      var usuarioNombre = '<?php echo addslashes($nombre_completo); ?>';
      var especialistaId = <?php echo ($especialista_id !== null) ? intval($especialista_id) : 'null'; ?>;

      $(function () {
        // --- Cámara nativa para foto paciente ---
        let stream = null;
        $('#btn-usar-camara').on('click', function () {
          $('#preview-camara').show();
          $('#img-capturada').hide();
          $('#canvas-camara').hide();
          navigator.mediaDevices.getUserMedia({
            video: true
          })
            .then(function (s) {
              stream = s;
              $('#video-camara')[0].srcObject = stream;
              $('#video-camara').show();
            })
            .catch(function (err) {
              alert('No se pudo acceder a la cámara: ' + err);
            });
        });
        $('#btn-capturar-foto').on('click', function () {
          // Oculta los botones de capturar y cerrar cámara
          $('#btn-capturar-foto').hide();
          $('#btn-cerrar-camara').hide();
          var video = $('#video-camara')[0];
          var canvas = $('#canvas-camara')[0];
          canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
          var dataUrl = canvas.toDataURL('image/png');
          $('#img-capturada').attr('src', dataUrl).show();
          $('#canvas-camara').hide();
          $('#video-camara').hide(); // Oculta el video de la cámara
          // Detiene el stream de la cámara
          if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
          }
          // Guardar la imagen en un input hidden para enviar al backend si lo necesitas
          if ($('#foto_capturada').length === 0) {
            $('<input>').attr({
              type: 'hidden',
              id: 'foto_capturada',
              name: 'foto_capturada'
            }).val(dataUrl).appendTo($(this).closest('form'));
          } else {
            $('#foto_capturada').val(dataUrl);
          }
        });
        $('#btn-cerrar-camara').on('click', function () {
          $('#preview-camara').hide();
          if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
          }
        });
        bsCustomFileInput.init();
        // --- Cámara nativa para foto paciente (modal editar) ---
        let streamEditar = null;
        $(document).on('click', '#btn-usar-camara-editar', function () {
          $('#preview-camara-editar').show();
          $('#img-capturada-editar').hide();
          $('#canvas-camara-editar').hide();
          $('#btn-capturar-foto-editar').show();
          $('#btn-cerrar-camara-editar').show();
          navigator.mediaDevices.getUserMedia({
            video: true
          })
            .then(function (s) {
              streamEditar = s;
              $('#video-camara-editar')[0].srcObject = streamEditar;
              $('#video-camara-editar').show();
            })
            .catch(function (err) {
              alert('No se pudo acceder a la cámara: ' + err);
            });
        });
        $(document).on('click', '#btn-capturar-foto-editar', function () {
          $('#btn-capturar-foto-editar').hide();
          $('#btn-cerrar-camara-editar').hide();
          var video = $('#video-camara-editar')[0];
          var canvas = $('#canvas-camara-editar')[0];
          canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
          var dataUrl = canvas.toDataURL('image/png');
          $('#img-capturada-editar').attr('src', dataUrl).show();
          $('#canvas-camara-editar').hide();
          $('#video-camara-editar').hide();
          if (streamEditar) {
            streamEditar.getTracks().forEach(track => track.stop());
            streamEditar = null;
          }
          // Guardar la imagen en un input hidden para enviar al backend si lo necesitas
          if ($('#foto_capturada_editar').length === 0) {
            $('<input>').attr({
              type: 'hidden',
              id: 'foto_capturada_editar',
              name: 'foto_capturada_editar'
            }).val(dataUrl).appendTo($(this).closest('form'));
          } else {
            $('#foto_capturada_editar').val(dataUrl);
          }
        });
        $(document).on('click', '#btn-cerrar-camara-editar', function () {
          $('#preview-camara-editar').hide();
          if (streamEditar) {
            streamEditar.getTracks().forEach(track => track.stop());
            streamEditar = null;
          }
        });
        // Inicializar Select2 para el select de especialidad
        $('#select-especialidad').select2({
          theme: 'bootstrap4',
          width: 'resolve'
        });

        // Cuando se abre el modal de consulta
        $('#modal-crear-consulta').on('show.bs.modal', function () {
          var $selectEspecialista = $(this).find('select[name="especialista_id"]');
          var $selectEspecialidad = $(this).find('#select-especialidad');
          $selectEspecialista.empty();
          $selectEspecialidad.empty();
          if (usuarioRol === 'especialista') {
            // Si es especialista, solo su opción y bloqueado
            $selectEspecialista.append('<option value="' + especialistaId + '">' + usuarioNombre + '</option>');
            $selectEspecialista.val(especialistaId).prop('disabled', true); // Deshabilitar el select
            // Añadir un campo hidden para enviar el especialista_id
            $('#form-crear-consulta').append('<input type="hidden" name="especialista_id" value="' + especialistaId + '">');
            // Cargar especialidades del especialista automáticamente
            $.get('api/especialistas.php?id=' + especialistaId, function (response) {
              if (response && response.status === 'success' && response.data) {
                var especialista = response.data;
                if (especialista.especialidades && Array.isArray(especialista.especialidades)) {
                  $selectEspecialidad.empty();
                  especialista.especialidades.forEach(function (es) {
                    var newOption = new Option(es.nombre, es.id, false, false);
                    $selectEspecialidad.append(newOption);
                  });
                  $selectEspecialidad.trigger('change.select2');
                }
              }
            }, 'json');
          } else {
            // Si no, cargar todos los especialistas y habilitar
            $selectEspecialista.prop('disabled', false);
            $.get('api/especialistas.php', function (data) {
              if (data && data.data) {
                data.data.forEach(function (e) {
                  $selectEspecialista.append('<option value="' + e.id + '">' + e.nombre_completo + '</option>');
                });
                $selectEspecialista.trigger('change'); // Disparar el evento change para cargar especialidades
              }
            }, 'json');
            // Limpiar especialidades hasta que se seleccione un especialista
            $selectEspecialidad.empty();
          }
        });
        tablaPacientes = $("#tabla-pacientes").DataTable({
          responsive: true,
          lengthChange: false,
          autoWidth: false,
          processing: true,
          ajax: {
            url: "api/pacientes.php",
            dataSrc: "data"
          },
          columns: [{
            "data": "id",
            render: function (data, type, row, meta) {
              return meta.settings._iDisplayStart + meta.row + 1;
            }
          },
          {
            "data": "cedula"
          },
          {
            "data": "foto_url",
            "className": "text-center", // Añadido para centrar el contenido
            render: function (data) {
              return '<img src="' + (data ? 'uploads/' + data : 'logo.png') + '?' + new Date().getTime() + '" class="img-circle elevation-2" width="40">';
            }
          },
          {
            "data": "nombres"
          },
          {
            "data": "apellidos"
          },
          {
            "data": "fecha_nacimiento",
            render: function (data) {
              if (!data) return '';
              // Si ya viene en formato dd-mm-yyyy
              if (/\\d{2}-\\d{2}-\\d{4}/.test(data)) return data;
              // Si viene yyyy-mm-dd
              if (/\\d{4}-\\d{2}-\\d{2}/.test(data)) {
                let [y, m, d] = data.split('-');
                return d + '-' + m + '-' + y;
              }
              // Si es fecha JS
              var d = new Date(data);
              if (!isNaN(d)) {
                let day = ('0' + d.getDate()).slice(-2);
                let month = ('0' + (d.getMonth() + 1)).slice(-2);
                let year = d.getFullYear();
                return day + '-' + month + '-' + year;
              }
              return data;
            }
          },
          {
            "data": "genero"
          },

          {
            "data": null,
            render: function (data, type, row) {
              var patientData = JSON.stringify(row);
              var historiaBtnClass = (row.historia_estado === 'completado') ? 'btn btn-success btn-sm btn-historia-clinica' : 'btn btn-secondary btn-sm btn-historia-clinica';
              var html = "<button class='btn btn-info btn-sm btn-ver-paciente' data-id='" + row.id + "' data-toggle='tooltip' data-placement='top' title='Ver paciente'><i class='fas fa-eye'></i></button> " +
                "<a class='" + historiaBtnClass + "' href='app_historia_clinica.php?id=" + row.id + "' data-toggle='tooltip' data-placement='top' title='Historia clínica'><i class='fas fa-notes-medical'></i></a> " +
                "<button class='btn btn-warning btn-sm btn-editar-paciente' data-paciente='" + patientData + "' data-toggle='tooltip' data-placement='top' title='Editar paciente'><i class='fas fa-edit'></i></button>";
              if (row.afiliacion_pagada) {
                html += " <button class='btn btn-success btn-sm btn-ver-carnet' data-id='" + row.id + "' data-toggle='tooltip' data-placement='top' title='Ver carnet'><i class='fas fa-id-card'></i></button>";
              }
              if (usuarioRol !== 'Estandar') {
                html += " <button class='btn btn-danger btn-sm btn-eliminar-paciente' data-id='" + row.id + "' data-toggle='tooltip' data-placement='top' title='Eliminar paciente'><i class='fas fa-trash'></i></button>";
              }
              return html;
            }
          }
          ],
          language: {
            url: "plugins/datatables/i18n/Spanish.json"
          }
        });

        // Inicializar tooltips después de dibujar la tabla
        tablaPacientes.on('draw', function () {
          $('[data-toggle="tooltip"]').tooltip();
        });
        // Inicializar tooltips para el primer draw
        $('[data-toggle="tooltip"]').tooltip();

        // Envío AJAX del formulario Registrar Paciente (evita GET con parámetros enormes en la URL)
        $('#form-registrar-paciente').on('submit', function (e) {
          e.preventDefault();
          var form = this;
          var formData = new FormData(form);
          $('#error-message-paciente').hide().text('');

          // Si existe foto capturada (base64) ya está en input hidden foto_capturada; no hacer nada extra.
          $.ajax({
            url: 'api/registrar_paciente.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function (response) {
              if (response && response.status === 'success') {
                // Cerrar cámara si quedara abierta
                if (typeof stream !== 'undefined' && stream) {
                  try {
                    stream.getTracks().forEach(t => t.stop());
                  } catch (err) { }
                  stream = null;
                }
                $('#modal-registrar-paciente').modal('hide');
                form.reset();
                $('#img-capturada').hide().attr('src', '');
                $('#preview-camara').hide();
                if (typeof tablaPacientes !== 'undefined' && tablaPacientes.ajax) {
                  tablaPacientes.ajax.reload(null, false);
                }
                var nuevoPacienteId = response && response.id ? parseInt(response.id, 10) : null;
                if (window.Swal) {
                  Swal.fire({
                    icon: 'success',
                    title: '¡Paciente Registrado!',
                    text: response.message || 'Registro exitoso.',
                    showCancelButton: !!nuevoPacienteId,
                    confirmButtonText: nuevoPacienteId ? 'Completar historia clínica' : 'OK',
                    cancelButtonText: 'Cerrar',
                    customClass: {
                      confirmButton: 'btn btn-success',
                      cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                  }).then(function (result) {
                    if (nuevoPacienteId && result.isConfirmed) {
                      window.location.href = 'app_historia_clinica.php?id=' + nuevoPacienteId;
                    }
                  });
                } else {
                  alert(response.message || 'Paciente registrado.');
                }
              } else {
                var msg = (response && response.message) ? response.message : 'Error al registrar el paciente';
                $('#error-message-paciente').text(msg).show();
                if (window.Swal) {
                  Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: msg,
                    confirmButtonText: 'OK',
                    customClass: {
                      confirmButton: 'btn btn-danger'
                    },
                    buttonsStyling: false
                  });
                }
              }
            },
            error: function (xhr, status, error) {
              let msg = 'Error de conexión al registrar el paciente.';
              if (xhr && xhr.responseText) msg += ' Respuesta: ' + xhr.responseText;
              $('#error-message-paciente').text(msg).show();
              if (window.Swal) {
                Swal.fire({
                  icon: 'error',
                  title: 'Error de Conexión',
                  text: msg,
                  confirmButtonText: 'OK',
                  customClass: {
                    confirmButton: 'btn btn-danger'
                  },
                  buttonsStyling: false
                });
              }
              console.error('AJAX registrar_paciente error:', status, error, xhr);
            }
          });
        });

        // MODAL EDITAR PACIENTE
        $(document.body).append(
          `<div class=\"modal fade\" id=\"modal-editar-paciente\">
      <div class=\"modal-dialog modal-lg\">
        <div class=\"modal-content\">
          <div class=\"modal-header modal-header-gradient-blue-green\">
            <h4 class=\"modal-title\">Editar Paciente</h4><button type=\"button\" class=\"close\" data-dismiss=\"modal" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>
          </div>
          <div class=\"modal-body\">
            <form id=\"form-editar-paciente\">
              <input type=\"hidden\" name=\"id\" id=\"edit-id-paciente">
              <div class=\"row\">
                <div class=\"col-md-4\">
                  <div class=\"form-group\"><label>Cédula</label><input type=\"text\" class=\"form-control\" name=\"cedula\" id=\"edit-cedula\" required></div>
                </div>
                <div class=\"col-md-4\">
                  <div class=\"form-group\"><label>Nombres</label><input type=\"text\" class=\"form-control\" name=\"nombres\" id=\"edit-nombres\" required></div>
                </div>
                <div class=\"col-md-4\">
                  <div class=\"form-group\"><label>Apellidos</label><input type=\"text\" class=\"form-control\" name=\"apellidos\" id=\"edit-apellidos\" required></div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group"><label>Género</label><select class="form-control" name="genero" id="edit-genero" required><option value="">Seleccione</option><option value="Masculino">Masculino</option><option value="Femenino">Femenino</option></select></div>
                </div>
                <div class="col-md-3">
                  <div class="form-group"><label>Fecha de Nacimiento</label><input type="date" class="form-control" name="fecha_nacimiento" id="edit-fecha-nacimiento" required></div>
                </div>
                <div class="col-md-3">
                  <div class="form-group"><label>Teléfono</label><input type="tel" class="form-control" name="telefono" id="edit-telefono"></div>
                </div>
                <div class="col-md-3">
                  <div class="form-group"><label>Email</label><input type="email" class="form-control" name="email" id="edit-email"></div>
                </div>
              </div>
              <div class=\"form-group\"><label>Dirección</label><textarea class=\"form-control\" name=\"direccion\" id=\"edit-direccion\" rows=\"2\"></textarea></div>
              <div class="form-group"><label>Foto del Paciente</label>
                <div class="input-group">
                  <div class="custom-file">
                    <input type="file" class="custom-file-input" name="edit-foto" accept="image/*">
                    <label class="custom-file-label">Elegir archivo</label>
                  </div>
                  <div class="input-group-append">
                    <button type="button" class="btn btn-primary" id="btn-usar-camara-editar">
                      <i class="fas fa-camera"></i> Usar cámara
                    </button>
                  </div>
                </div>
                <!-- Vista previa de la foto capturada -->
                <div id="preview-camara-editar" style="display:none; margin-top:10px; text-align:center;">
                  <video id="video-camara-editar" width="220" height="180" autoplay style="border-radius:8px;"></video>
                  <br>
                  <button type="button" class="btn btn-success btn-sm mt-2" id="btn-capturar-foto-editar">Capturar</button>
                  <canvas id="canvas-camara-editar" width="220" height="180" style="display:none;"></canvas>
                  <img id="img-capturada-editar" src="" alt="Foto capturada" style="display:none; margin-top:10px; max-width:220px; border-radius:8px;">
                  <button type="button" class="btn btn-danger btn-sm mt-2" id="btn-cerrar-camara-editar">Cerrar cámara</button>
                </div>
              </div>
            </form>
          </div>
          <div class=\"modal-footer justify-content-between\"><button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">Cancelar</button><button type=\"submit\" form=\"form-editar-paciente\" class=\"btn btn-primary\">Guardar Cambios</button></div>
        </div>
      </div>
    </div>
    `);

        // Editar paciente
        $('#tabla-pacientes tbody').on('click', '.btn-editar-paciente', function () {
          // Limpiar foto capturada y vista previa de cámara
          $('#foto_capturada_editar').remove();
          $('#img-capturada-editar').attr('src', '').hide();
          $('#preview-camara-editar').hide();
          $('#video-camara-editar').hide();
          $('#canvas-camara-editar').hide();
          $('#btn-capturar-foto-editar').show();
          $('#btn-cerrar-camara-editar').show();
          var data = $(this).data('paciente');
          $('#edit-id-paciente').val(data.id);
          $('#edit-cedula').val(data.cedula);
          $('#edit-nombres').val(data.nombres);
          $('#edit-apellidos').val(data.apellidos);
          // Formatear fecha a yyyy-mm-dd para el input type=date
          let fecha = data.fecha_nacimiento;
          if (fecha) {
            // Si viene en formato dd-mm-yyyy
            if (/^\d{2}-\d{2}-\d{4}$/.test(fecha)) {
              let [d, m, y] = fecha.split('-');
              fecha = `${y}-${m}-${d}`;
            } else if (/^\d{4}-\d{2}-\d{2}$/.test(fecha)) {
              // ya está en formato correcto
            } else {
              // intentar parsear fecha JS
              let d = new Date(fecha);
              if (!isNaN(d)) {
                let month = ('0' + (d.getMonth() + 1)).slice(-2);
                let day = ('0' + d.getDate()).slice(-2);
                let year = d.getFullYear();
                fecha = `${year}-${month}-${day}`;
              }
            }
          }
          $('#edit-fecha-nacimiento').val(fecha);
          $('#edit-telefono').val(data.telefono);
          $('#edit-email').val(data.email);
          $('#edit-direccion').val(data.direccion);
          $('#edit-genero').val(data.genero); // Set the gender value
          $('#modal-editar-paciente').modal('show');
        });
        // ...existing code...
        // Abrir ambas caras del carnet en nueva pestaña al hacer clic en el botón
        $('#btn-generar-carnet').on('click', function () {
          var pacienteId = $('#modal-ver-paciente').data('paciente-id');
          if (pacienteId) {
            var encryptedId = btoa(pacienteId + '|saludsonrisa2025');
            window.open('formatos/ver_carnet.php?id=' + encodeURIComponent(encryptedId), '_blank');
          }
        });

        $('#tabla-pacientes tbody').on('click', '.btn-crear-consulta', function () {
          var data = $(this).data('paciente');
          $('#form-crear-consulta')[0].reset();
          $('#error-message-consulta').hide();
          $('#paciente_id_consulta').val(data.id);
          $('#nombre-paciente-consulta').text(data.nombres + ' ' + data.apellidos);
          $.ajax({
            url: 'api/get_datos_consulta.php',
            dataType: 'json',
            success: function (response) {
              if (response.status === 'success') {
                var espSelect = $('select[name="especialista_id"]');
                espSelect.empty().append('<option selected disabled value="">Seleccione...</option>');
                // Guardar especialistas y especialidades para uso posterior
                var especialistasData = response.data.especialistas;
                var especialidadesData = response.data.especialidades;
                espSelect.data('especialistas', especialistasData);
                espSelect.data('especialidades', especialidadesData);
                especialistasData.forEach(function (esp) {
                  espSelect.append($('<option>', {
                    value: esp.id,
                    text: esp.nombre_completo,
                    'data-especialidades': JSON.stringify(esp.especialidades_ids || [])
                  }));
                });
                var especSelect = $('#select-especialidad');
                especSelect.empty();
                especSelect.append('<option disabled value="">Seleccione un especialista</option>');
                // Al cambiar el especialista, mostrar solo sus especialidades (multi-select)
                espSelect.off('change').on('change', function () {
                  var selectedId = $(this).val();
                  var especialista = especialistasData.find(function (e) {
                    return e.id == selectedId;
                  });
                  especSelect.empty();
                  if (especialista && especialista.especialidades_ids && especialista.especialidades_ids.length > 0) {
                    especialidadesData.forEach(function (espec) {
                      if (especialista.especialidades_ids.includes(parseInt(espec.id))) {
                        especSelect.append($('<option>', {
                          value: espec.id,
                          text: espec.nombre
                        }));
                      }
                    });
                  } else {
                    especSelect.append('<option disabled value="">Sin especialidades</option>');
                  }
                  especSelect.trigger('change'); // Actualizar select2
                });
                $('#modal-crear-consulta').modal('show');
              } else {
                alert('Error al cargar datos.');
              }
            }
          });
        });

        $('#form-crear-consulta').on('submit', function (e) {
          e.preventDefault();
          var formData = new FormData(this);
          $('#error-message-consulta').hide();
          $.ajax({
            url: 'api/registrar_consulta.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function (response) {
              if (response.status === 'success') {
                $('#modal-crear-consulta').modal('hide');
                Swal.fire({
                  icon: 'success',
                  title: '¡Consulta guardada!',
                  text: response.message,
                  confirmButtonText: 'OK',
                  customClass: {
                    confirmButton: 'btn btn-success'
                  },
                  buttonsStyling: false
                });
                // Si la API devolvió el id de la consulta, abrir el reporte en nueva pestaña
                if (response.consulta_id) {
                  // Abrir en nueva pestaña para permitir al usuario imprimir/guardar PDF
                  window.open('formatos/recipe.php?id=' + encodeURIComponent(response.consulta_id), '_blank');
                }
              } else {
                $('#error-message-consulta').text(response.message).show();
              }
            },
            error: function () {
              $('#error-message-consulta').text('Error de conexión.').show();
            }
          });
        });

        $('#tabla-pacientes tbody').on('click', '.btn-ver-paciente', function () {
          var pacienteId = $(this).data('id');
          // Guardar el id del paciente en el modal para referencia posterior
          $('#modal-ver-paciente').data('paciente-id', pacienteId);
          cargarHistorialPaciente(pacienteId);
          $('#modal-ver-paciente').modal('show');
        });

        // ...existing code...

      }); // Fin document ready

      // Función global para recargar el historial del paciente en el modal
      function cargarHistorialPaciente(pacienteId) {
        var modalBody = $('#historial-paciente-body');
        modalBody.html('<div class="text-center"><i class="fas fa-2x fa-sync-alt fa-spin"></i><p>Cargando...</p></div>');
        // Verificar si el paciente tiene pago de afiliación suficiente
        $.get('api/facturacion_listar_pagos.php?id_paciente=' + pacienteId, function (respPagos) {
          var puedeGenerarCarnet = false;
          if (respPagos.status === 'ok' && Array.isArray(respPagos.data)) {
            var pagosAfiliacion = respPagos.data.filter(function (p) {
              return p.tipo === 'inscripcion' || p.tipo === 'inscripcion_diferencia';
            });
            var montoTotal = pagosAfiliacion.reduce(function (sum, p) {
              return sum + parseFloat(p.monto);
            }, 0);
            // Aquí puedes definir el monto mínimo requerido
            puedeGenerarCarnet = montoTotal >= 1; // Cambia 1 por el monto mínimo real
          }
          $('#btn-generar-carnet').toggle(puedeGenerarCarnet);
        }, 'json');
        $.ajax({
          url: 'api/get_paciente_historial.php?id=' + encodeURIComponent(pacienteId),
          dataType: 'json',
          success: function (response) {
            if (response.status === 'success') {
              var data = response.data;
              var paciente = data.paciente;
              var consultas = data.consultas;
              var content = '';
              let fechaNac = paciente.fecha_nacimiento;
              if (fechaNac && /\d{4}-\d{2}-\d{2}/.test(fechaNac)) {
                let [y, m, d] = fechaNac.split('-');
                fechaNac = d + '-' + m + '-' + y;
              }
              content += `<div class="row"><div class="col-md-3 text-center"><img src="${paciente.foto_url ? 'uploads/' + paciente.foto_url : 'logo.png'}" class="img-fluid rounded-circle mb-2" style="width: 90px;"><h5 style="font-size:1.1rem; margin-top:0.5rem;">${paciente.nombres} ${paciente.apellidos}</h5></div><div class="col-md-9"><dl class="row"><dt class="col-sm-4">Cédula</dt><dd class="col-sm-8">${paciente.cedula || 'N/A'}</dd><dt class="col-sm-4">Fec. Nacimiento</dt><dd class="col-sm-8">${fechaNac}</dd><dt class="col-sm-4">Género</dt><dd class="col-sm-8">${paciente.genero || 'N/A'}</dd><dt class="col-sm-4">Teléfono</dt><dd class="col-sm-8">${paciente.telefono || 'N/A'}</dd><dt class="col-sm-4">Dirección</dt><dd class="col-sm-8">${paciente.direccion || 'N/A'}</dd><dt class="col-sm-4">Registrado</dt><dd class="col-sm-8">${paciente.fecha_registro ? new Date(paciente.fecha_registro).toLocaleDateString() : ''}</dd></dl></div></div><hr>`;
              content += '<h5>Historial de Consultas</h5>';
              if (consultas.length > 0) {
                content += '<div class="accordion" id="accordionConsultas">';
                consultas.forEach(function (c, index) {
                  // Cabecera con botón PDF
                  content += `<div class="card"><div class="card-header d-flex justify-content-between align-items-center" id="heading${c.id}"><div class="flex-fill"><h2 class="mb-0"><button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse${c.id}">${new Date(c.fecha_consulta).toLocaleString()} - ${c.especialidades_nombres || 'Sin especialidad'} - Dr. ${c.especialista_nombre_completo}</button></h2></div><div class="ml-2"><a class="btn btn-secondary btn-sm" href="formatos/recipe.php?id=${c.id}" target="_blank"><i class="fas fa-file-pdf"></i> PDF</a></div></div><div id="collapse${c.id}" class="collapse" data-parent="#accordionConsultas"><div class="card-body">`;
                  content += `<!-- <p><strong>Especialidades:</strong> ${c.especialidades_nombres || 'Sin especialidad'}</p> -->`;
                  content += `<p><strong>Diagnóstico:</strong> ${c.diagnostico}</p><p><strong>Tratamiento:</strong> ${c.tratamiento}</p><p><strong>Observaciones:</strong> ${c.observaciones || 'Ninguna'}</p>`;
                  if (c.fotos && c.fotos.length > 0) {
                    content += '<p><strong>Fotos:</strong></p><div class="row">';
                    c.fotos.forEach(function (foto) {
                      content += `<div class="col-sm-2"><a href="uploads/${foto}" data-toggle="lightbox" data-gallery="gallery-${c.id}"><img src="uploads/${foto}" class="img-fluid mb-2"/></a></div>`;
                    });
                    content += '</div>';
                  }
                  if (usuarioRol !== 'Estandar') {
                    content += `<button class='btn btn-danger btn-sm btn-eliminar-consulta mt-2' data-id='${c.id}'><i class='fas fa-trash'></i> Eliminar Consulta</button>`;
                  }
                  content += '</div></div></div>';
                });
                content += '</div>';
              } else {
                content += '<p class="text-muted">No hay consultas registradas.</p>';
              }
              modalBody.html(content);
            } else {
              modalBody.html(`<p class="text-danger">${response.message}</p>`);
            }
          },
          error: function () {
            modalBody.html('<p class="text-danger">Error al cargar el historial.</p>');
          }
        });
      }

      // Manejar el envío del formulario de edición de paciente
      $(document).on('submit', '#form-editar-paciente', function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        // Si existe foto capturada desde la cámara, elimina el campo de archivo para evitar conflicto
        if ($('#foto_capturada_editar').length && $('#foto_capturada_editar').val()) {
          formData.delete('edit-foto');
        }

        $.ajax({
          url: 'api/editar_paciente.php',
          type: 'POST',
          data: formData,
          contentType: false,
          processData: false,
          dataType: 'json',
          success: function (response) {
            try {
              if (typeof response !== 'object' || response === null) {
                throw new Error('Respuesta inválida del servidor.');
              }
              if (response.status === 'success') {
                $('#modal-editar-paciente').modal('hide');
                if (typeof tablaPacientes !== 'undefined' && tablaPacientes.ajax) {
                  tablaPacientes.ajax.reload(null, false);
                } else {
                  console.error('tablaPacientes no está inicializada correctamente.');
                }
                Swal.fire({
                  icon: 'success',
                  title: '¡Paciente Actualizado!',
                  text: response.message,
                  confirmButtonText: 'OK',
                  customClass: {
                    confirmButton: 'btn btn-success'
                  },
                  buttonsStyling: false
                });
              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'Error al Actualizar',
                  text: response.message || 'Error desconocido.',
                  confirmButtonText: 'OK',
                  customClass: {
                    confirmButton: 'btn btn-danger'
                  },
                  buttonsStyling: false
                });
              }
            } catch (err) {
              Swal.fire({
                icon: 'error',
                title: 'Error procesando respuesta',
                text: 'Error procesando la respuesta del servidor: ' + err.message,
                confirmButtonText: 'OK',
                customClass: {
                  confirmButton: 'btn btn-danger'
                },
                buttonsStyling: false
              });
              console.error('Error procesando la respuesta:', response, err);
            }
          },
          error: function (xhr, status, error) {
            let msg = 'Error de conexión con el servidor al intentar actualizar el paciente.';
            if (xhr && xhr.responseText) {
              msg += ' Respuesta: ' + xhr.responseText;
            }
            Swal.fire({
              icon: 'error',
              title: 'Error de Conexión',
              text: msg,
              confirmButtonText: 'OK',
              customClass: {
                confirmButton: 'btn btn-danger'
              },
              buttonsStyling: false
            });
            console.error('AJAX error:', status, error, xhr);
          }
        });
      });
      // Eliminar paciente
      $('#tabla-pacientes tbody').on('click', '.btn-eliminar-paciente', function () {
        var id = $(this).data('id');
        // Verificar si el paciente tiene un plan asignado
        $.get('api/facturacion_suscripcion_actual.php?id_paciente=' + id, function (response) {
          if (response.status === 'ok' && response.data) {
            Swal.fire('No se puede eliminar', 'El paciente tiene un plan de medicina prepagada asignado.', 'error');
          } else {
            Swal.fire({
              title: '¿Eliminar paciente?',
              text: 'Esta acción no se puede deshacer.',
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Sí, eliminar',
              cancelButtonText: 'Cancelar',
              confirmButtonColor: '#d33'
            }).then((result) => {
              if (result.isConfirmed) {
                $.ajax({
                  url: 'api/eliminar_paciente.php',
                  type: 'POST',
                  data: {
                    id: id
                  },
                  dataType: 'json',
                  success: function (response) {
                    if (response.status === 'success') {
                      Swal.fire('Eliminado', response.message, 'success');
                      tablaPacientes.ajax.reload();
                    } else {
                      Swal.fire('Error', response.message, 'error');
                    }
                  },
                  error: function () {
                    Swal.fire('Error', 'No se pudo eliminar el paciente.', 'error');
                  }
                });
              }
            });
          }
        }, 'json').fail(function () {
          Swal.fire('Error', 'No se pudo verificar el plan del paciente.', 'error');
        });
      });

      // Eliminar consulta desde historial
      $(document).on('click', '.btn-eliminar-consulta', function () {
        var id = $(this).data('id');
        // Obtener el id del paciente mostrado actualmente en el modal
        var pacienteId = $('#modal-ver-paciente').data('paciente-id');
        Swal.fire({
          title: '¿Eliminar consulta?',
          text: 'Esta acción no se puede deshacer.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'Cancelar',
          confirmButtonColor: '#d33'
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              url: 'api/eliminar_consulta.php',
              type: 'POST',
              data: {
                id: id
              },
              dataType: 'json',
              success: function (response) {
                if (response.status === 'success') {
                  Swal.fire('Eliminado', response.message, 'success');
                  // Recargar historial del paciente correcto
                  if (pacienteId) {
                    cargarHistorialPaciente(pacienteId);
                  }
                } else {
                  Swal.fire('Error', response.message, 'error');
                }
              },
              error: function () {
                Swal.fire('Error', 'No se pudo eliminar la consulta.', 'error');
              }
            });
          }
        });
      });
    </script>
    <!-- Script externo para el modal de cambio de contraseña -->
    <script src="js/modal_cambiar_contrasena.js"></script>
</body>

</html>