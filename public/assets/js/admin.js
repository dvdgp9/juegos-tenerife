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

    // ---------------------------------------------------------------------
    // Galería de fotos (entidades e instalaciones)
    //
    // Todo lo de aquí es comodidad sobre controles que ya funcionan solos: si
    // este guion no se carga, el formulario sigue permitiendo subir, ordenar,
    // destacar y eliminar fotos.
    // ---------------------------------------------------------------------

    function formatBytes(bytes) {
        if (bytes >= 1048576) {
            return (bytes / 1048576).toFixed(1).replace('.0', '') + ' MB';
        }

        return Math.max(1, Math.round(bytes / 1024)) + ' KB';
    }

    var pendingUrls = [];

    function releasePreviews() {
        pendingUrls.forEach(function (url) {
            URL.revokeObjectURL(url);
        });
        pendingUrls = [];
    }

    function renderPending(dropzone) {
        var input = dropzone.querySelector('input[type="file"]');
        var panel = document.querySelector('[data-media-pending]');

        if (!input || !panel) {
            return;
        }

        var list = panel.querySelector('[data-media-pending-list]');
        var count = panel.querySelector('[data-media-pending-count]');
        var warning = panel.querySelector('[data-media-pending-warning]');
        var files = input.files;

        releasePreviews();
        list.textContent = '';

        if (!files || files.length === 0) {
            panel.hidden = true;

            return;
        }

        var maxUpload = parseInt(dropzone.getAttribute('data-max-upload'), 10) || 0;
        var maxPost = parseInt(dropzone.getAttribute('data-max-post'), 10) || 0;
        var total = 0;
        var oversized = 0;

        Array.prototype.forEach.call(files, function (file) {
            var item = document.createElement('li');
            var tooBig = maxUpload > 0 && file.size > maxUpload;

            total += file.size;
            if (tooBig) {
                oversized++;
                item.className = 'is-too-big';
            }

            var preview = document.createElement('img');
            var url = URL.createObjectURL(file);
            pendingUrls.push(url);
            preview.src = url;
            preview.alt = '';
            // Un archivo con extensión de imagen pero contenido ilegible dejaría el icono
            // de imagen rota, que parece un fallo de la página.
            preview.addEventListener('error', function () {
                preview.style.visibility = 'hidden';
            });

            var name = document.createElement('span');
            name.textContent = file.name;

            var size = document.createElement('em');
            size.textContent = tooBig
                ? formatBytes(file.size) + ' · demasiado grande'
                : formatBytes(file.size);

            item.appendChild(preview);
            item.appendChild(name);
            item.appendChild(size);
            list.appendChild(item);
        });

        count.textContent = files.length === 1
            ? '1 foto lista para subir'
            : files.length + ' fotos listas para subir (' + formatBytes(total) + ')';

        // Avisar antes de intentarlo: si el envío supera post_max_size, PHP descarta
        // el formulario entero y se pierde todo lo escrito.
        var messages = [];
        if (oversized > 0) {
            messages.push(
                oversized === 1
                    ? 'Una de las fotos supera el máximo por archivo y será rechazada.'
                    : oversized + ' fotos superan el máximo por archivo y serán rechazadas.'
            );
        }
        if (maxPost > 0 && total > maxPost * 0.9) {
            messages.push(
                'El conjunto se acerca al límite del formulario (' + formatBytes(maxPost)
                + '). Sube las fotos en varias tandas para no perder los cambios.'
            );
        }

        warning.textContent = messages.join(' ');
        warning.hidden = messages.length === 0;
        panel.hidden = false;
    }

    function mergeFiles(input, incoming) {
        if (typeof DataTransfer === 'undefined') {
            return false;
        }

        var transfer = new DataTransfer();

        Array.prototype.forEach.call(input.files, function (file) {
            transfer.items.add(file);
        });

        Array.prototype.forEach.call(incoming, function (file) {
            if (file.type.indexOf('image/') === 0) {
                transfer.items.add(file);
            }
        });

        input.files = transfer.files;

        return true;
    }

    function renumberCards(grid) {
        var cards = grid.querySelectorAll('[data-media-card]');

        Array.prototype.forEach.call(cards, function (card, index) {
            var order = card.querySelector('.media-order-value input');
            if (order) {
                order.value = (index + 1) * 10;
            }

            var up = card.querySelector('[data-media-move="up"]');
            var down = card.querySelector('[data-media-move="down"]');
            if (up) {
                up.disabled = index === 0;
            }
            if (down) {
                down.disabled = index === cards.length - 1;
            }
        });
    }

    function syncFeaturedBadges() {
        var cards = document.querySelectorAll('[data-media-card]');

        Array.prototype.forEach.call(cards, function (card) {
            var radio = card.querySelector('input[name="featured_media_id"]');
            var badge = card.querySelector('[data-media-badge]');
            var isFeatured = !!radio && radio.checked;

            card.classList.toggle('is-featured', isFeatured);
            if (badge) {
                badge.hidden = !isFeatured;
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var grid = document.querySelector('[data-media-grid]');
        if (grid) {
            renumberCards(grid);
            syncFeaturedBadges();
        }

        var dropzone = document.querySelector('[data-media-dropzone]');
        if (!dropzone) {
            return;
        }

        ['dragenter', 'dragover'].forEach(function (name) {
            dropzone.addEventListener(name, function (event) {
                event.preventDefault();
                dropzone.classList.add('is-dragging');
            });
        });

        ['dragleave', 'drop'].forEach(function (name) {
            dropzone.addEventListener(name, function (event) {
                event.preventDefault();
                dropzone.classList.remove('is-dragging');
            });
        });

        dropzone.addEventListener('drop', function (event) {
            var input = dropzone.querySelector('input[type="file"]');
            if (!input || !event.dataTransfer) {
                return;
            }

            if (mergeFiles(input, event.dataTransfer.files)) {
                renderPending(dropzone);
            }
        });
    });

    document.addEventListener('change', function (event) {
        var target = event.target;

        if (!(target instanceof HTMLInputElement)) {
            return;
        }

        if (target.type === 'file' && target.closest('[data-media-dropzone]')) {
            renderPending(target.closest('[data-media-dropzone]'));

            return;
        }

        if (target.name === 'featured_media_id') {
            syncFeaturedBadges();

            return;
        }

        if (target.hasAttribute('data-media-remove')) {
            var card = target.closest('[data-media-card]');
            var strike = card ? card.querySelector('[data-media-strike]') : null;

            if (card) {
                card.classList.toggle('is-removing', target.checked);
            }
            if (strike) {
                strike.hidden = !target.checked;
            }
        }
    });

    document.addEventListener('click', function (event) {
        var target = event.target;

        if (!(target instanceof Element)) {
            return;
        }

        var clear = target.closest('[data-media-clear]');
        if (clear) {
            var dropzone = document.querySelector('[data-media-dropzone]');
            var input = dropzone ? dropzone.querySelector('input[type="file"]') : null;

            if (input) {
                input.value = '';
                renderPending(dropzone);
            }

            return;
        }

        var mover = target.closest('[data-media-move]');
        if (!mover) {
            return;
        }

        var card = mover.closest('[data-media-card]');
        var grid = card ? card.closest('[data-media-grid]') : null;

        if (!card || !grid) {
            return;
        }

        if (mover.getAttribute('data-media-move') === 'up') {
            var previous = card.previousElementSibling;
            if (previous) {
                grid.insertBefore(card, previous);
            }
        } else {
            var next = card.nextElementSibling;
            if (next) {
                grid.insertBefore(next, card);
            }
        }

        renumberCards(grid);
        // El foco viaja con la tarjeta: sin esto, mover varias veces seguidas
        // obligaría a volver a buscar el botón con el tabulador.
        mover.focus();
    });
})();
