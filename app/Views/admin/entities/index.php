<?php require dirname(__DIR__) . '/partials/layout-start.php'; ?>

<section class="admin-page-intro">
    <div>
        <p class="eyebrow">Administración</p>
        <h1>Entidades</h1>
        <p>Listado de entidades importadas o creadas manualmente. La edición completa se añadirá en el siguiente bloque del backend.</p>
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
        <span>Importa un Excel o crea una entidad manualmente cuando el CRUD esté activo.</span>
    </div>
<?php endif; ?>

<?php if (!empty($entities)): ?>
    <div class="admin-table-tools">
        <div>
            <strong><?= count($entities) ?> entidades</strong>
            <span>Ordenadas por última actualización</span>
        </div>
    </div>
    <div class="table-scroll admin-table-wrap">
        <table class="preview-table">
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
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars((string) $entity['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <small><?= htmlspecialchars((string) $entity['slug'], ENT_QUOTES, 'UTF-8') ?></small>
                        </td>
                        <td><?= htmlspecialchars((string) ($entity['entity_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($entity['municipality'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($entity['modalities'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($entity['geocoding_status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= ((int) $entity['is_published']) === 1 ? 'Publicada' : 'Borrador' ?></td>
                        <td>
                            <div class="admin-row-actions">
                                <a class="admin-table-link" href="/admin/entities/<?= (int) $entity['id'] ?>/edit">Editar</a>
                                <a class="admin-table-link muted" href="/entidades/<?= htmlspecialchars((string) $entity['slug'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Ver ficha</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require dirname(__DIR__) . '/partials/layout-end.php'; ?>
