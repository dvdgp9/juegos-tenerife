# Censo de Entidades y Colectivos de Deportes y Juegos Motores Tradicionales de Canarias en Tenerife

## Background and Motivation

El objetivo es desarrollar una plataforma web en PHP vanilla y MySQL para publicar y administrar un censo de modalidades, federaciones, entidades/colectivos e instalaciones vinculadas a juegos y deportes tradicionales de Canarias en Tenerife.

La plataforma debe permitir:

- Búsqueda pública de entidades por nombre, municipio, tipo de entidad y modalidades.
- Visualización de resultados en listado y mapa interactivo.
- Ficha pública detallada por entidad, con datos de contacto, modalidades, características, mapa, fotos y PDF descargable.
- Backend para usuarios no técnicos con login, roles `superadmin` y `admin`, CRUD amplio e importación inicial/recurrente desde Excel.
- Formulario "Actualizar Censo" dirigido a `deportesdetenerife@gmail.com`.

El diseño debe tomar como inspiración la web `instalacionesdeportivastenerife.es` y los mockups aportados, pero adaptándose a una UX institucional, fluida, clara y móvil. Se usará el criterio de `design-taste-frontend`: interfaz sobria, asimétrica donde aporte valor, mapa protagonista, estados claros, buena legibilidad y CSS centralizado en `styles.css`.

El Excel real aún no está disponible. Se puede avanzar en arquitectura, estructura base, modelo de datos preliminar, diseño UX, setup del proyecto y preparación de importador con mapeo configurable, pero no se debe cerrar el esquema definitivo de importación hasta revisar una muestra del Excel.

## Key Challenges and Analysis

- Importación Excel: el Excel será la fuente inicial y puede haber importaciones posteriores. Debe existir una fase de previsualización antes de guardar datos para detectar errores, duplicados, altas y actualizaciones.
- Modelo de datos: hay campos multivalor como modalidades, instalaciones, teléfonos, emails, redes, fotos y protocolos. Conviene normalizar lo suficiente para permitir CRUD y búsqueda, evitando un modelo rígido que dependa de columnas exactas aún desconocidas.
- Ubicaciones: inicialmente se espera recibir enlaces cortos de Google Maps, por ejemplo `https://maps.app.goo.gl/5NANy6NfJ8sKJ8yc7`. Es viable usarlos como enlace externo, pero no son ideales como fuente única de coordenadas. Para pintar marcadores en Leaflet necesitamos `lat`/`lng`. Se deberá intentar extraer coordenadas si el enlace se puede expandir o pedir al Excel que incluya coordenadas separadas si es posible. Si solo hay enlaces cortos, el proceso de importación debe marcar filas "pendientes de geolocalización".
- Mapa: se recomienda Leaflet + OpenStreetMap por coste, flexibilidad, rendimiento y control visual. Debe mostrarse atribución de OpenStreetMap. Para geocodificación masiva no se debe abusar del Nominatim público; si hay muchos registros sin coordenadas, mejor resolver manualmente, pedir coordenadas en Excel o usar un proveedor de geocodificación con condiciones adecuadas.
- PDF: la ficha descargable puede generarse con Dompdf desde una plantilla específica. No necesita ser idéntica a la ficha web, pero debe ser clara, imprimible y completa.
- Seguridad: usar PDO con prepared statements, `password_hash` / `password_verify`, sesiones seguras, protección CSRF en formularios admin, validación de uploads, permisos por rol y almacenamiento de contraseñas solo como hash.
- Datos públicos: por decisión actual, los datos de contacto se mostrarán públicamente para facilitar contacto con las federaciones/entidades.
- Textos legales: copiar y adaptar desde `deportestenerife.es`.
- Hosting: se desplegará en Plesk de la empresa. Hay que confirmar en implementación rutas, versión PHP, extensiones necesarias y configuración SMTP/mail.

## High-level Task Breakdown

### 1. Definir arquitectura base del proyecto

Success criteria:
- Estructura de carpetas propuesta para PHP vanilla documentada.
- Separación clara entre `public`, `app`, `config`, `database`, `storage` y `assets`.
- Decisión documentada sobre Composer y dependencias mínimas.
- Sin tocar base de datos real.

### 2. Diseñar modelo de datos inicial

Success criteria:
- Esquema preliminar MySQL cubre entidades, modalidades, municipios, instalaciones, contactos, redes, fotos, usuarios, roles, importaciones y auditoría básica.
- Campos multivalor se resuelven con tablas relacionales o tablas hijas.
- Se documentan campos pendientes de confirmar por el Excel.
- No se ejecutan migraciones definitivas hasta revisar muestra del Excel.

