<?php
/** @var array<string,mixed> $facility */
/** @var list<string> $errors */
/** @var string $action */
/** @var list<array<string,mixed>> $municipalities */
/** @var list<array<string,mixed>> $entities */
$facility = $facility ?? [];
$errors = $errors ?? [];
$municipalities = $municipalities ?? [];
$entities = $entities ?? [];

$h = static fn(mixed $value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$value = static fn(string $key): string => htmlspecialchars((string) ($facility[$key] ?? ''), ENT_QUOTES, 'UTF-8');
$selected = static fn(string $key, mixed $candidate): string => (string) ($facility[$key] ?? '') === (string) $candidate ? ' selected' : '';
$checkedEntity = static fn(mixed $id): string => in_array((int) $id, array_map('intval', (array) ($facility['entity_ids'] ?? [])), true) ? ' checked' : '';

require dirname(__DIR__) . '/partials/layout-start.php';
?>

<section class="admin-page-intro">
    <div>
        <p class="eyebrow">Instalaciones</p>
        <h1><?= !empty($facility['id']) ? 'Editar instalación' : 'Crear instalación' ?></h1>
        <p>Completa ubicación, coordenadas y entidades vinculadas. Estos datos alimentan mapas y fichas públicas.</p>
    </div>
    <div class="admin-form-actions top">
        <a class="button secondary" href="/admin/facilities">Volver al listado</a>
    </div>
</section>

<?php if ($errors !== []): ?>
    <div class="alert error" role="alert">
        <strong>Revisa el formulario</strong>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= $h($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form class="admin-entity-form" action="<?= $h($action) ?>" method="post">
    <input type="hidden" name="_csrf" value="<?= $h($csrf ?? '') ?>">

    <aside class="admin-form-rail" aria-label="Secciones de la instalación">
        <a href="#datos">Datos</a>
        <a href="#mapa">Mapa</a>
        <a href="#entidades">Entidades</a>
        <a href="#notas">Notas</a>
    </aside>

    <div class="admin-form-main">
        <section id="datos" class="admin-form-section">
            <div class="admin-form-section-head">
                <div>
                    <p class="eyebrow">01</p>
                    <h2>Datos principales</h2>
                </div>
            </div>

            <div class="form-grid two">
                <div class="field wide">
                    <label for="name">Nombre de la instalación</label>
                    <input id="name" name="name" type="text" value="<?= $value('name') ?>" required>
                </div>
                <div class="field">
                    <label for="slug">Slug</label>
                    <input id="slug" name="slug" type="text" value="<?= $value('slug') ?>" placeholder="se-genera-si-lo-dejas-vacio">
                </div>
                <div class="field">
                    <label for="municipality_id">Municipio</label>
                    <select id="municipality_id" name="municipality_id">
                        <option value="">Sin municipio</option>
                        <?php foreach ($municipalities as $municipality): ?>
                            <option value="<?= (int) $municipality['id'] ?>"<?= $selected('municipality_id', $municipality['id']) ?>><?= $h($municipality['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field wide">
                    <label for="address">Dirección</label>
                    <input id="address" name="address" type="text" value="<?= $value('address') ?>">
                </div>
                <div class="field">
                    <label for="locality">Localidad</label>
                    <input id="locality" name="locality" type="text" value="<?= $value('locality') ?>">
                </div>
                <div class="field">
                    <label for="postal_code">Código postal</label>
                    <input id="postal_code" name="postal_code" type="text" value="<?= $value('postal_code') ?>">
                </div>
            </div>
        </section>

        <section id="mapa" class="admin-form-section">
            <div class="admin-form-section-head">
                <div>
                    <p class="eyebrow">02</p>
                    <h2>Mapa y coordenadas</h2>
                </div>
            </div>

            <div class="form-grid two">
                <div class="field wide">
                    <label for="google_maps_url">Enlace de Google Maps</label>
                    <input id="google_maps_url" name="google_maps_url" type="url" value="<?= $value('google_maps_url') ?>">
                </div>
                <div class="field">
                    <label for="latitude">Latitud</label>
                    <input id="latitude" name="latitude" type="text" inputmode="decimal" value="<?= $value('latitude') ?>">
                </div>
                <div class="field">
                    <label for="longitude">Longitud</label>
                    <input id="longitude" name="longitude" type="text" inputmode="decimal" value="<?= $value('longitude') ?>">
                </div>
                <div class="field">
                    <label for="geocoding_status">Estado</label>
                    <select id="geocoding_status" name="geocoding_status">
                        <?php foreach (['pending' => 'Pendiente', 'resolved' => 'Resuelta', 'manual' => 'Manual', 'failed' => 'Fallida'] as $key => $label): ?>
                            <option value="<?= $h($key) ?>"<?= $selected('geocoding_status', $key) ?>><?= $h($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </section>

        <section id="entidades" class="admin-form-section">
            <div class="admin-form-section-head">
                <div>
                    <p class="eyebrow">03</p>
                    <h2>Entidades vinculadas</h2>
                </div>
            </div>

            <?php if ($entities === []): ?>
                <div class="empty-state">
                    <strong>No hay entidades disponibles.</strong>
                    <span>Crea o importa entidades antes de vincular instalaciones.</span>
                </div>
            <?php else: ?>
                <div class="admin-check-grid facility-entity-grid">
                    <?php foreach ($entities as $entity): ?>
                        <label>
                            <input name="entity_ids[]" type="checkbox" value="<?= (int) $entity['id'] ?>"<?= $checkedEntity($entity['id']) ?>>
                            <span>
                                <?= $h($entity['name']) ?>
                                <?php if (!empty($entity['municipality'])): ?>
                                    <small><?= $h($entity['municipality']) ?></small>
                                <?php endif; ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section id="notas" class="admin-form-section">
            <div class="admin-form-section-head">
                <div>
                    <p class="eyebrow">04</p>
                    <h2>Notas internas</h2>
                </div>
            </div>
            <div class="field">
                <label for="notes">Notas</label>
                <textarea id="notes" name="notes" rows="5"><?= $value('notes') ?></textarea>
            </div>
        </section>

        <div class="admin-form-submit">
            <a class="button secondary" href="/admin/facilities">Cancelar</a>
            <button class="button primary" type="submit">Guardar instalación</button>
        </div>
    </div>
</form>

<?php require dirname(__DIR__) . '/partials/layout-end.php'; ?>
