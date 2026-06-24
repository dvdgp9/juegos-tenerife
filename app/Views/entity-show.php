<?php
/** @var array<string,mixed> $entity */
/** @var list<array<string,mixed>> $mapPoints */
$entity = $entity ?? [];
$mapPoints = $mapPoints ?? [];

$modalities = $entity['modalities'] ?? [];
$contacts = $entity['contacts'] ?? [];
$socialLinks = $entity['social_links'] ?? [];
$facilities = $entity['facilities'] ?? [];
$ageRanges = $entity['age_ranges'] ?? [];

$modalityIcons = [];
foreach ($modalities as $m) {
    $modalityIcons[] = [
        'src' => !empty($m['icon_path']) ? (string) $m['icon_path'] : null,
        'name' => (string) ($m['name'] ?? ''),
    ];
}

$modalityInitials = static function (string $name): string {
    $words = preg_split('/\s+/', trim($name)) ?: [];
    $letters = '';
    foreach ($words as $word) {
        $normalized = function_exists('mb_strtolower') ? mb_strtolower($word, 'UTF-8') : strtolower($word);
        if ($word === '' || in_array($normalized, ['de', 'del', 'y', 'la', 'el'], true)) {
            continue;
        }
        $initial = function_exists('mb_substr') ? mb_substr($word, 0, 1, 'UTF-8') : substr($word, 0, 1);
        $letters .= function_exists('mb_strtoupper') ? mb_strtoupper($initial, 'UTF-8') : strtoupper($initial);
        $length = function_exists('mb_strlen') ? mb_strlen($letters, 'UTF-8') : strlen($letters);
        if ($length >= 3) {
            break;
        }
    }

    return $letters !== '' ? $letters : 'M';
};

$phones = [];
$emails = [];
$contactPeople = [];
foreach ($contacts as $c) {
    if (!empty($c['phone'])) { $phones[] = (string) $c['phone']; }
    if (!empty($c['email'])) { $emails[] = (string) $c['email']; }
    if (!empty($c['person_name'])) { $contactPeople[] = $c; }
}
$phones = array_values(array_unique($phones));
$emails = array_values(array_unique($emails));

$addressParts = array_filter([
    $entity['address'] ?? null,
    $entity['locality'] ?? null,
    $entity['municipality'] ?? null,
    $entity['postal_code'] ?? null,
]);

