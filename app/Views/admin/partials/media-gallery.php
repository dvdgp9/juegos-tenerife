<?php

declare(strict_types=1);

/**
 * Galería de fotos del panel. La usan entidades e instalaciones con el mismo aspecto.
 *
 * Variables esperadas:
 * @var list<array<string,mixed>> $mediaFiles     Fotos ya guardadas.
 * @var string                    $mediaFeaturedType  'entity_featured_photo' | 'facility_featured_photo'
 * @var string                    $mediaSectionNumber Número que se muestra en el encabezado.
 * @var string                    $mediaSectionTitle
 * @var string                    $mediaSectionHint
 * @var string                    $mediaExtraHtml  Campos propios del formulario (el logo).
 *
 * Funciona sin JavaScript: los campos de orden, destacada y eliminar son controles normales.
 * El guion de admin.js solo añade comodidad encima.
 */

use JuegosTenerife\Services\MediaUploadService;

require_once __DIR__ . '/icons.php';

$mediaFiles = $mediaFiles ?? [];
$mediaFeaturedType = $mediaFeaturedType ?? 'entity_featured_photo';
$mediaSectionNumber = $mediaSectionNumber ?? '05';
$mediaSectionTitle = $mediaSectionTitle ?? 'Fotos';
$mediaSectionHint = $mediaSectionHint ?? 'Se publican en la ficha, en el orden que indiques.';
$mediaExtraHtml = $mediaExtraHtml ?? '';

