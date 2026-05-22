# Base de datos

Los archivos SQL de esta carpeta se entregan para revisión y ejecución manual en servidor.

## Archivos

- `schema-draft.sql`: esquema preliminar. No ejecutar todavía hasta revisar el Excel real o una muestra.
- `000_drop_all_tables_EMPTY_DB_ONLY.sql`: limpieza opcional si una migración inicial falló en una base vacía.
- `001_initial_schema.sql`: esquema inicial ejecutable.
- `002_seed_reference_data.sql`: datos de referencia.
- `003_seed_superadmin.sql`: usuario superadmin inicial.

## Convención

Cuando el modelo se cierre, se añadirá un archivo versionado final, por ejemplo:

- `001_initial_schema.sql`
- `002_seed_municipalities_modalities.sql`

El usuario ejecutará esos SQL en el servidor Plesk.
