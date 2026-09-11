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

Executor activo para cambios solicitados por cliente el 2026-06-24.

Hito completado:

- Sustituir foto principal de cabecera por imagen de modalidad de lucha del garrote.
- Corregir etiquetas y valores públicos del cuadro "Información Complementaria" en ficha.
- Mostrar miembros de la directiva con desglose por género en una sola línea si existen datos importados.
- No implementar cambios para fotos de la entidad; solo responder criterio de ubicación al usuario.

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

2026-06-24:

- Se entiende el pedido de cabecera e información complementaria y se está ejecutando como cambio acotado de vistas/CSS.
- La pregunta "¿dónde irían en la ficha las fotos de la entidad?" se responde sin implementar: lo lógico es una sección "Fotos de la entidad" o "Galería" dentro de la ficha, separada de información complementaria, después de los datos principales y antes de bloques administrativos/documentales.
- Verificación técnica: `php -l app/Views/entity-show.php` sin errores; `composer audit` sin vulnerabilidades; imagen `public/assets/images/modalidades/lucha-del-garrote.jpg` existente.

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

---

## 2026-06-17 — Iconos secundarios de modalidades

- Copiados iconos de `Iconos-deportes/Fondo transparente dibujo oscuro/` a `public/assets/images/iconos-deportes/`.
- La carpeta fuente local `/Iconos-deportes/` queda ignorada; se versionan solo los assets públicos.
- `ModalityRepository::all()` ahora devuelve `icon_path`, necesario para miniaturas en resultados.
- `database/006_update_modality_icons.sql` actualiza la BDD existente con iconos para modalidades secundarias: Levantamiento de Arado, Levantamiento y Pulseo de Piedra, Petanca, Billarda Canaria y Pina.
- `EntityImportService` asigna iconos automáticamente a esas modalidades si aparecen en futuras importaciones.
- Las 6 modalidades principales del inicio no cambian de comportamiento; los iconos secundarios se usan en miniaturas de fichas/listados.

---

## 2026-06-19 — Fichas editoriales de modalidades

### Background and Motivation

Las seis tarjetas de modalidades principales de la portada deben enlazar a páginas públicas con la información facilitada en `DESCRIPCIÓN MODALIDADES PARA CENSO.docx` y las fotografías oficiales adjuntas.

### Key Challenges and Analysis

- El contenido es editorial y estable; se versionará en PHP para evitar una migración SQL innecesaria.
- La URL pública será `/modalidades/{slug}`, reutilizando los slugs ya existentes en el censo.
- Las fichas tendrán imagen protagonista, lectura por secciones y acceso al buscador filtrado por modalidad.
- Solo las seis modalidades principales tendrán ficha editorial por ahora; un slug no soportado devolverá 404.

### High-level Task Breakdown

1. Crear catálogo de contenido para las seis modalidades y ruta/controlador público.
2. Crear vista editorial responsive y enlazar las tarjetas de inicio.
3. Incorporar y optimizar las seis imágenes facilitadas.
4. Verificar sintaxis PHP, rutas, contenido y estilos; solicitar validación visual en Plesk.

### Project Status Board

- [x] Fichas editoriales de las seis modalidades principales — validadas por el usuario
- [x] Reactivar los enlaces desde las tarjetas de inicio — pendiente validación en Plesk tras pull
- [x] Adaptar importador al Excel definitivo `Listado Entidades Def.xlsx` — pendiente validación en Plesk

### Executor's Feedback or Assistance Requests

- Implementadas las rutas `/modalidades/{slug}`, el catálogo editorial, la vista responsive, los enlaces desde inicio y las seis fotografías.
- Verificación automática: las seis fichas responden 200 con imagen y CTA al buscador; slug inexistente responde 404; `php -l` y `git diff --check` limpios; `composer audit --no-dev` sin vulnerabilidades.
- Verificación visual local realizada a 1280×720 y 390×844, sin desbordamiento horizontal ni solapamientos.
- No se requiere cambio de base de datos. Se solicita validación manual del usuario antes de marcar la tarea como completada.
- Tras la validación, el usuario pidió volver a mostrar los botones. Los enlaces de las tarjetas se controlan con `$showModalityDetailLinks = true` en `home.php`; las fichas permanecen implementadas y enlazadas desde inicio.
- Executor revisó el Excel definitivo: hoja `ENTIDADES`, 41 filas con datos y 69 columnas. Cambios principales frente a la muestra: `Tipo Entidad` pasa a `Tipo Entidad1` + `Tipo Entidad2`; los teléfonos de contacto siguen como cabeceras duplicadas; muchas instalaciones no traen municipio explícito y deben heredar el municipio de la entidad.
- Ajuste implementado: `ExcelPreviewService` acepta `Tipo Entidad1`, mantiene compatibilidad con `Tipo Entidad`, crea aliases internos para teléfonos de contacto y normaliza `La Laguna`; `EntityImportService` usa `Tipo Entidad1` como tipo principal, conserva `Tipo Entidad2` en raw data, normaliza municipios y parsea instalaciones heredando el municipio de la entidad salvo prefijo de municipio conocido. Verificaciones: `php -l`, preview del Excel definitivo, prueba de aliases, prueba de instalaciones y `composer audit --no-dev` correctos.

---

## 2026-06-23 — Planner: backend editable para trabajadoras

### Background and Motivation

El frontend público está prácticamente cerrado y la plataforma ya cuenta con base PHP vanilla + MySQL, login admin, dashboard, importador Excel, listado admin de entidades y modelo relacional amplio. La siguiente fase debe convertir el área privada en una herramienta de gestión real para personal interno no técnico.

Decisiones confirmadas por el usuario:

- La importación Excel posterior a la carga inicial debe servir solo para **añadir** registros nuevos. Si una fila corresponde a una entidad ya existente, se debe marcar como repetida, no cargarla y no modificar ningún dato existente.
- Las trabajadoras deben poder editar lo máximo posible en cada entidad/instalación para evitar depender de soporte técnico.
- Las modalidades deben permitir subir/gestionar fotografía principal similar al patrón público revisado en `https://www.deportesyjuegostradicionalescanarios.es/`: página de modalidad con imagen protagonista y contenido editorial.
- Todas las usuarias internas tendrán el mismo permiso funcional en esta fase. No hace falta un sistema fino de permisos.
- El despliegue es en Plesk y hay control sobre límites de subida.

### Key Challenges and Analysis

1. **No crear backend nuevo.** Ya existe estructura PHP vanilla coherente con Plesk. La solución debe completar el admin existente con formularios server-side, CSRF, sesiones, PDO y transacciones.

2. **Regla crítica de importación.** El importador actual usa `ON DUPLICATE KEY UPDATE` en `entities` y reemplaza relaciones hijas. Eso contradice la decisión actual. Hay que cambiarlo a modo append-only:
   - Si la entidad ya existe por slug/nombre normalizado, registrar fila como `duplicate`.
   - No tocar `entities` ni tablas hijas para duplicados.
   - Mostrar resumen claro: creadas, repetidas, omitidas y errores.
   - Mantener raw data en `import_rows` para depuración.

3. **Edición amplia sin sobrecomplicar UX.** La ficha admin de entidad debe dividirse en secciones manejables:
   - Identidad y publicación.
   - Dirección y ubicación.
   - Datos públicos y contenido descriptivo.
   - Modalidades.
   - Contactos.
   - Redes.
   - Instalaciones.
   - Métricas, edades y protocolos.
   - Fotos/logo.

4. **Guardar entidades con seguridad.** Una edición de entidad toca muchas tablas. Cada guardado debe ir en transacción y, si falla, no debe dejar relaciones a medias.

5. **Instalaciones muy editables.** Las instalaciones no deben ser solo texto importado. Deben poder editar nombre, municipio, dirección, localidad, código postal, enlace Maps, latitud, longitud, notas y relación con una o varias entidades.

6. **Fotos y uploads.** Deben validarse tipo MIME, extensión y tamaño. En Plesk se puede subir el límite, pero el código debe seguir rechazando archivos peligrosos. Los assets subidos no deben permitir ejecutar PHP.

7. **Modalidades: BDD vs contenido PHP.** Hoy las fichas editoriales viven en `app/Content/ModalityContent.php`, mientras el listado/filtros usan `modalities` en BDD. Para edición interna real conviene mover gradualmente lo editable a BDD: imagen principal, texto corto, texto largo/secciones y estado destacado. Para no romper lo ya publicado, se puede hacer por fases.

8. **Permisos simples.** Aunque exista `role`, en esta fase todas las usuarias internas pueden gestionar contenido. Mantener `superadmin` solo para crear/desactivar usuarias si se implementa gestión de usuarios.

9. **Auditoría.** Como varias personas tocarán datos, conviene registrar creación, edición, publicación/despublicación, importación, duplicados detectados y subidas de archivos en `audit_logs`.

### High-level Task Breakdown

#### Paso 1 — Ajustar importador a modo solo altas

Success criteria:
- El importador detecta entidades existentes antes de insertar.
- Una fila duplicada queda registrada como `duplicate` o estado equivalente documentado.
- Las filas duplicadas no modifican `entities`, contactos, redes, instalaciones, modalidades ni tramos de edad.
- La pantalla de importación muestra contador de repetidas.
- Se documenta la regla en `docs/excel-field-mapping.md` o documentación de importación.
- Verificación con Excel de prueba que contenga una entidad existente y una nueva: la existente se marca repetida y la nueva se crea.

#### Paso 2 — Base común del admin

Success criteria:
- Layout admin común reutilizable para dashboard, listado, formularios e importación.
- Helper/método común para exigir sesión admin y evitar repetir lógica en cada controlador.
- Flash messages de éxito/error tras POST.
- Navegación admin limpia: Panel, Entidades, Instalaciones, Modalidades, Importar Excel, Usuarios.
- `php -l` limpio y sin estilos inline; todo CSS en `public/assets/css/styles.css`.

#### Paso 3 — CRUD de entidades: formulario principal

Success criteria:
- Rutas admin para crear, editar, guardar y publicar/despublicar entidades.
- Edición de campos principales de `entities`: nombre, slug controlado, tipo, municipio, dirección, localidad, CP, web, publicación, historia, principios, valores, entrenamientos, equipos, deportistas, directiva, socios, protocolos y apoyos.
- Validación server-side con mensajes útiles.
- Guardado en transacción.
- Enlace desde listado admin a editar y a ficha pública.
- Una entidad editada se refleja correctamente en `/entidades/{slug}`.

#### Paso 4 — CRUD de relaciones de entidad

Success criteria:
- En la edición de entidad se pueden gestionar modalidades asociadas.
- Se pueden gestionar teléfonos, emails y persona de contacto.
- Se pueden gestionar redes sociales.
- Se pueden gestionar tramos de edad/practicantes.
- Se pueden añadir, editar, ordenar y eliminar relaciones sin borrar accidentalmente datos no enviados.
- Cada guardado registra auditoría básica.

#### Paso 5 — Gestión completa de instalaciones

Success criteria:
- Listado admin de instalaciones con búsqueda por nombre/municipio.
- Crear/editar instalación con nombre, municipio, dirección, localidad, CP, enlace Maps, latitud, longitud, estado geocoding y notas.
- Asociar instalación a entidades desde la ficha de entidad y/o desde la ficha de instalación.
- Coordenadas manuales editables aunque el enlace Maps no resuelva.
- La instalación actualizada se refleja en mapas y fichas públicas.

#### Paso 6 — Uploads de logos, fotos de entidad e imagen de modalidad

Success criteria:
- Subida segura de logo/fotos de entidad y foto principal de modalidad.
- En la edición de cada entidad existe una sección **Fotos de la entidad**.
- Se pueden subir varias imágenes asociadas a una entidad.
- Cada foto de entidad puede tener orden, título opcional/alt text y marca de foto destacada.
- Las fotos de entidad se guardan como registros asociados en `media_files` con `owner_type = 'entity'`.
- La ficha pública de entidad muestra las fotos como Galería, respetando orden y destacada cuando exista.
- Validación de extensión/MIME/tamaño; rechazar SVG/PHP u otros formatos peligrosos salvo decisión explícita.
- Guardado con nombre generado, no con nombre original confiado.
- Registro en `media_files` o campos existentes (`logo_path`, `image_path` si se añade).
- Las imágenes se muestran en admin y en público.
- Documentar límites recomendados de Plesk y permisos de carpetas.

#### Paso 7 — CRUD de modalidades editable

Success criteria:
- Listado admin de modalidades.
- Crear/editar modalidad: nombre, slug, descripción corta, descripción larga o secciones, icono/pictograma, imagen principal, destacada, orden.
- Las seis modalidades principales siguen funcionando.
- La página `/modalidades/{slug}` puede leer primero desde BDD si hay contenido editable y mantener fallback al contenido PHP existente mientras se migra.
- Las fotos principales replican el uso visual de la web pública revisada: hero/foto representativa por modalidad.

