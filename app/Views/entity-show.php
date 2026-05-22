<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Ficha de Entidad', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="" defer></script>
    <script src="/assets/js/app.js" defer></script>
</head>
<body>
    <header class="site-header">
        <a class="brand" href="/" aria-label="Inicio">
            <img src="/assets/images/logo-dxt-tenerife.png" alt="Tenerife Deportes">
        </a>
        <nav class="main-nav" aria-label="Navegación principal">
            <a href="/busqueda">Búsqueda Entidades</a>
            <a href="/#sobre-censo">Sobre el Censo</a>
            <a href="/#modalidades">Deportes y Modalidades</a>
        </nav>
        <a class="header-action" href="mailto:deportesdetenerife@gmail.com">Comunicar actualización</a>
    </header>

    <main class="entity-page">
        <section class="entity-hero">
            <div class="entity-logo">
                <img src="/assets/images/pictogramas/ARRASTRE_DE_GANADO_1.png" alt="">
            </div>
            <div class="entity-title">
                <p class="eyebrow">Ficha de la entidad</p>
                <h1>Federación de Arrastre Canario</h1>
                <p class="modality-list">Arrastre Canario · Otras modalidades tradicionales</p>
                <dl class="contact-list">
                    <div>
                        <dt>Domicilio</dt>
                        <dd>Ctra. General del Norte, 60-A, San Lázaro, San Cristóbal de La Laguna, 38206</dd>
                    </div>
                    <div>
                        <dt>Teléfono</dt>
                        <dd>922 000 000 · 600 000 000</dd>
                    </div>
                    <div>
                        <dt>Correo electrónico</dt>
                        <dd>contacto@arrastrecanario.example</dd>
                    </div>
                    <div>
                        <dt>Web</dt>
                        <dd>Web no disponible</dd>
                    </div>
                    <div>
                        <dt>Redes Sociales</dt>
                        <dd>Redes sociales no disponibles</dd>
                    </div>
                </dl>
            </div>
        </section>

        <section class="entity-layout">
            <div class="entity-content">
                <section class="detail-section">
                    <p class="eyebrow">Información General</p>
                    <h2>Trayectoria, principios y valores</h2>
                    <p>La Federación de Arrastre Canario es el organismo encargado de la gestión, promoción y regulación de las pruebas de arrastre de ganado en las Islas Canarias.</p>
                    <div class="info-columns">
                        <article>
                            <h3>Breve historia / trayectoria</h3>
                            <p>Actividad vinculada a las tareas agrícolas tradicionales y a las pruebas ligadas a fiestas populares de Tenerife.</p>
                        </article>
                        <article>
                            <h3>Principios corporativos</h3>
                            <p>Promoción, conservación y regulación de la modalidad con atención al bienestar animal.</p>
                        </article>
                        <article>
                            <h3>Valores deportivos</h3>
                            <p>Respeto, colaboración, conocimiento del medio rural y transmisión intergeneracional.</p>
                        </article>
                    </div>
                </section>

                <section class="detail-section">
                    <p class="eyebrow">Deportes y Modalidades Lúdicas</p>
                    <div class="chips">
                        <span>Arrastre Canario</span>
                        <span>Otras modalidades tradicionales</span>
                    </div>
                </section>

                <section class="detail-section">
                    <p class="eyebrow">Características</p>
                    <div class="metrics-grid">
                        <article><span>Equipos</span><strong>12</strong><small>Por género y edad pendiente</small></article>
                        <article><span>Deportistas / Practicantes</span><strong>98</strong><small>64 hombres · 34 mujeres</small></article>
                        <article><span>Espacios deportivos</span><strong>5</strong><small>Instalaciones vinculadas</small></article>
                        <article><span>Entrenamientos | Prácticas</span><strong>Sábados</strong><small>11:00 - 14:00 según calendario</small></article>
                    </div>
                </section>

                <section class="detail-section">
                    <p class="eyebrow">Mapa</p>
                    <div class="map-shell entity-map" aria-label="Mapa preliminar de la entidad" data-map="entity">
                        <div class="map-fallback">
                            <div class="map-island">
                                <span class="pin pin-2"></span>
                            </div>
                        </div>
                        <div class="map-legend">
                            <strong>Casa del Ganadero</strong>
                            <span>Coordenada de ejemplo pendiente de importación real</span>
                        </div>
                    </div>
                </section>

                <section class="detail-section">
                    <p class="eyebrow">Fotos</p>
                    <div class="photo-placeholder">
                        <span>Galería pendiente de carga desde backend</span>
                    </div>
                </section>
            </div>

            <aside class="entity-sidebar">
                <details open>
                    <summary>Contacto</summary>
                    <p><strong>Persona de Contacto</strong><br>Nombre, cargo, teléfono y email pendientes de Excel.</p>
                </details>
                <details>
                    <summary>Información Complementaria</summary>
                    <ul>
                        <li>Miembros Directiva: pendiente</li>
                        <li>Celebran Asambleas Anuales: pendiente</li>
                        <li>Protocolo de Igualdad: pendiente</li>
                        <li>Cumple con la LOPIVI: pendiente</li>
                    </ul>
                </details>
                <a class="button primary full" href="#">Descargar ficha PDF</a>
            </aside>
        </section>
    </main>
</body>
</html>
