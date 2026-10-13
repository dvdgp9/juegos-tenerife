<?php
/** @var array<string,mixed> $modality */
/** @var list<string> $errors */
/** @var string $action */
$modality = $modality ?? [];
$errors = $errors ?? [];

$h = static fn(mixed $value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$value = static fn(string $key): string => htmlspecialchars((string) ($modality[$key] ?? ''), ENT_QUOTES, 'UTF-8');
$pageSections = json_decode((string) ($modality['page_sections'] ?? '[]'), true) ?: [];
$sectionFields = static function (array $section, string $index) use ($h): void {
    ?>
    <div class="admin-form-section" data-page-section>
        <div class="form-grid one">
            <div class="field">
                <label for="page_section_<?= $h($index) ?>_title">Título del apartado</label>
                <input id="page_section_<?= $h($index) ?>_title" data-section-field="title" name="page_sections[<?= $h($index) ?>][title]" type="text" value="<?= $h($section['title'] ?? '') ?>">
            </div>
            <div class="field">
                <label for="page_section_<?= $h($index) ?>_paragraphs">Párrafos</label>
                <textarea id="page_section_<?= $h($index) ?>_paragraphs" data-section-field="paragraphs" name="page_sections[<?= $h($index) ?>][paragraphs]" rows="7"><?= $h(implode("\n\n", $section['paragraphs'] ?? [])) ?></textarea>
                <small>Separa los párrafos con una línea en blanco.</small>
            </div>
            <div class="field">
                <label for="page_section_<?= $h($index) ?>_items">Lista de elementos (opcional)</label>
                <textarea id="page_section_<?= $h($index) ?>_items" data-section-field="items" name="page_sections[<?= $h($index) ?>][items]" rows="4"><?= $h(implode("\n", $section['items'] ?? [])) ?></textarea>
                <small>Un elemento por línea. Puedes usar **texto** para destacar una parte en negrita.</small>
            </div>
        </div>
        <button class="button secondary" type="button" data-remove-page-section>Eliminar apartado</button>
    </div>
    <?php
};
$checked = static fn(string $key): string => ((int) ($modality[$key] ?? 0)) === 1 ? ' checked' : '';

require dirname(__DIR__) . '/partials/layout-start.php';
?>

<section class="admin-page-intro">
    <div>
        <p class="eyebrow">Modalidades</p>
        <h1><?= !empty($modality['id']) ? 'Editar modalidad' : 'Crear modalidad' ?></h1>
        <p>Edita los textos de la ficha pública, el icono y la foto principal de la modalidad.</p>
    </div>
    <div class="admin-form-actions top">
        <a class="button secondary" href="/admin/modalities">Volver al listado</a>
        <?php if (!empty($modality['slug'])): ?>
            <a class="button secondary" href="/modalidades/<?= $h($modality['slug']) ?>" target="_blank" rel="noopener">Ver ficha</a>
        <?php endif; ?>
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

<form class="admin-entity-form" action="<?= $h($action) ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="_csrf" value="<?= $h($csrf ?? '') ?>">

    <aside class="admin-form-rail" aria-label="Secciones de la modalidad">
        <a href="#datos">Datos</a>
        <a href="#contenido">Contenido</a>
        <a href="#ficha-publica">Ficha pública</a>
        <a href="#imagenes">Imágenes</a>
    </aside>

    <div class="admin-form-main">
        <section id="datos" class="admin-form-section">
            <div class="admin-form-section-head">
                <div>
                    <p class="eyebrow">01</p>
                    <h2>Datos principales</h2>
                </div>
                <label class="switch-field">
                    <input name="is_featured" type="checkbox" value="1"<?= $checked('is_featured') ?>>
                    <span>Destacada</span>
                </label>
            </div>

            <div class="form-grid two">
                <div class="field wide">
                    <label for="name">Nombre</label>
                    <input id="name" name="name" type="text" value="<?= $value('name') ?>" required>
                </div>
                <div class="field">
                    <label for="slug">Slug</label>
                    <input id="slug" name="slug" type="text" value="<?= $value('slug') ?>" placeholder="se-genera-si-lo-dejas-vacio">
                </div>
                <div class="field">
                    <label for="sort_order">Orden</label>
                    <input id="sort_order" name="sort_order" type="number" min="0" value="<?= $value('sort_order') ?>">
                </div>
            </div>
        </section>

        <section id="contenido" class="admin-form-section">
            <div class="admin-form-section-head">
                <div>
                    <p class="eyebrow">02</p>
                    <h2>Descripciones del censo</h2>
                </div>
            </div>

            <div class="form-grid one">
                <div class="field">
                    <label for="short_description">Descripción corta</label>
                    <textarea id="short_description" name="short_description" rows="3"><?= $value('short_description') ?></textarea>
                    <small>Resumen de la tarjeta de modalidad en la página de inicio.</small>
                </div>
                <div class="field">
                    <label for="full_description">Descripción larga</label>
                    <textarea id="full_description" name="full_description" rows="8"><?= $value('full_description') ?></textarea>
                    <small>Texto guardado para uso editorial. Los textos publicados en la página de la modalidad se editan en «Ficha pública».</small>
                </div>
                <div class="field">
                    <label for="extra_info">Información adicional</label>
                    <textarea id="extra_info" name="extra_info" rows="5"><?= $value('extra_info') ?></textarea>
                    <small>Información interna para la gestión editorial.</small>
                </div>
            </div>
        </section>

        <section id="ficha-publica" class="admin-form-section">
            <div class="admin-form-section-head">
                <div>
                    <p class="eyebrow">03</p>
                    <h2>Ficha pública</h2>
                    <p>Estos textos aparecen en la página de la modalidad. Los apartados se publican en el orden mostrado.</p>
                </div>
            </div>
            <input type="hidden" name="page_content_present" value="1">
            <div class="field">
                <label for="page_lead">Introducción de la ficha</label>
                <textarea id="page_lead" name="page_lead" rows="6"><?= $value('page_lead') ?></textarea>
                <small>Texto que aparece junto al botón «Ver entidades».</small>
            </div>
            <div data-page-sections>
                <?php foreach ($pageSections as $index => $section): ?>
                    <?php $sectionFields($section, (string) $index); ?>
                <?php endforeach; ?>
            </div>
            <template id="page-section-template"><?php $sectionFields([], '__INDEX__'); ?></template>
            <button class="button secondary" type="button" data-add-page-section>Añadir apartado</button>
        </section>

        <section id="imagenes" class="admin-form-section">
            <div class="admin-form-section-head">
                <div>
                    <p class="eyebrow">04</p>
                    <h2>Icono y foto principal</h2>
                </div>
            </div>

            <div class="form-grid two">
                <div class="field">
                    <label for="icon">Subir icono</label>
                    <input id="icon" name="icon" type="file" accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif">
                    <small>PNG, JPG, WEBP o GIF. Máximo 8 MB. Recomendado: PNG con fondo transparente. Al subir uno, se sustituye el icono actual.</small>
                    <?php if (!empty($modality['icon_path'])): ?>
                        <div class="admin-current-media">
                            <img src="<?= $h($modality['icon_path']) ?>" alt="">
                            <span>Icono actual</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="field">
                    <label for="main_image">Foto principal</label>
                    <input id="main_image" name="main_image" type="file" accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif">
                    <small>JPG, PNG, WEBP o GIF. Máximo 8 MB.</small>
                </div>
                <div class="field wide">
                    <label for="main_image_alt">Texto alternativo de la foto</label>
                    <input id="main_image_alt" name="main_image_alt" type="text" value="<?= $value('main_image_alt') ?>">
                </div>
                <?php if (!empty($modality['main_image_path'])): ?>
                    <div class="field wide">
                        <div class="admin-current-media wide">
                            <img src="<?= $h($modality['main_image_path']) ?>" alt="">
                            <span>Foto principal actual</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <div class="admin-form-submit">
            <a class="button secondary" href="/admin/modalities">Cancelar</a>
            <button class="button primary" type="submit">Guardar modalidad</button>
        </div>
    </div>
</form>

<?php require dirname(__DIR__) . '/partials/layout-end.php'; ?>