#### Paso 8 — Gestión simple de usuarias

Success criteria:
- Crear usuaria interna con nombre, email/usuario y contraseña.
- Cambiar contraseña.
- Activar/desactivar usuaria.
- Todas las usuarias activas pueden gestionar contenido.
- Solo `superadmin` ve la pantalla de usuarias si se mantiene distinción.

#### Paso 9 — Auditoría y protección ante errores

Success criteria:
- `audit_logs` registra importaciones, duplicados, creación/edición de entidades, instalaciones, modalidades, uploads y cambios de publicación.
- Los errores de guardado muestran información útil para depuración sin revelar secretos.
- Soft delete para entidades e instalaciones; no borrado físico por defecto.
- Confirmaciones antes de acciones destructivas.

#### Paso 10 — QA en Plesk y documentación de uso

Success criteria:
- Checklist manual: login, crear entidad, editar entidad importada, duplicado en importación, editar instalación, subir foto de modalidad, editar modalidad, crear usuaria.
- `composer audit --no-dev` sin vulnerabilidades.
- Revisión móvil/escritorio del admin.
- Documento corto para trabajadoras: cómo importar, editar, publicar/despublicar y subir fotos.
- Documento técnico de despliegue actualizado con permisos de `storage/uploads`, límites de subida y backups.

### Project Status Board — Fase backend editable

- [ ] Paso 1: Ajustar importador a modo solo altas. Implementado por Executor; pendiente validación manual en Plesk antes de marcar completado.
- [ ] Paso 2: Base común del admin. Implementado por Executor; pendiente revisión visual/manual en Plesk.
- [ ] Paso 3: CRUD de entidades — formulario principal. Implementado por Executor; pendiente validación manual en Plesk.
- [ ] Paso 4: CRUD de relaciones de entidad. Implementado por Executor; pendiente validación manual en Plesk.
- [ ] Paso 5: Gestión completa de instalaciones. Implementado por Executor; pendiente validación manual en Plesk.
- [ ] Paso 6: Uploads de logos/fotos/foto de modalidad. Implementado por Executor; pendiente validación manual en Plesk.
- [ ] Paso 7: CRUD de modalidades editable. Implementado por Executor; pendiente validación manual en Plesk.
- [ ] Paso 8: Gestión simple de usuarias. Implementado por Executor; pendiente validación manual en Plesk.
- [ ] Paso 9: Auditoría y protección ante errores. Implementado y verificado por Executor contra MariaDB local el 2026-09-09; pendiente validación manual en Plesk.
- [ ] Paso 10: QA en Plesk y documentación de uso.

### Executor's Feedback or Assistance Requests

Planner recomienda que Executor empiece solo por el Paso 1. Es el cambio con mayor impacto funcional porque evita que una importación futura sobrescriba cambios manuales.

Antes de tocar base de datos real:

- Confirmar si se acepta añadir un nuevo estado `duplicate` en `import_rows.status` aunque la columna sea `VARCHAR` y no requiera migración.
- Confirmar si la detección de duplicado debe hacerse por slug derivado de nombre+municipio o por nombre exacto normalizado. Recomendación Planner: slug actual derivado del nombre, con fallback nombre+municipio si hay conflicto, para respetar la lógica existente.

2026-06-24 Executor:

- Implementado el Paso 1 en `app/Services/Import/EntityImportService.php`.
- El resumen de importación ahora incluye `duplicate`.
- Las filas repetidas devuelven estado `duplicate` y se registran en `import_rows.status`.
- Para filas repetidas, el importador detecta existencia antes de insertar o reemplazar relaciones de entidad; no toca `entities`, `entity_contacts`, `entity_social_links`, `entity_facilities`, `entity_modalities` ni `entity_age_ranges`.
- La pantalla `app/Views/admin/import.php` muestra `Repetidas` y `Omitidas`, y aclara que la importación solo añade entidades nuevas.
- Documentada la regla append-only en `docs/excel-field-mapping.md`.
- Verificación ejecutada: `php -l app/Services/Import/EntityImportService.php`, `php -l app/Views/admin/import.php` y `composer audit --no-dev` correctos.
- No se pudo hacer prueba end-to-end con MySQL/Plesk desde este entorno. Se solicita validación manual: subir un Excel que contenga una entidad ya existente y una entidad nueva; la existente debe aparecer como repetida y no cambiar su ficha pública/admin, la nueva debe crearse.

2026-06-24 Executor:

- Implementado el Paso 2: base común del admin.
- Añadido `app/Controllers/Admin/AdminController.php` con helper de autenticación y flash messages.
- Añadidos partials `app/Views/admin/partials/layout-start.php` y `layout-end.php` para cabecera/navegación compartida.
- Adaptadas vistas `dashboard`, `entities/index` e `import` para usar layout común.
- La navegación admin ahora muestra estado activo y marca herramientas pendientes como deshabilitadas en vez de usar enlaces `#`.
- Mejorada la interfaz admin en `public/assets/css/styles.css`: cabecera sticky, menú más escaneable, usuario visible, estados pendientes, resumen de tablas y confirmación de importación.
- Verificación ejecutada: `php -l` en controladores/vistas modificadas, `composer audit --no-dev`, servidor local temporal en `127.0.0.1:8768`, `/admin/login` responde 200 y `/admin` redirige a login sin sesión.
- Pendiente revisión visual/manual en Plesk con sesión real.

2026-06-24 Executor:

- Implementado el Paso 3: CRUD principal de entidades.
- Añadidas rutas `GET /admin/entities/new`, `POST /admin/entities`, `GET /admin/entities/{id}/edit` y `POST /admin/entities/{id}`.
- `Admin\EntityController` ahora permite crear y editar campos principales de `entities`: identidad, slug, tipo, municipio, dirección, mapa, publicación, web, textos, actividad, directiva, socios, protocolos y apoyos.
- Guardado con CSRF, validación server-side y transacción.
- Listado admin actualizado con acciones `Editar` y `Ver ficha`, además de botón `Crear entidad`.
- Añadida vista `app/Views/admin/entities/form.php`, estructurada por secciones para usuarios no técnicos.
- `MunicipalityRepository::allForAdmin()` permite mostrar también municipios no filtrables/no Tenerife en el admin.
- CSS añadido para formularios admin cómodos: navegación lateral por secciones, grids responsivos, helper text, barra de guardado sticky y acciones claras.
- Verificación ejecutada: `php -l` en rutas/controlador/vistas, `composer audit --no-dev`, `git diff --check` y rutas protegidas locales en `127.0.0.1:8768` redirigiendo a login sin sesión.
- Pendiente validación manual en Plesk con sesión real: crear entidad de prueba, editar una entidad existente, comprobar que la ficha pública refleja cambios y que slugs duplicados muestran error.

2026-06-24 Executor:

- Avance del Paso 4: relaciones de entidad.
- El formulario de entidad ahora permite gestionar modalidades asociadas con checkboxes cómodos.
- El formulario permite editar teléfonos, emails y persona de contacto.
- El formulario permite editar Facebook, Instagram, YouTube, X y TikTok.
- El guardado de `entities`, `entity_modalities`, `entity_contacts` y `entity_social_links` queda en la misma transacción.
- Al editar una entidad existente se precargan modalidades, contactos y redes actuales.
- Verificación ejecutada: `php -l app/Controllers/Admin/EntityController.php`, `php -l app/Views/admin/entities/form.php`, `git diff --check` y `composer audit --no-dev`.
- Añadidos tramos de edad al Paso 4: los siete rangos del Excel se editan en el formulario y se guardan en `entity_age_ranges` dentro de la misma transacción.
- Paso 4 queda técnicamente implementado para modalidades, contactos, redes y tramos de edad. Instalaciones quedan para Paso 5 y fotos para Paso 6.

2026-06-24 Executor:

- Implementado el Paso 5: gestión de instalaciones.
- Añadidas rutas `GET /admin/facilities`, `GET /admin/facilities/new`, `POST /admin/facilities`, `GET /admin/facilities/{id}/edit` y `POST /admin/facilities/{id}`.
- Añadido `Admin\FacilityController` con listado, crear, editar, validar y guardar instalaciones.
- El formulario permite editar nombre, slug, municipio, dirección, localidad, código postal, enlace Maps, latitud, longitud, estado de geolocalización y notas.
- El formulario permite asociar una instalación a una o varias entidades.
- El guardado de `facilities` y `entity_facilities` va en transacción.
- La navegación admin ya enlaza a `Instalaciones`.
- Añadidas vistas `app/Views/admin/facilities/index.php` y `app/Views/admin/facilities/form.php`.
- Ajustes CSS para listado/formulario de instalaciones, manteniendo la UX limpia y consistente con entidades.
- Verificación ejecutada: `php -l` en controlador/vistas/rutas, `composer audit --no-dev`, `git diff --check` y rutas protegidas locales en `127.0.0.1:8768` redirigiendo a login sin sesión.
- Pendiente validación manual en Plesk con sesión real: crear instalación, editar coordenadas, vincularla a una entidad y comprobar que aparece en ficha/mapa público.

2026-06-24 Executor:

- Avance del Paso 6: uploads de entidad.
- Añadido `MediaUploadService` con validación estricta de JPG, PNG, WEBP y GIF, máximo 8 MB, detección MIME con `finfo`, `getimagesize` y nombres generados.
- Añadido `public/uploads/.htaccess` para desactivar índices y bloquear ejecución de scripts dentro de uploads en Apache/Plesk.
- El formulario de entidad permite subir logo.
- El formulario de entidad permite subir varias fotos de galería.
- Las fotos existentes pueden editar título/caption, texto alternativo, orden, destacada y marcarse para eliminar.
- Los logos actualizan `entities.logo_path`; las fotos de galería se guardan en `media_files` con `owner_type = entity`.
- La ficha pública carga `media_files` y muestra una sección Galería con foto destacada primero.
- Verificación ejecutada: `php -l` en servicio/controlador/repositorio/vistas, `composer audit --no-dev` y `git diff --check`.
- Pendiente del Paso 6/7: foto principal de modalidad, que se implementará junto con el CRUD de modalidades porque todavía no existe pantalla admin de modalidades.

2026-06-24 Executor:

- Implementado el Paso 7: CRUD editable de modalidades.
- Añadidas rutas `GET /admin/modalities`, `GET /admin/modalities/new`, `POST /admin/modalities`, `GET /admin/modalities/{id}/edit` y `POST /admin/modalities/{id}`.
- Añadido `Admin\ModalityController` con listado, crear, editar, validar slug y guardar modalidad.
- La pantalla permite editar nombre, slug, descripción corta, descripción completa, información adicional, ruta de icono, destacada y orden.
- La pantalla permite subir foto principal de modalidad, con validación mediante `MediaUploadService`, almacenamiento en `public/uploads/modalities/{id}` y registro en `media_files` con `owner_type = modality`.
- La navegación admin ya enlaza a `Modalidades`.
- La página pública `/modalidades/{slug}` mantiene el contenido PHP existente como fallback y usa la foto principal administrada si existe en BDD.
- Si la base de datos no está disponible en la página pública de modalidad, se conserva el fallback estático para no romper la web.
- Añadidas vistas `app/Views/admin/modalities/index.php` y `app/Views/admin/modalities/form.php`.
- Ajustes CSS para miniaturas, foto principal y listados de modalidades, manteniendo consistencia con entidades e instalaciones.
- Verificación ejecutada: `php -l` en controlador/vistas/rutas, `composer audit --no-dev`, `git diff --check`, rutas protegidas locales en `127.0.0.1:8769` redirigiendo a login sin sesión y `/modalidades/lucha-del-garrote` respondiendo 200.
- Pendiente validación manual en Plesk con sesión real: crear modalidad de prueba, editar modalidad existente, subir foto principal y comprobar que se muestra en la ficha pública.

2026-06-24 Executor:

- Implementado el Paso 8: gestión simple de usuarias.
- No requiere cambios de base de datos porque la tabla `users` existente ya incluye `role`, `username`, `email`, `password_hash`, `full_name`, `is_active` y `last_login_at`.
- Añadidas rutas `GET /admin/users`, `GET /admin/users/new`, `POST /admin/users`, `GET /admin/users/{id}/edit` y `POST /admin/users/{id}`.
- Añadido `Admin\UserController` con listado, crear, editar, activar/desactivar y cambio opcional de contraseña.
- La creación y cambio de contraseña usan `password_hash`.
- Validación server-side: usuario obligatorio y único, correo válido y único, contraseña mínima de 10 caracteres y confirmación.
- La pantalla de usuarias queda limitada a `superadmin`; las usuarias `admin` activas siguen pudiendo gestionar contenido.
- Protección añadida para que la superadmin actual no pueda desactivar ni degradar accidentalmente su propia cuenta desde el formulario.
- La navegación admin ya enlaza a `Usuarios`.
- Añadidas vistas `app/Views/admin/users/index.php` y `app/Views/admin/users/form.php`.
- Añadidos estilos en `public/assets/css/styles.css` para ayuda de campos e interruptor de cuenta activa.
- Verificación ejecutada: `php -l` en controlador/vistas/rutas/layout, `composer audit --no-dev`, `git diff --check` y rutas protegidas locales en `127.0.0.1:8770` redirigiendo a login sin sesión.
- Pendiente validación manual en Plesk con sesión real superadmin: crear usuaria admin, editar sus datos, cambiar contraseña, desactivarla y confirmar que no puede acceder.

