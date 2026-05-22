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
                zoomControl: true
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

            node.classList.add('leaflet-ready');
        });
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

    document.addEventListener('DOMContentLoaded', function () {
        initHomeSearch();
        initResultFilters();
        initMaps();
    });
}());

