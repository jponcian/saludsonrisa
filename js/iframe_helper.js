/**
 * Muestra un SweetAlert en la ventana principal desde un iframe.
 * @param {object} options - Las opciones para Swal.fire().
 * @param {function} [callback] - Una función opcional que se ejecuta cuando el SweetAlert se cierra. 
 *                                Recibe el resultado de la promesa de Swal.fire().
 */
function showGlobalSweetAlert(options, callback) {
    // Enviar el evento a la ventana padre
    window.parent.postMessage({ action: 'showSweetAlert', options: options }, '*');

    // Si hay un callback, escuchar la respuesta de la ventana padre
    if (callback && typeof callback === 'function') {
        const listener = function(event) {
            if (event.data && event.data.action === 'sweetAlertResult') {
                // Remover el listener para no ejecutarlo múltiples veces
                window.removeEventListener('message', listener);
                // Ejecutar el callback con el resultado
                callback(event.data.result);
            }
        };
        window.addEventListener('message', listener);
    }
}