### 3. Preparar prototipo visual estático público

Success criteria:
- Home estática con logo, menú, hero/collage, buscador, texto "Sobre el Censo" y sección de 6 modalidades.
- Página de resultados estática con listado y espacio de mapa.
- Ficha de entidad estática basada en el mockup, adaptada a los campos solicitados.
- Diseño responsive.
- CSS en `styles.css`, sin estilos inline.
- Uso de assets locales: logo y pictogramas.

### 4. Preparar frontend interactivo mínimo

Success criteria:
- Filtros de búsqueda con estados vacío, cargando y sin resultados.
- Leaflet integrado con datos mock y marcadores.
- Ficha con bloques laterales plegables.
- Web/redes se muestran condicionalmente según disponibilidad.
- Accesibilidad básica: labels, focus visible, navegación por teclado razonable.

### 5. Implementar autenticación y estructura admin

Success criteria:
- Login con usuario/correo y contraseña.
- Roles `superadmin` y `admin`.
- Passwords con `password_hash` y validación con `password_verify`.
- Protección CSRF en formularios.
- Dashboard admin básico con navegación.

### 6. Implementar CRUD administrativo inicial

Success criteria:
- CRUD de entidades.
- CRUD de modalidades.
- CRUD de instalaciones.
- CRUD de usuarios al menos para `superadmin`.
- Upload seguro de logo/fotos/pictogramas o selección desde assets.
- Validación server-side y mensajes de error útiles.

### 7. Implementar importador Excel

Success criteria:
- Carga de `.xlsx`, `.xls` o `.csv` con PhpSpreadsheet.
- Previsualización antes de guardar.
- Detección de columnas esperadas y aviso de columnas faltantes.
- Registro de importación con fecha, usuario, archivo, filas procesadas, errores y resultado.
- Filas sin coordenadas quedan marcadas para revisión si no se puede extraer ubicación desde Maps.

### 8. Implementar buscador público real

Success criteria:
- Búsqueda por texto libre en nombre y campos relevantes.
- Filtros por municipio, tipo de entidad y modalidad.
- Resultados paginados o limitados con UX clara.
- Filtros sincronizados con mapa.
- URLs compartibles con query string.

### 9. Implementar ficha pública real

Success criteria:
- Ficha renderiza datos reales.
- Logo de entidad o pictograma fallback.
- Datos de contacto visibles.
- Web/redes condicionales.
- Bloques "Información General", "Deportes y Modalidades Lúdicas", "Características", mapa y fotos.
- Bloques laterales plegables para contacto, información complementaria y PDF.

### 10. Implementar PDF de entidad

Success criteria:
- Botón descarga PDF desde ficha.
- PDF incluye todos los datos relevantes.
- Plantilla visual clara, imprimible y con logo.
- Maneja campos vacíos sin romper layout.

### 11. Implementar formulario "Actualizar Censo"

Success criteria:
- Formulario público enlazado desde el header.
- Envía a `deportesdetenerife@gmail.com` mediante SMTP/mail disponible en Plesk.
- Validación, CSRF, honeypot o protección antispam básica.
- Mensaje de éxito/error claro.

### 12. Textos legales y footer

Success criteria:
- Páginas de Aviso Legal, Privacidad y Cookies adaptadas desde `deportestenerife.es`.
- Footer institucional con enlaces.
- No se publican textos sin revisión humana final.

### 13. QA, pruebas y preparación despliegue

Success criteria:
- Pruebas manuales documentadas de flujos principales.
- Tests básicos para importador, autenticación y consultas críticas donde sea práctico.
- Revisión responsive en móvil y escritorio.
- Checklist Plesk: PHP, extensiones, permisos de storage, base de datos, SMTP, backups.
- `npm audit` solo aplica si se introduce tooling Node; si aparece vulnerabilidad en terminal, ejecutar audit antes de seguir.

## Project Status Board

- [x] Recopilar requisitos iniciales del usuario.
- [x] Revisar assets locales disponibles.
- [x] Investigar librerías/API principales.
- [x] Crear documentación inicial de investigación.
- [x] Crear plan inicial en scratchpad.
- [x] Esperar confirmación del usuario para ejecutar la tarea 1.
- [x] Validar manualmente la tarea 1: arquitectura base del proyecto.
- [x] Ejecutar tarea 2: modelo de datos inicial.
- [x] Ejecutar tarea 3: prototipo visual estático público.
- [x] Ejecutar tarea 4: frontend interactivo mínimo con filtros mock y mapa.
- [x] Inicializar repositorio git local y configurar remoto origin.
- [x] Revisar Excel real o muestra cuando esté disponible.
- [x] Definir esquema de datos inicial tras revisar Excel.
- [x] Implementar autenticación/admin privado base.
- [x] Implementar previsualización de importación Excel.
- [x] Implementar confirmación de importación a base de datos.
- [x] Implementar listado admin de entidades importadas.
- [x] Probar login e importación completa contra MySQL/MariaDB real.
- [x] Corregir incompatibilidad SQL en MariaDB por nombre reservado en `import_rows`.

