<?php
/** @var string|null $title */
/** @var array<string,mixed>|null $user */
/** @var array{type:string,message:string}|null $flash */
/** @var string|null $activeNav */
$title = $title ?? 'Panel de administración';
$user = $user ?? null;
$flash = $flash ?? null;
$activeNav = $activeNav ?? '';
$adminNav = [
    ['href' => '/admin', 'label' => 'Panel', 'key' => 'dashboard'],
    ['href' => '/admin/entities', 'label' => 'Entidades', 'key' => 'entities'],
    ['href' => '/admin/facilities', 'label' => 'Instalaciones', 'key' => 'facilities'],
    ['href' => '/admin/modalities', 'label' => 'Modalidades', 'key' => 'modalities'],
    ['href' => '/admin/import', 'label' => 'Importar Excel', 'key' => 'import'],
    ['href' => '/admin/users', 'label' => 'Usuarios', 'key' => 'users'],
];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="icon" type="image/png" href="/assets/images/favicon.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="admin-body">
    <header class="admin-header">
        <a class="brand" href="/admin" aria-label="Panel de administración">
            <img src="/assets/images/logo-dxt-activa-tu-vida.png" alt="Tenerife Deportes">
        </a>
        <nav class="admin-nav" aria-label="Administración">
            <?php foreach ($adminNav as $item): ?>
                <?php if ($item['href'] === null): ?>
                    <span class="admin-nav-disabled" aria-disabled="true" title="Pendiente de implementar"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>"<?= $activeNav === $item['key'] ? ' aria-current="page"' : '' ?>><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
        <?php if (is_array($user)): ?>
            <div class="admin-user-menu">
                <span><?= htmlspecialchars((string) ($user['full_name'] ?? $user['email'] ?? $user['username'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8') ?></span>
                <?php if (!empty($csrf)): ?>
                    <form action="/admin/logout" method="post">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrf, ENT_QUOTES, 'UTF-8') ?>">
                        <button class="button secondary compact" type="submit">Salir</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </header>

    <main class="admin-shell">
        <?php if (is_array($flash) && !empty($flash['message'])): ?>
            <div class="alert <?= htmlspecialchars((string) ($flash['type'] ?? 'info'), ENT_QUOTES, 'UTF-8') ?>" role="status">
                <?= htmlspecialchars((string) $flash['message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>
