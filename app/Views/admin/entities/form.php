<?php
/** @var array<string,mixed> $entity */
/** @var list<string> $errors */
/** @var string $action */
/** @var list<array<string,mixed>> $entityTypes */
/** @var list<array<string,mixed>> $municipalities */
/** @var list<array<string,mixed>> $modalities */
/** @var list<array<string,mixed>> $mediaFiles */
$entity = $entity ?? [];
$errors = $errors ?? [];
$entityTypes = $entityTypes ?? [];
$municipalities = $municipalities ?? [];
$modalities = $modalities ?? [];
$mediaFiles = $mediaFiles ?? [];

$h = static fn(mixed $value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$value = static fn(string $key): string => htmlspecialchars((string) ($entity[$key] ?? ''), ENT_QUOTES, 'UTF-8');
$selected = static fn(string $key, mixed $candidate): string => (string) ($entity[$key] ?? '') === (string) $candidate ? ' selected' : '';
$checked = static fn(string $key): string => ((int) ($entity[$key] ?? 0)) === 1 ? ' checked' : '';
$triSelected = static fn(string $key, string $candidate): string => (string) ($entity[$key] ?? '') === $candidate ? ' selected' : '';
$checkedModality = static fn(mixed $id): string => in_array((int) $id, array_map('intval', (array) ($entity['modality_ids'] ?? [])), true) ? ' checked' : '';

require dirname(__DIR__) . '/partials/layout-start.php';
?>

<section class="admin-page-intro">
    <div>
        <p class="eyebrow">Entidades</p>
        <h1><?= !empty($entity['id']) ? 'Editar entidad' : 'Crear entidad' ?></h1>
        <p>Completa los datos principales que alimentan la ficha pública. Contactos, redes, modalidades, instalaciones y fotos tendrán bloques propios después.</p>
    </div>
    <div class="admin-form-actions top">
        <a class="button secondary" href="/admin/entities">Volver al listado</a>
        <?php if (!empty($entity['slug'])): ?>
            <a class="button secondary" href="/entidades/<?= $h($entity['slug']) ?>" target="_blank" rel="noopener">Ver ficha</a>
        <?php endif; ?>
    </div>
</section>

<?php if ($errors !== []): ?>
    <div class="alert error" role="alert">
        <strong>Revisa el formulario</strong>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= $h($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form class="admin-entity-form" action="<?= $h($action) ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="_csrf" value="<?= $h($csrf ?? '') ?>">

    <aside class="admin-form-rail" aria-label="Secciones de la entidad">
        <a href="#identidad">Identidad</a>
        <a href="#modalidades">Modalidades</a>
        <a href="#contacto">Contacto</a>
        <a href="#redes">Redes</a>
        <a href="#fotos">Fotos</a>
        <a href="#ubicacion">Ubicación</a>
        <a href="#contenido">Contenido</a>
        <a href="#actividad">Actividad</a>
        <a href="#edades">Edades</a>
        <a href="#gestion">Gestión</a>
    </aside>

    <div class="admin-form-main">
        <section id="identidad" class="admin-form-section">
            <div class="admin-form-section-head">
                <div>
                    <p class="eyebrow">01</p>
                    <h2>Identidad y publicación</h2>
                </div>
                <label class="switch-field">
                    <input name="is_published" type="checkbox" value="1"<?= $checked('is_published') ?>>
                    <span>Publicada</span>
                </label>
            </div>

            <div class="form-grid two">
                <div class="field wide">
                    <label for="name">Nombre de la entidad</label>
                    <input id="name" name="name" type="text" value="<?= $value('name') ?>" required>
                    <small>Nombre público que aparecerá en listados y ficha.</small>
                </div>
                <div class="field">
                    <label for="slug">Slug público</label>
                    <input id="slug" name="slug" type="text" value="<?= $value('slug') ?>" placeholder="se-genera-si-lo-dejas-vacio">
                    <small>Solo letras, números y guiones. Se usa en la URL.</small>
                </div>
                <div class="field">
                    <label for="entity_type_id">Tipo de entidad</label>
                    <select id="entity_type_id" name="entity_type_id">
                        <option value="">Sin tipo</option>
                        <?php foreach ($entityTypes as $type): ?>
                            <option value="<?= (int) $type['id'] ?>"<?= $selected('entity_type_id', $type['id']) ?>><?= $h($type['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="municipality_id">Municipio</label>
                    <select id="municipality_id" name="municipality_id">
                        <option value="">Sin municipio</option>
                        <?php foreach ($municipalities as $municipality): ?>
                            <option value="<?= (int) $municipality['id'] ?>"<?= $selected('municipality_id', $municipality['id']) ?>>
                                <?= $h($municipality['name']) ?><?= ((int) ($municipality['is_tenerife'] ?? 1)) === 1 ? '' : ' · fuera de Tenerife' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </section>

        <section id="modalidades" class="admin-form-section">
            <div class="admin-form-section-head">
                <div>
                    <p class="eyebrow">02</p>
                    <h2>Modalidades</h2>
                </div>
            </div>

            <?php if ($modalities === []): ?>
                <div class="empty-state">
                    <strong>No hay modalidades cargadas.</strong>
                    <span>Se crearán al importar Excel o cuando esté activo el CRUD de modalidades.</span>
                </div>
            <?php else: ?>
                <div class="admin-check-grid">
                    <?php foreach ($modalities as $modality): ?>
                        <label>
                            <input name="modality_ids[]" type="checkbox" value="<?= (int) $modality['id'] ?>"<?= $checkedModality($modality['id']) ?>>
                            <span><?= $h($modality['name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section id="contacto" class="admin-form-section">
            <div class="admin-form-section-head">
                <div>
                    <p class="eyebrow">03</p>
                    <h2>Contacto público</h2>
                </div>
            </div>

            <div class="form-grid two">
                <div class="field">
                    <label for="phone_1">Teléfono principal</label>
                    <input id="phone_1" name="phone_1" type="text" value="<?= $value('phone_1') ?>">
                </div>
                <div class="field">
                    <label for="phone_2">Teléfono secundario</label>
                    <input id="phone_2" name="phone_2" type="text" value="<?= $value('phone_2') ?>">
                </div>
                <div class="field">
                    <label for="email_1">Email principal</label>
                    <input id="email_1" name="email_1" type="email" value="<?= $value('email_1') ?>">
                </div>
                <div class="field">
                    <label for="email_2">Email secundario</label>
                    <input id="email_2" name="email_2" type="email" value="<?= $value('email_2') ?>">
                </div>
                <div class="field">
                    <label for="contact_person">Persona de contacto</label>
                    <input id="contact_person" name="contact_person" type="text" value="<?= $value('contact_person') ?>">
                </div>
                <div class="field">
                    <label for="contact_role">Cargo</label>
                    <input id="contact_role" name="contact_role" type="text" value="<?= $value('contact_role') ?>">
                </div>
                <div class="field">
                    <label for="contact_phone">Teléfono contacto</label>
                    <input id="contact_phone" name="contact_phone" type="text" value="<?= $value('contact_phone') ?>">
                </div>
                <div class="field">
                    <label for="contact_email">Email contacto</label>
                    <input id="contact_email" name="contact_email" type="email" value="<?= $value('contact_email') ?>">
                </div>
            </div>
        </section>

        <section id="redes" class="admin-form-section">
            <div class="admin-form-section-head">
                <div>
                    <p class="eyebrow">04</p>
                    <h2>Web y redes sociales</h2>
                </div>
            </div>

            <div class="form-grid two">
                <div class="field wide">
                    <label for="website_url">Web</label>
                    <input id="website_url" name="website_url" type="url" value="<?= $value('website_url') ?>">
                </div>
                <div class="field">
                    <label for="facebook_url">Facebook</label>
                    <input id="facebook_url" name="facebook_url" type="url" value="<?= $value('facebook_url') ?>">
                </div>
                <div class="field">
                    <label for="instagram_url">Instagram</label>
                    <input id="instagram_url" name="instagram_url" type="url" value="<?= $value('instagram_url') ?>">
                </div>
                <div class="field">
                    <label for="youtube_url">YouTube</label>
                    <input id="youtube_url" name="youtube_url" type="url" value="<?= $value('youtube_url') ?>">
                </div>
                <div class="field">
                    <label for="x_url">X</label>
                    <input id="x_url" name="x_url" type="url" value="<?= $value('x_url') ?>">
                </div>
                <div class="field">
                    <label for="tiktok_url">TikTok</label>
                    <input id="tiktok_url" name="tiktok_url" type="url" value="<?= $value('tiktok_url') ?>">
                </div>
            </div>
        </section>

        <section id="fotos" class="admin-form-section">
            <div class="admin-form-section-head">
                <div>
                    <p class="eyebrow">05</p>
                    <h2>Fotos de la entidad</h2>
                </div>
            </div>

            <div class="form-grid two">
                <div class="field">
                    <label for="logo">Logo de la entidad</label>
                    <input id="logo" name="logo" type="file" accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif">
                    <small>Opcional. JPG, PNG, WEBP o GIF. Máximo 8 MB.</small>
                    <?php if (!empty($entity['logo_path'])): ?>
                        <div class="admin-current-media">
                            <img src="<?= $h($entity['logo_path']) ?>" alt="">
                            <span>Logo actual</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="field">
                    <label for="gallery_images">Añadir fotos a la galería</label>
                    <input id="gallery_images" name="gallery_images[]" type="file" multiple accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif">
                    <small>Puedes seleccionar varias imágenes. Se añadirán a la galería pública.</small>
                </div>
            </div>

            <?php if ($mediaFiles === []): ?>
                <div class="empty-state">
                    <strong>Sin fotos de galería todavía.</strong>
                    <span>Sube imágenes para mostrarlas en la ficha pública.</span>
                </div>
            <?php else: ?>
                <div class="admin-media-grid">
                    <?php foreach ($mediaFiles as $media): ?>
                        <?php $isFeatured = (string) ($media['media_type'] ?? '') === 'entity_featured_photo'; ?>
                        <article>
                            <img src="<?= $h($media['file_path']) ?>" alt="">
                            <div class="admin-media-fields">
                                <label>
                                    <span>Destacada</span>
                                    <input name="featured_media_id" type="radio" value="<?= (int) $media['id'] ?>"<?= $isFeatured ? ' checked' : '' ?>>
                                </label>
                                <label>
                                    <span>Orden</span>
                                    <input name="media[<?= (int) $media['id'] ?>][sort_order]" type="number" min="0" value="<?= (int) ($media['sort_order'] ?? 100) ?>">
                                </label>
                                <label>
                                    <span>Título</span>
                                    <input name="media[<?= (int) $media['id'] ?>][caption]" type="text" value="<?= $h($media['caption'] ?? '') ?>">
                                </label>
                                <label>
                                    <span>Texto alternativo</span>
                                    <input name="media[<?= (int) $media['id'] ?>][alt_text]" type="text" value="<?= $h($media['alt_text'] ?? '') ?>">
                                </label>
                                <label class="admin-delete-check">
                                    <input name="delete_media_ids[]" type="checkbox" value="<?= (int) $media['id'] ?>">
                                    <span>Eliminar</span>
                                </label>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section id="ubicacion" class="admin-form-section">
            <div class="admin-form-section-head">
                <div>
                    <p class="eyebrow">07</p>
                    <h2>Dirección y mapa</h2>
                </div>
            </div>

            <div class="form-grid two">
                <div class="field wide">
                    <label for="address">Domicilio</label>
                    <input id="address" name="address" type="text" value="<?= $value('address') ?>">
                </div>
                <div class="field">
                    <label for="locality">Localidad</label>
                    <input id="locality" name="locality" type="text" value="<?= $value('locality') ?>">
                </div>
                <div class="field">
                    <label for="postal_code">Código postal</label>
                    <input id="postal_code" name="postal_code" type="text" value="<?= $value('postal_code') ?>">
                </div>
                <div class="field wide">
                    <label for="google_maps_url">Enlace de Google Maps</label>
                    <input id="google_maps_url" name="google_maps_url" type="url" value="<?= $value('google_maps_url') ?>">
                </div>
                <div class="field">
                    <label for="latitude">Latitud</label>
                    <input id="latitude" name="latitude" type="text" inputmode="decimal" value="<?= $value('latitude') ?>">
                </div>
                <div class="field">
                    <label for="longitude">Longitud</label>
                    <input id="longitude" name="longitude" type="text" inputmode="decimal" value="<?= $value('longitude') ?>">
                </div>
                <div class="field">
                    <label for="geocoding_status">Estado de geolocalización</label>
                    <select id="geocoding_status" name="geocoding_status">
                        <?php foreach (['pending' => 'Pendiente', 'resolved' => 'Resuelta', 'manual' => 'Manual', 'failed' => 'Fallida'] as $key => $label): ?>
                            <option value="<?= $h($key) ?>"<?= $selected('geocoding_status', $key) ?>><?= $h($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </section>

        <section id="contenido" class="admin-form-section">
            <div class="admin-form-section-head">
                <div>
                    <p class="eyebrow">06</p>
                    <h2>Contenido público</h2>
                </div>
            </div>

            <div class="form-grid one">
                <div class="field">
                    <label for="history">Breve historia / trayectoria</label>
                    <textarea id="history" name="history" rows="6"><?= $value('history') ?></textarea>
                </div>
                <div class="field">
                    <label for="corporate_principles">Principios corporativos</label>
                    <textarea id="corporate_principles" name="corporate_principles" rows="5"><?= $value('corporate_principles') ?></textarea>
                </div>
                <div class="field">
                    <label for="sports_values">Valores deportivos</label>
                    <textarea id="sports_values" name="sports_values" rows="5"><?= $value('sports_values') ?></textarea>
                </div>
            </div>
        </section>

        <section id="actividad" class="admin-form-section">
            <div class="admin-form-section-head">
                <div>
                    <p class="eyebrow">08</p>
                    <h2>Actividad deportiva</h2>
                </div>
            </div>

            <div class="form-grid three">
                <div class="field">
                    <label for="total_teams">Equipos</label>
                    <input id="total_teams" name="total_teams" type="number" min="0" value="<?= $value('total_teams') ?>">
                </div>
                <div class="field">
                    <label for="teams_by_gender">Equipos por género</label>
                    <input id="teams_by_gender" name="teams_by_gender" type="text" value="<?= $value('teams_by_gender') ?>">
                </div>
                <div class="field">
                    <label for="teams_by_age">Equipos por edad</label>
                    <input id="teams_by_age" name="teams_by_age" type="text" value="<?= $value('teams_by_age') ?>">
                </div>
                <div class="field">
                    <label for="total_practitioners">Total practicantes</label>
                    <input id="total_practitioners" name="total_practitioners" type="number" min="0" value="<?= $value('total_practitioners') ?>">
                </div>
                <div class="field">
                    <label for="female_practitioners">Mujeres / niñas</label>
                    <input id="female_practitioners" name="female_practitioners" type="number" min="0" value="<?= $value('female_practitioners') ?>">
                </div>
                <div class="field">
                    <label for="male_practitioners">Hombres / niños</label>
                    <input id="male_practitioners" name="male_practitioners" type="number" min="0" value="<?= $value('male_practitioners') ?>">
                </div>
                <div class="field wide">
                    <label for="training_practices">Entrenamientos / prácticas</label>
                    <textarea id="training_practices" name="training_practices" rows="4"><?= $value('training_practices') ?></textarea>
                </div>
                <div class="field">
                    <label for="training_days">Días</label>
                    <input id="training_days" name="training_days" type="text" value="<?= $value('training_days') ?>">
                </div>
                <div class="field">
                    <label for="training_hours">Horarios</label>
                    <input id="training_hours" name="training_hours" type="text" value="<?= $value('training_hours') ?>">
                </div>
            </div>
        </section>

        <section id="edades" class="admin-form-section">
            <div class="admin-form-section-head">
                <div>
                    <p class="eyebrow">09</p>
                    <h2>Tramos de edad</h2>
                </div>
            </div>

            <div class="form-grid age-ranges-grid">
                <?php foreach (($entity['age_ranges'] ?? []) as $key => $range): ?>
                    <div class="field">
                        <label for="age_<?= $h($key) ?>"><?= $h($range['label'] ?? $key) ?></label>
                        <input id="age_<?= $h($key) ?>" name="age_ranges[<?= $h($key) ?>]" type="text" inputmode="numeric" value="<?= $h($range['raw_value'] ?? '') ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section id="gestion" class="admin-form-section">
            <div class="admin-form-section-head">
                <div>
                    <p class="eyebrow">10</p>
                    <h2>Gestión, socios y protocolos</h2>
                </div>
            </div>

            <div class="form-grid three">
                <div class="field">
                    <label for="has_board">Tiene directiva</label>
                    <select id="has_board" name="has_board">
                        <option value=""<?= $triSelected('has_board', '') ?>>Sin datos</option>
                        <option value="1"<?= $triSelected('has_board', '1') ?>>Sí</option>
                        <option value="0"<?= $triSelected('has_board', '0') ?>>No</option>
                    </select>
                </div>
                <div class="field">
                    <label for="board_members">Miembros directiva</label>
                    <input id="board_members" name="board_members" type="number" min="0" value="<?= $value('board_members') ?>">
                </div>
                <div class="field">
                    <label for="holds_annual_assemblies">Asambleas anuales</label>
                    <select id="holds_annual_assemblies" name="holds_annual_assemblies">
                        <option value=""<?= $triSelected('holds_annual_assemblies', '') ?>>Sin datos</option>
                        <option value="1"<?= $triSelected('holds_annual_assemblies', '1') ?>>Sí</option>
                        <option value="0"<?= $triSelected('holds_annual_assemblies', '0') ?>>No</option>
                    </select>
                </div>
                <div class="field">
                    <label for="board_male">Directivos hombres</label>
                    <input id="board_male" name="board_male" type="number" min="0" value="<?= $value('board_male') ?>">
                </div>
                <div class="field">
                    <label for="board_female">Directivas mujeres</label>
                    <input id="board_female" name="board_female" type="number" min="0" value="<?= $value('board_female') ?>">
                </div>
                <div class="field">
                    <label for="has_members">Tiene socios/as</label>
                    <select id="has_members" name="has_members">
                        <option value=""<?= $triSelected('has_members', '') ?>>Sin datos</option>
                        <option value="1"<?= $triSelected('has_members', '1') ?>>Sí</option>
                        <option value="0"<?= $triSelected('has_members', '0') ?>>No</option>
                    </select>
                </div>
                <div class="field">
                    <label for="total_members">Total socios/as</label>
                    <input id="total_members" name="total_members" type="text" value="<?= $value('total_members') ?>">
                </div>
                <div class="field">
                    <label for="male_members">Socios hombres</label>
                    <input id="male_members" name="male_members" type="text" value="<?= $value('male_members') ?>">
                </div>
                <div class="field">
                    <label for="female_members">Socias mujeres</label>
                    <input id="female_members" name="female_members" type="text" value="<?= $value('female_members') ?>">
                </div>
                <div class="field">
                    <label for="equality_protocol_status">Protocolo igualdad</label>
                    <select id="equality_protocol_status" name="equality_protocol_status">
                        <?php foreach (['' => 'Sin datos', 'yes_own' => 'Sí, propio', 'yes_external' => 'Sí, externo', 'in_progress' => 'En proceso', 'no' => 'No', 'unknown' => 'Otro / revisar'] as $key => $label): ?>
                            <option value="<?= $h($key) ?>"<?= $selected('equality_protocol_status', $key) ?>><?= $h($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="violence_protocol_status">Protocolo violencia</label>
                    <select id="violence_protocol_status" name="violence_protocol_status">
                        <?php foreach (['' => 'Sin datos', 'yes_own' => 'Sí, propio', 'yes_external' => 'Sí, externo', 'in_progress' => 'En proceso', 'no' => 'No', 'unknown' => 'Otro / revisar'] as $key => $label): ?>
                            <option value="<?= $h($key) ?>"<?= $selected('violence_protocol_status', $key) ?>><?= $h($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="lopivi_status">LOPIVI</label>
                    <select id="lopivi_status" name="lopivi_status">
                        <?php foreach (['' => 'Sin datos', 'yes_own' => 'Sí, propio', 'yes_external' => 'Sí, externo', 'in_progress' => 'En proceso', 'no' => 'No', 'unknown' => 'Otro / revisar'] as $key => $label): ?>
                            <option value="<?= $h($key) ?>"<?= $selected('lopivi_status', $key) ?>><?= $h($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="joined_educar_entrenando">Educar Entrenando</label>
                    <select id="joined_educar_entrenando" name="joined_educar_entrenando">
                        <option value=""<?= $triSelected('joined_educar_entrenando', '') ?>>Sin datos</option>
                        <option value="1"<?= $triSelected('joined_educar_entrenando', '1') ?>>Sí</option>
                        <option value="0"<?= $triSelected('joined_educar_entrenando', '0') ?>>No</option>
                    </select>
                </div>
                <div class="field">
                    <label for="supports_educational_needs">Necesidades educativas</label>
                    <select id="supports_educational_needs" name="supports_educational_needs">
                        <option value=""<?= $triSelected('supports_educational_needs', '') ?>>Sin datos</option>
                        <option value="1"<?= $triSelected('supports_educational_needs', '1') ?>>Sí</option>
                        <option value="0"<?= $triSelected('supports_educational_needs', '0') ?>>No</option>
                    </select>
                </div>
                <div class="field">
                    <label for="supports_disability">Discapacidad</label>
                    <select id="supports_disability" name="supports_disability">
                        <option value=""<?= $triSelected('supports_disability', '') ?>>Sin datos</option>
                        <option value="1"<?= $triSelected('supports_disability', '1') ?>>Sí</option>
                        <option value="0"<?= $triSelected('supports_disability', '0') ?>>No</option>
                    </select>
                </div>
            </div>
        </section>

        <div class="admin-form-submit">
            <a class="button secondary" href="/admin/entities">Cancelar</a>
            <button class="button primary" type="submit">Guardar entidad</button>
        </div>
    </div>
</form>

<?php require dirname(__DIR__) . '/partials/layout-end.php'; ?>