### Lessons

- En este proyecto, las importaciones posteriores a la carga inicial son append-only: si una entidad ya existe, se marca como repetida y no se actualiza.
- Para evitar soporte constante, el admin debe exponer edición amplia de entidades e instalaciones, incluidas coordenadas manuales.
- Las modalidades necesitan imagen principal administrable, siguiendo el patrón de página pública con foto protagonista por modalidad.

## 2026-06-26 — Planner: adaptar importación al Excel final 20260608

### Background and Motivation

El usuario aporta dos ficheros para revisar la importación Excel:

- Excel de prueba inicial: `/Users/dvdgp/Downloads/Plataforma Juegos Tenerife/Listado Entidades Prueba 20260522.xlsx`.
- Excel final actual: `/Users/dvdgp/Downloads/Plataforma Juegos Tenerife/Listado Entidades 20260608.xlsx`.

La estructura que debe soportar la plataforma a partir de ahora es la del Excel final. La importación debe seguir permitiendo revisar antes de guardar y debe resolver las ubicaciones incluidas en las columnas de instalaciones, donde las celdas mezclan nombre de instalación y enlace corto de Google Maps.

### Key Challenges and Analysis

- El Excel de prueba tiene 68 columnas y 11 registros. El Excel final tiene 69 columnas y 41 registros.
- La columna nueva relevante en el final es `Tipo Entidad2`; el importador actual ya crea el alias interno `Tipo Entidad Complementaria`, pero hoy solo conserva ese dato en `raw_data` y no lo guarda en un campo consultable.
- El final mantiene cabeceras duplicadas `Teléfono1` y `Teléfono2` en posiciones distintas. El mapeo actual por ocurrencia funciona y crea `Teléfono Contacto1` y `Teléfono Contacto2`.
- El preview actual del Excel final no detecta cabeceras faltantes. Detecta 35 celdas con enlaces de Google Maps, 2 filas sin `Domicilio`/`Código Postal`, y 1 fila con `Agüimes` como municipio fuera de Tenerife.
- Comprobación puntual y masiva ejecutada el 2026-06-26: los 35 enlaces únicos `maps.app.goo.gl` del Excel final resolvieron coordenadas correctamente con `GoogleMapsCoordinateExtractor` en este entorno.
- La plataforma ya tiene modelo de instalaciones con `google_maps_url`, `latitude`, `longitude` y `geocoding_status`, más edición manual desde admin. Esto encaja con la necesidad de "que la plataforma encuentre esa ubicación".
- Riesgo técnico: depender de la expansión de enlaces cortos de Google Maps en tiempo de importación puede fallar por red, cambios de Google o timeout. El importador debe conservar siempre la URL y dejar `geocoding_status = pending` si no puede resolver coordenadas.
- Riesgo funcional: si `Tipo Entidad2` debe mostrarse o filtrarse públicamente, el modelo actual de un único tipo principal no basta. Recomendación Planner: para esta fase no cambiar filtros públicos; conservar `Tipo Entidad2` como dato complementario visible en admin/raw o añadir un campo simple si el usuario confirma que debe aparecer.

### High-level Task Breakdown

#### Paso 1: Actualizar documentación y tests de contrato del Excel final

Success criteria:
- `docs/excel-field-mapping.md` referencia explícitamente `Listado Entidades 20260608.xlsx` como estructura vigente.
- Existe una prueba o script verificable que compare cabeceras esperadas contra el Excel final y falle si cambia una cabecera crítica.
- La prueba valida al menos: 69 columnas, hoja `ENTIDADES`, duplicados de `Teléfono1`/`Teléfono2`, alias `Tipo Entidad`, alias `Tipo Entidad Complementaria`, y ausencia de cabeceras faltantes.
- No se toca base de datos real.

#### Paso 2: Hacer explícita la estrategia de `Tipo Entidad2`

Success criteria:
- El importador conserva `Tipo Entidad2` de forma auditable.
- Si se decide guardarlo en `entities`, existe migración no destructiva y el admin lo muestra/edita.
- Si no se decide guardarlo todavía, queda documentado que se conserva solo en `import_rows.raw_data`.
- El preview muestra `Tipo Entidad1`/`Tipo Entidad2` de forma clara cuando existan.

#### Paso 3: Endurecer importación de instalaciones y geolocalización

Success criteria:
- El parseo de `Instalaciones1` a `Instalaciones9` cubre celdas con formato `Nombre https://maps.app.goo.gl/...` y `Municipio: Nombre https://...`.
- El importador guarda siempre instalación y URL aunque falle la extracción de coordenadas.
- Si resuelve coordenadas, guarda `latitude`, `longitude` y `geocoding_status = resolved`.
- Si no resuelve, deja `geocoding_status = pending` y una advertencia útil en `import_rows.warnings`.
- La importación no queda bloqueada por timeouts o fallos de una URL concreta.

#### Paso 4: Probar preview e importación contra el Excel final

Success criteria:
- `ExcelPreviewService` devuelve para el Excel final: 41 registros, 69 cabeceras, 35 enlaces Maps detectados y sin cabeceras faltantes.
- Prueba de importación en base de datos de desarrollo o staging crea entidades nuevas sin sobrescribir duplicadas.
- Al menos una entidad con instalación geolocalizada aparece con marcador en la ficha/mapa público.
- Las dos filas sin domicilio/código postal se importan con warning, no como error fatal.

#### Paso 5: QA manual en Plesk antes de dar por cerrada la adaptación

Success criteria:
- Subir `Listado Entidades 20260608.xlsx` desde `/admin/import`.
- Revisar la previsualización: filas, modalidades, municipios, repetidas y avisos.
- Confirmar importación y revisar resumen de creadas/repetidas/errores.
- Abrir listado de instalaciones y comprobar coordenadas resueltas o pendientes.
- Abrir varias fichas públicas con instalaciones y confirmar mapa/listado de ubicaciones.

### Project Status Board — Fase importación Excel final

- [ ] Paso 1: Actualizar documentación y tests de contrato del Excel final. Validación manual omitida por falta de SSH; verificación local de Executor correcta.
- [ ] Paso 2: Hacer explícita la estrategia de `Tipo Entidad2`. Implementado por Executor; pendiente ejecutar migración y validar en Plesk antes de marcar completado.
- [ ] Paso 3: Endurecer importación de instalaciones y geolocalización. Implementado por Executor; pendiente validar con importación real en Plesk.
- [ ] Paso 4: Probar preview e importación contra el Excel final.
- [ ] Paso 5: QA manual en Plesk antes de dar por cerrada la adaptación.

### Executor's Feedback or Assistance Requests

Planner recomienda que Executor empiece solo por el Paso 1. Es pequeño, no toca base de datos y deja una prueba de contrato para que los siguientes cambios no rompan la estructura final del Excel.

Antes del Paso 2, confirmar con el usuario si `Tipo Entidad2` debe mostrarse públicamente o solo conservarse para auditoría interna. Recomendación Planner por simplicidad: conservarlo para auditoría en esta fase y no añadirlo a filtros públicos salvo petición explícita.

Antes de pruebas contra base de datos real, usar entorno de desarrollo/staging o copia; no ejecutar importaciones destructivas. La regla vigente sigue siendo append-only: las entidades existentes se registran como repetidas y no se sobrescriben.

2026-06-26 Executor:

- El usuario confirmó que `Tipo Entidad2` debe añadirse como subtipo secundario de entidad y mostrarse solo en la ficha pública, con menor jerarquía visual que el tipo principal.
- Implementado el Paso 1.
- `docs/excel-field-mapping.md` ahora referencia `Listado Entidades 20260608.xlsx` como estructura vigente.
- Actualizado el mapeo documentado de `Tipo Entidad2`: será subtipo secundario, consultable/editable en admin y visible en ficha pública, sin cambiar el filtro principal.
- Añadido `tests/excel_contract.php` para validar el contrato del Excel final: hoja `ENTIDADES`, 41 registros, 69 columnas, cabeceras exactas, duplicados de `Teléfono1`/`Teléfono2`, 35 enlaces Maps, alias `Tipo Entidad`, alias `Tipo Entidad Complementaria` y teléfonos de contacto por posición.
- Añadido script Composer `test:excel-contract`.
- Verificación ejecutada: `php -l tests/excel_contract.php`, `composer test:excel-contract`, `composer audit --no-dev` y `git diff --check` correctos.
- Solicitud de validación manual: ejecutar `composer test:excel-contract` en el entorno local con el Excel final disponible. Si pasa, el Planner puede marcar el Paso 1 como completado y autorizar el Paso 2.

2026-06-26 Executor:

- El usuario indicó que no tiene acceso SSH ahora mismo y autorizó continuar sin la validación manual del Paso 1.
- Implementado el Paso 2.
- Añadida columna no destructiva `entities.entity_subtype` en `database/007_add_entity_subtype.sql`.
- Actualizados `database/001_initial_schema.sql` y `database/schema-draft.sql` para nuevas instalaciones.
- La importación Excel guarda `Tipo Entidad2`/`Tipo Entidad Complementaria` en `entities.entity_subtype`.
- La previsualización de importación ahora devuelve `subtypes`, añade `subtype` en filas de muestra y muestra "Subtipos detectados" en `/admin/import`.
- El formulario admin de entidad permite editar `Subtipo de entidad`.
- El listado admin muestra el subtipo como dato secundario bajo el tipo.
- La ficha pública muestra el subtipo bajo el nombre de la entidad con estilo secundario, sin añadirlo a filtros ni listados principales.
- Verificación ejecutada: `php -l` en servicios/controlador/vistas modificadas, `composer test:excel-contract`, `composer audit --no-dev`, `git diff --check`, comprobación de esquema y comprobación directa de preview del Excel final. El preview detecta `subtypes={"Escuela":2}`.
- Pendiente validación manual en Plesk: ejecutar `database/007_add_entity_subtype.sql`, importar o editar una entidad con subtipo y comprobar que se ve en ficha pública.

2026-06-26 Executor:

- Implementado el Paso 3.
- `GoogleMapsCoordinateExtractor` ahora usa timeout de conexión de 4 segundos y timeout total de 8 segundos para evitar bloqueos largos por enlaces de Maps que no respondan.
- `EntityImportService::replaceFacilities()` ahora devuelve avisos de geolocalización por instalación.
- `EntityImportService::importRow()` añade esos avisos a `import_rows.warnings` antes de registrar la fila.
- Si una URL de Google Maps falla, lanza excepción o no contiene coordenadas parseables, la instalación se guarda igualmente con `google_maps_url`, `latitude = NULL`, `longitude = NULL` y `geocoding_status = pending`.
- Si resuelve coordenadas, se guarda con `geocoding_status = resolved`.
- El parseo existente cubre formatos `Nombre https://maps.app.goo.gl/...` y `Municipio: Nombre https://maps.app.goo.gl/...`; se mantiene el municipio de la entidad como fallback.
- Verificación ejecutada: `php -l` en servicios afectados, `composer test:excel-contract`, `composer audit --no-dev`, `git diff --check` y prueba controlada de los 35 enlaces únicos del Excel final. Resultado: `unique_maps_urls=35 resolved=35 failed=0`.
- Pendiente validación manual en Plesk: importar el Excel final tras aplicar la migración de subtipo y revisar que las instalaciones aparecen como `resolved` o `pending` sin bloquear la importación.

2026-06-26 Executor:

- El usuario validó que el Paso 4 y el listado funcionan en Plesk, pero detectó que el GEO de entidades queda `pending` aunque las instalaciones asociadas estén `resolved`.
- Causa: el Excel aporta coordenadas a través de instalaciones, no de entidad; el importador estaba resolviendo `facilities` pero no propagaba una ubicación de referencia a `entities`.
- Corrección aplicada: tras importar instalaciones, `EntityImportService` sincroniza la entidad con la primera instalación geolocalizada por `entity_facilities.sort_order`, copiando `latitude`, `longitude` y `geocoding_status = resolved`.
- Para evitar marcadores duplicados en ficha pública, `EntityController` ahora usa marcadores de instalaciones cuando existen y solo usa coordenadas directas de entidad como fallback.
- Añadido `database/008_backfill_entity_geo_from_facilities.sql` para corregir entidades ya importadas en Plesk sin sobrescribir coordenadas manuales existentes.
- Verificación ejecutada: `php -l` en archivos afectados, `composer test:excel-contract`, `composer audit --no-dev`, `git diff --check` y comprobación de existencia de SQL `007`/`008`.
- Pendiente validación manual en Plesk: ejecutar el SQL `008`, refrescar listado de entidades y comprobar que las que tienen instalaciones resueltas pasan a GEO `resolved`.

