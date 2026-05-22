# Arquitectura base

## Objetivo

Mantener una aplicación PHP vanilla clara, fácil de subir a Plesk y preparada para crecer sin introducir un framework pesado.

## Estructura

- `public/`: única carpeta pública del proyecto. Contiene `index.php` y assets servidos por el navegador.
- `public/assets/css/styles.css`: hoja de estilos principal. No se deben añadir estilos inline.
- `public/assets/js/`: JavaScript progresivo para buscador, mapa, formularios y admin.
- `public/assets/images/`: imágenes públicas optimizadas para la web.
- `app/`: código PHP de aplicación.
- `app/Core/`: piezas base como router, respuesta y render de vistas.
- `app/Controllers/`: controladores HTTP.
- `app/Models/`: acceso a datos y entidades del dominio.
- `app/Services/`: lógica de negocio como importación Excel, PDF, geocodificación o email.
- `app/Views/`: plantillas PHP.
- `config/`: configuración de aplicación, base de datos y servicios.
- `database/`: SQL que se entregará para ejecución manual en servidor, además de migraciones o seeders documentados.
- `storage/`: archivos generados o privados, como uploads, importaciones, logs y cache.
- `docs/`: documentación del proyecto, investigación y despliegue.

## Dependencias previstas

Composer se usará aunque el proyecto sea PHP vanilla.

Dependencias:

- `openspout/openspout`: previsualización e importación de Excel.
- `dompdf/dompdf`: generación de PDF de fichas.
- PHPMailer o SMTP nativo equivalente si Plesk no cubre el envío del formulario de contacto de forma suficiente.

Se descartó PhpSpreadsheet después de `composer audit` porque el paquete instalado reportaba avisos críticos/altos vigentes. OpenSpout cubre mejor el caso de lectura de XLSX y no reportó vulnerabilidades al instalarlo.

## Frontend

- HTML renderizado desde vistas PHP.
- CSS centralizado en `public/assets/css/styles.css`.
- JavaScript progresivo en `public/assets/js/`.
- Leaflet se integrará en una fase posterior para el mapa.
- Diseño institucional, sobrio y legible, con especial cuidado en móvil.

## Base de datos

No se ejecuta SQL en esta fase.

Cuando el esquema esté definido, los scripts SQL se guardarán en `database/` para que el usuario los ejecute manualmente en servidor.

## GitHub y despliegue

Repositorio remoto previsto: `https://github.com/dvdgp9/juegos-tenerife.git`.

El workspace local aún no estaba inicializado como repositorio git al comenzar esta tarea. Puede inicializarse y conectarse al remoto cuando el usuario lo confirme o cuando se vaya a realizar el primer commit.

En Plesk, el document root debe apuntar a `public/`.
