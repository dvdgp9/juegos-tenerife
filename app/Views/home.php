<?php

$modalities = [
    ['name' => 'Lucha Canaria', 'icon' => '/assets/images/pictogramas/LUCHA_CANARIA_1.png', 'text' => 'Deporte vernáculo de Canarias basado en la habilidad, la nobleza y el desequilibrio del contrario.'],
    ['name' => 'Juego del Palo', 'icon' => '/assets/images/pictogramas/JUEGO_DEL_PALO_1.png', 'text' => 'Práctica lúdica de enfrentamiento con bastones de madera y reglas pactadas entre jugadores.'],
    ['name' => 'Arrastre Canario', 'icon' => '/assets/images/pictogramas/ARRASTRE_DE_GANADO_1.png', 'text' => 'Competición vinculada a las tareas agrícolas tradicionales y a la relación entre guayero y yunta.'],
    ['name' => 'Salto del Pastor', 'icon' => '/assets/images/pictogramas/SALTO_DEL_PASTOR_1.png', 'text' => 'Uso de lanza o garrote con regatón para desplazarse por la orografía irregular de la isla.'],
    ['name' => 'Bola Canaria', 'icon' => '/assets/images/pictogramas/BOLA_CANARIA_1.png', 'text' => 'Juego de precisión que busca situar las bolas lo más cerca posible del boliche.'],
    ['name' => 'Lucha del Garrote', 'icon' => '/assets/images/pictogramas/JUEGO_DEL_GARROTE_1.png', 'text' => 'Sistema tradicional de combate y defensa personal con garrote o lata.'],
];

