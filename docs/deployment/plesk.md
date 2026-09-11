# Despliegue en Plesk

## Configuración recomendada

- Document root: `public/`.
- PHP: 8.1 o superior.
- Base de datos: MySQL 8 o MariaDB compatible.
- Composer: ejecutar `composer install --no-dev --optimize-autoloader` en despliegue.
- Extensiones esperadas para importación Excel: `zip` y `xmlreader`.
- Extensiones esperadas para imágenes: `imagick` y `fileinfo`. `gd` y `exif` no se usan
  mientras Imagick esté disponible.

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

## Comprobación del entorno

`php tests/env_check.php` verifica de una vez las extensiones de imagen, los límites de
subida y los permisos de escritura. No modifica nada. Devuelve código de salida 1 si
encuentra algún problema bloqueante.

Si no hay acceso por consola, los mismos valores se ven en Plesk:

- Extensiones: *Sitios web y dominios > PHP Settings > Extensiones de PHP*.
- Límites: *Sitios web y dominios > PHP Settings*, campos `upload_max_filesize`,
  `post_max_size`, `memory_limit` y `max_file_uploads`.

## Pendiente

- Confirmar SMTP o método de envío de correo disponible en Plesk.
- ~~Confirmar límites de subida de archivos para Excel e imágenes.~~ Confirmado el
  2026-09-11 y ajustado en Plesk: `upload_max_filesize` 10M, `post_max_size` 64M,
  `memory_limit` 256M, `max_execution_time` 120.
- Confirmar permisos de escritura reales tras subir a servidor.
- Confirmar que PHP puede hacer peticiones HTTPS salientes a `maps.app.goo.gl` para intentar extraer coordenadas de enlaces Maps.
