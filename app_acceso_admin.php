<?php
require_once 'api/auth_check.php';
require_once 'api/conexion.php';

// Validar acceso según permisos configurados
$paginaRuta = basename(__FILE__);
$stmtPagina = $pdo->prepare('SELECT id FROM paginas WHERE ruta = ? LIMIT 1');
$stmtPagina->execute([$paginaRuta]);
$paginaId = $stmtPagina->fetchColumn();

if (!$paginaId || !in_array((int) $paginaId, $permisos_usuario, true)) {
    header('Location: app_inicio.php');
    exit;
}


// Obtener todos los roles desde la tabla de roles
$roles = $pdo->query('SELECT nombre FROM roles ORDER BY nombre')->fetchAll(PDO::FETCH_COLUMN);
// Obtener todas las páginas
$paginas = $pdo->query('SELECT * FROM paginas ORDER BY nombre')->fetchAll(PDO::FETCH_ASSOC);

// Procesar la actualización de accesos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_accesos'])) {
    $rolSeleccionado = $_POST['rol'];
    $paginasSeleccionadas = isset($_POST['paginas']) ? $_POST['paginas'] : [];

    if ($rolSeleccionado) {
        // Obtener rol_id
        $stmtRol = $pdo->prepare('SELECT id FROM roles WHERE nombre = ?');
        $stmtRol->execute([$rolSeleccionado]);
        $rolId = $stmtRol->fetchColumn();

        if ($rolId) {
            // Eliminar accesos previos para el rol
            $stmt = $pdo->prepare('DELETE FROM rol_permisos WHERE rol_id = ?');
            $stmt->execute([$rolId]);

            // Insertar nuevos accesos
            $stmtInsert = $pdo->prepare('INSERT INTO rol_permisos (rol_id, permiso_id) VALUES (?, ?)');
            foreach ($paginasSeleccionadas as $paginaId) {
                $stmtInsert->execute([$rolId, $paginaId]);
            }
            $msg = "Accesos actualizados correctamente para el rol '<strong>" . htmlspecialchars($rolSeleccionado) . "</strong>'.";
        } else {
            $msg_error = "Rol no encontrado.";
        }
    } else {
        $msg_error = "Por favor, seleccione un rol para actualizar los accesos.";
    }
}

// Procesar la creación de una nueva página
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_pagina'])) {
    $nombre = $_POST['nombre_pagina'];
    $ruta = $_POST['ruta_pagina'];
    $activo = isset($_POST['activa_pagina']) ? 1 : 0;

    if (!empty($nombre) && !empty($ruta)) {
        $stmt = $pdo->prepare('INSERT INTO paginas (nombre, ruta, activo) VALUES (?, ?, ?)');
        $stmt->execute([$nombre, $ruta, $activo]);
        $msg = "Página '<strong>" . htmlspecialchars($nombre) . "</strong>' creada exitosamente.";
        // Recargar las páginas para mostrar la nueva
        $paginas = $pdo->query('SELECT * FROM paginas ORDER BY nombre')->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $msg_error = "El nombre y la ruta de la página son obligatorios.";
    }
}

// Procesar la actualización de una página
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_pagina'])) {
    $id = $_POST['id_pagina_edit'];
    $nombre = $_POST['nombre_pagina_edit'];
    $ruta = $_POST['ruta_pagina_edit'];
    $activo = isset($_POST['activa_pagina_edit']) ? 1 : 0;

    if (!empty($id) && !empty($nombre) && !empty($ruta)) {
        $stmt = $pdo->prepare('UPDATE paginas SET nombre = ?, ruta = ?, activo = ? WHERE id = ?');
        $stmt->execute([$nombre, $ruta, $activo, $id]);
        $msg = "Página '<strong>" . htmlspecialchars($nombre) . "</strong>' actualizada exitosamente.";
        // Recargar las páginas para mostrar los cambios
        $paginas = $pdo->query('SELECT * FROM paginas ORDER BY nombre')->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $msg_error = "El nombre y la ruta de la página son obligatorios.";
    }
}

// Procesar la eliminación de una página
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_pagina'])) {
    $id = $_POST['id_pagina_eliminar'];

    if (!empty($id)) {
        // Eliminar accesos a la página en rol_permisos
        $stmt = $pdo->prepare('DELETE FROM rol_permisos WHERE permiso_id = ?');
        $stmt->execute([$id]);

        // Eliminar la página
        $stmt = $pdo->prepare('DELETE FROM paginas WHERE id = ?');
        $stmt->execute([$id]);

        $msg = "Página eliminada exitosamente.";
        // Recargar las páginas para mostrar los cambios
        $paginas = $pdo->query('SELECT * FROM paginas ORDER BY nombre')->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $msg_error = "No se ha seleccionado ninguna página para eliminar.";
    }
}