## Current Status / Progress Tracking

Executor activo. El usuario autorizó avanzar y evaluar continuamente.

Tarea 1 completada: se creó la arquitectura base PHP vanilla:

- `public/index.php` como front controller.
- `routes/web.php` con ruta home inicial.
- `app/Core/` con `Application`, `Router`, `Response` y `View`.
- `app/Controllers/HomeController.php`.
- `app/Views/home.php`.
- `public/assets/css/styles.css`.
- `config/app.php` y `config/database.php`.
- `composer.json`, `.env.example`, `.gitignore`, `README.md`.
- Carpetas `database/`, `storage/`, `docs/` y documentación de Plesk.

Verificación ejecutada:

- `composer install` generó autoload correctamente sin dependencias externas.
- `php -S 127.0.0.1:8767 -t public` levantó servidor local porque `8766` estaba ocupado.
- `curl -s http://127.0.0.1:8767/` devolvió la home.
- `curl -s -I http://127.0.0.1:8767/` devuelve `HTTP/1.1 200 OK`.
- `php -l public/index.php`, `php -l app/Core/Router.php` y `php -l app/Core/Application.php` sin errores.

Tarea 2 completada: se diseñó el modelo de datos inicial.

- `docs/data-model.md` documenta el modelo y campos pendientes de confirmar con Excel.
- `database/schema-draft.sql` contiene un borrador MySQL con 14 tablas: usuarios, municipios, tipos, modalidades, entidades, relaciones, contactos, redes, instalaciones, medios, importaciones y auditoría.
- `database/seed-reference-draft.sql` contiene seed preliminar de 31 municipios y 6 modalidades principales.
- No se ejecutó SQL.
- Verificación: extracción de `CREATE TABLE` confirmó 14 tablas en el borrador.

Tarea 3 completada: se montó prototipo visual público.

- Home en `/` con logo, menú, llamada "Actualizar Censo", hero/collage, búsqueda, bloque sobre el censo y modalidades.
- Resultados en `/busqueda` con filtros, listado mock y mapa.
- Ficha en `/entidades/federacion-arrastre-canario` con estructura solicitada: cabecera con pictograma, datos de contacto, información general, modalidades, características, mapa, fotos y bloques laterales plegables.
- Assets copiados a `public/assets/images/`.
- CSS centralizado en `public/assets/css/styles.css`.

Tarea 4 completada en versión mock/progresiva.

- `public/assets/js/app.js` añade filtros client-side en resultados, estado de carga breve y estado vacío.
- Leaflet 1.9.4 integrado desde CDN oficial estable, con fallback visual si no carga.
- Mapa mock con marcadores para home/resultados/ficha.
- Verificación con navegador integrado: sin imágenes rotas, sin overflow horizontal en desktop ni móvil, filtros reducen resultados y estado vacío aparece correctamente.

Git:

- Se inicializó repositorio local.
- Se configuró `origin` como `https://github.com/dvdgp9/juegos-tenerife.git`.
- No se hizo commit ni push.

Excel de prueba revisado:

- Archivo: `/Users/dvdgp/Downloads/Plataforma Juegos Tenerife/Listado Entidades Prueba 20260522.xlsx`.
- Hoja: `ENTIDADES`.
- 11 registros reales de prueba, 68 columnas.
- Documentación añadida en `docs/excel-field-mapping.md`.
- Ajustes aplicados al SQL borrador:
  - `Modalidad4` cubierta por relación `entity_modalities`.
  - Municipios permiten marcar `is_tenerife` e `is_filterable` porque aparece `Agüimes`.
  - Protocolos Igualdad/Violencia/LOPIVI pasan de booleanos a estado (`Sí, propio`, `En proceso`, `No`, etc.).
  - Socios se guardan como texto porque hay valores como `291 en activo`.
  - Añadida tabla `entity_age_ranges` para tramos de edad del Excel.
- El botón público pasó de `Actualizar Censo` a `Comunicar actualización` para evitar que parezca acceso administrativo.
- Verificación: SQL borrador contiene 15 tablas; vistas PHP sin errores; CTA sin overflow en móvil/escritorio.

