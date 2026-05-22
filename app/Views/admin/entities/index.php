<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Entidades', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <header class="admin-header">
        <a class="brand" href="/admin" aria-label="Panel de administración">
            <img src="/assets/images/logo-dxt-tenerife.png" alt="Tenerife Deportes">
        </a>
        <nav class="admin-nav" aria-label="Administración">
            <a href="/admin">Panel</a>
            <a href="/admin/entities">Entidades</a>
            <a href="/admin/import">Importar Excel</a>
            <a href="#">Usuarios</a>
        </nav>
    </header>

    <main class="admin-shell">
        <section class="page-intro">
            <p class="eyebrow">Administración</p>
            <h1>Entidades</h1>
            <p>Listado de las entidades importadas o creadas manualmente. La edición detallada se añadirá en el siguiente bloque.</p>
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
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>