### Lessons

- En OpenSpout dentro de este proyecto, las filas se leen con `$row->toArray()`. No usar `$row->getCells()`, porque no existe en la versión instalada.
- Los enlaces cortos `maps.app.goo.gl` del Excel final 20260608 pueden resolverse con el extractor actual, pero la importación debe tolerar fallos de red y conservar la URL con estado pendiente.
- El alias `Tipo Entidad Complementaria` solo aparece cuando `Tipo Entidad2` tiene valor; las pruebas deben validar una fila con subtipo informado, no asumir que la primera fila lo trae.
- `Tipo Entidad2` queda como `entities.entity_subtype`: subtipo secundario visible solo en ficha pública/admin, no en filtros públicos.
- Los fallos de geolocalización en instalaciones no deben convertir la fila en error: se guardan como warning y quedan pendientes de revisión manual.
- El estado GEO de entidades debe derivarse de la primera instalación resuelta cuando el Excel no trae coordenadas propias de entidad.

---

## 2026-07-06 — Executor: ajustes de identidad pública solicitados por cliente

### Background and Motivation

La cliente solicita tres ajustes de identidad y copy público:

- El favicon no debe usar el logo completo porque se deforma y pierde legibilidad; debe usar el símbolo.
- Las páginas interiores deben mantener el faldón institucional para reforzar identidad.
- En las fichas, el bloque "Instalaciones" debe llamarse "Instalaciones / Espacios de práctica" porque algunos espacios no son instalaciones formales.

### Current Status / Progress Tracking

Executor completado:

- Se confirmó que `public/assets/images/favicon.png` era el logo completo comprimido en 64x64.
- El usuario facilitó el símbolo correcto en `/Users/dvdgp/Downloads/favicon-dxt.png` y se sustituyó `public/assets/images/favicon.png` por ese archivo.
- Se añadió footer institucional a páginas públicas interiores que no lo tenían: resultados y ficha de entidad.
- Se cambió el rótulo público de ficha a "Instalaciones / Espacios de práctica".
- Verificación ejecutada: `php -l app/Views/search-results.php`, `php -l app/Views/entity-show.php` y `composer audit --no-dev` correctos.

### Executor's Feedback or Assistance Requests

- Sin solicitudes pendientes para esta tanda.

---

## 2026-09-09 — Executor: Paso 9 (auditoría y protección ante errores)

### Current Status / Progress Tracking

Implementado el Paso 9 de la fase "backend editable para trabajadoras".

Auditoría:

- Añadido `app/Services/AuditLogger.php`: escribe en la tabla `audit_logs` (ya existente en `001_initial_schema.sql`, no hace falta migración) y expone `recent()` para la pantalla de actividad.
- El registro nunca rompe la operación que lo origina: si falla la escritura se anota con `error_log` y la petición continúa.
- Añadido helper `audit()` en `AdminController`, que atribuye la acción a la usuaria en sesión.
- Acciones registradas: `auth.login`, `auth.login_failed`, `auth.logout`, `entity.created`, `entity.updated`, `entity.published`, `entity.unpublished`, `entity.deleted`, `facility.created`, `facility.updated`, `facility.deleted`, `modality.created`, `modality.updated`, `media.uploaded`, `media.deleted`, `user.created`, `user.updated`, `import.completed` (con creadas/repetidas/omitidas/errores), `import.failed`.
- `EntityImportService::import()` devuelve ahora `import_id` en el resumen para poder referenciar la importación en la auditoría.
- Nunca se registran contraseñas: en `user.updated` solo se guarda el booleano `password_changed`.

Borrado lógico:

- Nuevas rutas `POST /admin/entities/{id}/delete` y `POST /admin/facilities/{id}/delete`.
- `EntityController::destroy()` marca `deleted_at` y fuerza `is_published = 0`; `FacilityController::destroy()` marca `deleted_at`.
- No hay borrado físico en ninguna de las dos pantallas.
- Ambas exigen sesión y CSRF; sin CSRF válido no se archiva nada y se muestra aviso.

Confirmaciones:

- Añadido `public/assets/js/admin.js`, cargado desde el layout admin: intercepta el submit de cualquier formulario con `data-confirm` y pide confirmación.
- Botón "Archivar" en los listados de entidades e instalaciones, con texto de confirmación que nombra el registro afectado.

Errores de guardado:

- `Database::connection()` ya no lanza el detalle de infraestructura (host, base, usuario, longitud de contraseña) en el mensaje de la excepción; ese diagnóstico va a `error_log` y el mensaje visible es genérico.
- Los errores de guardado de entidades, instalaciones y modalidades siguen mostrando el motivo concreto dentro del admin, que está tras login.

Copy obsoleto corregido:

- Dashboard: la tarjeta "Próximas herramientas" pasa a "Instalaciones y modalidades" con enlace real; los textos ya no anuncian como pendiente lo que ya existe.
- Listado de entidades: eliminado el aviso de que la edición llegaría "en el siguiente bloque".
- Dashboard: nueva sección "Actividad reciente" con las últimas 12 entradas de `audit_logs`.

### Verificación ejecutada (contra MariaDB real, por primera vez)

Se montó base de datos local de pruebas `juegos_tenerife_test` en la MariaDB del equipo (12.2.2), con usuario `juegos_test@127.0.0.1` limitado a esa base. No se tocó ninguna base de producción.

- Migraciones `001`, `002` y `003` se ejecutan sin errores.
- `007_add_entity_subtype.sql` falla con "Duplicate column name" sobre una base nueva, porque `001` ya incluye la columna. Es correcto: `007` solo aplica a bases creadas antes de esa corrección.
- Login correcto y login fallido: ambos quedan registrados en `audit_logs`.
- Crear entidad publicada → `entity.created`. Editar y quitar publicación → `entity.updated` + `entity.unpublished`.
- Archivar sin CSRF válido: rechazado, `deleted_at` sigue a NULL.
- Archivar con CSRF válido: `deleted_at` fijado, `entity.deleted` registrado.
- La entidad archivada desaparece del listado admin y su ficha pública devuelve 404.
- Crear y archivar instalación: `facility.created` y `facility.deleted` registrados, `deleted_at` fijado.
- Editar modalidad → `modality.updated`. Crear usuaria → `user.created` con el id correcto.
- Confirmación del navegador: al cancelar el diálogo, el formulario no se envía y el registro no se archiva.
- Dashboard renderiza la actividad reciente con autor y fecha; sin desbordamiento horizontal a 375 px.
- Home, buscador y ficha pública siguen respondiendo 200 con datos reales.
- `php -l` limpio en todos los archivos tocados, `git diff --check` limpio y `composer audit --no-dev` sin vulnerabilidades.

### Executor's Feedback or Assistance Requests

- `import.completed` / `import.failed` están implementados pero no probados end-to-end: no hay ningún Excel del censo disponible en el equipo para lanzar una importación real. Queda para la prueba manual en Plesk.
- Se creó un `.env` local apuntando a `juegos_tenerife_test`. Está en `.gitignore`, no viaja al repositorio y no afecta a Plesk.
- Decisión tomada dentro del Paso 9: el borrado lógico se aplica solo a entidades e instalaciones, como pedía el criterio. Modalidades no se pueden archivar (romperían fichas editoriales públicas) y las usuarias se desactivan con `is_active`, que ya existía.

### Lessons

- La tabla `audit_logs` existía desde la migración inicial pero ningún código escribía en ella; conviene verificar que las tablas previstas en el esquema tengan escritura real antes de darlas por hechas.
- El registro de auditoría no debe poder tumbar la operación auditada: capturar cualquier excepción y seguir.
- Los mensajes de error visibles no deben describir la infraestructura; el diagnóstico va al log del servidor.
- `Slugger::slug()` depende de `iconv('ASCII//TRANSLIT')`, cuyo resultado cambia según el sistema: en macOS "Federación" produce `federaci-on-de-...`. En Linux/Plesk (glibc) suele resolverse como `federacion-de-...`. Conviene sustituirlo por un mapa de transliteración explícito para que los slugs públicos no dependan del servidor.

---

## 2026-09-09 — Executor: ajustes tras la primera importación real

### Current Status / Progress Tracking

La importación en producción funcionó: 51 filas, 12 creadas, 39 repetidas, 0 errores. El usuario pidió tres ajustes.

1. Comprobación de repetidos en la previsualización:

- Añadido `app/Services/Import/EntityDuplicateFinder.php` con la detección de duplicados (normalización de municipio, cálculo de slug, búsqueda por slug y por nombre+municipio).
- `EntityImportService` delega en él en vez de tener la lógica duplicada, para que preview e importación no puedan divergir.
- `ExcelPreviewService` devuelve ahora `entityRows` con fila, nombre y municipio de todos los registros, no solo de la muestra.
- `ImportController::preview()` clasifica esas filas contra el censo y pasa `duplicateCheck` a la vista. Si la consulta falla, se muestra el aviso y la previsualización sigue funcionando.
- La pantalla muestra "Se crearían / Repetidas / Sin nombre" y el listado de filas repetidas con el motivo.
- La clasificación contempla también los repetidos dentro del propio Excel: si un nombre aparece dos veces, la primera se crearía y la segunda queda como repetida, igual que hace la importación.
- El texto de confirmación anuncia las cifras reales, con singular y plural correctos.

2. Estilo de la pantalla de importación:

- El campo de subida se maqueta con `::file-selector-button` y caja punteada propia, sin JavaScript.
- El titular del resumen baja a 1.25rem y las cifras suben a 1.5rem: el peso visual va al dato, no al rótulo.

3. Botones de las tablas:

- Añadido `app/Views/admin/partials/icons.php` con la función `admin_icon()`. Los SVG son trazados de Lucide (licencia ISC) incrustados en línea: sin CDN, sin dependencia nueva y sin petición extra.
- Iconos en Editar, Ver ficha y Archivar de los cuatro listados del panel.
- Botones más compactos: 0.79rem, altura mínima 30px, separación de 6px.
- Corregidos los contadores en singular: "1 instalación" en vez de "1 instalaciones".

### Verificación ejecutada

Contra la base local `juegos_tenerife_test`, con un Excel generado para el caso:

- Fila con nombre idéntico a una entidad existente → repetida.
- Fila con el mismo nombre en distinta caja y con espacios sobrantes → repetida (confirma que la comparación normaliza).
- Fila nueva → se crearía.
- Fila repetida dentro del propio archivo → repetida, indicando la fila original.
- Fila sin nombre → sin nombre.
- Previsualización: 1 nueva, 3 repetidas, 1 sin nombre. Importación real: Creadas 1, Repetidas 3, Omitidas 1, Errores 0. **El preview coincide exactamente con el resultado.**
- `import.completed` quedó registrado en `audit_logs` con el desglose correcto: es la parte que no se pudo probar en el Paso 9.
- Las seis pantallas del panel responden 200 y renderizan los iconos.
- Sin errores ni avisos PHP en el log del servidor. `php -l`, `git diff --check` y `composer audit --no-dev` limpios.

### Executor's Feedback or Assistance Requests

- La previsualización hace una consulta por fila para calcular el slug y buscar coincidencias. Con 51 filas es inmediato; si el censo creciera a varios miles de filas por importación habría que agrupar las consultas.

### Lessons

- La lógica de detección de duplicados debe vivir en un único sitio compartido por preview e importación: si se duplica, el preview acabaría prometiendo algo distinto de lo que hace la importación.
- El preview debe contemplar los duplicados dentro del propio archivo, no solo contra la base de datos.
- Para un puñado de iconos, incrustar los SVG en línea sale más barato que añadir una librería: sin dependencia, sin CDN y sin petición extra.

---

## 2026-09-09 — Executor: formulario de usuaria descuadrado y mostrar contraseña

### Current Status / Progress Tracking

Causa del descuadre: en `.form-grid`, las celdas se estiraban a la altura de la fila. Un campo con `field-help` es más alto que su vecino, así que el `select` de "Permiso" crecía hasta 46px + el sobrante y su etiqueta quedaba descolgada respecto a "Usuario". Lo mismo pasaba en la fila de contraseñas.

- Añadido `align-self: start` a las celdas de `.form-grid` y `align-content: start` en `.field`: cada campo conserva su altura natural.
- La corrección es global, así que también alinea los formularios de entidad, instalación y modalidad, que tenían el mismo defecto.
- Añadido "Mostrar contraseña": un checkbox que controla los dos campos a la vez mediante `data-toggle-password` en `admin.js`. Sin JS inline y con el CSS en `styles.css`.
- El toggle aparece tanto al crear como al editar usuaria.