Admin privado:

- Ruta privada prevista: `/admin/login`.
- No hay enlace visible al admin desde la web pública.
- Implementados login, logout, sesión, CSRF y dashboard privado.
- Usuario superadmin inicial preparado en `database/003_seed_superadmin.sql` con hash, sin contraseña en claro.
- `/admin` y `/admin/import` redirigen a `/admin/login` si no hay sesión.
- No se pudo probar login real porque no hay base de datos local configurada todavía.

SQL inicial:

- `database/001_initial_schema.sql`
- `database/002_seed_reference_data.sql`
- `database/003_seed_superadmin.sql`

Importador Excel:

- Se instaló temporalmente PhpSpreadsheet, pero `composer audit` reportó vulnerabilidades críticas/altas vigentes.
- Se eliminó PhpSpreadsheet.
- Se instaló `openspout/openspout` v5.7.0.
- `composer audit` tras OpenSpout: sin vulnerabilidades.
- Implementado `ExcelPreviewService` con OpenSpout.
- Implementada pantalla `/admin/import` y POST `/admin/import/preview`.
- El servicio previsualiza correctamente el Excel real: hoja `ENTIDADES`, 11 registros, 68 columnas, 11 enlaces Maps, modalidades y municipios detectados.

Confirmación de importación:

- Implementado `EntityImportService`.
- La confirmación crea registro en `imports`, procesa filas, upserta entidades, tipos, municipios, modalidades, contactos, redes, instalaciones, tramos de edad y registra `import_rows`.
- La pantalla `/admin/import` ahora muestra botón de confirmación después de previsualizar.
- La ruta `POST /admin/import/confirm` exige sesión y CSRF.
- Sin servidor MySQL local activo: `mysqladmin ping -uroot` no pudo conectar a `/tmp/mysql.sock`. Queda pendiente prueba de integración real.
- MariaDB local existe, pero `root` devuelve `Access denied`, así que no se pudo crear una BD local de prueba sin credenciales.
- Implementado listado admin `/admin/entities` para revisar entidades importadas; exige sesión y redirige a login si no hay sesión.

Geolocalización Maps:

- Prueba shell `curl -L` con `https://maps.app.goo.gl/5NANy6NfJ8sKJ8yc7` resolvió a una URL con coordenadas `28.438214, -16.456722`.
- Implementado `GoogleMapsCoordinateExtractor` para intentar expandir enlaces y parsear coordenadas.
- En este sandbox PHP/cURL no pudo resolver DNS de `maps.app.goo.gl`; en Plesk habrá que confirmar salida HTTPS desde PHP.

Corrección despliegue MariaDB (Plesk):

- Error reportado en migración `001` por `row_number` en `import_rows` (parser de MariaDB lo trata como identificador problemático según configuración/versión).
- Se renombró la columna a `source_row_number` en:
  - `database/001_initial_schema.sql`
  - `database/schema-draft.sql`
  - `app/Services/Import/EntityImportService.php`
- Se añadió `database/004_fix_import_rows_reserved_name.sql` para entornos donde `001` quedó a medias: recrea `import_rows` con el nombre corregido y constraints correctas.
- Verificación local: `php -l app/Services/Import/EntityImportService.php` sin errores.

## Executor's Feedback or Assistance Requests

Tareas 1 a 4 implementadas y verificadas técnicamente por Executor. Pendiente de revisión visual/manual del usuario cuando quiera.

Notas:

- Repositorio remoto configurado: `https://github.com/dvdgp9/juegos-tenerife.git`.
- No se ejecutó SQL ni se creó esquema de base de datos real.
- El SQL creado es un borrador en `database/schema-draft.sql` y `database/seed-reference-draft.sql`; no ejecutarlo todavía en producción.
- El Excel no trae latitud/longitud ni logos/fotos. Trae enlaces cortos de Google Maps dentro de instalaciones.
- La dependencia de Excel será OpenSpout, no PhpSpreadsheet, por los avisos de seguridad detectados.
- La importación confirmada ya está codificada, pero falta probarla con MySQL/MariaDB real.
- El listado admin de entidades está codificado, pero falta probarlo con datos reales tras ejecutar SQL/importar.

Antes de ejecutar implementación, el Executor debe:

- Confirmar que empieza por una sola tarea del Project Status Board.
- No tocar base de datos real sin confirmación.
- No usar `-force` en git sin pedir permiso.
- Mantener CSS en `styles.css`.
- Leer archivos antes de editarlos.
- Documentar errores y soluciones en "Lessons".