$protocolLabel = static function (?string $status): string {
    if ($status === null || $status === '') { return 'Sin datos'; }

    return match ($status) {
        'yes_own' => 'Sí, propio',
        'yes_external' => 'Sí',
        'in_progress' => 'En proceso',
        'no' => 'No',
        default => 'Sin datos',
    };
};
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars((string) ($title ?? $entity['name'] ?? 'Ficha de Entidad'), ENT_QUOTES, 'UTF-8') ?></title>
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
            <a href="/#sobre-censo">Sobre el Censo</a>
            <a href="/#modalidades">Modalidades</a>
        </nav>
        <a class="header-action" href="mailto:deportesdetenerife@gmail.com">Comunicar actualización</a>
    </header>

    <main class="entity-page">
        <section class="entity-hero">
            <div class="entity-logo">
                <?php if (!empty($entity['logo_path'])): ?>
                    <img src="<?= htmlspecialchars((string) $entity['logo_path'], ENT_QUOTES, 'UTF-8') ?>" alt="">
                <?php elseif ($modalityIcons !== []): ?>
                    <div class="modality-mosaic modality-mosaic-large modality-mosaic-count-<?= min(count($modalityIcons), 6) ?>" aria-label="Pictogramas de modalidades">
                        <?php foreach ($modalityIcons as $icon): ?>
                            <?php if ($icon['src'] !== null): ?>
                                <img src="<?= htmlspecialchars($icon['src'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($icon['name'], ENT_QUOTES, 'UTF-8') ?>">
                            <?php else: ?>
                                <span title="<?= htmlspecialchars($icon['name'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($modalityInitials($icon['name']), ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="entity-title">
                <p class="eyebrow">
                    Ficha de
                    <?= htmlspecialchars((string) ($entity['entity_type'] ?? 'Entidad'), ENT_QUOTES, 'UTF-8') ?>
                </p>
                <h1><?= htmlspecialchars((string) $entity['name'], ENT_QUOTES, 'UTF-8') ?></h1>
                <?php if ($modalities !== []): ?>
                    <p class="modality-list">
                        <?php
                        $names = array_map(static fn($m) => (string) $m['name'], $modalities);
                        echo htmlspecialchars(implode(' · ', $names), ENT_QUOTES, 'UTF-8');
                        ?>
                    </p>
                <?php endif; ?>
                <dl class="contact-list">
                    <?php if ($addressParts !== []): ?>
                        <div>
                            <dt>Domicilio</dt>
                            <dd><?= htmlspecialchars(implode(', ', $addressParts), ENT_QUOTES, 'UTF-8') ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if ($phones !== []): ?>
                        <div>
                            <dt>Teléfono</dt>
                            <dd><?= htmlspecialchars(implode(' · ', $phones), ENT_QUOTES, 'UTF-8') ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if ($emails !== []): ?>
                        <div>
                            <dt>Correo electrónico</dt>
                            <dd>
                                <?php foreach ($emails as $i => $email): ?>
                                    <?php if ($i > 0): ?> · <?php endif; ?>
                                    <a href="mailto:<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></a>
                                <?php endforeach; ?>
                            </dd>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($entity['website_url'])): ?>
                        <div>
                            <dt>Web</dt>
                            <dd><a href="<?= htmlspecialchars((string) $entity['website_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"><?= htmlspecialchars((string) $entity['website_url'], ENT_QUOTES, 'UTF-8') ?></a></dd>
                        </div>
                    <?php endif; ?>
                    <?php if ($socialLinks !== []): ?>
                        <div>
                            <dt>Redes Sociales</dt>
                            <dd>
                                <?php foreach ($socialLinks as $i => $link): ?>
                                    <?php if ($i > 0): ?> · <?php endif; ?>
                                    <a href="<?= htmlspecialchars((string) $link['url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"><?= htmlspecialchars((string) ($link['label'] ?: $link['platform']), ENT_QUOTES, 'UTF-8') ?></a>
                                <?php endforeach; ?>
                            </dd>
                        </div>
                    <?php endif; ?>
                </dl>
            </div>
        </section>

        <section class="entity-layout">
            <div class="entity-content">
                <?php if (!empty($entity['history']) || !empty($entity['corporate_principles']) || !empty($entity['sports_values'])): ?>
                    <section class="detail-section">
                        <p class="eyebrow">Información General</p>
                        <h2>Trayectoria, principios y valores</h2>
                        <div class="info-columns entity-general-info">
                            <?php if (!empty($entity['history'])): ?>
                                <article>
                                    <h3>Breve historia / trayectoria</h3>
                                    <p><?= nl2br(htmlspecialchars((string) $entity['history'], ENT_QUOTES, 'UTF-8')) ?></p>
                                </article>
                            <?php endif; ?>
                            <?php if (!empty($entity['corporate_principles'])): ?>
                                <article>
                                    <h3>Principios corporativos</h3>
                                    <p><?= nl2br(htmlspecialchars((string) $entity['corporate_principles'], ENT_QUOTES, 'UTF-8')) ?></p>
                                </article>
                            <?php endif; ?>
                            <?php if (!empty($entity['sports_values'])): ?>
                                <article>
                                    <h3>Valores deportivos</h3>
                                    <p><?= nl2br(htmlspecialchars((string) $entity['sports_values'], ENT_QUOTES, 'UTF-8')) ?></p>
                                </article>
                            <?php endif; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if ($modalities !== []): ?>
                    <section class="detail-section">
                        <p class="eyebrow">Modalidades Lúdicas</p>
                        <div class="chips">
                            <?php foreach ($modalities as $m): ?>
                                <span><?= htmlspecialchars((string) $m['name'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <?php
                $hasMetrics = !empty($entity['total_teams']) || !empty($entity['total_practitioners'])
                    || !empty($entity['training_days']) || !empty($entity['training_hours']) || $facilities !== [];
                ?>
                <?php if ($hasMetrics): ?>
                    <section class="detail-section">
                        <p class="eyebrow">Características</p>
                        <div class="metrics-grid">
                            <?php if (!empty($entity['total_teams'])): ?>
                                <article>
                                    <span>Equipos</span>
                                    <strong><?= htmlspecialchars((string) $entity['total_teams'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <?php if (!empty($entity['teams_by_gender']) || !empty($entity['teams_by_age'])): ?>
                                        <small><?= htmlspecialchars(trim(($entity['teams_by_gender'] ?? '') . ' · ' . ($entity['teams_by_age'] ?? ''), ' ·'), ENT_QUOTES, 'UTF-8') ?></small>
                                    <?php endif; ?>
                                </article>
                            <?php endif; ?>
                            <?php if (!empty($entity['total_practitioners'])): ?>
                                <article>
                                    <span>Deportistas / Practicantes</span>
                                    <strong><?= htmlspecialchars((string) $entity['total_practitioners'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <?php if (!empty($entity['male_practitioners']) || !empty($entity['female_practitioners'])): ?>
                                        <small><?= (int) $entity['male_practitioners'] ?> hombres · <?= (int) $entity['female_practitioners'] ?> mujeres</small>
                                    <?php endif; ?>
                                </article>
                            <?php endif; ?>
                            <?php if ($facilities !== []): ?>
                                <article>
                                    <span>Espacios deportivos</span>
                                    <strong><?= count($facilities) ?></strong>
                                    <small>Instalaciones vinculadas</small>
                                </article>
                            <?php endif; ?>
                            <?php if (!empty($entity['training_days']) || !empty($entity['training_hours'])): ?>
                                <article>
                                    <span>Entrenamientos | Prácticas</span>
                                    <strong><?= htmlspecialchars((string) ($entity['training_days'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></strong>
                                    <?php if (!empty($entity['training_hours'])): ?>
                                        <small><?= htmlspecialchars((string) $entity['training_hours'], ENT_QUOTES, 'UTF-8') ?></small>
                                    <?php endif; ?>
                                </article>
                            <?php endif; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if ($facilities !== []): ?>
                    <section class="detail-section">
                        <p class="eyebrow">Instalaciones</p>
                        <ul>
                            <?php foreach ($facilities as $f): ?>
                                <li>
                                    <strong><?= htmlspecialchars((string) $f['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <?php if (!empty($f['address']) || !empty($f['locality'])): ?>
                                        — <?= htmlspecialchars(trim(($f['address'] ?? '') . ', ' . ($f['locality'] ?? ''), ', '), ENT_QUOTES, 'UTF-8') ?>
                                    <?php endif; ?>
                                    <?php if (!empty($f['google_maps_url'])): ?>
                                        · <a href="<?= htmlspecialchars((string) $f['google_maps_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Ver en Maps</a>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endif; ?>

                <?php if ($ageRanges !== []): ?>
                    <section class="detail-section">
                        <p class="eyebrow">Tramos de edad</p>
                        <ul>
                            <?php foreach ($ageRanges as $age): ?>
                                <li><?= htmlspecialchars((string) $age['label'], ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars((string) ($age['raw_value'] ?? $age['practitioners_count'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endif; ?>

                <?php if ($mapPoints !== []): ?>
                    <section class="detail-section">
                        <p class="eyebrow">Mapa</p>
                        <div class="map-shell entity-map" aria-label="Mapa de la entidad" data-map="entity">
                            <div class="map-fallback">
                                <div class="map-island">
                                    <span class="pin pin-2"></span>
                                </div>
                            </div>
                            <div class="map-legend">
                                <strong><?= htmlspecialchars((string) $entity['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <span><?= count($mapPoints) ?> ubicación<?= count($mapPoints) === 1 ? '' : 'es' ?></span>
                            </div>
                        </div>
                    </section>
                <?php endif; ?>
            </div>

            <aside class="entity-sidebar">
                <?php if ($contactPeople !== []): ?>
                    <details open>
                        <summary>Contacto</summary>
                        <?php foreach ($contactPeople as $person): ?>
                            <p>
                                <strong><?= htmlspecialchars((string) $person['person_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <?php if (!empty($person['role_title'])): ?>
                                    <br><?= htmlspecialchars((string) $person['role_title'], ENT_QUOTES, 'UTF-8') ?>
                                <?php endif; ?>
                                <?php if (!empty($person['phone'])): ?>
                                    <br>Tel.: <?= htmlspecialchars((string) $person['phone'], ENT_QUOTES, 'UTF-8') ?>
                                <?php endif; ?>
                                <?php if (!empty($person['email'])): ?>
                                    <br><a href="mailto:<?= htmlspecialchars((string) $person['email'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $person['email'], ENT_QUOTES, 'UTF-8') ?></a>
                                <?php endif; ?>
                            </p>
                        <?php endforeach; ?>
                    </details>
                <?php endif; ?>

                <details>
                    <summary>Información Complementaria</summary>
                    <ul>
                        <?php if (($entity['has_board'] ?? null) !== null): ?>
                            <li>Directiva: <?= $entity['has_board'] ? 'Sí' : 'No' ?></li>
                        <?php endif; ?>
                        <?php if (!empty($entity['board_members'])): ?>
                            <li>
                                Miembros directiva: <?= (int) $entity['board_members'] ?>
                                <?php if (($entity['board_male'] ?? null) !== null || ($entity['board_female'] ?? null) !== null): ?>
                                    (<?= (int) ($entity['board_male'] ?? 0) ?> hombres / <?= (int) ($entity['board_female'] ?? 0) ?> mujeres)
                                <?php endif; ?>
                            </li>
                        <?php endif; ?>
                        <?php if (($entity['holds_annual_assemblies'] ?? null) !== null): ?>
                            <li>Asambleas anuales: <?= $entity['holds_annual_assemblies'] ? 'Sí' : 'No' ?></li>
                        <?php endif; ?>
                        <?php if (!empty($entity['total_members'])): ?>
                            <li>Socios/as: <?= htmlspecialchars((string) $entity['total_members'], ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endif; ?>
                        <li>Protocolo de Igualdad: <?= htmlspecialchars($protocolLabel($entity['equality_protocol_status'] ?? null), ENT_QUOTES, 'UTF-8') ?></li>
                        <li>Protocolo de Actuación contra la Violencia: <?= htmlspecialchars($protocolLabel($entity['violence_protocol_status'] ?? null), ENT_QUOTES, 'UTF-8') ?></li>
                        <li>Cumple con LOPIVI: <?= htmlspecialchars($protocolLabel($entity['lopivi_status'] ?? null), ENT_QUOTES, 'UTF-8') ?></li>
                        <?php if (($entity['supports_educational_needs'] ?? null) !== null): ?>
                            <li>Atiende a deportistas/practicantes con Necesidades de Apoyo Educativo: <?= $entity['supports_educational_needs'] ? 'Sí' : 'No' ?></li>
                        <?php endif; ?>
                        <?php if (($entity['supports_disability'] ?? null) !== null): ?>
                            <li>Atiende a deportistas/practicantes con Discapacidad: <?= $entity['supports_disability'] ? 'Sí' : 'No' ?></li>
                        <?php endif; ?>
                        <?php if (($entity['joined_educar_entrenando'] ?? null) !== null): ?>
                            <li>Adscrito al programa Educar Entrenando: <?= $entity['joined_educar_entrenando'] ? 'Sí' : 'No' ?></li>
                        <?php endif; ?>
                    </ul>
                </details>
            </aside>
        </section>
    </main>
</body>
</html>