// Obtener los accesos actuales para el script de JS
$accesos = [];
$rows = $pdo->query('SELECT r.nombre AS rol, rp.permiso_id AS pagina_id FROM rol_permisos rp JOIN roles r ON rp.rol_id = r.id')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $accesos[$row['rol']][] = $row['pagina_id'];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Accesos por Rol</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .modal-header-gradient-blue-green {
            background: linear-gradient(to right, #007bff, #28a745);
            color: white;
        }

        .card-header-form {
            background-color: #f4f6f9;
            border-bottom: 1px solid #dee2e6;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <?php include 'sidebar.php'; // Menú lateral 
        ?>

        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Gestión de Accesos por Rol</h1>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">

                    <div class="row">
                        <div class="col-md-8">
                            <div class="card card-primary card-outline">
                                <div class="card-header modal-header-gradient-blue-green">
                                    <h3 class="card-title">Asignar Páginas a Roles</h3>
                                </div>
                                <form method="post">
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="rol">Seleccione un Rol:</label>
                                            <select name="rol" id="rol" class="form-control" required>
                                                <option value="">-- Seleccionar Rol --</option>
                                                <?php foreach ($roles as $r): ?>
                                                    <option value="<?php echo htmlspecialchars($r); ?>"><?php echo htmlspecialchars($r); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Páginas Permitidas:</label>
                                            <div class="row">
                                                <?php foreach ($paginas as $p): ?>
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="paginas[]" value="<?php echo $p['id']; ?>" id="pagina_<?php echo $p['id']; ?>">
                                                            <label class="form-check-label" for="pagina_<?php echo $p['id']; ?>">
                                                                <?php echo htmlspecialchars($p['nombre']); ?>
                                                                <small class="text-muted">(<?php echo htmlspecialchars($p['ruta']); ?>)</small>
                                                            </label>
                                                            <button type="button" class="btn btn-xs btn-info float-right" onclick="editarPagina(<?php echo htmlspecialchars(json_encode($p)); ?>)">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-xs btn-danger float-right mr-1" onclick="confirmarEliminarPagina(<?php echo $p['id']; ?>)">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <button type="submit" name="guardar_accesos" class="btn btn-primary"><i class="fas fa-save mr-2"></i>Guardar Accesos</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-secondary card-outline">
                                <div class="card-header card-header-form">
                                    <h3 class="card-title">Añadir Nueva Página</h3>
                                </div>
                                <form method="post">
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="nombre_pagina">Nombre de la Página</label>
                                            <input type="text" name="nombre_pagina" id="nombre_pagina" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="ruta_pagina">Ruta del Archivo</label>
                                            <input type="text" name="ruta_pagina" id="ruta_pagina" class="form-control" placeholder="ej: app_nueva_pagina.php" required>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" name="activa_pagina" id="activa_pagina" class="form-check-input" value="1" checked>
                                            <label class="form-check-label" for="activa_pagina">Página Activa</label>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <button type="submit" name="crear_pagina" class="btn btn-success"><i class="fas fa-plus-circle mr-2"></i>Crear Página</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <footer class="main-footer">
            <div class="float-right d-none d-sm-inline">Innovando la Gestión Médica</div>
            <strong>&copy; 2024-2025 <a href="#">Clínica SaludSonrisa</a>.</strong>
        </footer>
    </div>

    <!-- Modal para Editar Página -->
    <div class="modal fade" id="modalEditarPagina" tabindex="-1" role="dialog" aria-labelledby="modalEditarPaginaLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditarPaginaLabel">Editar Página</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_pagina_edit" id="id_pagina_edit">
                        <div class="form-group">
                            <label for="nombre_pagina_edit">Nombre de la Página</label>
                            <input type="text" name="nombre_pagina_edit" id="nombre_pagina_edit" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="ruta_pagina_edit">Ruta del Archivo</label>
                            <input type="text" name="ruta_pagina_edit" id="ruta_pagina_edit" class="form-control" required>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="activa_pagina_edit" id="activa_pagina_edit" class="form-check-input">
                            <label class="form-check-label" for="activa_pagina_edit">Página Activa</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" name="actualizar_pagina" class="btn btn-primary">Actualizar Página</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Confirmar Eliminación -->
    <div class="modal fade" id="modalEliminarPagina" tabindex="-1" role="dialog" aria-labelledby="modalEliminarPaginaLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEliminarPaginaLabel">Confirmar Eliminación</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>¿Está seguro de que desea eliminar esta página? Esta acción no se puede deshacer.</p>
                        <input type="hidden" name="id_pagina_eliminar" id="id_pagina_eliminar">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" name="eliminar_pagina" class="btn btn-danger">Eliminar Página</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script>
        $(function() {
            const accesos = <?php echo json_encode($accesos); ?>;

            $('#rol').on('change', function() {
                const rol = $(this).val();
                // Desmarcar todas las páginas
                $('input[name="paginas[]"]').prop('checked', false);

                // Marcar las páginas asignadas al rol seleccionado
                if (rol && accesos[rol]) {
                    accesos[rol].forEach(function(paginaId) {
                        $('#pagina_' + paginaId).prop('checked', true);
                    });
                }
            });

            // Mostrar mensajes con SweetAlert
            var msg = "<?php echo addslashes($msg ?? ''); ?>";
            var msg_error = "<?php echo addslashes($msg_error ?? ''); ?>";
            if (msg) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    html: msg,
                    confirmButtonText: 'Aceptar'
                });
            }
            if (msg_error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: msg_error,
                    confirmButtonText: 'Aceptar'
                });
            }
        });

        function editarPagina(pagina) {
            $('#id_pagina_edit').val(pagina.id);
            $('#nombre_pagina_edit').val(pagina.nombre);
            $('#ruta_pagina_edit').val(pagina.ruta);
            $('#activa_pagina_edit').prop('checked', pagina.activo == 1);
            $('#modalEditarPagina').modal('show');
        }

        function confirmarEliminarPagina(id) {
            $('#id_pagina_eliminar').val(id);
            $('#modalEliminarPagina').modal('show');
        }
    </script>
</body>

</html>