## Lessons

- Incluir información útil de depuración en la salida del programa.
- Leer el archivo antes de editarlo.
- Si aparecen vulnerabilidades en terminal, ejecutar `npm audit` antes de proceder.
- Preguntar siempre antes de usar comandos git con `-force`.
- Poner siempre CSS en `styles.css`, no inline.
- Para este proyecto, los datos de contacto se muestran públicamente porque la finalidad es facilitar contacto con las entidades/federaciones.
- Los enlaces cortos de Google Maps son útiles como enlace externo, pero para pintar un mapa propio se necesitan coordenadas `lat`/`lng`; el importador debe detectar y marcar ubicaciones pendientes si no puede extraerlas.
- Leaflet estable actual revisado el 2026-05-22: 1.9.4. Existe una 2.0 alpha, pero para producción se usará la estable.
- En Plesk, el document root debe apuntar a `public/`.
- El Excel real de prueba contiene cabeceras duplicadas `Teléfono1` y `Teléfono2`; el importador debe mapear por posición/grupo, no solo por nombre de columna.
- `Agüimes` aparece en la muestra; el modelo debe permitir municipios fuera de Tenerife aunque el filtro público principal muestre solo los 31 municipios de Tenerife.
- Los protocolos no deben modelarse como booleanos puros porque existen estados intermedios como `En proceso`.
- Si aparece un aviso de seguridad en Composer, ejecutar `composer audit` y no continuar con una dependencia vulnerable si existe alternativa razonable.
- Para enlaces cortos de Google Maps, `curl -L` puede revelar coordenadas en la URL final si el enlace apunta a una búsqueda/coordenada. Si falla desde PHP, dejar `geocoding_status = pending` y completar manualmente en admin.
- En MariaDB/Plesk, evitar columnas con nombres ambiguos o potencialmente reservados en migraciones iniciales; usar nombres explícitos como `source_row_number` para tablas de staging/import.

---

## Planner — Fase: Cablear front público a BDD (2026-05-22)

### Background and Motivation (anexo)

Estado verificado en código: el front público (`HomeController`, `SearchController`, `EntityController`) **no consulta la base de datos**. Las únicas zonas conectadas a BDD son `AuthService`, `Admin\EntityController` (listado admin) y el importador. Como consecuencia, aunque el usuario haya ejecutado las migraciones y la importación en producción, la home pública sigue mostrando datos quemados — entre ellos las dos `result-card` mock en `app/Views/home.php:100-110` que el usuario percibe como "el listado solo muestra dos modalidades".

Objetivo de esta fase: cablear las tres pantallas públicas (home, búsqueda, ficha) a las tablas reales que ya pobló la importación, sin tocar admin ni importador.

### Key Challenges and Analysis (anexo)

1. **No hay capa de modelos.** `app/Models` está vacía. Hay dos opciones razonables:
   - Crear una clase repositorio por tabla relevante (`EntityRepository`, `ModalityRepository`, `MunicipalityRepository`, `EntityTypeRepository`). Mejor para mantenimiento y testabilidad.
   - Hacer las queries directamente en los controllers como ya hace `Admin\EntityController`. Más rápido, menos código, pero rompe la separación.
   - **Propuesta:** repositorios ligeros (PHP simple, sin ORM). Es coherente con el estilo "PHP vanilla" del proyecto, y la query del listado admin ya es lo bastante grande como para no querer duplicarla.

2. **Rutas de ficha.** Hoy hay una ruta fija `GET /entidades/federacion-arrastre-canario` apuntando a un controller con título quemado. El esquema tiene `entities.slug UNIQUE`. Hay que pasar a `GET /entidades/{slug}` y resolver por slug. El `Router` actual habrá que revisarlo: si no soporta parámetros, hay que añadir soporte mínimo (segmento `{slug}` → regex `[a-z0-9-]+`). Riesgo bajo, pero hay que verificar primero qué hace `app/Core/Router.php`.

3. **Filtros de búsqueda.** El form de búsqueda manda `q`, `municipio`, `tipo`, `modalidad` por GET. Decisiones:
   - `q`: `LIKE '%…%'` sobre `entities.name`. Suficiente para 11–500 registros. Full-text se puede añadir después si hace falta.
   - `municipio`, `tipo`, `modalidad`: hoy el form manda el **nombre** (string visible). Para no romper URLs compartibles ni el HTML actual, joinear por nombre exacto. Alternativa más limpia: cambiar el form a usar `slug` en `value=""`. Recomiendo **slug en `value=""`** porque es robusto frente a acentos/cambios de nombre, y el render visible sigue siendo el name. Coste: una iteración pequeña en el form.

