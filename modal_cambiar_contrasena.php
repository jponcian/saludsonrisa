<!-- Modal Cambiar Contraseña reutilizable -->
<div class="modal fade" id="modal-cambiar-contrasena" tabindex="-1" role="dialog" aria-labelledby="modalCambiarContrasenaLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-gradient">
                <h4 class="modal-title" id="modalCambiarContrasenaLabel">Cambiar Contraseña</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="form-cambiar-contrasena">
                    <div class="alert alert-danger" id="error-message-password" style="display:none;"></div>
                    <div class="form-group"><label for="current_password">Contraseña Actual</label><input type="password" class="form-control" id="current_password" name="current_password" required></div>
                    <div class="form-group"><label for="new_password">Nueva Contraseña</label><input type="password" class="form-control" id="new_password" name="new_password" required></div>
                    <div class="form-group"><label for="confirm_new_password">Confirmar Nueva Contraseña</label><input type="password" class="form-control" name="confirm_new_password" required></div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="submit" form="form-cambiar-contrasena" class="btn btn-warning">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>