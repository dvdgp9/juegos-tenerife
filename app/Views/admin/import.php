<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Importar Excel', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <header class="admin-header">
        <a class="brand" href="/admin" aria-label="Panel de administración">
            <img src="/assets/images/logo-dxt-activa-tu-vida.png" alt="Tenerife Deportes">
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
            <p class="eyebrow">Importación</p>
            <h1>Revisar Excel antes de guardar</h1>
            <p>Esta pantalla solo previsualiza el archivo. La confirmación de importación se añadirá después de cerrar el mapeo y las validaciones.</p>
        </section>

        <section class="import-layout">
            <form class="admin-import-panel" action="/admin/import/preview" method="post" enctype="multipart/form-data">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrf, ENT_QUOTES, 'UTF-8') ?>">
                <div class="field">
                    <label for="excel">Archivo Excel</label>
                    <input id="excel" name="excel" type="file" accept=".xlsx" required>
                </div>
                <button class="button primary" type="submit">Previsualizar</button>
            </form>

            <?php if (!empty($error)): ?>
                <div class="alert error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <?php if (is_array($summary)): ?>
                <section class="preview-panel" aria-labelledby="summary-title">
                    <div class="preview-head">
                        <div>
                            <p class="eyebrow">Importación completada</p>
                            <h2 id="summary-title">Resumen de cambios</h2>
                        </div>
                        <dl>
                            <div><dt>Filas</dt><dd><?= (int) $summary['total'] ?></dd></div>
                            <div><dt>Creadas</dt><dd><?= (int) $summary['created'] ?></dd></div>
                            <div><dt>Actualizadas</dt><dd><?= (int) $summary['updated'] ?></dd></div>
                            <div><dt>Errores</dt><dd><?= (int) $summary['errors'] ?></dd></div>
                        </dl>
                    </div>
                </section>
            <?php endif; ?>

            <?php if (is_array($preview)): ?>
                <section class="preview-panel" aria-labelledby="preview-title">
                    <div class="preview-head">
                        <div>
                            <p class="eyebrow">Resultado</p>
                            <h2 id="preview-title">Previsualización del Excel</h2>
                        </div>
                        <dl>
                            <div><dt>Hoja</dt><dd><?= htmlspecialchars((string) $preview['sheet'], ENT_QUOTES, 'UTF-8') ?></dd></div>
                            <div><dt>Registros</dt><dd><?= (int) $preview['records'] ?></dd></div>
                            <div><dt>Columnas</dt><dd><?= count($preview['headers']) ?></dd></div>
                            <div><dt>Enlaces Maps</dt><dd><?= (int) $preview['mapsCount'] ?></dd></div>
                        </dl>
                    </div>

                    <?php if (!empty($preview['warnings'])): ?>
                        <div class="warning-list">
                            <strong>Avisos detectados</strong>
                            <ul>
                                <?php foreach ($preview['warnings'] as $warning): ?>
                                    <li><?= htmlspecialchars((string) $warning, ENT_QUOTES, 'UTF-8') ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="preview-grid">
                        <article>
                            <h3>Modalidades detectadas</h3>
                            <ul>
                                <?php foreach ($preview['modalities'] as $name => $count): ?>
                                    <li><?= htmlspecialchars((string) $name, ENT_QUOTES, 'UTF-8') ?> <span><?= (int) $count ?></span></li>
                                <?php endforeach; ?>
                            </ul>
                        </article>
                        <article>
                            <h3>Municipios detectados</h3>
                            <ul>
                                <?php foreach ($preview['municipalities'] as $name => $count): ?>
                                    <li><?= htmlspecialchars((string) $name, ENT_QUOTES, 'UTF-8') ?> <span><?= (int) $count ?></span></li>
                                <?php endforeach; ?>
                            </ul>
                        </article>
                    </div>

                    <div class="table-scroll">
                        <table class="preview-table">
                            <thead>
                                <tr>
                                    <th>Fila</th>
                                    <th>Tipo</th>
                                    <th>Entidad</th>
                                    <th>Municipio</th>
                                    <th>Modalidades</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($preview['sampleRows'] as $row): ?>
                                    <tr>
                                        <td><?= (int) $row['row'] ?></td>
                                        <td><?= htmlspecialchars((string) $row['type'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) $row['municipality'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars(implode(', ', $row['modalities']), ENT_QUOTES, 'UTF-8') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <form action="/admin/import/confirm" method="post">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrf, ENT_QUOTES, 'UTF-8') ?>">
                        <button class="button primary" type="submit">Confirmar importación</button>
                    </form>
                </section>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