4. **Home dinámica.** Tres bloques a alimentar:
   - Hero collage y sección "Modalidades": leer `modalities WHERE is_featured = 1 ORDER BY sort_order, name`. Si la tabla tiene <6 destacadas, completar con las primeras `is_featured = 0` o aceptar mostrar menos — pregunta abierta para el usuario.
   - Selector de municipios del buscador: `municipalities WHERE is_filterable = 1 ORDER BY sort_order, name`.
   - Selector de tipos: `entity_types ORDER BY name`. (Hoy hay 4 quemados; mejor reflejar lo que realmente exista en BDD).
   - `result-stack` (las dos tarjetas que el usuario ve como "el listado"): mostrar últimas N entidades publicadas o entidades destacadas. **Pregunta abierta.** Mientras se decide, propongo "últimas 6 publicadas" (`is_published = 1 AND deleted_at IS NULL ORDER BY updated_at DESC LIMIT 6`).

5. **Búsqueda + mapa.** La vista resultados tiene contenedor Leaflet. Hay que pintar marcadores solo de entidades con `latitude IS NOT NULL AND longitude IS NOT NULL`. El resto se listan pero no aparecen en el mapa, con aviso. Las coordenadas viajan al JS embebidas como JSON.

6. **Sanitización y seguridad.**
   - Todos los inputs del form pasan por `htmlspecialchars` al re-renderizar valores actuales.
   - SQL solo con prepared statements. Para `LIKE`, escapar `%` y `_` del input antes de bindear.
   - Sin cambios de permisos: estas pantallas son públicas, sin sesión.

7. **Paginación.** Para 11–50 entidades no es prioridad. Limitar a `LIMIT 100` por seguridad. Documentar como deuda técnica.

8. **Riesgo de despliegue.** Producción ya está en marcha con datos importados. Estos cambios solo añaden SELECTs, no modifican esquema ni escriben datos. Riesgo bajo. Sugiero validar en local contra una BD de prueba antes de subir, pero como no hay BD local ahora mismo (ver "Current Status"), una alternativa es hacer un release pequeño y verificar in situ inmediatamente.

### Preguntas abiertas para el usuario (responder antes de Executor)

- **P1 — Result-stack de la home:** ¿qué entidades listar? Opciones:
  - (a) Últimas N entidades publicadas (`ORDER BY updated_at DESC LIMIT 6`).
  - (b) Una entidad por modalidad destacada (representativa).
  - (c) Marcar manualmente "entidades destacadas" en admin (requiere añadir columna `is_featured` a `entities`, fuera del scope).
  - *Sugerencia: (a) para esta fase, (c) más adelante si se quiere comisariar.*

- **P2 — Modalidades destacadas:** ¿se quedan las 6 actuales (Lucha Canaria, Juego del Palo, Arrastre, Salto del Pastor, Bola Canaria, Lucha del Garrote) marcadas como `is_featured = 1` en `modalities`, o se renombra/amplía? El seed actual es `database/002_seed_reference_data.sql`. *Sugerencia: mantener las 6 actuales.*

- **P3 — Slug en values del form:** ¿OK cambiar `<option value="…">` para que envíe slug en lugar del name visible? Mejora robustez pero rompe URLs ya compartidas si las hubiera. *Sugerencia: sí, aún no hay tráfico que dependa de esas URLs.*

- **P4 — Ficha pública por slug:** la ruta actual `/entidades/federacion-arrastre-canario` se convierte en `/entidades/{slug}`. ¿Confirmas?

- **P5 — Tipos de entidad:** ¿mantener `Federación / Club / Colectivo / Asociación` quemados en el form como hoy, o leer de `entity_types`? *Sugerencia: leer de BDD, así si la importación crea un tipo nuevo aparece sin tocar código.*

### High-level Task Breakdown (anexo)

Cada paso es pequeño, verificable y se valida con el usuario antes del siguiente.

#### Paso A — Inspección y soporte de parámetros en el router

- Leer `app/Core/Router.php`. Confirmar si soporta `{param}`; si no, añadir soporte mínimo con regex.
- Verificar query existente del admin contra `001_initial_schema.sql` para no inventar columnas.
- **Success criteria:** test manual de una ruta dummy `/test/{slug}` que devuelve el slug recibido. (Sin commit; solo verificar mecánica).

#### Paso B — Crear repositorios

