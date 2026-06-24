<?php
/** @var array<string,mixed> $adminUser */
/** @var list<string> $errors */
/** @var string $action */
$adminUser = $adminUser ?? [];
$errors = $errors ?? [];

$h = static fn(mixed $value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$value = static fn(string $key): string => htmlspecialchars((string) ($adminUser[$key] ?? ''), ENT_QUOTES, 'UTF-8');
$selected = static fn(string $key, mixed $candidate): string => (string) ($adminUser[$key] ?? '') === (string) $candidate ? ' selected' : '';
$checked = static fn(string $key): string => (int) ($adminUser[$key] ?? 0) === 1 ? ' checked' : '';
$isEditing = !empty($adminUser['id']);

require dirname(__DIR__) . '/partials/layout-start.php';
?>

<section class="admin-page-intro">
    <div>
        <p class="eyebrow">Usuarias</p>
        <h1><?= $isEditing ? 'Editar usuaria' : 'Crear usuaria' ?></h1>
        <p>Define datos de acceso, permiso y estado de la cuenta interna.</p>
    </div>
    <div class="admin-form-actions top">
        <a class="button secondary" href="/admin/users">Volver al listado</a>
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

<form class="admin-entity-form admin-user-form" action="<?= $h($action) ?>" method="post">
    <input type="hidden" name="_csrf" value="<?= $h($csrf ?? '') ?>">

    <aside class="admin-form-rail" aria-label="Secciones de la usuaria">
        <a href="#cuenta">Cuenta</a>
        <a href="#acceso">Acceso</a>
    </aside>

    <div class="admin-form-main">
        <section id="cuenta" class="admin-form-section">
            <div class="admin-form-section-head">
                <div>
                    <p class="eyebrow">01</p>
                    <h2>Datos de la cuenta</h2>
                </div>
            </div>

            <div class="form-grid two">
                <div class="field">
                    <label for="full_name">Nombre completo</label>
                    <input id="full_name" name="full_name" type="text" value="<?= $value('full_name') ?>" autocomplete="name">
                </div>
                <div class="field">
                    <label for="email">Correo</label>
                    <input id="email" name="email" type="email" value="<?= $value('email') ?>" autocomplete="email" required>
                </div>
                <div class="field">
                    <label for="username">Usuario</label>
                    <input id="username" name="username" type="text" value="<?= $value('username') ?>" autocomplete="username" required>
                    <span class="field-help">Mínimo 3 caracteres. Puedes usar letras, números, punto, guion y guion bajo.</span>
                </div>
                <div class="field">
                    <label for="role">Permiso</label>
                    <select id="role" name="role">
                        <option value="admin"<?= $selected('role', 'admin') ?>>Admin</option>
                        <option value="superadmin"<?= $selected('role', 'superadmin') ?>>Superadmin</option>
                    </select>
                </div>
                <label class="admin-toggle-field wide">
                    <input name="is_active" type="checkbox" value="1"<?= $checked('is_active') ?>>
                    <span>
                        <strong>Cuenta activa</strong>
                        <small>Si está desactivada, no podrá entrar al panel.</small>
                    </span>
                </label>
            </div>
        </section>

        <section id="acceso" class="admin-form-section">
            <div class="admin-form-section-head">
                <div>
                    <p class="eyebrow">02</p>
                    <h2><?= $isEditing ? 'Cambiar contraseña' : 'Contraseña inicial' ?></h2>
                </div>
            </div>

            <div class="form-grid two">
                <div class="field">
                    <label for="password">Contraseña</label>
                    <input id="password" name="password" type="password" autocomplete="<?= $isEditing ? 'new-password' : 'new-password' ?>"<?= $isEditing ? '' : ' required' ?>>
                    <span class="field-help"><?= $isEditing ? 'Déjalo vacío para mantener la contraseña actual.' : 'Debe tener al menos 10 caracteres.' ?></span>
                </div>
                <div class="field">
                    <label for="password_confirmation">Repetir contraseña</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"<?= $isEditing ? '' : ' required' ?>>
                </div>
            </div>
        </section>

        <div class="admin-form-submit">
            <a class="button secondary" href="/admin/users">Cancelar</a>
            <button class="button primary" type="submit">Guardar usuaria</button>
        </div>
    </div>
</form>

<?php require dirname(__DIR__) . '/partials/layout-end.php'; ?>
