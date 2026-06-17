<?php
/** @var list<array<string,mixed>> $modalities */
/** @var list<array<string,mixed>> $municipalities */
/** @var list<array<string,mixed>> $entityTypes */
/** @var list<array{modality: array<string,mixed>, entity: array<string,mixed>}> $featuredEntities */
/** @var list<array<string,mixed>> $mapPoints */
/** @var ?string $dbError */
$modalities = $modalities ?? [];
$municipalities = $municipalities ?? [];
$entityTypes = $entityTypes ?? [];
$featuredEntities = $featuredEntities ?? [];
$mapPoints = $mapPoints ?? [];
$dbError = $dbError ?? null;
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Juegos Tenerife', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="icon" type="image/png" href="/assets/images/favicon.png">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="" defer></script>
    <script>window.__mapPoints = <?= json_encode($mapPoints, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>
    <script src="/assets/js/app.js" defer></script>
</head>
<body>
    <header class="site-header">
        <a class="brand" href="/" aria-label="Inicio">
            <img src="/assets/images/logo-dxt-activa-tu-vida.png" alt="Tenerife Deportes">
        </a>
        <nav class="main-nav" aria-label="Navegación principal">
            <a href="/busqueda">Búsqueda Entidades</a>
            <a href="#sobre-censo">Sobre el Censo</a>
            <a href="#modalidades">Modalidades</a>
        </nav>
        <a class="header-action" href="mailto:deportesdetenerife@gmail.com">Comunicar actualización</a>
    </header>

    <main>
        <section class="hero">
            <div class="hero-copy">
                <h1>Censo de Entidades y Colectivos de Deportes y Juegos Motores Tradicionales de Canarias</h1>
                <p>Consulta entidades, colectivos, modalidades e instalaciones vinculadas a los deportes y juegos tradicionales en Tenerife.</p>
            </div>
        </section>

        <section class="search-panel" aria-labelledby="busqueda-title">
            <div class="section-heading compact">
                <p class="eyebrow">Búsqueda Entidades</p>
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
                            <option value="<?= htmlspecialchars((string) $municipality['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $municipality['name'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="tipo">Tipo de Entidad</label>
                    <select id="tipo" name="tipo">
                        <option value="">Cualquier tipo</option>
                        <?php foreach ($entityTypes as $type): ?>
                            <option value="<?= htmlspecialchars((string) $type['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $type['name'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="modalidad">Modalidades Lúdicas</label>
                    <select id="modalidad" name="modalidad">
                        <option value="">Todas las modalidades</option>
                        <?php foreach ($modalities as $modality): ?>
                            <option value="<?= htmlspecialchars((string) $modality['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $modality['name'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="button primary" type="submit" data-default-label="Buscar">Buscar</button>
                <p class="form-status" data-form-status hidden></p>
            </form>
        </section>

        <section class="map-preview">
            <div class="result-stack">
                <?php if ($featuredEntities === []): ?>
                    <article class="result-card">
                        <p class="result-meta">Sin entidades destacadas todavía</p>
                        <h3>Aún no hay entidades publicadas</h3>
                        <p>Cuando se publiquen entidades del censo aparecerán destacadas aquí.</p>
                    </article>
                <?php else: ?>
                    <?php foreach ($featuredEntities as $index => $featured): ?>
                        <?php $entity = $featured['entity']; $modality = $featured['modality']; ?>
                        <article class="result-card<?= $index === 0 ? ' featured' : '' ?>">
                            <p class="result-meta"><?= htmlspecialchars((string) ($entity['municipality'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                            <h3><?= htmlspecialchars((string) $entity['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p>
                                <?= htmlspecialchars((string) $modality['name'], ENT_QUOTES, 'UTF-8') ?>
                                <?php if (!empty($entity['entity_type'])): ?>
                                    · <?= htmlspecialchars((string) $entity['entity_type'], ENT_QUOTES, 'UTF-8') ?>
                                <?php endif; ?>
                            </p>
                            <a href="/entidades/<?= htmlspecialchars((string) $entity['slug'], ENT_QUOTES, 'UTF-8') ?>">Ver ficha</a>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
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
                <h2>Una herramienta para preservar y acercar los Juegos Motores y Deportes tradicionales</h2>
            </div>
            <p>El Cabildo de Tenerife, a través de su Área de Deportes, ha desarrollado un Censo de Entidades y Colectivos de Deportes y Juegos Motores Tradicionales de Canarias en Tenerife con el objetivo de facilitar a la población una herramienta de búsqueda para identificar las entidades y colectivos que desarrollan actividades vinculadas a los deportes y juegos motores tradicionales canarios en su entorno cercano, promoviendo así la visibilidad y el acercamiento de estas modalidades a toda la ciudadanía, especialmente a la población infantil y juvenil, lo que ayudará a mejorar su preservación y arraigo en la Isla.</p>
        </section>

        <section id="modalidades" class="modalities-section">
            <div class="section-heading">
                <p class="eyebrow">Modalidades</p>
            </div>
            <div class="modalities-grid">
                <?php foreach ($modalities as $modality): ?>
                    <article class="modality-card">
                        <?php if (!empty($modality['icon_path'])): ?>
                            <img src="<?= htmlspecialchars((string) $modality['icon_path'], ENT_QUOTES, 'UTF-8') ?>" alt="">
                        <?php endif; ?>
                        <div>
                            <h3><?= htmlspecialchars((string) $modality['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <?php if (!empty($modality['short_description'])): ?>
                                <p><?= htmlspecialchars((string) $modality['short_description'], ENT_QUOTES, 'UTF-8') ?></p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <strong>Área de Deportes Cabildo de Tenerife</strong>
        <nav aria-label="Enlaces legales">
            <a href="#">Aviso Legal</a>
            <a href="#">Privacidad</a>
            <a href="#">Cookies</a>
            <a href="#">Accesibilidad</a>
        </nav>
    </footer>
</body>
</html>
