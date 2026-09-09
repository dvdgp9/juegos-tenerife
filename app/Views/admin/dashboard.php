<?php
/** @var list<array<string,mixed>> $activity */
$activity = $activity ?? [];

$actionLabels = [
    'auth.login' => 'Inicio de sesión',
    'auth.login_failed' => 'Intento de acceso fallido',
    'auth.logout' => 'Cierre de sesión',
    'entity.created' => 'Entidad creada',
    'entity.updated' => 'Entidad editada',
    'entity.published' => 'Entidad publicada',
    'entity.unpublished' => 'Entidad despublicada',
    'entity.deleted' => 'Entidad archivada',
    'facility.created' => 'Instalación creada',
    'facility.updated' => 'Instalación editada',
    'facility.deleted' => 'Instalación archivada',
    'modality.created' => 'Modalidad creada',
    'modality.updated' => 'Modalidad editada',
    'media.uploaded' => 'Fotos subidas',
    'media.deleted' => 'Fotos eliminadas',
    'user.created' => 'Usuaria creada',
    'user.updated' => 'Usuaria editada',
    'import.completed' => 'Importación completada',
    'import.failed' => 'Importación fallida',
];

/**
 * Resumen legible de la fila de auditoría, sin exponer datos internos.
 */
$describe = static function (array $row): string {
    $metadata = [];
    if (!empty($row['metadata'])) {
        $decoded = json_decode((string) $row['metadata'], true);
        if (is_array($decoded)) {
            $metadata = $decoded;
        }
    }

    if (isset($metadata['name'])) {
        return (string) $metadata['name'];
    }

    if (isset($metadata['username'])) {
        return (string) $metadata['username'];
    }

    if (isset($metadata['identifier'])) {
        return (string) $metadata['identifier'];
    }

    if (isset($metadata['file'])) {
        $parts = [(string) $metadata['file']];
        if (isset($metadata['created'])) {
            $parts[] = $metadata['created'] . ' creadas';
        }
        if (isset($metadata['duplicate'])) {
            $parts[] = $metadata['duplicate'] . ' repetidas';
        }
        if (!empty($metadata['errors'])) {
            $parts[] = $metadata['errors'] . ' con error';
        }

        return implode(' · ', $parts);
    }

    if (isset($metadata['files'])) {
        return $metadata['files'] . ' archivo' . ((int) $metadata['files'] === 1 ? '' : 's');
    }

    if (isset($metadata['kind'])) {
        return (string) $metadata['kind'];
    }

    return '';
};
?>
<?php require __DIR__ . '/partials/layout-start.php'; ?>

<section class="admin-page-intro">
    <div>
        <p class="eyebrow">Administración</p>
        <h1>Panel de control</h1>
        <p>Gestiona el censo desde un único punto: importaciones, entidades, instalaciones, modalidades y usuarias.</p>
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
        <p>Edita fichas completas, contactos, modalidades y fotos. Publica, despublica o archiva lo que ya no deba verse.</p>
        <a href="/admin/entities">Ver entidades</a>
    </article>
    <article>
        <span>03</span>
        <h2>Instalaciones y modalidades</h2>
        <p>Ajusta espacios de práctica, coordenadas del mapa y las fichas editoriales de cada modalidad.</p>
        <a href="/admin/facilities">Ver instalaciones</a>
    </article>
</section>

<section class="admin-activity" aria-labelledby="activity-title">
    <div class="admin-activity-head">
        <h2 id="activity-title">Actividad reciente</h2>
        <span>Últimos cambios registrados en el panel</span>
    </div>

    <?php if ($activity === []): ?>
        <div class="empty-state admin-empty">
            <strong>Todavía no hay actividad registrada.</strong>
            <span>Aquí aparecerán importaciones, ediciones, publicaciones y archivados.</span>
        </div>
    <?php else: ?>
        <ol class="admin-activity-list">
            <?php foreach ($activity as $row): ?>
                <?php
                $action = (string) $row['action'];
                $label = $actionLabels[$action] ?? $action;
                $detail = $describe($row);
                $author = (string) ($row['full_name'] ?? $row['username'] ?? $row['email'] ?? 'Sistema');
                $when = (string) $row['created_at'];
                ?>
                <li>
                    <div>
                        <strong><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></strong>
                        <?php if ($detail !== ''): ?>
                            <span><?= htmlspecialchars($detail, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="admin-activity-meta">
                        <span><?= htmlspecialchars($author, ENT_QUOTES, 'UTF-8') ?></span>
                        <time datetime="<?= htmlspecialchars($when, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($when) ?: time()), ENT_QUOTES, 'UTF-8') ?></time>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/partials/layout-end.php'; ?>
