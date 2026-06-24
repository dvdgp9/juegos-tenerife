<?php
/** @var list<array<string,mixed>> $users */
$users = $users ?? [];
$h = static fn(mixed $value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$formatDate = static function (mixed $value): string {
    if (empty($value)) {
        return 'Nunca';
    }

    $timestamp = strtotime((string) $value);

    return $timestamp === false ? (string) $value : date('d/m/Y H:i', $timestamp);
};

require dirname(__DIR__) . '/partials/layout-start.php';
?>

<section class="admin-page-intro">
    <div>
        <p class="eyebrow">Administración</p>
        <h1>Usuarias</h1>
        <p>Gestiona quién puede entrar al panel interno y mantener la información del censo.</p>
    </div>
    <div class="admin-form-actions top">
        <a class="button primary" href="/admin/users/new">Crear usuaria</a>
    </div>
</section>

<?php if ($users === []): ?>
    <div class="empty-state admin-empty">
        <strong>No hay usuarias creadas.</strong>
        <span>Crea la primera usuaria interna para acceder al panel.</span>
    </div>
<?php else: ?>
    <div class="admin-table-tools">
        <div>
            <strong><?= count($users) ?> usuaria<?= count($users) === 1 ? '' : 's' ?></strong>
            <span>Las cuentas activas pueden acceder al panel.</span>
        </div>
    </div>

    <div class="table-scroll admin-table-wrap admin-entities-table-wrap">
        <table class="preview-table admin-entities-table">
            <thead>
                <tr>
                    <th>Usuaria</th>
                    <th>Correo</th>
                    <th>Permiso</th>
                    <th>Estado</th>
                    <th>Último acceso</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $adminUser): ?>
                    <?php $isActive = (int) ($adminUser['is_active'] ?? 0) === 1; ?>
                    <tr>
                        <td class="entity-name-cell">
                            <strong><?= $h($adminUser['full_name'] ?: $adminUser['username']) ?></strong>
                            <small><?= $h($adminUser['username']) ?></small>
                        </td>
                        <td><span class="admin-muted-text"><?= $h($adminUser['email']) ?></span></td>
                        <td>
                            <span class="status-badge <?= (string) ($adminUser['role'] ?? '') === 'superadmin' ? 'success' : 'neutral' ?>">
                                <?= (string) ($adminUser['role'] ?? '') === 'superadmin' ? 'Superadmin' : 'Admin' ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge <?= $isActive ? 'success' : 'draft' ?>">
                                <?= $isActive ? 'Activa' : 'Inactiva' ?>
                            </span>
                        </td>
                        <td><span class="admin-muted-text"><?= $h($formatDate($adminUser['last_login_at'] ?? null)) ?></span></td>
                        <td>
                            <div class="admin-row-actions">
                                <a class="admin-action-button primary" href="/admin/users/<?= (int) $adminUser['id'] ?>/edit">Editar</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require dirname(__DIR__) . '/partials/layout-end.php'; ?>
