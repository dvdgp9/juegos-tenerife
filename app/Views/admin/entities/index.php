<?php require dirname(__DIR__) . '/partials/layout-start.php'; ?>

<section class="admin-page-intro">
    <div>
        <p class="eyebrow">Administración</p>
        <h1>Entidades</h1>
        <p>Listado de entidades importadas o creadas manualmente. Edita cualquier ficha, publícala o archívala cuando deje de tener uso.</p>
    </div>
    <div class="admin-form-actions top">
        <a class="button secondary" href="/admin/import">Importar nuevas</a>
        <a class="button primary" href="/admin/entities/new">Crear entidad</a>
    </div>
</section>

<?php if (!empty($error)): ?>
    <div class="alert error" role="alert"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if (empty($entities) && empty($error)): ?>
    <div class="empty-state admin-empty">
        <strong>No hay entidades cargadas.</strong>
        <span>Importa un Excel o crea una entidad manualmente.</span>
    </div>
<?php endif; ?>

<?php if (!empty($entities)): ?>
    <div class="admin-table-tools">
        <div>
            <strong><?= count($entities) ?> entidad<?= count($entities) === 1 ? '' : 'es' ?></strong>
            <span>Ordenadas por última actualización</span>
        </div>
    </div>
    <div class="table-scroll admin-table-wrap admin-entities-table-wrap">
        <table class="preview-table admin-entities-table">
            <thead>
                <tr>
                    <th>Entidad</th>
                    <th>Tipo</th>
                    <th>Municipio</th>
                    <th>Modalidades</th>
                    <th>Geo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entities as $entity): ?>
                    <?php
                    $modalities = array_values(array_filter(array_map('trim', explode(',', (string) ($entity['modalities'] ?? '')))));
                    $geoStatus = (string) ($entity['geocoding_status'] ?? '');
                    $isPublished = ((int) $entity['is_published']) === 1;
                    ?>
                    <tr>
                        <td class="entity-name-cell">
                            <strong><?= htmlspecialchars((string) $entity['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <small>/entidades/<?= htmlspecialchars((string) $entity['slug'], ENT_QUOTES, 'UTF-8') ?></small>
                        </td>
                        <td>
                            <span class="admin-muted-text"><?= htmlspecialchars((string) ($entity['entity_type'] ?? 'Sin tipo'), ENT_QUOTES, 'UTF-8') ?></span>
                            <?php if (!empty($entity['entity_subtype'])): ?>
                                <small><?= htmlspecialchars((string) $entity['entity_subtype'], ENT_QUOTES, 'UTF-8') ?></small>
                            <?php endif; ?>
                        </td>
                        <td><span class="admin-muted-text"><?= htmlspecialchars((string) ($entity['municipality'] ?? 'Sin municipio'), ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td>
                            <?php if ($modalities === []): ?>
                                <span class="admin-muted-text">Sin modalidades</span>
                            <?php else: ?>
                                <div class="admin-chip-list">
                                    <?php foreach (array_slice($modalities, 0, 3) as $modality): ?>
                                        <span><?= htmlspecialchars($modality, ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($modalities) > 3): ?>
                                        <span>+<?= count($modalities) - 3 ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><span class="status-badge <?= $geoStatus === 'resolved' ? 'success' : 'neutral' ?>"><?= htmlspecialchars($geoStatus !== '' ? $geoStatus : 'pending', ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td><span class="status-badge <?= $isPublished ? 'success' : 'draft' ?>"><?= $isPublished ? 'Publicada' : 'Borrador' ?></span></td>
                        <td>
                            <div class="admin-row-actions">
                                <a class="admin-action-button primary" href="/admin/entities/<?= (int) $entity['id'] ?>/edit"><?= admin_icon('edit') ?>Editar</a>
                                <a class="admin-action-button" href="/entidades/<?= htmlspecialchars((string) $entity['slug'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"><?= admin_icon('external') ?>Ver ficha</a>
                                <form class="admin-inline-form" action="/admin/entities/<?= (int) $entity['id'] ?>/delete" method="post" data-confirm="Se archivará &quot;<?= htmlspecialchars((string) $entity['name'], ENT_QUOTES, 'UTF-8') ?>&quot;. Dejará de verse en la web pública y en este listado. ¿Continuar?">
                                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrf, ENT_QUOTES, 'UTF-8') ?>">
                                    <button class="admin-action-button danger" type="submit"><?= admin_icon('archive') ?>Archivar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require dirname(__DIR__) . '/partials/layout-end.php'; ?>
