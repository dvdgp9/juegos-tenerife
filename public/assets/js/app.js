(function () {
    'use strict';

    var t = {
        normalize: function (value) {
            return String(value || '')
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .trim();
        }
    };

    function initMaps() {
        if (!window.L) {
            document.documentElement.classList.add('leaflet-unavailable');
            return;
        }

        var points = Array.isArray(window.__mapPoints) ? window.__mapPoints : [];
        var TENERIFE_CENTER = [28.2916, -16.6291];

        document.querySelectorAll('[data-map]').forEach(function (node) {
            var mode = node.getAttribute('data-map');
            var defaultZoom = mode === 'entity' ? 12 : 10;

            var map = window.L.map(node, {
                scrollWheelZoom: false,
                zoomControl: false
            }).setView(TENERIFE_CENTER, defaultZoom);

            window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            var bounds = [];
            points.forEach(function (point) {
                var popup = '<strong>' + point.title + '</strong>';
                if (point.municipality) { popup += '<br>' + point.municipality; }
                if (point.modalities) { popup += '<br>' + point.modalities; }
                if (point.url && point.url !== '#') {
                    popup += '<br><a href="' + point.url + '">Ver ficha</a>';
                }
                window.L.marker([point.lat, point.lng]).addTo(map).bindPopup(popup);
                bounds.push([point.lat, point.lng]);
            });

            if (bounds.length > 1) {
                map.fitBounds(bounds, { padding: [30, 30] });
            } else if (bounds.length === 1) {
                map.setView(bounds[0], mode === 'entity' ? 14 : 12);
            }

            addMapZoomControls(node, map);
            node.classList.add('leaflet-ready');
        });
    }

    function addMapZoomControls(node, map) {
        var controls = document.createElement('div');
        controls.className = 'map-zoom-controls';
        controls.setAttribute('aria-label', 'Controles de zoom del mapa');

        [
            ['+', 'Acercar', function () { map.zoomIn(); }],
            ['-', 'Alejar', function () { map.zoomOut(); }]
        ].forEach(function (item) {
            var button = document.createElement('button');
            button.type = 'button';
            button.textContent = item[0];
            button.setAttribute('aria-label', item[1]);

            button.addEventListener('mousedown', function (event) {
                event.preventDefault();
            });

            button.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                item[2]();
            });

            controls.appendChild(button);
        });

        if (window.L.DomEvent) {
            window.L.DomEvent.disableClickPropagation(controls);
            window.L.DomEvent.disableScrollPropagation(controls);
        }

        node.appendChild(controls);
    }

    function setLoading(form, loading) {
        var button = form.querySelector('button[type="submit"]');
        var status = form.querySelector('[data-form-status]');

        if (!button || !status) {
            return;
        }

        if (loading) {
            button.textContent = 'Buscando...';
            button.disabled = true;
            status.hidden = false;
            status.textContent = 'Consultando resultados de ejemplo.';
            form.classList.add('is-loading');
            return;
        }

        button.textContent = button.getAttribute('data-default-label') || 'Buscar';
        button.disabled = false;
        status.hidden = true;
        status.textContent = '';
        form.classList.remove('is-loading');
    }

    function initHomeSearch() {
        var form = document.querySelector('[data-search-form]');

        if (!form) {
            return;
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            setLoading(form, true);

            window.setTimeout(function () {
                var params = new URLSearchParams(new FormData(form));
                window.location.href = '/busqueda?' + params.toString();
            }, 280);
        });
    }

    function initResultFilters() {
        var form = document.querySelector('[data-filter-form]');
        var cards = Array.prototype.slice.call(document.querySelectorAll('[data-result-card]'));
        var count = document.querySelector('[data-results-count]');
        var emptyState = document.querySelector('[data-empty-state]');

        if (!form || cards.length === 0 || !count || !emptyState) {
            return;
        }

        function applyFilters() {
            var formData = new FormData(form);
            var query = t.normalize(formData.get('q'));
            var municipality = t.normalize(formData.get('municipio'));
            var visibleCount = 0;

            cards.forEach(function (card) {
                var name = t.normalize(card.getAttribute('data-name'));
                var cardMunicipality = t.normalize(card.getAttribute('data-municipality'));
                var matchesQuery = query === '' || name.indexOf(query) !== -1;
                var matchesMunicipality = municipality === '' || municipality === 'todos' || cardMunicipality === municipality;
                var isVisible = matchesQuery && matchesMunicipality;

                card.hidden = !isVisible;
                if (isVisible) {
                    visibleCount += 1;
                }
            });

            count.textContent = visibleCount + (visibleCount === 1 ? ' coincidencia' : ' coincidencias');
            emptyState.hidden = visibleCount !== 0;
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            setLoading(form, true);
            window.setTimeout(function () {
                setLoading(form, false);
                applyFilters();
            }, 220);
        });

        form.addEventListener('input', applyFilters);
        form.addEventListener('change', applyFilters);
        applyFilters();
    }


    // ---------------------------------------------------------------------
    // Visor de fotos de la galería
    //
    // Cada foto es un enlace a la imagen completa. Sin este guion, o si el
    // navegador no admite <dialog>, el enlace sigue abriendo la foto.
    // ---------------------------------------------------------------------

    function initLightbox() {
        var dialog = document.querySelector('[data-lightbox]');

        if (!dialog || typeof dialog.showModal !== 'function') {
            return;
        }

        var image = dialog.querySelector('[data-lightbox-image]');
        var caption = dialog.querySelector('[data-lightbox-caption]');
        var counter = dialog.querySelector('[data-lightbox-counter]');
        var previousButton = dialog.querySelector('[data-lightbox-prev]');
        var nextButton = dialog.querySelector('[data-lightbox-next]');

        var items = [];
        var index = 0;
        var opener = null;

        function show(position) {
            if (items.length === 0) {
                return;
            }

            // Se da la vuelta por los extremos: llegar al final y no poder seguir
            // obliga a volver atrás foto a foto.
            index = (position + items.length) % items.length;

            var item = items[index];
            var text = item.getAttribute('data-caption') || '';
            var alt = item.querySelector('img') ? item.querySelector('img').getAttribute('alt') : '';

            image.src = item.getAttribute('href');
            image.alt = alt || text;
            caption.textContent = text;
            counter.textContent = items.length > 1 ? (index + 1) + ' de ' + items.length : '';

            previousButton.disabled = items.length < 2;
            nextButton.disabled = items.length < 2;

            // Sin texto ni contador, el pie sería una pastilla vacía flotando bajo la foto.
            caption.parentNode.hidden = text === '' && counter.textContent === '';
        }

        function open(link) {
            var gallery = link.closest('[data-gallery]');

            // Se navega solo dentro de la galería pulsada: saltar de las fotos del club
            // a las de una instalación sin avisar desorienta.
            items = gallery
                ? Array.prototype.slice.call(gallery.querySelectorAll('[data-gallery-item]'))
                : [link];

            opener = link;
            show(items.indexOf(link));
            dialog.showModal();
        }

        document.addEventListener('click', function (event) {
            var link = event.target.closest ? event.target.closest('[data-gallery-item]') : null;

            if (link) {
                event.preventDefault();
                open(link);

                return;
            }

            if (event.target.closest('[data-lightbox-close]')) {
                dialog.close();

                return;
            }

            if (event.target.closest('[data-lightbox-prev]')) {
                show(index - 1);

                return;
            }

            if (event.target.closest('[data-lightbox-next]')) {
                show(index + 1);
            }
        });

        // Cierra al pulsar en cualquier sitio que no sea la propia foto o un botón: el
        // diálogo ocupa toda la pantalla, así que eso incluye el hueco alrededor de la
        // imagen, el pie y los márgenes.
        dialog.addEventListener('click', function (event) {
            if (!event.target.closest || !event.target.closest('[data-lightbox-image], button')) {
                dialog.close();
            }
        });

        dialog.addEventListener('keydown', function (event) {
            // El propio <dialog> ya cierra con Esc, pero se hace explícito: así el visor no
            // depende de que el navegador implemente esa parte, y el comportamiento es el
            // mismo en todos.
            if (event.key === 'Escape') {
                event.preventDefault();
                dialog.close();

                return;
            }

            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                show(index - 1);
            } else if (event.key === 'ArrowRight') {
                event.preventDefault();
                show(index + 1);
            }
        });

        dialog.addEventListener('close', function () {
            // Devolver el foco a la miniatura de la que se salió: sin esto, quien navega
            // con teclado vuelve al principio de la página.
            if (opener) {
                opener.focus();
                opener = null;
            }

            image.removeAttribute('src');
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initHomeSearch();
        initResultFilters();
        initMaps();
        initLightbox();
    });
}());
