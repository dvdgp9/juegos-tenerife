# Despliegue en Plesk

## Configuración recomendada

- Document root: `public/`.
- PHP: 8.1 o superior.
- Base de datos: MySQL 8 o MariaDB compatible.
- Composer: ejecutar `composer install --no-dev --optimize-autoloader` en despliegue.
- Extensiones esperadas para importación Excel: `zip` y `xmlreader`.

## Carpetas con escritura

El usuario web debe poder escribir en:

- `storage/uploads/`
- `storage/imports/`
- `storage/logs/`
- `storage/cache/`

## SQL

El SQL inicial está separado en:

1. `database/001_initial_schema.sql`
2. `database/002_seed_reference_data.sql`
3. `database/003_seed_superadmin.sql`

El usuario lo ejecutará manualmente en el servidor.

## Pendiente

- Confirmar SMTP o método de envío de correo disponible en Plesk.
- Confirmar límites de subida de archivos para Excel e imágenes.
- Confirmar permisos de escritura reales tras subir a servidor.
- Confirmar que PHP puede hacer peticiones HTTPS salientes a `maps.app.goo.gl` para intentar extraer coordenadas de enlaces Maps.