- `app/Models/ModalityRepository.php`: `featured(): array`, `all(): array`, `findBySlug(string $slug): ?array`.
- `app/Models/MunicipalityRepository.php`: `filterable(): array`, `findBySlug(string $slug): ?array`.
- `app/Models/EntityTypeRepository.php`: `all(): array`, `findBySlug(string $slug): ?array`.
- `app/Models/EntityRepository.php`:
  - `latestPublished(int $limit = 6): array` (con join a tipo + municipio + GROUP_CONCAT modalidades).
  - `search(array $filters, int $limit = 100): array` (filtros `q`, `municipio_slug`, `tipo_slug`, `modalidad_slug`).
  - `findBySlugWithRelations(string $slug): ?array` (entidad + contactos + redes + modalidades + instalaciones + tramos de edad).
- **Success criteria:** `php -l` limpio en cada archivo. (No se ejecutan queries todavía, solo se valida sintaxis).

#### Paso C — Cablear `HomeController`

- Pasar a la vista: `modalities` (desde repo), `municipalities`, `entity_types`, `featured_entities`.
- Reemplazar arrays quemados de `home.php` por las variables que llegan del controller.
- Las dos `result-card` se convierten en bucle sobre `$featured_entities`. Si vacío, mostrar estado vacío ("Aún no hay entidades destacadas").
- **Success criteria:**
  - La home pública lista todas las modalidades destacadas de BDD (no las 6 hardcodeadas).
  - El selector de municipios muestra los reales (incluyendo no-Tenerife si `is_filterable = 1`, aunque por defecto solo los 31 de Tenerife).
  - El bloque de tarjetas muestra las entidades reales recién importadas.
  - El usuario confirma visualmente en producción.

#### Paso D — Cablear `SearchController` + vista resultados

- Leer `q`, `municipio`, `tipo`, `modalidad` del GET, sanear, pasar a `EntityRepository::search()`.
- Renderizar listado real con link a `/entidades/{slug}`.
- Pintar marcadores Leaflet solo de entidades con coords.
- Re-poblar selectores del form con las mismas fuentes que la home.
- Manejar estados: sin resultados, sin filtros (mostrar todo limitado), error de BDD.
- **Success criteria:**
  - `/busqueda` sin filtros devuelve listado real.
  - `/busqueda?modalidad=lucha-canaria` filtra correctamente.
  - `/busqueda?municipio=adeje&tipo=federacion` combina filtros.
  - URLs compartibles (mantener slugs en query string).

#### Paso E — Cablear ficha pública

- Cambiar ruta `/entidades/federacion-arrastre-canario` por `/entidades/{slug}` con parámetro.
- `EntityController::show(string $slug)` resuelve con `findBySlugWithRelations`. Si no existe, 404.
- Vista `entity-show.php` consume datos reales: cabecera, contacto, modalidades, características, mapa con coords reales, instalaciones, tramos de edad.
- Bloques condicionales: si no hay redes, no mostrar el bloque; si no hay coords, ocultar mapa con aviso.
- **Success criteria:**
  - Una entidad importada es accesible por su slug.
  - Slug inexistente devuelve 404 limpio.
  - Bloques vacíos no rompen layout.

#### Paso F — Verificación end-to-end

- Pruebas manuales documentadas: home, búsqueda con cada filtro, ficha de 2–3 entidades distintas, móvil + desktop.
- Confirmar que `composer audit` sigue sin vulnerabilidades.
- Deuda técnica documentada: paginación, full-text, marcar entidades destacadas desde admin.

### Project Status Board — Fase Cableado público

- [x] Responder preguntas P1–P5. Decisiones tomadas por el modelo con autorización del usuario:
  - P1 home stack → 1 entidad por modalidad destacada, sin repetir, omitiendo huecos.
  - P2 modalidades destacadas → mantener las 6 actuales (seed 002).
  - P3 slugs en form → sí, slug en `value=""`.
  - P4 ruta ficha → `/entidades/{slug}`.
  - P5 tipos → leer de `entity_types`.
- [x] Paso A: router soporta `{param}` (regex `[^/]+`, args posicionales al handler).
- [x] Paso B: repositorios en `app/Models/`: Modality, Municipality, EntityType, Entity.
- [x] Paso C: `HomeController` y `app/Views/home.php` consumen BDD (modalidades, municipios, tipos y bloque destacado).
- [x] Paso D: `SearchController` filtra por `q`/`municipio`/`tipo`/`modalidad` (slug), `app/Views/search-results.php` renderiza server-side y emite `window.__mapPoints`.
- [x] Paso E: `EntityController::show($slug)` resuelve por slug o 404; `app/Views/entity-show.php` renderiza con datos reales y bloques condicionales.
- [x] Paso F: `php -l` limpio en todos los archivos tocados; `composer audit` sin vulnerabilidades. Verificación end-to-end contra BDD real queda pendiente del usuario (no hay MySQL local accesible).