### Verificación ejecutada

- Medido en el navegador: "Usuario" y "Permiso" comparten `top` (532) y altura (46). "Contraseña" y "Repetir contraseña" comparten `top` (919) y altura (46).
- Formulario de entidad: 62 controles, ninguno con altura anómala; los textareas conservan su altura propia (175/150/150).
- Mostrar contraseña: alterna `password` → `text` → `password` en los dos campos y conserva lo escrito.
- Las cinco pantallas de formulario responden 200.
- Guardar usuaria sigue funcionando (302 al listado) y la validación de contraseñas distintas sigue avisando.
- `php -l`, `node --check` sobre `admin.js` y `git diff --check` limpios.

---

## 2026-09-11 — Planner: galerías de fotos en instalaciones y entidades

### Background and Motivation

Carmen vuelve a preguntar por las fotos: hace falta una forma de añadir fotografías en formato galería a instalaciones, clubes y asociaciones, gestionada desde el panel.

Auditoría del código antes de planificar: **la galería de entidades ya existe de punta a punta** (Paso 6 de la fase backend editable, implementado y nunca validado en Plesk). Están `media_files` polimórfica, `MediaUploadService` con validación estricta, la sección "Fotos de la entidad" del formulario de entidad y el bloque "Galería" de la ficha pública.

Lo que no existe es lo que probablemente se está pidiendo: **las instalaciones (`facilities`) no tienen fotos**. Y las fotografías disponibles en el repositorio (`Fotos-instalaciones-tenerife/`: terreros, campo de bolas de El Socorro, Casa del Ganadero, Casa de Carta) son justamente de instalaciones, no de clubes.

Decisión de alcance acordada con el usuario el 2026-09-11: **las dos cosas**. Las instalaciones tendrán galería propia porque es donde están las fotos reales y porque una instalación compartida por varios clubes debe subirse una sola vez; las entidades conservan la suya para fotos del club en sí (equipo, actividades, entregas de trofeos).

### Key Challenges and Analysis

1. **No hay que construir el modelo de datos, hay que reutilizarlo.** `media_files` es polimórfica (`owner_type` / `owner_id` / `media_type`). Añadir instalaciones significa usar `owner_type = 'facility'`. **No hace falta migración de base de datos.** Esto reduce mucho el riesgo del trabajo.

2. **El riesgo real no son las galerías, es el peso de las imágenes.** Hoy `MediaUploadService` guarda el original tal cual, hasta 8 MB. Una ficha con seis fotos de móvil puede superar los 40 MB. Esto es lo primero que hay que resolver: en cuanto alguien suba fotos de verdad, la web pública se vuelve inusable en móvil, que es donde más se va a consultar. El redimensionado va antes que cualquier galería nueva.

3. **Orientación EXIF.** Las fotos verticales hechas con móvil llevan la rotación en metadatos. Al redimensionar con GD esos metadatos se pierden y la foto sale tumbada. Hay que leer la orientación con `exif_read_data()` y rotar antes de reescalar. Es un fallo que se ve a simple vista y que descubriríamos tarde si no se contempla desde el principio.

4. **Ficheros huérfanos.** Al eliminar una foto solo se borra la fila de `media_files` (`EntityController.php:765`), el fichero se queda en disco para siempre. Con derivados (versión web + miniatura) el problema se multiplica por tres. Hay que borrar físicamente, validando siempre que la ruta cae dentro de `public/uploads` para que un `file_path` manipulado no pueda borrar otra cosa.

5. **Duplicación de código entre entidad e instalación.** La gestión de galería en `EntityController` ocupa unas 150 líneas (`syncEntityMedia`, `updateExistingMedia`, `storeGalleryUploads`, `normalizeMultipleUpload`, `mediaFilesForEntity`). Copiarlas a `FacilityController` sería el camino corto y el error clásico: dos copias que divergen en cuanto alguien arregle un fallo en una sola. Conviene extraer un servicio compartido parametrizado por `owner_type` y que ambos controladores lo usen. El coste extra es pequeño y se paga solo en el primer arreglo.

6. **Dos orígenes de fotos en la misma ficha pública.** La ficha de un club mostrará sus fotos propias y las de sus instalaciones vinculadas. Si se mezclan en una sola rejilla, parecerá que el terrero es propiedad del club. Deben ir en dos bloques con rótulos distintos, e indicar de qué instalación procede cada foto.

7. **Entorno Plesk sin verificar.** GD está disponible en local (PHP 8.4), pero hay que confirmarlo en Plesk junto con `upload_max_filesize`, `post_max_size` y `max_file_uploads`, que pueden cortar en silencio una subida de varias fotos a la vez. También los permisos de escritura de `public/uploads`. Conviene comprobarlo antes de escribir código de redimensionado, no después.

8. **Las instalaciones no tienen ficha pública propia.** No hay ruta `/instalaciones/{slug}`. Sus fotos se verán a través de las fichas de las entidades vinculadas. Darles página propia es una decisión de producto aparte y **queda fuera del alcance de este plan**.

### High-level Task Breakdown

#### Paso A — Comprobación previa del entorno Plesk

Sin código. Verificar en producción: extensión GD activa, extensión exif activa, valores de `upload_max_filesize`, `post_max_size` y `max_file_uploads`, y permisos de escritura en `public/uploads`.

Criterio de éxito: documentados los cuatro valores y confirmadas ambas extensiones. Si GD no estuviera disponible, el Paso B cambia de enfoque y hay que volver al Planner antes de seguir.

#### Paso B — Redimensionado y saneamiento de imágenes

Ampliar `MediaUploadService` para que, además de validar, genere versiones utilizables: corrección de orientación por EXIF, versión web con el lado mayor limitado a 1600 px y miniatura con el lado mayor a 400 px. El registro en `media_files` guarda la ruta de la versión web; la miniatura se deriva por convención de nombre.

Criterios de éxito:
- Una foto de 4000 px y 6 MB produce una versión web por debajo de 400 KB.
- Se genera la miniatura correspondiente.
- Una foto vertical de móvil se muestra vertical en el panel y en la ficha.
- Una imagen ya pequeña no se reescala hacia arriba.
- `php -l` limpio y `composer audit --no-dev` sin vulnerabilidades.

#### Paso C — Borrado físico de ficheros al eliminar una foto

Al borrar un registro de `media_files`, eliminar del disco la versión web y la miniatura. Validar con `realpath()` que la ruta está dentro de `public/uploads` antes de borrar nada.

Criterios de éxito:
- Eliminar una foto desde el panel deja de existir el fichero en disco.
- Un `file_path` manipulado que apunte fuera de `public/uploads` no borra nada y queda registrado.
- Si el fichero ya no existe, la eliminación del registro no falla.

#### Paso D — Extraer la gestión de galería a un servicio compartido

Mover la lógica de galería de `EntityController` a un servicio parametrizado por `owner_type`, y dejar `EntityController` usándolo. Sin cambios funcionales visibles: es una refactorización previa para no duplicar en el Paso E.

Criterios de éxito:
- La galería de entidades sigue funcionando igual: subir, ordenar, destacada, título, alt, eliminar.
- Los eventos `media.uploaded` y `media.deleted` se siguen registrando en auditoría.
- `EntityController` ya no contiene lógica de galería duplicable.

#### Paso E — Galería de instalaciones

Añadir la sección "Fotos de la instalación" al formulario de instalación, usando el servicio del Paso D con `owner_type = 'facility'` y `media_type = 'facility_photo'` / `facility_featured_photo`.

Criterios de éxito:
- Desde la edición de una instalación se pueden subir varias fotos a la vez.
- Cada foto admite orden, título, texto alternativo, marca de destacada y eliminación.
- Las operaciones quedan registradas en auditoría con `owner_type = 'facility'`.
- Crear una instalación nueva y subirle fotos funciona igual que editarla.

#### Paso F — Mostrar las fotos de instalaciones en la ficha pública

`EntityRepository` recupera también las fotos de las instalaciones vinculadas. La ficha muestra dos bloques separados: "Galería" para las fotos propias de la entidad y "Fotos de las instalaciones" para las de sus espacios, indicando el nombre de la instalación.

Criterios de éxito:
- Un club con una instalación con fotos muestra el segundo bloque con el nombre de la instalación.
- Un club sin fotos de ningún tipo no muestra ningún bloque vacío.
- Una instalación compartida por dos clubes aparece en las fichas de ambos sin duplicar ficheros.
- La consulta no dispara una petición por instalación.

#### Paso G — Lightbox y carga diferida

Ampliar las fotos al hacer clic y cargar de forma diferida las que no se ven al entrar.

Criterios de éxito:
- Al hacer clic la foto se amplía; se cierra con Esc y con clic fuera.
- Se puede recorrer la galería con el teclado y el foco vuelve a su sitio al cerrar.
- Todas las imágenes salvo la primera llevan `loading="lazy"`.
- La rejilla usa las miniaturas y el lightbox la versión web.
- Sin JavaScript en línea y con el CSS en `styles.css`.

#### Paso H — QA en Plesk y documentación

Prueba manual completa en producción y una nota breve para las trabajadoras sobre cómo subir fotos y dónde aparecen.

Criterios de éxito:
- Subida real de varias fotos a una instalación y a una entidad desde Plesk.
- Comprobado el peso total de una ficha con galería en móvil.
- Documento corto añadido a `docs/`.

### Project Status Board — Fotos y galerías

- [ ] Paso A: Comprobación previa del entorno Plesk (GD, exif, límites de subida, permisos).
- [ ] Paso B: Redimensionado, miniaturas y corrección de orientación EXIF.
- [ ] Paso C: Borrado físico de ficheros al eliminar una foto.
- [ ] Paso D: Extraer la gestión de galería a un servicio compartido.
- [ ] Paso E: Galería de instalaciones en el panel.
- [ ] Paso F: Fotos de instalaciones en la ficha pública.
- [ ] Paso G: Lightbox y carga diferida.
- [ ] Paso H: QA en Plesk y documentación de uso.

### Executor's Feedback or Assistance Requests

Planner recomienda empezar por el **Paso A**, que no toca código y condiciona el enfoque del Paso B.

Decisiones pendientes antes de ejecutar el Paso B:

- **¿Se conserva el archivo original además de la versión web?** Recomendación del Planner: no conservarlo. Un original de 8 MB no aporta nada en una web pública de consulta y multiplica el uso de disco en Plesk. Si en el futuro hiciera falta material en alta resolución, debería vivir fuera de la aplicación.
- **¿Se re-codifican las imágenes a un único formato?** Recomendación del Planner: mantener el formato de origen y limitarse a reescalar. Convertirlo todo a JPG estropearía los PNG con transparencia (por ejemplo un logotipo subido por error a la galería) y aporta poco.
- **Límite de 8 MB por archivo:** con redimensionado en servidor se puede mantener, ya que el peso final deja de depender del original. Queda condicionado a lo que se encuentre en el Paso A.

Fuera de alcance de este plan, anotado para no perderlo: las instalaciones no tienen ficha pública propia (`/instalaciones/{slug}`). Si se quisiera que una instalación fuese enlazable por sí misma, es una decisión de producto que requiere su propio plan.

---

## 2026-09-11 — Executor: Paso A (comprobación del entorno)

### Current Status / Progress Tracking

Decisiones del usuario registradas antes de empezar:

1. **No se conserva el archivo original.** Solo se guardan la versión web y la miniatura.
2. **No se re-codifican las imágenes.** Se mantiene el formato de origen y solo se reescala.
3. **Se mantiene el límite de 8 MB por archivo**, condicionado a los límites reales de Plesk.

No hay acceso por consola al servidor documentado en el repositorio, así que el Paso A no se
puede ejecutar desde aquí. En su lugar se ha creado la herramienta que lo resuelve en un paso:

- Añadido `tests/env_check.php`: comprueba versión de PHP, extensiones `gd`, `exif` y
  `fileinfo`, formatos que GD puede generar, `upload_max_filesize`, `post_max_size`,
  `max_file_uploads`, `memory_limit`, `max_execution_time` y permisos de escritura de las
  cinco carpetas que la aplicación necesita. Solo lee: no modifica nada.
- Distingue problemas bloqueantes de avisos, y devuelve código de salida 1 si hay bloqueantes,
  para poder encadenarlo en un despliegue.
- Documentado en `docs/deployment/plesk.md`, junto con la ruta equivalente en la interfaz de
  Plesk para cuando no haya consola.

### Verificación ejecutada

En local (PHP 8.4.11): `gd`, `exif` y `fileinfo` presentes; GD genera JPEG, PNG, WEBP y GIF;
las cinco carpetas son escribibles. Salida correcta, código 0. `php -l` limpio.

