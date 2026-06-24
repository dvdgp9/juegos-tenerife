<?php require dirname(__DIR__) . '/partials/layout-start.php'; ?>

<section class="admin-page-intro">
    <div>
        <p class="eyebrow">Administración</p>
        <h1>Modalidades</h1>
        <p>Edita nombres, orden, descripción, iconos y foto principal de cada modalidad.</p>
    </div>
    <div class="admin-form-actions top">
        <a class="button primary" href="/admin/modalities/new">Crear modalidad</a>
    </div>
</section>

<?php if (empty($modalities)): ?>
    <div class="empty-state admin-empty">
        <strong>No hay modalidades cargadas.</strong>
        <span>Crea una modalidad o importa un Excel para empezar.</span>
    </div>
<?php else: ?>
    <div class="admin-table-tools">
        <div>
            <strong><?= count($modalities) ?> modalidades</strong>
            <span>Destacadas primero, después por orden</span>
        </div>
    </div>
    <div class="table-scroll admin-table-wrap admin-entities-table-wrap">
        <table class="preview-table admin-entities-table">
            <thead>
                <tr>
                    <th>Modalidad</th>
                    <th>Icono</th>
                    <th>Foto</th>
                    <th>Orden</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($modalities as $modality): ?>
                    <tr>
                        <td class="entity-name-cell">
                            <strong><?= htmlspecialchars((string) $modality['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <small>/modalidades/<?= htmlspecialchars((string) $modality['slug'], ENT_QUOTES, 'UTF-8') ?></small>
                        </td>
                        <td>
                            <?php if (!empty($modality['icon_path'])): ?>
                                <img class="admin-mini-thumb" src="<?= htmlspecialchars((string) $modality['icon_path'], ENT_QUOTES, 'UTF-8') ?>" alt="">
                            <?php else: ?>
                                <span class="admin-muted-text">Sin icono</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($modality['main_image_path'])): ?>
                                <img class="admin-wide-thumb" src="<?= htmlspecialchars((string) $modality['main_image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="">
                            <?php else: ?>
                                <span class="status-badge draft">Sin foto</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="admin-muted-text"><?= (int) ($modality['sort_order'] ?? 100) ?></span></td>
                        <td><span class="status-badge <?= ((int) $modality['is_featured']) === 1 ? 'success' : 'neutral' ?>"><?= ((int) $modality['is_featured']) === 1 ? 'Destacada' : 'Normal' ?></span></td>
                        <td>
                            <div class="admin-row-actions">
                                <a class="admin-action-button primary" href="/admin/modalities/<?= (int) $modality['id'] ?>/edit">Editar</a>
                                <a class="admin-action-button" href="/modalidades/<?= htmlspecialchars((string) $modality['slug'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Ver ficha</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require dirname(__DIR__) . '/partials/layout-end.php'; ?>