$municipalities = ['Adeje', 'Arafo', 'Arico', 'Arona', 'Buenavista del Norte', 'Candelaria', 'El Rosario', 'El Sauzal', 'El Tanque', 'Fasnia', 'Garachico', 'Granadilla de Abona', 'Guía de Isora', 'Güímar', 'Icod de los Vinos', 'La Guancha', 'La Matanza de Acentejo', 'La Orotava', 'La Victoria de Acentejo', 'Los Realejos', 'Los Silos', 'Puerto de la Cruz', 'San Cristóbal de La Laguna', 'San Juan de la Rambla', 'San Miguel de Abona', 'Santa Cruz de Tenerife', 'Santa Úrsula', 'Santiago del Teide', 'Tacoronte', 'Tegueste', 'Vilaflor de Chasna'];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Juegos Tenerife', ENT_QUOTES, 'UTF-8') ?></title>
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
            <a href="#sobre-censo">Sobre el Censo</a>
            <a href="#modalidades">Deportes y Modalidades</a>
        </nav>
        <a class="header-action" href="mailto:deportesdetenerife@gmail.com">Comunicar actualización</a>
    </header>

    <main>
        <section class="hero">
            <div class="hero-copy">
                <p class="eyebrow">Censo oficial en Tenerife</p>
                <h1>Censo de Entidades y Colectivos de Deportes y Juegos Motores Tradicionales de Canarias</h1>
                <p>Consulta entidades, colectivos, modalidades e instalaciones vinculadas a los deportes y juegos tradicionales en Tenerife.</p>
            </div>
            <div class="hero-collage" aria-label="Modalidades destacadas">
                <?php foreach ($modalities as $index => $modality): ?>
                    <article class="collage-tile collage-tile-<?= $index + 1 ?>">
                        <img src="<?= htmlspecialchars($modality['icon'], ENT_QUOTES, 'UTF-8') ?>" alt="">
                        <span><?= htmlspecialchars($modality['name'], ENT_QUOTES, 'UTF-8') ?></span>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="search-panel" aria-labelledby="busqueda-title">
            <div class="section-heading compact">
                <p class="eyebrow">Búsqueda Entidades</p>
                <h2 id="busqueda-title">Encuentra entidades por municipio, tipo o modalidad</h2>
            </div>
            <form class="search-form" action="/busqueda" method="get" data-search-form>
                <div class="field">
                    <label for="q">Nombre</label>
                    <input id="q" name="q" type="search" placeholder="Nombre de entidad o colectivo">
                </div>
                <div class="field">
                    <label for="municipio">Municipio</label>
                    <select id="municipio" name="municipio">
                        <option value="">Todos los municipios</option>
                        <?php foreach ($municipalities as $municipality): ?>
                            <option value="<?= htmlspecialchars($municipality, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($municipality, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="tipo">Tipo de Entidad</label>
                    <select id="tipo" name="tipo">
                        <option value="">Cualquier tipo</option>
                        <option>Federación</option>
                        <option>Club</option>
                        <option>Colectivo</option>
                        <option>Asociación</option>
                    </select>
                </div>
                <div class="field">
                    <label for="modalidad">Deportes y Modalidades Lúdicas</label>
                    <select id="modalidad" name="modalidad">
                        <option value="">Todas las modalidades</option>
                        <?php foreach ($modalities as $modality): ?>
                            <option><?= htmlspecialchars($modality['name'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="button primary" type="submit" data-default-label="Buscar">Buscar</button>
                <p class="form-status" data-form-status hidden></p>
            </form>
        </section>

        <section class="map-preview">
            <div class="result-stack">
                <article class="result-card featured">
                    <p class="result-meta">San Cristóbal de La Laguna</p>
                    <h3>Federación de Arrastre Canario</h3>
                    <p>Arrastre Canario · Instalaciones asociadas · Contacto público</p>
                    <a href="/entidades/federacion-arrastre-canario">Ver ficha</a>
                </article>
                <article class="result-card">
                    <p class="result-meta">Santa Cruz de Tenerife</p>
                    <h3>Club de Bola Canaria</h3>
                    <p>Bola Canaria · Datos de ejemplo</p>
                </article>
            </div>
            <div class="map-shell" aria-label="Mapa preliminar" data-map="home">
                <div class="map-fallback">
                    <div class="map-island">
                        <span class="pin pin-1"></span>
                        <span class="pin pin-2"></span>
                        <span class="pin pin-3"></span>
                        <span class="pin pin-4"></span>
                        <span class="pin pin-5"></span>
                    </div>
                </div>
                <div class="map-legend">
                    <strong>Mapa interactivo</strong>
                    <span>Entidades e instalaciones filtrables</span>
                </div>
            </div>
        </section>

        <section id="sobre-censo" class="content-band">
            <div class="section-heading">
                <p class="eyebrow">Sobre el Censo</p>
                <h2>Una herramienta para preservar y acercar las modalidades tradicionales</h2>
            </div>
            <p>El Cabildo de Tenerife, a través de su Área de Deportes, ha desarrollado un Censo de Entidades y Colectivos de Deportes y Juegos Motores Tradicionales de Canarias en Tenerife con el objetivo de facilitar a la población una herramienta de búsqueda para identificar las entidades y colectivos que desarrollan actividades vinculadas a los deportes y juegos motores tradicionales canarios en su entorno cercano, promoviendo así la visibilidad y el acercamiento de estas modalidades a toda la ciudadanía, especialmente a la población infantil y juvenil, lo que ayudará a mejorar su preservación y arraigo en la Isla.</p>
        </section>

        <section id="modalidades" class="modalities-section">
            <div class="section-heading">
                <p class="eyebrow">Deportes y Modalidades</p>
                <h2>Seis modalidades principales como punto de entrada al censo</h2>
            </div>
            <div class="modalities-grid">
                <?php foreach ($modalities as $modality): ?>
                    <article class="modality-card">
                        <img src="<?= htmlspecialchars($modality['icon'], ENT_QUOTES, 'UTF-8') ?>" alt="">
                        <div>
                            <h3><?= htmlspecialchars($modality['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p><?= htmlspecialchars($modality['text'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <strong>Tenerife Deportes</strong>
        <nav aria-label="Enlaces legales">
            <a href="#">Aviso Legal</a>
            <a href="#">Privacidad</a>
            <a href="#">Cookies</a>
            <a href="#">Accesibilidad</a>
        </nav>
    </footer>
</body>
</html>