### Notas de implementación

- `app/Core/Router.php` reescrito para soportar parámetros `{name}`. Mantiene compatibilidad: rutas literales siguen funcionando porque se compilan como regex sin captura.
- `EntityRepository::featuredByModality` evita duplicados: si una entidad ya fue elegida para una modalidad anterior, se busca la siguiente candidata.
- `EntityRepository::search` escapa `%` y `_` en el `LIKE` para `q` y bindea todo lo demás con prepared statements.
- `public/assets/js/app.js`: se eliminaron los `mapPoints` hardcoded y el filtrado client-side; ahora lee `window.__mapPoints` (JSON inyectado por el servidor) y hace `fitBounds` automático.
- `public/assets/js/app.js`: se desactivó el control nativo `zoomControl` de Leaflet porque renderiza anchors internos. Se sustituyó por botones propios `.map-zoom-controls` para evitar desplazamientos de página tipo `href="#"` al acercar/alejar.
- `home.php` ya no fuerza ruta fija; los enlaces "Ver ficha" usan `/entidades/{slug}` real.
- Las dos `result-card` mock que el usuario veía en producción ahora se generan dinámicamente desde BDD (una por modalidad destacada que tenga al menos una entidad publicada).

### Pendiente para el usuario

- Desplegar a Plesk (subir cambios en `app/`, `routes/web.php`, `public/assets/js/app.js`).
- Verificar visualmente en `https://www.deportesyjuegostradicionalescanarios.es/`:
  - Home muestra entidades reales en el bloque destacado (no Federación de Arrastre Canario / Club de Bola Canaria de mockup).
  - `/busqueda?modalidad=lucha-canaria` filtra correctamente.
  - `/entidades/{slug}` carga una entidad real importada.
  - Slug inexistente devuelve 404.

---

## 2026-05-28 — Cambios solicitados (revisión cliente)

### High-level Task Breakdown

1. **Home — copy y estructura**
   - Quitar `<p class="eyebrow">Censo oficial en Tenerife</p>` del hero.
   - Quitar `<h2 id="busqueda-title">Encuentra entidades por municipio…</h2>` del bloque de búsqueda.
   - Quitar `<h2>Modalidades principales como punto de entrada al censo</h2>` del bloque modalidades.
   - Cambiar eyebrow "Deportes y Modalidades" → "Modalidades".
   - Cambiar h2 de "Sobre el Censo" → "Una herramienta para preservar y acercar los Juegos Motores y Deportes tradicionales".

2. **Search results — copy**
   - Eyebrow "Resultados del Buscador" → "Buscador de entidades".

3. **Footer (ambas vistas)**
   - "Tenerife Deportes" → "Área de Deportes Cabildo de Tenerife".

4. **Pictograma Lucha Canaria**
   - Actualizar seed `002_seed_reference_data.sql`: `LUCHA_CANARIA_1.png` → `LUCHA_CANARIA_2.png`.
   - SQL puntual para BD existente: `UPDATE modalities SET icon_path='/assets/images/pictogramas/LUCHA_CANARIA_2.png' WHERE slug='lucha-canaria';`

5. **Favicon**
   - Añadir `<link rel="icon" ...>` en `home.php` y `search-results.php` apuntando a `/assets/images/favicon.png`.
   - El usuario debe guardar la imagen adjunta en `public/assets/images/favicon.png`.

6. **Bug municipios "contaminados" en el desplegable**
   - Restringir `MunicipalityRepository::filterable()` con `AND is_tenerife = 1 AND sort_order < 900` para excluir Agüimes y futuras filtraciones.
   - Cambiar `EntityImportService::upsertMunicipality()` para que los municipios nuevos creados por importación entren con `is_filterable = 0` por defecto (no aparecen hasta validación manual).
   - SQL de saneamiento para BD actual: marcar como no-filterable todo lo que no esté en la lista canónica de 31.

7. **Destacados — Agüimes en Salto del Pastor**
   - Decisión: opción (b) — no borrar la entidad, pero excluir entidades no-Tenerife de los destacados.
   - Añadir `AND m.is_tenerife = 1` en `EntityRepository::featuredByModality()`.

### Project Status Board

- [x] 1. Home copy
- [x] 2. Search results copy
- [x] 3. Footer
- [x] 4. Lucha Canaria icon
- [x] 5. Favicon
- [x] 6. Municipios dropdown
- [x] 7. Destacados Tenerife-only
