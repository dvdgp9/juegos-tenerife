<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Resultados de búsqueda', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="" defer></script>
    <script src="/assets/js/app.js" defer></script>
</head>
<body>
    <header class="site-header">
        <a class="brand" href="/" aria-label="Inicio">
            <img src="/assets/images/logo-dxt-tenerife.png" alt="Tenerife Deportes">
        </a>
        <nav class="main-nav" aria-label="Navegación principal">
            <a href="/busqueda">Búsqueda Entidades</a>
            <a href="/#sobre-censo">Sobre el Censo</a>
            <a href="/#modalidades">Deportes y Modalidades</a>
        </nav>
        <a class="header-action" href="mailto:deportesdetenerife@gmail.com">Comunicar actualización</a>
    </header>

    <main class="results-page">
        <section class="page-intro">
            <p class="eyebrow">Resultados del Buscador</p>
            <h1>Entidades encontradas</h1>
            <p>Listado estático de ejemplo. En la siguiente fase se conectará con MySQL y filtros reales.</p>
        </section>

        <section class="results-layout">
            <aside class="filters-panel" aria-label="Filtros activos">
                <h2>Filtros</h2>
                <form class="stacked-form" action="/busqueda" method="get" data-filter-form>
                    <div class="field">
                        <label for="q">Nombre</label>
                        <input id="q" name="q" type="search" placeholder="Buscar por nombre">
                    </div>
                    <div class="field">
                        <label for="municipio">Municipio</label>
                        <select id="municipio" name="municipio">
                            <option>Todos</option>
                            <option>San Cristóbal de La Laguna</option>
                            <option>Santa Cruz de Tenerife</option>
                        </select>
                    </div>
                    <button class="button primary" type="submit" data-default-label="Filtrar resultados">Filtrar resultados</button>
                    <p class="form-status" data-form-status hidden></p>
                </form>
            </aside>

            <div class="results-main">
                <div class="results-summary">
                    <strong data-results-count>2 coincidencias</strong>
                    <span>Ordenadas por relevancia</span>
                </div>
                <article class="entity-row" data-result-card data-name="Federación de Arrastre Canario" data-municipality="San Cristóbal de La Laguna">
                    <img src="/assets/images/pictogramas/ARRASTRE_DE_GANADO_1.png" alt="">
                    <div>
                        <p class="result-meta">Federación · San Cristóbal de La Laguna</p>
                        <h2>Federación de Arrastre Canario</h2>
                        <p>Arrastre Canario · Casa del Ganadero · Contacto público disponible</p>
                    </div>
                    <a class="button secondary" href="/entidades/federacion-arrastre-canario">Ver ficha</a>
                </article>
                <article class="entity-row" data-result-card data-name="Club de Bola Canaria" data-municipality="Santa Cruz de Tenerife">
                    <img src="/assets/images/pictogramas/BOLA_CANARIA_1.png" alt="">
                    <div>
                        <p class="result-meta">Club · Santa Cruz de Tenerife</p>
                        <h2>Club de Bola Canaria</h2>
                        <p>Bola Canaria · Datos de ejemplo para la maqueta estática</p>
                    </div>
                    <a class="button secondary" href="#">Ver ficha</a>
                </article>
                <div class="empty-state" data-empty-state hidden>
                    <strong>No hay coincidencias para esos filtros.</strong>
                    <span>Prueba con otro municipio o limpia el nombre de búsqueda.</span>
                </div>
            </div>

            <div class="map-shell results-map" aria-label="Mapa preliminar" data-map="results">
                <div class="map-fallback">
                    <div class="map-island">
                        <span class="pin pin-1"></span>
                        <span class="pin pin-3"></span>
                    </div>
                </div>
                <div class="map-legend">
                    <strong>Ubicaciones</strong>
                    <span>Marcadores de ejemplo con Leaflet</span>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