El único aviso en local es `memory_limit` de 128 MB. Es relevante para el Paso B: una foto de
4000x3000 ocupa unos 48 MB ya descomprimida en memoria, y GD necesita tener a la vez el origen
y el destino. Con 128 MB se puede trabajar si se libera el recurso de origen antes de generar
la miniatura, pero conviene comprobar qué valor tiene Plesk antes de decidir el tamaño máximo
admitido.

### Executor's Feedback or Assistance Requests

**Paso A pendiente de que el usuario ejecute `tests/env_check.php` en Plesk y comparta la
salida.** Los valores locales no sirven: el servidor tiene su propia configuración de PHP.

De esa salida dependen dos cosas del Paso B:

- Si faltara `gd`, el enfoque del redimensionado cambia y hay que volver a Planner.
- El `memory_limit` de Plesk determina si 8 MB por archivo es un límite sostenible o hay que
  bajarlo.

---

## 2026-09-11 — Executor: resultado del Paso A

### Current Status / Progress Tracking

El usuario no dispone de consola en el servidor, así que `tests/env_check.php` no se ha podido
ejecutar. Los datos se han obtenido de la interfaz de Plesk, en dos pantallas.

**Entorno:** PHP 8.4.25 FPM.

**Extensiones** (*Herramientas y configuración > Configuración de PHP > Extensiones*):

- `gd`: activa.
- `imagick`: activa. **No estaba previsto y es mejor opción que GD.**
- `fileinfo`: activa.
- `exif`: no aparece en la lista.
- Otras relevantes ya confirmadas: `zip`, `xmlreader` (importación Excel), `pdo_mysql`.

**Límites actuales:**

- `upload_max_filesize`: 2M
- `post_max_size`: 8M
- `memory_limit`: 128M
- `max_execution_time`: 30
- `max_input_time`: 60

### Key Challenges and Analysis — revisión del Paso B

1. **Imagick sustituye a GD en el Paso B.** Resuelve de golpe tres de los problemas que el
   plan daba por asumidos:
   - Lee la orientación de la foto por sí mismo (`getImageOrientation()` / `autoOrient`), así
     que **la ausencia de `exif` deja de importar**. Con GD habría sido bloqueante.
   - Su consumo de memoria no cuenta contra el `memory_limit` de PHP, sino contra los límites
     propios de ImageMagick. El aviso sobre los 128 MB deja de ser crítico, aunque subir a
     256 MB sigue siendo recomendable por el resto del proceso.
   - Redimensiona con mejor calidad que GD, que es visible en fotografía.

   Se usará Imagick sin respaldo en GD: está confirmado en el servidor y mantener dos caminos
   para el mismo trabajo duplicaría la superficie de error a cambio de nada.

2. **`upload_max_filesize` a 2M es bloqueante y ya está afectando a lo que existe.** La
   galería de entidades, que está implementada desde el Paso 6, no puede recibir una foto de
   móvil normal (3-5 MB): PHP la rechaza antes de que el código la vea. No es un fallo de la
   aplicación, es configuración del servidor.

3. **`post_max_size` a 8M limita el envío completo del formulario**, no cada archivo. El
   formulario de entidad es largo; con tres o cuatro fotos se supera.

4. **Efecto secundario comprobado en código:** al superarse `post_max_size`, PHP descarta
   `$_POST` entero. En `EntityController::update()` eso hace fallar la verificación CSRF, de
   modo que **no se guarda nada y la entidad queda intacta** — el CSRF actúa aquí como red de
   seguridad involuntaria. Pero el mensaje que se muestra es "La sesión ha caducado. Vuelve a
   intentarlo." y, además, `entityFromPost()` rellena todos los campos con cadena vacía, así
   que el formulario se repinta en blanco. Quien lo sufra creerá que ha perdido el trabajo.

### Executor's Feedback or Assistance Requests

**Paso A cerrado salvo por los cambios de configuración, que dependen del usuario.**

Valores a cambiar en Plesk antes del Paso B:

| Ajuste | Actual | Necesario | Motivo |
|---|---|---|---|
| `upload_max_filesize` | 2M | 10M | Sin esto no entra ninguna foto de móvil. Bloqueante. |
| `post_max_size` | 8M | 64M | Permite varias fotos en un mismo envío. Bloqueante. |
| `memory_limit` | 128M | 256M | Recomendable, ya no crítico gracias a Imagick. |
| `max_execution_time` | 30 | 120 | Procesar varias fotos en un envío. |

Queda sin confirmar `max_file_uploads` y el permiso de escritura real de `public/uploads`.
Ninguno de los dos bloquea el desarrollo: se verificarán en el Paso H con una subida real.

Propuesta de paso nuevo, surgida de este análisis y pendiente de aprobación:

- **Paso I — Mensaje claro al superarse el tamaño del formulario.** Detectar el caso
  (`$_POST` vacío con `CONTENT_LENGTH` mayor que cero) y mostrar "Las fotos superan el tamaño
  máximo del formulario" en lugar del aviso de sesión caducada. Aplica a entidades,
  instalaciones y modalidades.

### Paso A — cerrado el 2026-09-11

El usuario confirma que ya ha aplicado los cuatro cambios de configuración en Plesk. Quedan
por verificar con una subida real en el Paso H, no antes: la interfaz de Plesk no siempre
refleja lo que acaba aplicando el pool de FPM.

Decisión sobre el entorno de desarrollo: **instalar Imagick en local** para que lo que se
prueba aquí sea exactamente lo que corre en el servidor. Descartado el doble camino
Imagick/GD, que habría dejado sin probar justo la rama que se despliega.

Instalado en local: ImageMagick 7.1.2-31 (Homebrew) y la extensión `imagick` 3.8.1 vía PECL.

Incidencia durante la instalación, por si se repite en otra máquina: `pecl install imagick`
falla al final con `failed to mkdir /opt/homebrew/Cellar/php/8.4.11/pecl/20240924`. La causa
es que Homebrew deja `Cellar/php/<versión>/pecl` como enlace simbólico a
`/opt/homebrew/lib/php/pecl`, carpeta que no existe. Se resuelve creándola a mano
(`mkdir -p /opt/homebrew/lib/php/pecl/20240924`) y repitiendo la instalación. El módulo
compila bien en el primer intento; lo único que falla es el copiado final.

`tests/env_check.php` actualizado al nuevo enfoque: `imagick` y `fileinfo` pasan a ser
obligatorias; `gd` y `exif` quedan como informativas, con la salvedad de que si faltara
Imagick entonces `exif` vuelve a ser imprescindible. Añadido aviso por `max_execution_time`
bajo y suprimido el de `memory_limit` cuando hay Imagick, porque su memoria no cuenta contra
el límite de PHP.

Salida en local: todo correcto, código 0.

### Project Status Board — Fotos y galerías (actualizado 2026-09-11)

- [x] Paso A: Comprobación previa del entorno Plesk. Cerrado: Imagick disponible en servidor
      y en local, límites de subida ajustados por el usuario.
- [ ] Paso B: Redimensionado, miniaturas y corrección de orientación **con Imagick**.
- [ ] Paso C: Borrado físico de ficheros al eliminar una foto.
- [ ] Paso D: Extraer la gestión de galería a un servicio compartido.
- [ ] Paso E: Galería de instalaciones en el panel.
- [ ] Paso F: Fotos de instalaciones en la ficha pública.
- [ ] Paso G: Lightbox y carga diferida.
- [ ] Paso I: Mensaje claro al superarse el tamaño del formulario. **Aprobado por el usuario
      el 2026-09-11.** Conviene hacerlo junto al Paso D, que es cuando se toca el flujo de
      guardado de los tres controladores.
- [ ] Paso H: QA en Plesk y documentación de uso.

### Lessons

- Comprobar las extensiones disponibles en el servidor **antes** de decidir el enfoque técnico,
  no después. El plan original daba por hecho GD + exif y el servidor no tiene `exif`; de haber
  escrito el código primero, las fotos verticales habrían salido tumbadas en producción y el
  fallo habría aparecido tarde, con fotos ya subidas.
- Un límite de `upload_max_filesize` por debajo del que declara la aplicación convierte una
  función terminada en una función que no existe: la galería de entidades llevaba desde el
  Paso 6 sin poder aceptar una foto de móvil, y nada en la aplicación lo delataba.
- Cuando PHP supera `post_max_size` descarta `$_POST` entero. Si el formulario lleva
  verificación CSRF, el síntoma visible es "la sesión ha caducado", que no tiene ninguna
  relación con la causa real. Merece la pena detectarlo de forma explícita.

---

## 2026-09-11 — Executor: Paso B (redimensionado con Imagick)

### Current Status / Progress Tracking

`app/Services/MediaUploadService.php` reescrito sobre Imagick. La interfaz pública no cambia
salvo un parámetro opcional, así que las llamadas existentes siguen valiendo.

Qué hace ahora `storeImage()`, además de las validaciones que ya tenía:

- Gira la imagen físicamente según su orientación EXIF (`autoOrient()`).
- Borra el resto de metadatos (`stripImage()`). Además de aligerar, evita publicar la
  geolocalización que traen las fotos de móvil: son fotos de instalaciones reales y la
  coordenada exacta no tiene por qué acabar en la web.
- Reescala a 1600 px de lado mayor. **Nunca amplía**: una imagen más pequeña se deja igual.
- Genera la miniatura de 400 px de lado mayor, salvo que se pida lo contrario.
- Devuelve en `file_size` el peso del archivo publicado, no el del original.

Decisiones de implementación:

1. **El original no se conserva**, según lo acordado. Se escribe la versión web y se descarta
   el temporal de PHP.
2. **La miniatura no se registra en base de datos.** Se deriva del nombre con el sufijo
   `-thumb` mediante `MediaUploadService::thumbnailPath()`. Añadir una columna habría obligado
   a migrar `media_files` y a mantener dos filas por foto para nada.
3. **Nunca se recomprime por encima de la calidad de origen.** Esto salió de probar con las
   fotos reales del repositorio: ya venían a 1200 px, por debajo del umbral, así que solo se
   recomprimían. Con calidad fija del 82 % una de ellas *crecía* de tamaño y todas perdían
   definición a cambio de nada. Ahora se usa el menor valor entre la calidad de origen y 82.
4. **Guarda defensiva de 50 megapíxeles.** Un PNG de pocos kilobytes puede descomprimirse hasta
   agotar la memoria; el límite de 8 MB por archivo no protege de eso.
5. **Los GIF animados se recorren fotograma a fotograma**, o el reescalado se quedaría con el
   primero y perdería la animación. La miniatura sí se queda en un solo fotograma a propósito.
6. **El logo de entidad ya no genera miniatura** (`storeImage(..., false)`): es pequeño y nunca
   aparece en una rejilla. La imagen principal de modalidad sí la genera, porque el listado del
   panel la muestra.

### Verificación ejecutada

Nuevo `tests/media_resize.php`, registrado como `composer test:media-resize`. Genera sus propias
imágenes de muestra y comprueba el procesado real. **17 comprobaciones, todas correctas:**

- Foto de 4000x3000 → versión web de 1600x1200 y miniatura de 400x300.
- Foto vertical de móvil: se construye una muestra con la etiqueta EXIF de orientación 6
  insertada a mano, porque ImageMagick no la escribe al generar la imagen. Tras el procesado la
  foto queda 800x1200, es decir girada de verdad, y sin etiqueta pendiente de aplicar.
- Imagen de 200x150: conserva su tamaño, no se amplía.
- PNG con transparencia: el canal alfa sobrevive.
- GIF animado de 2000x1500: conserva los tres fotogramas y los reescala; la miniatura queda en
  uno solo.
- Sin miniatura cuando no se pide.
- Convención de nombre de la miniatura, incluida una ruta sin extensión.

Con las 23 fotos reales de `Fotos-instalaciones-tenerife/`: 5796 KB → 5506 KB, **ningún archivo
crece**. La mejora es pequeña porque esas fotos ya venían optimizadas a 1200 px.

Con una foto de tamaño de móvil (4032x3024, 12 Mpx, contenido ruidoso que es el peor caso para
comprimir): **5961 KB → 344 KB de versión web más 27 KB de miniatura.** Una galería de seis
fotos pasa de unos 35 MB a unos 2 MB. Es el resultado que buscaba el Paso B.

`php -l` limpio en los tres archivos tocados, `composer validate` correcto y `composer audit
--no-dev` sin vulnerabilidades.

### Executor's Feedback or Assistance Requests

Paso B terminado a falta de la verificación del usuario. No se ha probado todavía una subida
real por HTTP: `storeImage()` exige `is_uploaded_file()`, que solo es cierto en una petición
real, así que el test ataca el procesado directamente. La subida completa se prueba en el
Paso H, o antes si se levanta el servidor local con `composer serve`.

Aviso para el Paso C: las miniaturas se derivan del nombre y **no están en base de datos**, así
que el borrado físico tendrá que eliminar los dos archivos usando `thumbnailPath()`. Si se
olvida, cada foto borrada dejará su miniatura huérfana.

