(function () {
    'use strict';

    // Mostrar/ocultar contraseña. El checkbox indica en data-toggle-password
    // los id de los campos que controla, separados por espacios.
    document.addEventListener('change', function (event) {
        var toggle = event.target;

        if (!(toggle instanceof HTMLInputElement) || !toggle.hasAttribute('data-toggle-password')) {
            return;
        }

        var ids = toggle.getAttribute('data-toggle-password').split(/\s+/);

        ids.forEach(function (id) {
            var field = document.getElementById(id);

            if (field instanceof HTMLInputElement) {
                field.type = toggle.checked ? 'text' : 'password';
            }
        });
    });

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
