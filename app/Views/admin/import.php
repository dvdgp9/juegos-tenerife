<?php
/** @var array<string,mixed>|null $duplicateCheck */
/** @var ?string $duplicateCheckError */
$duplicateCheck = $duplicateCheck ?? null;
$duplicateCheckError = $duplicateCheckError ?? null;
?>
<?php require __DIR__ . '/partials/layout-start.php'; ?>

<section class="admin-page-intro">
    <div>
        <p class="eyebrow">Importación</p>
        <h1>Revisar Excel antes de guardar</h1>
        <p>La importación solo añade entidades nuevas. Si el Excel contiene entidades existentes, se registran como repetidas y no modifican datos.</p>
    </div>
</section>

<section class="import-layout">
    <form class="admin-import-panel" action="/admin/import/preview" method="post" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrf, ENT_QUOTES, 'UTF-8') ?>">
        <div class="field file-field">
            <label for="excel">Archivo Excel</label>
            <input id="excel" name="excel" type="file" accept=".xlsx" required>
            <small class="field-help">Formato .xlsx. Se comparará con el censo actual antes de guardar nada.</small>
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
                    <div><dt>Repetidas</dt><dd><?= (int) ($summary['duplicate'] ?? 0) ?></dd></div>
                    <div><dt>Omitidas</dt><dd><?= (int) ($summary['skipped'] ?? 0) ?></dd></div>
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

            <?php if ($duplicateCheckError !== null): ?>
                <div class="alert error" role="alert"><?= htmlspecialchars($duplicateCheckError, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <?php if (is_array($duplicateCheck)): ?>
                <section class="duplicate-check" aria-labelledby="duplicate-title">
                    <div class="duplicate-check-head">
                        <h3 id="duplicate-title">Comparación con el censo actual</h3>
                        <dl>
                            <div><dt>Se crearían</dt><dd><?= (int) $duplicateCheck['new'] ?></dd></div>
                            <div><dt>Repetidas</dt><dd><?= (int) $duplicateCheck['duplicate'] ?></dd></div>
                            <div><dt>Sin nombre</dt><dd><?= (int) $duplicateCheck['skipped'] ?></dd></div>
                        </dl>
                    </div>

                    <?php if ($duplicateCheck['duplicate'] === 0): ?>
                        <p class="duplicate-check-note">Ninguna fila coincide con entidades ya existentes: todas se darían de alta.</p>
                    <?php else: ?>
                        <p class="duplicate-check-note">Estas filas no se importarán y no modificarán los datos actuales:</p>
                        <ul class="duplicate-list">
                            <?php foreach ($duplicateCheck['duplicates'] as $duplicate): ?>
                                <li>
                                    <span class="duplicate-row">Fila <?= (int) $duplicate['row'] ?></span>
                                    <strong><?= htmlspecialchars((string) $duplicate['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <?php if ($duplicate['municipality'] !== ''): ?>
                                        <span><?= htmlspecialchars((string) $duplicate['municipality'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php endif; ?>
                                    <em><?= htmlspecialchars((string) $duplicate['reason'], ENT_QUOTES, 'UTF-8') ?></em>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <div class="preview-grid">
                <?php if (!empty($preview['subtypes'])): ?>
                    <article>
                        <h3>Subtipos detectados</h3>
                        <ul>
                            <?php foreach ($preview['subtypes'] as $name => $count): ?>
                                <li><?= htmlspecialchars((string) $name, ENT_QUOTES, 'UTF-8') ?> <span><?= (int) $count ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                    </article>
                <?php endif; ?>
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
                            <th>Subtipo</th>
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
                                <td><?= htmlspecialchars((string) ($row['subtype'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) $row['municipality'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars(implode(', ', $row['modalities']), ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <form class="admin-confirm-row" action="/admin/import/confirm" method="post">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrf, ENT_QUOTES, 'UTF-8') ?>">
                <?php if (is_array($duplicateCheck)): ?>
                    <?php
                    $newCount = (int) $duplicateCheck['new'];
                    $duplicateCount = (int) $duplicateCheck['duplicate'];
                    ?>
                    <p>
                        <?php if ($newCount === 0): ?>
                            No se creará ninguna entidad nueva.
                        <?php elseif ($newCount === 1): ?>
                            Al confirmar se creará <strong>1</strong> entidad nueva.
                        <?php else: ?>
                            Al confirmar se crearán <strong><?= $newCount ?></strong> entidades nuevas.
                        <?php endif; ?>
                        <?php if ($duplicateCount === 1): ?>
                            La fila repetida se registrará sin tocar los datos existentes.
                        <?php elseif ($duplicateCount > 1): ?>
                            Las <strong><?= $duplicateCount ?></strong> filas repetidas se registrarán sin tocar los datos existentes.
                        <?php endif; ?>
                    </p>
                <?php else: ?>
                    <p>Confirma solo si el archivo está revisado. Las filas repetidas no cambiarán información existente.</p>
                <?php endif; ?>
                <button class="button primary" type="submit">Confirmar importación</button>
            </form>
        </section>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/partials/layout-end.php'; ?>
