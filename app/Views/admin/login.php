<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Acceso administración', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="admin-auth-body">
    <main class="admin-auth">
        <a class="brand admin-brand" href="/" aria-label="Volver a la web pública">
            <img src="/assets/images/logo-dxt-activa-tu-vida.png" alt="Tenerife Deportes">
        </a>
        <section class="admin-auth-panel" aria-labelledby="login-title">
            <p class="eyebrow">Área privada</p>
            <h1 id="login-title">Acceso administración</h1>
            <p>Entrada reservada a personal autorizado para gestionar el censo.</p>

            <?php if (!empty($error)): ?>
                <div class="alert error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form class="stacked-form" action="/admin/login" method="post">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrf, ENT_QUOTES, 'UTF-8') ?>">
                <div class="field">
                    <label for="identifier">Usuario o correo</label>
                    <input id="identifier" name="identifier" type="text" autocomplete="username" required>
                </div>
                <div class="field">
                    <label for="password">Contraseña</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required>
                </div>
                <button class="button primary full" type="submit">Entrar</button>
            </form>
        </section>
    </main>
</body>
</html>

