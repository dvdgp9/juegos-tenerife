<?php
/** @var string $slug */
/** @var array<string,mixed> $modality */
$slug = $slug ?? '';
$modality = $modality ?? [];
$sections = $modality['sections'] ?? [];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= htmlspecialchars((string) ($modality['lead'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <title><?= htmlspecialchars((string) ($title ?? $modality['name'] ?? 'Modalidad'), ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="icon" type="image/png" href="/assets/images/favicon.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <header class="site-header">
        <a class="brand" href="/" aria-label="Inicio">
            <img src="/assets/images/logo-dxt-activa-tu-vida.png" alt="Tenerife Deportes">
        </a>
        <nav class="main-nav" aria-label="Navegación principal">
            <a href="/busqueda">Búsqueda Entidades</a>
            <a href="/#sobre-censo">Sobre el Censo</a>
            <a href="/#modalidades" aria-current="page">Modalidades</a>
        </nav>
        <a class="header-action" href="mailto:deportesdetenerife@gmail.com">Comunicar actualización</a>
    </header>

    <main class="modality-page">
        <section class="modality-hero">
            <img src="<?= htmlspecialchars((string) $modality['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string) $modality['image_alt'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="modality-hero-overlay"></div>
            <div class="modality-hero-content">
                <a class="modality-back-link" href="/#modalidades">Modalidades</a>
                <p class="eyebrow">Deportes y juegos tradicionales</p>
                <h1><?= htmlspecialchars((string) $modality['name'], ENT_QUOTES, 'UTF-8') ?></h1>
            </div>
        </section>

        <div class="modality-content-layout">
            <aside class="modality-summary">
                <p><?= htmlspecialchars((string) $modality['lead'], ENT_QUOTES, 'UTF-8') ?></p>
                <a class="button primary" href="/busqueda?modalidad=<?= rawurlencode($slug) ?>">Ver entidades</a>
            </aside>

            <article class="modality-article">
                <?php foreach ($sections as $section): ?>
                    <section>
                        <h2><?= htmlspecialchars((string) $section['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                        <?php foreach (($section['paragraphs'] ?? []) as $paragraph): ?>
                            <p><?= htmlspecialchars((string) $paragraph, ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endforeach; ?>
                        <?php if (!empty($section['items'])): ?>
                            <ul>
                                <?php foreach ($section['items'] as $item): ?>
                                    <li><?= $item ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </section>
                <?php endforeach; ?>

                <div class="modality-next-action">
                    <p class="eyebrow">Censo de Tenerife</p>
                    <h2>Entidades que practican <?= htmlspecialchars((string) $modality['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <a class="button primary" href="/busqueda?modalidad=<?= rawurlencode($slug) ?>">Consultar el censo</a>
                </div>
            </article>
        </div>
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