$e = static fn (mixed $value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');

$mediaCount = count($mediaFiles);
$hasFeatured = false;
foreach ($mediaFiles as $mediaFile) {
    if ((string) ($mediaFile['media_type'] ?? '') === $mediaFeaturedType) {
        $hasFeatured = true;
        break;
    }
}

// Límites reales del servidor, para poder avisar antes de intentar la subida en vez de
// después, cuando PHP ya ha descartado el envío entero.
$toBytes = static function (string $value): int {
    $value = trim($value);
    if ($value === '') {
        return 0;
    }
    $unit = strtolower($value[strlen($value) - 1]);
    $number = (int) $value;

    return match ($unit) {
        'g' => $number * 1024 * 1024 * 1024,
        'm' => $number * 1024 * 1024,
        'k' => $number * 1024,
        default => $number,
    };
};

$maxPost = $toBytes((string) ini_get('post_max_size'));
$maxUpload = min(8 * 1024 * 1024, $toBytes((string) ini_get('upload_max_filesize')) ?: 8 * 1024 * 1024);
?>

<section id="fotos" class="admin-form-section">
    <div class="admin-form-section-head">
        <div>
            <p class="eyebrow"><?= $e($mediaSectionNumber) ?></p>
            <h2><?= $e($mediaSectionTitle) ?></h2>
            <p class="section-hint"><?= $e($mediaSectionHint) ?></p>
        </div>
        <?php if ($mediaCount > 0): ?>
            <p class="media-count"><?= $mediaCount ?> foto<?= $mediaCount === 1 ? '' : 's' ?></p>
        <?php endif; ?>
    </div>

    <?php if ($mediaExtraHtml !== ''): ?>
        <?= $mediaExtraHtml ?>
    <?php endif; ?>

    <div class="media-dropzone"
         data-media-dropzone
         data-max-upload="<?= $maxUpload ?>"
         data-max-post="<?= $maxPost ?>">
        <label class="media-dropzone-label" for="gallery_images">
            <span class="media-dropzone-icon"><?= admin_icon('image-up') ?></span>
            <span class="media-dropzone-text">
                <strong>Añadir fotos</strong>
                <span>Arrastra los archivos aquí o pulsa para elegirlos.</span>
                <small>JPG, PNG, WEBP o GIF. Hasta <?= (int) round($maxUpload / 1048576) ?> MB por foto.</small>
            </span>
        </label>
        <input id="gallery_images"
               name="gallery_images[]"
               type="file"
               multiple
               accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif">
    </div>

    <div class="media-pending" data-media-pending hidden>
        <p class="media-pending-head">
            <strong data-media-pending-count></strong>
            <button type="button" class="button ghost small" data-media-clear>Quitar selección</button>
        </p>
        <ul data-media-pending-list></ul>
        <p class="media-pending-warning" data-media-pending-warning role="alert" hidden></p>
    </div>

    <?php if ($mediaFiles === []): ?>
        <div class="empty-state">
            <strong>Todavía no hay fotos.</strong>
            <span>Las que subas se mostrarán aquí para ordenarlas, titularlas y elegir la destacada.</span>
        </div>
    <?php else: ?>
        <fieldset class="media-featured-reset">
            <legend class="sr-only">Foto destacada</legend>
            <p>La foto destacada encabeza la galería en la ficha pública.</p>
            <label class="media-chip">
                <input type="radio" name="featured_media_id" value="0"<?= $hasFeatured ? '' : ' checked' ?>>
                <span>Sin destacada</span>
            </label>
        </fieldset>

        <div class="media-grid" data-media-grid>
            <?php foreach ($mediaFiles as $position => $media): ?>
                <?php
                $mediaId = (int) ($media['id'] ?? 0);
                $path = (string) ($media['file_path'] ?? '');
                $isFeatured = (string) ($media['media_type'] ?? '') === $mediaFeaturedType;
                $caption = (string) ($media['caption'] ?? '');
                $altText = (string) ($media['alt_text'] ?? '');
                $label = $caption !== '' ? $caption : (string) ($media['original_name'] ?? 'Foto sin título');
                ?>
                <article class="media-card<?= $isFeatured ? ' is-featured' : '' ?>" data-media-card>
                    <div class="media-card-figure">
                        <img src="<?= $e(MediaUploadService::displayThumbnail($path)) ?>"
                             alt="<?= $e($altText !== '' ? $altText : $label) ?>"
                             loading="lazy">
                        <span class="media-badge" data-media-badge<?= $isFeatured ? '' : ' hidden' ?>><?= admin_icon('star') ?> Destacada</span>
                        <span class="media-strike" data-media-strike hidden><?= admin_icon('trash') ?> Se eliminará al guardar</span>
                    </div>

                    <div class="media-card-body">
                        <p class="media-card-name" title="<?= $e($media['original_name'] ?? '') ?>"><?= $e($label) ?></p>

                        <div class="media-card-controls">
                            <label class="media-chip media-chip-featured">
                                <input type="radio"
                                       name="featured_media_id"
                                       value="<?= $mediaId ?>"<?= $isFeatured ? ' checked' : '' ?>>
                                <span><?= admin_icon('star') ?> Destacada</span>
                            </label>

                            <div class="media-order" role="group" aria-label="Orden de la foto">
                                <button type="button"
                                        class="media-order-button"
                                        data-media-move="up"
                                        aria-label="Mover antes"
                                        title="Mover antes"><?= admin_icon('arrow-up') ?></button>
                                <label class="media-order-value">
                                    <span class="sr-only">Orden</span>
                                    <input name="media[<?= $mediaId ?>][sort_order]"
                                           type="number"
                                           min="0"
                                           step="10"
                                           inputmode="numeric"
                                           value="<?= (int) ($media['sort_order'] ?? (($position + 1) * 10)) ?>">
                                </label>
                                <button type="button"
                                        class="media-order-button"
                                        data-media-move="down"
                                        aria-label="Mover después"
                                        title="Mover después"><?= admin_icon('arrow-down') ?></button>
                            </div>
                        </div>

                        <label class="media-field">
                            <span>Título</span>
                            <input name="media[<?= $mediaId ?>][caption]"
                                   type="text"
                                   maxlength="255"
                                   placeholder="Terrero al atardecer"
                                   value="<?= $e($caption) ?>">
                        </label>

                        <label class="media-field">
                            <span>Texto alternativo</span>
                            <input name="media[<?= $mediaId ?>][alt_text]"
                                   type="text"
                                   maxlength="255"
                                   placeholder="Qué se ve en la foto"
                                   value="<?= $e($altText) ?>">
                            <small>Lo leen quienes navegan con lector de pantalla.</small>
                        </label>

                        <label class="media-remove">
                            <input name="delete_media_ids[]" type="checkbox" value="<?= $mediaId ?>" data-media-remove>
                            <span class="media-remove-mark"><?= admin_icon('trash') ?> Eliminar</span>
                            <span class="media-remove-undo"><?= admin_icon('undo') ?> Conservar</span>
                        </label>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
