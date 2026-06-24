<?php require __DIR__ . '/partials/layout-start.php'; ?>

<section class="admin-page-intro">
    <div>
        <p class="eyebrow">Administración</p>
        <h1>Panel de control</h1>
        <p>Gestiona el censo desde un único punto: importaciones, entidades y próximas herramientas de edición.</p>
    </div>
    <div class="admin-status-panel">
        <span>Sesión activa</span>
        <strong><?= htmlspecialchars((string) ($user['email'] ?? $user['username'] ?? 'usuario'), ENT_QUOTES, 'UTF-8') ?></strong>
    </div>
</section>

<section class="admin-actions" aria-label="Acciones principales">
    <article>
        <span>01</span>
        <h2>Importar Excel</h2>
        <p>Sube archivo, revisa avisos y confirma solo altas nuevas. Las entidades repetidas quedan registradas sin modificar datos.</p>
        <a href="/admin/import">Abrir importador</a>
    </article>
    <article>
        <span>02</span>
        <h2>Gestionar entidades</h2>
        <p>Consulta el censo cargado y accede a las fichas públicas. La edición detallada será el siguiente bloque.</p>
        <a href="/admin/entities">Ver entidades</a>
    </article>
    <article class="admin-action-muted">
        <span>03</span>
        <h2>Próximas herramientas</h2>
        <p>Instalaciones, modalidades, fotos y usuarias quedarán integradas en esta misma navegación.</p>
    </article>
</section>

<?php require __DIR__ . '/partials/layout-end.php'; ?>
