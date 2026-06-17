<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Panel de administración', ENT_QUOTES, 'UTF-8') ?></title>
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
        <form action="/admin/logout" method="post">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrf, ENT_QUOTES, 'UTF-8') ?>">
            <button class="button secondary" type="submit">Salir</button>
        </form>
    </header>

    <main class="admin-shell">
        <section class="page-intro">
            <p class="eyebrow">Administración</p>
            <h1>Panel de control</h1>
            <p>Sesión iniciada como <?= htmlspecialchars((string) ($user['email'] ?? $user['username'] ?? 'usuario'), ENT_QUOTES, 'UTF-8') ?>.</p>
        </section>

        <section class="admin-actions" aria-label="Acciones principales">
            <article>
                <span>01</span>
                <h2>Importar Excel</h2>
                <p>Subir archivo, previsualizar filas, detectar errores y confirmar cambios.</p>
                <a href="/admin/import">Abrir importador</a>
            </article>
            <article>
                <span>02</span>
                <h2>Gestionar entidades</h2>
                <p>Editar datos de contacto, modalidades, instalaciones, protocolos y publicación.</p>
                <a href="/admin/entities">Ver entidades</a>
            </article>
            <article>
                <span>03</span>
                <h2>Revisar ubicaciones</h2>
                <p>Completar manualmente coordenadas cuando el enlace de Google Maps no sea suficiente.</p>
            </article>
        </section>
    </main>
</body>
</html>
