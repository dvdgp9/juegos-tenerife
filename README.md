# Juegos Tenerife

Plataforma PHP vanilla + MySQL para el censo de entidades, colectivos, modalidades e instalaciones de juegos y deportes tradicionales de Canarias en Tenerife.

## Estado

Proyecto en fase inicial. La arquitectura base está preparada, pero el modelo SQL definitivo queda pendiente de revisar el Excel real o una muestra representativa.

## Requisitos previstos

- PHP 8.1 o superior.
- MySQL 8 o MariaDB compatible.
- Composer 2.
- Extensiones PHP habituales: PDO MySQL, mbstring, fileinfo, zip, xmlreader, gd o imagick si se procesan imágenes.

## Desarrollo local

```bash
composer install
php -S 127.0.0.1:8766 -t public
```

## Despliegue Plesk

La raíz pública del dominio debe apuntar a `public/`. El resto de carpetas (`app`, `config`, `database`, `storage`) no deben quedar expuestas como documentos públicos.

El SQL necesario se entregará en archivos dentro de `database/` para ejecución manual en servidor.

Scripts iniciales:

0. `database/000_drop_all_tables_EMPTY_DB_ONLY.sql` solo si una migración falló a medias en una BD vacía.
1. `database/001_initial_schema.sql`
2. `database/002_seed_reference_data.sql`
3. `database/003_seed_superadmin.sql`
