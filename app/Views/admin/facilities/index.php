<?php require dirname(__DIR__) . '/partials/layout-start.php'; ?>

<section class="admin-page-intro">
    <div>
        <p class="eyebrow">Administración</p>
        <h1>Instalaciones</h1>
        <p>Gestiona espacios, coordenadas y entidades vinculadas para que mapas y fichas públicas estén siempre al día.</p>
    </div>
    <div class="admin-form-actions top">
        <a class="button primary" href="/admin/facilities/new">Crear instalación</a>
    </div>
</section>

<?php if (empty($facilities)): ?>
    <div class="empty-state admin-empty">
        <strong>No hay instalaciones cargadas.</strong>
        <span>Crea una instalación o importa un Excel con espacios deportivos.</span>
    </div>
<?php else: ?>
    <div class="admin-table-tools">
        <div>
            <strong><?= count($facilities) ?> instalaciones</strong>
            <span>Ordenadas por última actualización</span>
        </div>
    </div>
    <div class="table-scroll admin-table-wrap admin-entities-table-wrap">
        <table class="preview-table admin-entities-table">
            <thead>
                <tr>
                    <th>Instalación</th>
                    <th>Municipio</th>
                    <th>Entidades</th>
                    <th>Coordenadas</th>
                    <th>Geo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($facilities as $facility): ?>
                    <?php
                    $hasCoords = $facility['latitude'] !== null && $facility['longitude'] !== null;
                    $entitiesCount = (int) ($facility['entities_count'] ?? 0);
                    ?>
                    <tr>
                        <td class="entity-name-cell">
                            <strong><?= htmlspecialchars((string) $facility['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <small><?= htmlspecialchars((string) $facility['slug'], ENT_QUOTES, 'UTF-8') ?></small>
                        </td>
                        <td><span class="admin-muted-text"><?= htmlspecialchars((string) ($facility['municipality'] ?? $facility['locality'] ?? 'Sin municipio'), ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td>
                            <span class="status-badge neutral"><?= $entitiesCount ?> vinculada<?= $entitiesCount === 1 ? '' : 's' ?></span>
                            <?php if (!empty($facility['entities'])): ?>
                                <small><?= htmlspecialchars((string) $facility['entities'], ENT_QUOTES, 'UTF-8') ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($hasCoords): ?>
                                <span class="admin-muted-text"><?= htmlspecialchars((string) $facility['latitude'], ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars((string) $facility['longitude'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php else: ?>
                                <span class="status-badge draft">Pendientes</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="status-badge <?= ((string) $facility['geocoding_status']) === 'resolved' ? 'success' : 'neutral' ?>"><?= htmlspecialchars((string) ($facility['geocoding_status'] ?? 'pending'), ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td>
                            <div class="admin-row-actions">
                                <a class="admin-action-button primary" href="/admin/facilities/<?= (int) $facility['id'] ?>/edit">Editar</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require dirname(__DIR__) . '/partials/layout-end.php'; ?>
