(function () {
    'use strict';

    // Confirmación para acciones destructivas del panel.
    // Se activa con data-confirm="texto" en cualquier formulario del admin.
    document.addEventListener('submit', function (event) {
        var form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        var message = form.getAttribute('data-confirm');
        if (!message) {
            return;
        }

        if (!window.confirm(message)) {
            event.preventDefault();
        }
    });
})();
