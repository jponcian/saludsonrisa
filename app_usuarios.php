<?php require_once 'api/auth_check.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Clínica SaludSonrisa | Usuarios</title>

  <link rel="stylesheet" href="css/Source Sans Pro.css">
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <link rel="stylesheet" href="css/custom.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.css">
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
              <h1 class="m-0">Gestión de Usuarios</h1>
            </div>
          </div>
        </div>
      </div>
      <div class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">Listado de Usuarios del Sistema</h3>
                  <div class="card-tools"><button type="button" class="btn btn-primary" data-toggle="modal"
                      data-target="#modal-registrar-usuario"><i class="fas fa-plus"></i> Registrar Nuevo
                      Usuario</button></div>
                </div>
                <div class="card-body">
                  <table id="tabla-usuarios" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Foto</th>
                        <th>Nombre Completo</th>
                        <th>Cédula</th>
                        <th>Teléfono</th>
                        <th>Rol</th>
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

    <div class="modal fade" id="modal-registrar-usuario">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header modal-header-gradient">
            <h4 class="modal-title">Registrar Nuevo Usuario</h4><button type="button" class="close"
              data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
          </div>
          <div class="modal-body">
            <form id="form-registrar-usuario">
              <div class="alert alert-danger" id="error-message" style="display:none;"></div>
              <div class="form-group"><label>Nombre de Usuario</label><input type="text" class="form-control"
                  name="username" required></div>
              <div class="form-group"><label>Nombre Completo</label><input type="text" class="form-control"
                  name="nombre_completo" required></div>
              <div class="form-group"><label>Cédula</label><input type="text" class="form-control" name="cedula"></div>
              <div class="form-group"><label>Teléfono</label><input type="text" class="form-control" name="telefono">
              </div>
              <div class="form-group"><label>Contraseña</label><input type="password" class="form-control"
                  name="password" required></div>
              <div class="form-group"><label>Rol</label><select class="form-control" name="rol" required>
                  <option value="" disabled selected>Seleccione un rol</option>
                  <option value="especialista">Especialista</option>
                  <option value="Estandar">Estandar</option>
                  <option value="admin_usuarios">Admin. de Usuarios</option>

                </select></div>
              <div class="form-group">
                <label for="foto">Foto de Perfil</label>
                <div class="input-group">
                  <div class="custom-file">
                    <input type="file" class="custom-file-input" id="foto" name="foto" accept="image/*">
                    <label class="custom-file-label" for="foto">Seleccionar archivo</label>
                  </div>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer justify-content-between"><button type="button" class="btn btn-default"
              data-dismiss="modal">Cancelar</button><button type="submit" form="form-registrar-usuario"
              class="btn btn-primary">Guardar</button></div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="modal-editar-usuario">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header modal-header-gradient">
            <h4 class="modal-title">Editar Usuario</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form id="form-editar-usuario" enctype="multipart/form-data">
              <input type="hidden" name="id" id="edit-id">
              <div class="form-group">
                <label for="edit-username">Nombre de Usuario</label>
                <input type="text" class="form-control" id="edit-username" name="username" required>
              </div>
              <div class="form-group">
                <label for="edit-nombre-completo">Nombre Completo</label>
                <input type="text" class="form-control" id="edit-nombre-completo" name="nombre_completo" required>
              </div>
              <div class="form-group">
                <label for="edit-cedula">Cédula</label>
                <input type="text" class="form-control" id="edit-cedula" name="cedula">
              </div>
              <div class="form-group">
                <label for="edit-telefono">Teléfono</label>
                <input type="text" class="form-control" id="edit-telefono" name="telefono">
              </div>
              <div class="form-group">
                <label for="edit-rol">Rol</label>
                <select class="form-control" id="edit-rol" name="rol" required>
                  <option value="especialista">Especialista</option>
                  <option value="admin_usuarios">Admin. de Usuarios</option>
                </select>
              </div>
              <div class="form-group">
                <label for="edit-password">Nueva Contraseña (opcional)</label>
                <input type="password" class="form-control" id="edit-password" name="password">
              </div>
              <div class="form-group">
                <label for="edit-foto">Cambiar Foto de Perfil</label>
                <div class="input-group">
                  <div class="custom-file">
                    <input type="file" class="custom-file-input" id="edit-foto" name="foto" accept="image/*">
                    <label class="custom-file-label" for="edit-foto">Seleccionar archivo</label>
                  </div>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            <button type="submit" form="form-editar-usuario" class="btn btn-primary">Guardar Cambios</button>
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
  <script src="dist/js/adminlte.min.js"></script>
  <script src="plugins/sweetalert2/sweetalert2.all.min.js"></script>
  <script src="plugins/cropper.min.js"></script>

  <script>
    // Definir el rol del usuario para el renderizado de botones
    var usuarioRol = '<?php echo $rol; ?>';
    var cropper;
    var currentFileInput;

    $(function () {
      // Actualizar el label del input de archivo al seleccionar un archivo
      $('.custom-file-input').on('change', function () {
        var file = this.files[0];
        currentFileInput = $(this);

        if (file) {
          var reader = new FileReader();
          reader.onload = function (e) {
            $('#image-to-crop').attr('src', e.target.result);
            $('#modal-recortar-imagen').modal('show');
          };
          reader.readAsDataURL(file);
        }
      });

      // Al mostrar el modal, inicializar Cropper
      $('#modal-recortar-imagen').on('shown.bs.modal', function () {
        if (cropper) {
          cropper.destroy();
        }
        cropper = new Cropper(document.getElementById('image-to-crop'), {
          aspectRatio: 1,
          viewMode: 1
        });
      });

      // Al cerrar el modal, destruir Cropper y limpiar la imagen
      $('#modal-recortar-imagen').on('hidden.bs.modal', function () {
        if (cropper) {
          cropper.destroy();
          cropper = null;
        }
        $('#image-to-crop').attr('src', '');
      });

      $('#crop-button').on('click', function () {
        if (cropper) {
          cropper.getCroppedCanvas({
            width: 200,
            height: 200,
          }).toBlob(function (blob) {
            // Crear un objeto File a partir del Blob
            var croppedFile = new File([blob], "cropped_image.png", {
              type: "image/png"
            });

            // Asignar el archivo recortado al input original
            var dataTransfer = new DataTransfer();
            dataTransfer.items.add(croppedFile);
            currentFileInput[0].files = dataTransfer.files;

            // Actualizar el label del input
            currentFileInput.next('.custom-file-label').html("Imagen recortada.png");

            // Reiniciar Cropper.js y limpiar la imagen al cerrar el modal
            $('#modal-recortar-imagen').on('hidden.bs.modal', function () {
              if (cropper) {
                cropper.destroy();
                cropper = null; // Asegurarse de que la variable cropper sea nula
              }
              $('#image-to-crop').attr('src', ''); // Limpiar la imagen
            });

            $('#modal-recortar-imagen').modal('hide');
          });
        }
      });

      var tablaUsuarios = $("#tabla-usuarios").DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "ajax": {
          "url": "api/usuarios.php",
          "dataSrc": "data"
        },
        "columns": [{
          "data": "id"
        }, {
          "data": "username"
        }, {
          "data": "foto",
          "className": "text-center", // Añadido para centrar el contenido
          "render": function (data, type, row) {
            if (data) {
              return `<img src="uploads/${data}" class="img-circle elevation-2" style="width: 40px; height: 40px; object-fit: cover;">`;
            } else {
              return `<img src="logo.png" class="img-circle elevation-2" style="width: 40px; height: 40px; object-fit: cover;">`;
            }
          }
        }, {
          "data": "nombre_completo"
        }, {
          "data": "cedula"
        }, {
          "data": "telefono"
        }, {
          "data": "rol"
        },
        {
          "data": null,
          "render": function (data, type, row) {
            var html = `<button class="btn btn-info btn-sm btn-editar" data-id="${row.id}"><i class="fas fa-pencil-alt"></i> Editar</button>`;
            if (typeof usuarioRol !== 'undefined' && usuarioRol !== 'Estandar') {
              html += ` <button class="btn btn-danger btn-sm btn-eliminar" data-id="${row.id}"><i class="fas fa-trash"></i> Eliminar</button>`;
            }
            return html;
          }
        }
        ],
        "language": {
          "url": "plugins/datatables/i18n/Spanish.json"
        }
      });

      $('#form-registrar-usuario').on('submit', function (e) {
        e.preventDefault();
        var formData = new FormData(this);

        $.ajax({
          url: 'api/registrar_usuario.php',
          type: 'POST',
          data: formData,
          dataType: 'json',
          processData: false,
          contentType: false,
          success: function (response) {
            if (response.status === 'success') {
              $('#modal-registrar-usuario').modal('hide');
              $('#form-registrar-usuario')[0].reset();
              $('#foto').next('.custom-file-label').html('Seleccionar archivo'); // Reset label
              tablaUsuarios.ajax.reload();
              Swal.fire('¡Éxito!', response.message, 'success');
            } else {
              $('#error-message').text(response.message).show();
            }
          },
          error: function (jqXHR) {
            $('#error-message').text(jqXHR.responseJSON.message || 'Error de conexión.').show();
          }
        });
      });

      $('#tabla-usuarios tbody').on('click', '.btn-editar', function () {
        var id = $(this).data('id');
        $.ajax({
          url: 'api/get_usuario.php',
          type: 'GET',
          data: {
            id: id
          },
          dataType: 'json',
          success: function (response) {
            if (response.status === 'success') {
              var usuario = response.data;
              $('#edit-id').val(usuario.id);
              $('#edit-username').val(usuario.username);
              $('#edit-nombre-completo').val(usuario.nombre_completo);
              $('#edit-cedula').val(usuario.cedula);
              $('#edit-telefono').val(usuario.telefono);
              $('#edit-rol').val(usuario.rol);
              // Mostrar la foto de perfil
              if (usuario.foto) {
                $('#edit-foto-preview').attr('src', 'uploads/' + usuario.foto);
              } else {
                $('#edit-foto-preview').attr('src', 'dist/img/default-150x150.png');
              }
              $('#edit-foto').next('.custom-file-label').html('Seleccionar archivo'); // Reset label
              $('#modal-editar-usuario').modal('show');
            } else {
              Swal.fire('Error', response.message, 'error');
            }
          }
        });
      });

      $('#form-editar-usuario').on('submit', function (e) {
        e.preventDefault();
        var formData = new FormData(this);

        $.ajax({
          url: 'api/editar_usuario.php',
          type: 'POST',
          data: formData,
          dataType: 'json',
          processData: false,
          contentType: false,
          success: function (response) {
            if (response.status === 'success') {
              $('#modal-editar-usuario').modal('hide');
              tablaUsuarios.ajax.reload();
              Swal.fire('¡Éxito!', response.message, 'success');
            } else {
              Swal.fire('Error', response.message, 'error');
            }
          }
        });
      });

      // Eliminar usuario (un solo handler, mensaje consistente)
      $('#tabla-usuarios tbody').on('click', '.btn-eliminar', function () {
        var id = $(this).data('id');
        Swal.fire({
          title: '¿Eliminar usuario?',
          text: 'Esta acción no se puede deshacer.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'Cancelar',
          confirmButtonColor: '#d33',
          reverseButtons: true
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              url: 'api/eliminar_usuario.php',
              type: 'POST',
              data: {
                id: id
              },
              dataType: 'json',
              success: function (response) {
                if (response.status === 'success') {
                  Swal.fire({
                    icon: 'success',
                    title: 'Usuario eliminado',
                    text: response.message,
                    confirmButtonText: 'OK',
                    customClass: {
                      confirmButton: 'btn btn-success'
                    },
                    buttonsStyling: false
                  });
                  tablaUsuarios.ajax.reload();
                } else {
                  Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message,
                    confirmButtonText: 'OK',
                    customClass: {
                      confirmButton: 'btn btn-danger'
                    },
                    buttonsStyling: false
                  });
                }
              },
              error: function () {
                Swal.fire({
                  icon: 'error',
                  title: 'Error',
                  text: 'No se pudo eliminar el usuario.',
                  confirmButtonText: 'OK',
                  customClass: {
                    confirmButton: 'btn btn-danger'
                  },
                  buttonsStyling: false
                });
              }
            });
          }
        });

      }); // Fin handler eliminar usuario
    }); // Fin document ready
  </script>

  <div class="modal fade" id="modal-recortar-imagen" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header modal-header-gradient">
          <h5 class="modal-title" id="modalLabel">Recortar Imagen</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="d-flex justify-content-center align-items-center" style="min-height:200px;">
            <div class="img-container"
              style="max-height: 400px; overflow: auto; display: flex; justify-content: center; align-items: center; width: 100%;">
              <img id="image-to-crop" src="" style="display: block; margin: 0 auto; max-width: 100%; height: auto;">
            </div>
          </div>
        </div>
        <div class="modal-footer d-flex justify-content-center">
          <button type="button" class="btn btn-secondary mx-2" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success mx-2" id="crop-button">Recortar y Guardar</button>
        </div>
      </div>
    </div>
  </div>
</body>

</html>