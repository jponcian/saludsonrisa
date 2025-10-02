$(function() {
    $('#form-cambiar-contrasena').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $('#error-message-password').hide();
        $.ajax({
            url: 'api/cambiar_contrasena.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modal-cambiar-contrasena').modal('hide');
                    $('#form-cambiar-contrasena')[0].reset();
                    alert(response.message);
                } else {
                    $('#error-message-password').text(response.message).show();
                }
            },
            error: function(jqXHR) {
                $('#error-message-password').text(jqXHR.responseJSON && jqXHR.responseJSON.message ? jqXHR.responseJSON.message : 'Error de conexión.').show();
            }
        });
    });
});