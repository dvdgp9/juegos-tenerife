<?php
/** @var array<string,mixed> $modality */
/** @var list<string> $errors */
/** @var string $action */
$modality = $modality ?? [];
$errors = $errors ?? [];

$h = static fn(mixed $value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$value = static fn(string $key): string => htmlspecialchars((string) ($modality[$key] ?? ''), ENT_QUOTES, 'UTF-8');
$checked = static fn(string $key): string => ((int) ($modality[$key] ?? 0)) === 1 ? ' checked' : '';

require dirname(__DIR__) . '/partials/layout-start.php';
?>

<section class="admin-page-intro">
    <div>
        <p class="eyebrow">Modalidades</p>
        <h1><?= !empty($modality['id']) ? 'Editar modalidad' : 'Crear modalidad' ?></h1>
        <p>Gestiona la información base y la foto principal que se utilizará como referencia visual.</p>
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
                    <h2>Contenido</h2>
                </div>
            </div>

            <div class="form-grid one">
                <div class="field">
                    <label for="short_description">Descripción corta</label>
                    <textarea id="short_description" name="short_description" rows="3"><?= $value('short_description') ?></textarea>
                </div>
                <div class="field">
                    <label for="full_description">Descripción larga</label>
                    <textarea id="full_description" name="full_description" rows="8"><?= $value('full_description') ?></textarea>
                    <small>Campo preparado para gestión editorial. Las fichas públicas actuales mantienen fallback al contenido ya maquetado.</small>
                </div>
                <div class="field">
                    <label for="extra_info">Información adicional</label>
                    <textarea id="extra_info" name="extra_info" rows="5"><?= $value('extra_info') ?></textarea>
                </div>
            </div>
        </section>

        <section id="imagenes" class="admin-form-section">
            <div class="admin-form-section-head">
                <div>
                    <p class="eyebrow">03</p>
                    <h2>Icono y foto principal</h2>
                </div>
            </div>

            <div class="form-grid two">
                <div class="field">
                    <label for="icon_path">Ruta del icono</label>
                    <input id="icon_path" name="icon_path" type="text" value="<?= $value('icon_path') ?>" placeholder="/assets/images/iconos-deportes/...">
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
