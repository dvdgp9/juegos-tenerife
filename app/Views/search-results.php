<?php
/** @var array{q:string,municipio:string,tipo:string,modalidad:string} $filters */
/** @var list<array<string,mixed>> $results */
/** @var list<array<string,mixed>> $modalities */
/** @var list<array<string,mixed>> $municipalities */
/** @var list<array<string,mixed>> $entityTypes */
/** @var list<array<string,mixed>> $mapPoints */
/** @var ?string $dbError */
$filters = $filters ?? ['q' => '', 'municipio' => '', 'tipo' => '', 'modalidad' => ''];
$results = $results ?? [];
$modalities = $modalities ?? [];
$municipalities = $municipalities ?? [];
$entityTypes = $entityTypes ?? [];
$mapPoints = $mapPoints ?? [];
$dbError = $dbError ?? null;

$modalityIcons = [];
foreach ($modalities as $m) {
    if (!empty($m['slug'])) {
        $modalityIcons[(string) $m['slug']] = $m['icon_path'] ?? null;
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Resultados de búsqueda', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="" defer></script>
    <script>window.__mapPoints = <?= json_encode($mapPoints, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>
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
            <?php if ($dbError !== null): ?>
                <p>Hubo un problema consultando la base de datos. Inténtalo de nuevo en unos minutos.</p>
            <?php else: ?>
                <p><?= count($results) ?> <?= count($results) === 1 ? 'coincidencia' : 'coincidencias' ?>.</p>
            <?php endif; ?>
        </section>

        <section class="results-layout">
            <aside class="filters-panel" aria-label="Filtros">
                <h2>Filtros</h2>
                <form class="stacked-form" action="/busqueda" method="get">
                    <div class="field">
                        <label for="q">Nombre</label>
                        <input id="q" name="q" type="search" placeholder="Buscar por nombre" value="<?= htmlspecialchars($filters['q'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="field">
                        <label for="municipio">Municipio</label>
                        <select id="municipio" name="municipio">
                            <option value="">Todos los municipios</option>
                            <?php foreach ($municipalities as $municipality): ?>
                                <option value="<?= htmlspecialchars((string) $municipality['slug'], ENT_QUOTES, 'UTF-8') ?>" <?= $filters['municipio'] === $municipality['slug'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $municipality['name'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="tipo">Tipo de Entidad</label>
                        <select id="tipo" name="tipo">
                            <option value="">Cualquier tipo</option>
                            <?php foreach ($entityTypes as $type): ?>
                                <option value="<?= htmlspecialchars((string) $type['slug'], ENT_QUOTES, 'UTF-8') ?>" <?= $filters['tipo'] === $type['slug'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $type['name'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="modalidad">Modalidad</label>
                        <select id="modalidad" name="modalidad">
                            <option value="">Todas las modalidades</option>
                            <?php foreach ($modalities as $modality): ?>
                                <option value="<?= htmlspecialchars((string) $modality['slug'], ENT_QUOTES, 'UTF-8') ?>" <?= $filters['modalidad'] === $modality['slug'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $modality['name'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="button primary" type="submit">Filtrar resultados</button>
                    <a class="button secondary" href="/busqueda">Limpiar</a>
                </form>
            </aside>

            <div class="results-main">
                <div class="results-summary">
                    <strong><?= count($results) ?> <?= count($results) === 1 ? 'coincidencia' : 'coincidencias' ?></strong>
                    <span>Ordenadas por nombre</span>
                </div>

                <?php if ($results === []): ?>
                    <div class="empty-state">
                        <strong>No hay coincidencias para esos filtros.</strong>
                        <span>Prueba con otro municipio, modalidad o tipo de entidad.</span>
                    </div>
                <?php else: ?>
                    <?php foreach ($results as $row): ?>
                        <article class="entity-row">
                            <?php
                            $iconSrc = null;
                            $modalityNames = (string) ($row['modalities'] ?? '');
                            if ($modalityNames !== '') {
                                $firstModality = trim(explode(',', $modalityNames)[0]);
                                foreach ($modalities as $m) {
                                    if ($m['name'] === $firstModality && !empty($m['icon_path'])) {
                                        $iconSrc = (string) $m['icon_path'];
                                        break;
                                    }
                                }
                            }
                            ?>
                            <?php if ($iconSrc !== null): ?>
                                <img src="<?= htmlspecialchars($iconSrc, ENT_QUOTES, 'UTF-8') ?>" alt="">
                            <?php endif; ?>
                            <div>
                                <p class="result-meta">
                                    <?= htmlspecialchars((string) ($row['entity_type'] ?? 'Entidad'), ENT_QUOTES, 'UTF-8') ?>
                                    <?php if (!empty($row['municipality'])): ?>
                                        · <?= htmlspecialchars((string) $row['municipality'], ENT_QUOTES, 'UTF-8') ?>
                                    <?php endif; ?>
                                </p>
                                <h2><?= htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                                <?php if (!empty($row['modalities'])): ?>
                                    <p><?= htmlspecialchars((string) $row['modalities'], ENT_QUOTES, 'UTF-8') ?></p>
                                <?php endif; ?>
                            </div>
                            <a class="button secondary" href="/entidades/<?= htmlspecialchars((string) $row['slug'], ENT_QUOTES, 'UTF-8') ?>">Ver ficha</a>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="map-shell results-map" aria-label="Mapa de resultados" data-map="results">
                <div class="map-fallback">
                    <div class="map-island">
                        <span class="pin pin-1"></span>
                        <span class="pin pin-3"></span>
                    </div>
                </div>
                <div class="map-legend">
                    <strong>Ubicaciones</strong>
                    <span><?= count($mapPoints) ?> instalación<?= count($mapPoints) === 1 ? '' : 'es' ?> en el mapa</span>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
