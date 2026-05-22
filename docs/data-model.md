# Modelo de datos inicial

Este modelo es preliminar hasta revisar el Excel real. Está pensado para cubrir la web pública, el backend CRUD, el importador Excel, el mapa y la generación de PDFs sin depender de campos multivalor dentro de una sola tabla.

## Principios

- Las entidades son el núcleo del censo.
- Municipios, tipos de entidad y modalidades se normalizan para facilitar filtros.
- Teléfonos, emails, redes, fotos, instalaciones y modalidades se separan en tablas hijas o relaciones.
- Las coordenadas `latitude` y `longitude` son opcionales, porque el Excel puede traer solo enlaces de Google Maps.
- Cada importación Excel queda registrada y cada fila puede guardar errores o avisos.
- Los datos de contacto son públicos por decisión del proyecto.

## Tablas principales

### `users`

Usuarios del backend.

Campos relevantes:

- `role`: `superadmin` o `admin`.
- `username`, `email`, `password_hash`.
- `is_active`, `last_login_at`.

### `municipalities`

Municipios usados para filtros y direcciones. La búsqueda pública debe mostrar los 31 municipios de Tenerife, pero el Excel de prueba incluye al menos una entidad con municipio fuera de Tenerife (`Agüimes`). Por eso la tabla permite marcar `is_tenerife` e `is_filterable`.

### `entity_types`

Tipos de entidad cargados desde Excel o gestionados desde admin. Ejemplos posibles: federación, club, colectivo, asociación.

### `modalities`

Modalidades deportivas/lúdicas. Incluye las 6 principales con descripción y pictograma, pero permite añadir más.

Campos relevantes:

- `name`, `slug`.
- `short_description`, `full_description`, `extra_info`.
- `icon_path`.
- `is_featured`.

### `entities`

Ficha central de cada entidad/colectivo/federación.

Campos relevantes:

- Identidad: `name`, `slug`, `entity_type_id`.
- Dirección: `address`, `locality`, `municipality_id`, `postal_code`.
- Ubicación: `google_maps_url`, `latitude`, `longitude`, `geocoding_status`.
- Contenido: `history`, `corporate_principles`, `sports_values`, `training_practices`, `training_days`, `training_hours`.
- Características: equipos, deportistas/practicantes, directiva, socios, protocolos y servicios.
- Medios: `logo_path`.
- Estado: `is_published`.

### `entity_modalities`

Relación muchos-a-muchos entre entidades y modalidades. Sustituye los campos Excel `Modalidad1`, `Modalidad2`, `Modalidad3` y `Modalidad4`.

### `entity_contacts`

Teléfonos, emails y personas de contacto.

Permite:

- Teléfonos visibles de entidad.
- Emails visibles de entidad.
- Persona de contacto con cargo, teléfono y email.

### `entity_social_links`

Redes sociales condicionales. Solo se muestran las que tengan URL.

Plataformas previstas:

- Facebook
- Instagram
- YouTube
- X
- TikTok
- Otra

### `facilities`

Instalaciones o espacios deportivos.

Campos:

- Nombre.
- Dirección.
- Municipio.
- Enlace Maps y coordenadas opcionales.

### `entity_facilities`

Relación entre entidades e instalaciones. Sustituye `Instalaciones1`, `Instalaciones2`, `Instalaciones3...`.

### `entity_age_ranges`

Tramos de edad de deportistas/practicantes detectados en el Excel (`0 a 5`, `6 a 11`, `12 a 17`, `18 a 29`, `30 a 45`, `46 a 59`, `60+`). Se separan para no endurecer el modelo si cambian los tramos en futuros Excel.

### `media_files`

Archivos asociados a entidades o modalidades: logos, fotos, documentos.

### `imports` e `import_rows`

Registro de importaciones Excel:

- Archivo.
- Usuario.
- Estado.
- Filas procesadas.
- Errores.
- Avisos.
- Datos originales por fila en JSON.

### `audit_logs`

Auditoría básica de acciones administrativas: creación, edición, eliminación, importaciones y cambios críticos.

## Observaciones del Excel de prueba 2026-05-22

- Hoja detectada: `ENTIDADES`.
- Registros de prueba: 11.
- Columnas detectadas: 68.
- Hay cabeceras duplicadas `Teléfono1` y `Teléfono2`: primero para la entidad y después para la persona de contacto.
- Hay `Modalidad4`; el modelo relacional ya lo absorbe sin añadir más columnas.
- Valores reales de `Tipo Entidad`: `Federación`, `Club`.
- El Excel incluye `Agüimes`, fuera de los 31 municipios de Tenerife. Se guardará como municipio no filtrable salvo decisión contraria.
- Protocolos como Igualdad, Violencia y LOPIVI no son booleanos simples: aparecen valores como `Sí, propio`, `No` y `En proceso`.
- Socios/as puede contener texto, por ejemplo `291 en activo`; por eso los totales de socios se guardan como texto normalizado, no solo como número.
- Instalaciones aparecen como texto libre con URL corta de Google Maps embebida.
- No hay columnas de latitud/longitud.
- No se observan columnas para logos o fotos en esta muestra.

## Campos pendientes de confirmar con Excel final

- Nombre exacto de todas las columnas.
- Si cada entidad tiene identificador único o hay que deduplicar por nombre + municipio.
- Si el Excel incluye coordenadas o solo enlaces de Maps.
- Si teléfonos/emails vienen separados o en una misma celda.
- Formato de redes sociales.
- Si hay fotos/logos referenciados por nombre de archivo, URL o se cargarán manualmente.
- Estructura real de instalaciones múltiples.
- Si hay campos separados para días y horarios de entrenamientos/prácticas.
- Si los municipios fuera de Tenerife deben publicarse y filtrarse o solo guardarse como dato de sede.
- Si el Excel final mantendrá cabeceras duplicadas para teléfonos o conviene renombrarlas.
- Si se podrán añadir columnas `Latitud` y `Longitud`.

## Decisiones para importación

- No crear entidades directamente sin previsualización.
- Guardar una copia del archivo importado en `storage/imports/`.
- Registrar cada fila con estado `pending`, `created`, `updated`, `skipped` o `error`.
- Si no hay `latitude`/`longitude`, usar `geocoding_status = 'pending'`.
- No ejecutar geocodificación masiva contra servicios públicos sin revisar volumen y condiciones.