### Lessons

- Probar con material real antes de dar por buena una optimización. Con imágenes sintéticas
  grandes el redimensionado parecía un éxito rotundo; con las fotos reales del repositorio, que
  ya venían optimizadas, la recompresión a calidad fija hacía crecer algún archivo y perder
  definición en todos. El caso "la imagen ya está bien" es tan real como el caso "la imagen es
  enorme".
- Al reescalar un GIF animado con Imagick hay que recorrer los fotogramas explícitamente; el
  reescalado directo solo afecta al primero y la animación se pierde sin dar ningún error.

---

## 2026-09-11 — Executor: Paso C (borrado físico de archivos)

### Current Status / Progress Tracking

Al revisar dónde se quedaban archivos sueltos aparecieron **tres** sitios, no uno. El plan solo
contemplaba el primero:

1. **Borrar una foto de la galería** (`EntityController::updateExistingMedia`): se borraba la
   fila y el archivo se quedaba.
2. **Sustituir el logo de una entidad** (`EntityController::storeLogoUpload`): se
   sobrescribía `logo_path` y el logo anterior quedaba huérfano.
3. **Sustituir la foto principal de una modalidad** (`ModalityController::storeMainImageUpload`):
   se borraba la fila anterior y se insertaba la nueva; el archivo antiguo se quedaba.

Los tres quedan resueltos.

**`MediaUploadService::deleteStoredImage()`** borra la imagen y su miniatura. Es idempotente:
si el archivo ya no está, se considera hecho. Devuelve `false` solo cuando la ruta no es
aceptable o el borrado falla de verdad.

La validación de la ruta se hace en dos pasos a propósito:

- Primero sobre la ruta declarada: debe empezar por `/uploads/` y ningún segmento puede estar
  vacío, ser `.` o ser `..`. Así se rechaza un `file_path` manipulado **aunque el archivo no
  exista**, que es el caso que una comprobación basada solo en `realpath()` dejaría pasar sin
  más porque `realpath()` de algo inexistente devuelve `false`.
- Después sobre la ruta real: `realpath()` tiene que seguir cayendo dentro de la carpeta de
  subidas. Esto es lo que descarta un enlace simbólico que apunte fuera.

**El borrado de archivos ocurre siempre después de confirmar la transacción.** Al revés, una
reversión dejaría filas en base de datos apuntando a imágenes ya borradas, que es peor que un
archivo suelto: lo primero rompe la ficha pública, lo segundo solo ocupa disco.

Si un borrado falla, se registra `media.file_orphaned` en auditoría y el guardado continúa.
Abortar el guardado de una entidad porque no se pudo borrar un archivo sería desproporcionado,
pero tiene que quedar constancia para poder limpiarlo.

De paso se corrige una imprecisión de auditoría: `media.deleted` contaba las casillas marcadas
en el formulario, no las fotos realmente borradas. Ahora cuenta las filas que existían y
pertenecían de verdad a esa entidad.

**El archivado de una entidad no borra sus archivos, y es intencionado.** `destroy()` es un
borrado lógico (`deleted_at`), la entidad puede recuperarse, y sin sus fotos volvería mutilada.

**El logo que apunta a `/assets` no se toca.** Solo se borra lo que está bajo `/uploads/`:
lo demás pertenece al repositorio.

### Verificación ejecutada

Nuevo `tests/media_delete.php`, registrado como `composer test:media-delete`. Trabaja dentro de
`public/uploads/__test__` y limpia al terminar. **16 comprobaciones, todas correctas:**

- Borra la imagen y su miniatura.
- Volver a borrar lo ya borrado no falla.
- Rechaza una ruta que no empiece por `/uploads/`.
- Rechaza el recorrido con `..`, y el archivo de fuera sigue estando.
- Rechaza ruta vacía, solo el prefijo, segmentos vacíos y `.` como segmento.
- Rechaza un enlace simbólico que sale de la carpeta, y el archivo apuntado sigue estando.
- Borra la indicada sin llevarse la de al lado.
- La carpeta de pruebas queda limpia.

`public/uploads` queda como estaba, solo con su `.htaccess`. Las tres pruebas
(`media_resize`, `media_delete`, `env_check`) pasan. `php -l` limpio en los tres archivos
tocados, `git diff --check` limpio y `composer audit --no-dev` sin vulnerabilidades.

### Executor's Feedback or Assistance Requests

Sin bloqueos. Paso C terminado a falta de verificación.

Nota para cuando se despliegue: **las fotos ya subidas antes de este cambio no tienen
miniatura**, porque el Paso B es posterior. Si en producción ya hay alguna, la rejilla del Paso
G intentará mostrar una miniatura que no existe. Hay dos salidas: volver a subirlas, o que la
vista use la versión web cuando no encuentre miniatura. Lo segundo es más robusto y lo daré por
hecho en el Paso G, pero conviene decidirlo antes.

### Lessons

- Al buscar dónde se quedan archivos huérfanos, el borrado explícito es solo uno de los casos.
  Las **sustituciones** (cambiar un logo, cambiar una foto principal) generan huérfanos igual y
  son más fáciles de pasar por alto porque el usuario no ha pedido borrar nada.
- Validar una ruta solo con `realpath()` deja un hueco: `realpath()` de un archivo inexistente
  devuelve `false`, así que hay que comprobar también la ruta declarada para poder rechazar un
  valor manipulado que no corresponda a ningún archivo.
- El orden importa: borrar archivos dentro de la transacción y revertirla después deja la base
  de datos apuntando a imágenes que ya no existen. Primero se confirma, después se borra.

---

## 2026-09-11 — Executor: Pasos D e I (servicio compartido y aviso de envío grande)

### Current Status / Progress Tracking

**Paso D.** La lógica de galería sale de `EntityController` a
`app/Services/Media/MediaGallery.php`, parametrizado por tipo de propietario. Los tipos de
medio se derivan del propietario (`entity_photo` / `entity_featured_photo`,
`facility_photo` / `facility_featured_photo`), así que el Paso E no tendrá que tocar el
servicio: le bastará con `MediaGallery::forFacilities()`.

`EntityController` baja de 995 a 880 líneas. Desaparecen `updateExistingMedia`,
`storeGalleryUploads`, `normalizeMultipleUpload`, `mediaPathsFor` y `deleteMediaFiles`; queda
`syncEntityMedia` como traductor entre el resultado del servicio y la auditoría.

Decisiones:

1. **El servicio recibe `$_POST` y `$_FILES` como argumentos**, no los lee por su cuenta. El
   código existente los leía directamente y así era imposible probarlo sin una petición HTTP
   real, que es justo lo que no se puede automatizar aquí. Pasándolos como parámetros, la
   galería entera se prueba desde consola.
2. **El servicio no registra auditoría.** Devuelve un `MediaGalleryResult` con lo ocurrido y
   es el controlador, que es quien sabe qué usuaria hay en sesión, el que lo anota. Meter la
   auditoría dentro habría obligado a arrastrar la sesión hasta el servicio.
3. **Añadido `deleteAllFor()`**, que no existía. No se usa todavía: hoy el archivado de entidad
   es lógico y no debe borrar nada. Queda disponible para cuando haya un borrado definitivo.

**Paso I.** `AdminController::postExceededLimit()` detecta el envío que PHP ha descartado por
superar `post_max_size`: petición POST, `CONTENT_LENGTH` mayor que cero y `$_POST` y `$_FILES`
ambos vacíos. Se comprueban los dos, no solo `$_POST`, para no confundirlo con un formulario
legítimo que solo lleve archivos.

Enganchado en `store()` y `update()` de entidades, instalaciones y modalidades, **antes de la
verificación CSRF**, que es lo que hoy se lleva el golpe y produce el mensaje equivocado. El
aviso cita el límite real leído de la configuración y aclara que no se ha guardado nada.

Las instalaciones se enganchan ya, aunque todavía no tengan subida de archivos, para que el
Paso E no tenga que volver a pasar por aquí.

### Verificación ejecutada

Nuevo `tests/media_gallery.php`, registrado como `composer test:media-gallery`. Es de
integración: trabaja contra `juegos_tenerife_test`, se niega a arrancar si el nombre de la base
no contiene "test", usa identificadores de propietario 999901 y 999902 para no cruzarse con
datos reales y limpia filas y archivos al terminar. **31 comprobaciones, todas correctas:**

- Galería vacía, orden con la destacada primero y después por `sort_order`.
- **Aislamiento entre entidades e instalaciones**, que es lo que había que demostrar del
  refactor: cada galería ve solo lo suyo y los tipos de instalación usan su propio prefijo.
- Edición de título, texto alternativo y orden.
- Cambio de destacada, comprobando que solo queda una.
- Borrado: fila, archivo y miniatura, sin huérfanos.
- **No se puede borrar una foto que pertenece a otro propietario**, aunque se envíe su
  identificador en el formulario.
- Borrado completo sin tocar al vecino.
- Paso I: los cinco escenarios de detección y el texto del aviso.

Nota sobre la conexión en local: la base autentica por socket, así que el test fuerza
`DB_HOST=localhost`; por TCP (`127.0.0.1`) pediría contraseña y fallaría. Es configuración de la
máquina de desarrollo, no del proyecto.

Suite completa (`media_resize`, `media_delete`, `media_gallery`, `env_check`) en verde. `php -l`
sobre todo `app/` y `tests/` sin errores, `git diff --check` limpio, `composer audit --no-dev`
sin vulnerabilidades. `public/uploads` queda solo con su `.htaccess` y la base de pruebas sin
filas residuales.

### Executor's Feedback or Assistance Requests

Sin bloqueos. El Paso E queda preparado: el formulario de instalación necesita la sección de
fotos y `FacilityController` llamar a `MediaGallery::forFacilities()`.

Sigue pendiente de confirmar lo apuntado en el Paso C: qué hacer con las fotos ya subidas en
producción, que no tienen miniatura. Plan previsto salvo indicación contraria: que la vista use
la versión web cuando no encuentre miniatura.

### Lessons

- Un servicio que lee `$_POST` por su cuenta no se puede probar sin levantar una petición HTTP.
  Recibir la entrada como argumento cuesta lo mismo de escribir y convierte algo no
  automatizable en algo que se verifica en dos segundos desde consola.
- Al extraer lógica compartida, la prueba que de verdad justifica el refactor no es que siga
  funcionando lo de antes, sino que **los dos usos no se pisen**: que la galería de entidad no
  vea ni pueda borrar las fotos de una instalación.

---

## 2026-09-11 — Executor: Paso E (galería de instalaciones, con interfaz rehecha)

### Current Status / Progress Tracking

Las instalaciones ya tienen galería, y la interfaz se ha rehecho para las dos: entidades e
instalaciones comparten ahora el mismo componente.

**`app/Views/admin/partials/media-gallery.php`** es el nuevo partial compartido, la pareja en
vistas de lo que `MediaGallery` hizo en lógica. El formulario de entidad le inyecta su campo de
logo mediante `$mediaExtraHtml`, para no partir la sección en dos ni duplicar la galería entera
solo por un campo que no tienen las instalaciones.

Qué cambia respecto a la galería anterior:

1. **Zona de subida con arrastrar y soltar.** Los archivos arrastrados se **suman** a los ya
   elegidos en lugar de reemplazarlos, que es lo que hace el control nativo y lo que sorprende
   a quien selecciona fotos en dos tandas.
2. **Lista previa de lo seleccionado**, con miniatura, nombre y peso de cada archivo, antes de
   guardar.
3. **Aviso preventivo de tamaño.** El formulario conoce `upload_max_filesize` y
   `post_max_size` reales del servidor: marca en rojo los archivos que van a ser rechazados y
   avisa cuando el conjunto se acerca al límite del formulario. Es el Paso I atacado por el
   otro lado: en vez de explicar bien el error, se evita que ocurra.
4. **Destacada como distintivo visible**, no un botón de radio suelto: la tarjeta se resalta y
   la foto lleva una insignia. Añadida la opción **"Sin destacada"**, que antes no existía: con
   botones de radio no había forma de quitar la destacada sin elegir otra.
5. **Eliminar es reversible y se ve.** Marcar una foto la deja en gris con el aviso "Se
   eliminará al guardar", y el botón pasa a "Conservar". Nada se borra hasta guardar, y ahora
   eso se lee en la propia tarjeta.
6. **Reordenar con flechas**, que renumeran todas las fotos de diez en diez y desactivan la
   flecha que no lleva a ninguna parte. El campo numérico sigue ahí para quien prefiera
   escribir el orden.
7. **Miniaturas en la rejilla** en vez de la imagen completa, con respaldo a la versión web
   mediante `MediaUploadService::displayThumbnail()`. Esto resuelve además lo que quedaba
   pendiente del Paso C: las fotos subidas antes del redimensionado no tienen miniatura y
   sin el respaldo se verían rotas.

**Todo funciona sin JavaScript.** Subir, ordenar, destacar y eliminar son controles normales;
el guion solo añade comodidad encima. Nada de JavaScript en línea y todo el CSS en
`styles.css`, según las reglas del proyecto.

`FacilityController` pasa a usar `MediaGallery::forFacilities()` en alta y en edición, con la
misma auditoría que entidades pero bajo `owner_type = 'facility'`.

### Verificación ejecutada

Revisión visual con el servidor local y una página de prueba desechable, ya eliminada, contra
fotos reales de `Fotos-instalaciones-tenerife/` procesadas con el servicio del Paso B.
Comprobado a 1200 px y a 375 px.

Interacciones verificadas en el navegador:

- Reordenar: al mover la primera foto hacia abajo, el orden queda 10/20/30/40 y las flechas de
  los extremos se desactivan solas.
- Eliminar: la tarjeta pasa a gris con la insignia roja y el botón cambia a "Conservar".
- Selección de archivos: con una foto de 300 KB y otra de 9 MB, la lista muestra ambas, marca
  la segunda como "demasiado grande" y avisa de que será rechazada.
- El logo se inyecta dentro de la sección de fotos y se maqueta como el resto de campos.

Tres defectos encontrados y corregidos durante la revisión:

1. **El atributo `hidden` no funcionaba.** Cualquier regla propia de `display` le gana por
   especificidad, así que los avisos y las insignias que el guion oculta se veían igualmente.
   Faltaba la regla `[hidden] { display: none !important; }`, que no existía en la hoja. Era un
   agujero general del proyecto, no solo de la galería.
2. **"Eliminar" y "Conservar" se veían a la vez**, por el mismo motivo de especificidad:
   `.media-remove span` ganaba a `.media-remove-undo`.
3. **El texto de apoyo de la zona de subida salía en negrita**, porque toda la zona es un
   `label` y hereda el peso global de las etiquetas, aplastando la jerarquía de los tres
   niveles de texto.

Suite completa (`media_resize`, `media_delete`, `media_gallery`, `env_check`) en verde. `php -l`
sobre `app/` y `tests/`, `node --check` sobre los dos guiones, `git diff --check` limpio y
`composer audit --no-dev` sin vulnerabilidades. `public/uploads` queda solo con su `.htaccess`.

### Executor's Feedback or Assistance Requests

Un descubrimiento durante el paso: **el formulario de instalación no tenía
`enctype="multipart/form-data"`**. Sin ese atributo el navegador no envía los archivos, solo
sus nombres, así que la galería habría fallado en silencio. Ya está añadido.

Queda por decidir, y ahora es el momento porque afecta al Paso F: en la ficha pública de una
entidad, **¿cómo se separan sus fotos propias de las de sus instalaciones?** Plan previsto: dos
bloques, "Galería" y "Fotos de las instalaciones", indicando de qué instalación procede cada
foto para que no parezca que el terrero es propiedad del club.

### Lessons

- Una regla propia de `display` gana al atributo `hidden`. Sin `[hidden] { display: none
  !important; }` en la hoja, ocultar algo desde JavaScript con `el.hidden = true` no hace nada
  y el fallo solo se ve mirando la página, no leyendo el código.
- Un formulario sin `enctype="multipart/form-data"` acepta el campo de archivo sin protestar y
  envía solo el nombre. No hay error: simplemente no llega nada. Conviene comprobarlo al añadir
  la primera subida a un formulario que antes no tenía ninguna.
- Al arrastrar archivos sobre un campo de subida, reemplazar la selección anterior es el
  comportamiento nativo y es el que sorprende. Sumar los nuevos a los ya elegidos cuesta unas
  líneas con `DataTransfer` y es lo que la gente espera.

---

## 2026-09-11 — Executor: Paso F (fotos de instalaciones en la ficha pública)

### Current Status / Progress Tracking

La ficha pública de una entidad muestra ahora dos bloques separados, según lo acordado:

- **Galería**: fotos propias de la entidad.
- **Fotos de las instalaciones**: un subgrupo por instalación, encabezado por su nombre.

Cuando la vinculación entre entidad e instalación tiene etiqueta propia (`entity_facilities.label`),
se usa esa en lugar del nombre genérico. Así una instalación cedida puede aparecer como
"Pabellón municipal (cedido)" en la ficha del club que la usa, sin tocar el nombre de la
instalación, que es compartido.

`EntityRepository::facilityMediaFor()` **resuelve todas las instalaciones en una sola consulta**
y agrupa en PHP. Una consulta por instalación habría multiplicado las consultas de la ficha por
cada espacio de práctica. El agrupado recorre las instalaciones, no el resultado de la consulta,
para conservar el orden en que aparecen en la ficha y descartar de paso las que no tienen fotos.

### Verificación ejecutada

Nuevo `tests/entity_facility_media.php`, registrado como `composer test:entity-facility-media`.
Crea una entidad y tres instalaciones, una de ellas sin fotos. **13 comprobaciones, todas
correctas:**

- Los dos orígenes llegan separados y las fotos propias no incluyen las de instalación.
- Solo aparecen las instalaciones que tienen fotos: una instalación vacía no genera un bloque
  vacío en la ficha.
- Se respeta el orden de vinculación y se usa la etiqueta de la vinculación cuando existe.
- Dentro de cada instalación, la destacada va primero aunque tenga el `sort_order` más alto.
- Una entidad sin instalaciones vinculadas no falla.

Revisión visual en el navegador con datos y fotos reales, a 1280 px y a 375 px, borrados al
terminar. Se corrigió un defecto que venía de antes y que solo se ve mirando la página:

**La foto destacada ocupaba dos filas de la rejilla pero conservaba su proporción 4/3**, así que
su pie de foto quedaba descolgado al final de la segunda fila con un hueco vacío en medio. Se
corrige haciendo que la imagen destacada rellene las dos filas. Además, **la destacada solo pasa
a doble altura cuando hay tres fotos o más**: con una o dos, el hueco a su lado quedaba vacío.
Afectaba también a la galería de entidad que ya existía.

Suite completa (cinco pruebas) en verde, `php -l` limpio, `git diff --check` limpio,
`composer validate` correcto. La base de pruebas y `public/uploads` quedan sin restos.

### Executor's Feedback or Assistance Requests

Sin bloqueos. Queda el **Paso G**, que es donde toca la optimización que hoy falta: la ficha
sirve las versiones web de 1600 px incluso en móvil, donde se muestran a 375 px. El Paso G las
cambiará por miniaturas con respaldo, añadirá `loading="lazy"` y el lightbox para ver la foto
completa.

Aviso sobre los datos de prueba, por si se repite: al insertar por consola con el cliente
`mysql` hay que pasar `--default-character-set=utf8mb4`. Sin eso, los acentos se guardan
doblemente codificados y parece un fallo de la aplicación cuando es del cliente.

### Lessons

- Un `grid-row: span 2` sobre una figura cuya imagen tiene `aspect-ratio` fijo no estira la
  imagen: estira la celda y deja el pie de foto descolgado con un hueco en medio. Si la celda
  ocupa dos filas, la imagen necesita `height: 100%` y soltar la proporción.
- Un diseño destacado a doble altura necesita un mínimo de elementos para que la columna de al
  lado no quede a medias. Conviene condicionarlo al número real de fotos, no aplicarlo siempre
  al primer elemento.

---

## 2026-09-11 — Executor: Paso G (visor de fotos y carga diferida)

### Current Status / Progress Tracking

**Peso de la ficha.** La rejilla pasa a usar las miniaturas en lugar de las versiones web.
Medido con las siete fotos de prueba: **1417 KB → 189 KB, un 87 % menos**, y eso antes de
contar la carga diferida, que evita descargar siquiera las que no se ven. Solo la primera foto
de la página se carga de inmediato; el resto llevan `loading="lazy"` y `decoding="async"`.

**Visor.** Cada foto es ahora un enlace a la imagen completa, y el visor se abre encima. Se usa
**`<dialog>` nativo** en lugar de montar una capa a mano: trae de serie el cierre con Esc, el
foco atrapado dentro y el resto de la página inerte, que a mano sale caro y se rompe fácil.

- Se navega **solo dentro de la galería pulsada**: saltar de las fotos del club a las de una
  instalación sin avisar desorienta.
- Las flechas dan la vuelta por los extremos.
- Con una sola foto las flechas se ocultan pero conservan su hueco, para que la imagen no
  salte de sitio al abrir una galería u otra.
- Contador "2 de 5" solo cuando hay más de una.
- Al cerrar, **el foco vuelve a la miniatura de la que se salió**, y se libera el `src` de la
  imagen ampliada.

**Sin JavaScript sigue funcionando**: el enlace abre la foto completa. Lo mismo si el navegador
no admite `<dialog>`, porque el guion se retira solo si no encuentra `showModal`.

La vista se ha refactorizado a un único ayudante `$renderGallery`, que pintan igual la galería
de la entidad y la de cada instalación. Antes eran dos bloques de marcado gemelos.

### Verificación ejecutada

Revisión en el navegador con datos y fotos reales, a 1280 px y a 375 px, borrados al terminar:

- La rejilla pide las miniaturas (`-thumb.jpg`) y el enlace apunta a la versión completa.
- `loading`: `eager` solo en la primera foto de la página, `lazy` en las otras seis.
- Abrir con clic: se muestra la foto correcta con su pie y el contador "3 de 3".
- Flechas de teclado: derecha en la última vuelve a la primera, izquierda en la primera va a
  la última.
- Esc cierra, el foco vuelve a la miniatura y se libera la imagen.
- Pulsar fuera cierra, con el mismo retorno de foco.
- Galería de una sola foto: contador vacío y flechas ocultas.
- En móvil, las flechas bajan a una fila propia bajo la foto.

Dos correcciones durante la revisión:

1. **Pulsar fuera no cerraba, y no era un fallo del código sino del diseño.** El diálogo medía
   lo mismo que el panel, así que el margen "fuera" eran unos 40 px a cada lado en escritorio
   y **ninguno en móvil**, donde ocupaba todo el ancho. Se rehízo para que el diálogo ocupe
   toda la pantalla y sea transparente, con el panel oscuro dentro: ahora cualquier punto
   alrededor de la foto cierra, y la comprobación en el guion se reduce a "¿el destino del clic
   es el propio diálogo?".
2. **Esc se hace explícito** en lugar de confiar en el comportamiento nativo del navegador, para
   que el visor se cierre igual en todos.

Suite completa (cinco pruebas) en verde, `php -l` limpio sobre `app/` y `tests/`, `node --check`
sobre los dos guiones, `git diff --check` limpio y `composer audit --no-dev` sin
vulnerabilidades. Base de pruebas y `public/uploads` sin restos.

### Executor's Feedback or Assistance Requests

Con esto quedan cerrados todos los pasos de desarrollo. **Solo falta el Paso H**: la prueba
manual en Plesk con una subida real y el documento de uso para las trabajadoras.

Recordatorio de lo que hay que comprobar en Plesk, porque no se puede verificar desde aquí:

- Que los límites de PHP que se cambiaron están realmente aplicados en el pool de FPM.
- Que `public/uploads` tiene permiso de escritura con el usuario web.
- Una subida real de varias fotos a una instalación y a una entidad.
- Que `max_file_uploads` no corta la subida múltiple.

### Lessons

- Para "cerrar al pulsar fuera", el diálogo debe ocupar toda la pantalla y ser transparente,
  con el panel visible dentro. Si el diálogo mide lo mismo que el panel, el área "fuera" es el
  margen que sobra, que en móvil no existe: el gesto más natural para cerrar deja de funcionar
  justo donde más se usa.
- `<dialog>` resuelve gratis el foco atrapado, el fondo inerte y el orden de apilado, que es la
  parte que más cuesta hacer bien a mano en un visor de imágenes.
- Al capturar pantalla de forma automática, la capa `::backdrop` puede no aparecer aunque esté
  bien aplicada. Antes de "arreglar" lo que no está roto, conviene comprobar el estilo
  calculado: se confirmó con `getComputedStyle(dialog, '::backdrop')` y con la vista en móvil.

---

## 2026-09-11 — Executor: documentación de uso (parte del Paso H)

Añadido `docs/guia-fotos.md`: guía de una página para las trabajadoras, deliberadamente corta.

Lo primero que resuelve es la única decisión que tienen que tomar y que no es evidente: **si la
foto es del espacio va en la instalación, y si es de la gente va en la entidad**. El resto
(cómo subir, destacada, orden, título, texto alternativo, eliminar) cabe en media página.

Incluye una tabla de mensajes de error con qué hacer en cada caso, incluido el de envío
demasiado grande, dejando claro que **no se pierde nada** cuando aparece.

El Paso H queda a falta de la prueba manual en Plesk, que depende del usuario.
