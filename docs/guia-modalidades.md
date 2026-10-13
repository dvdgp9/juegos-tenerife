# Edición de modalidades

En **Administración > Modalidades > Editar**:

- **Descripción corta**: resumen de la tarjeta en la página de inicio.
- **Ficha pública > Introducción de la ficha**: texto junto al botón «Ver entidades».
- **Ficha pública > Apartados**: títulos, párrafos y listas de la página de la modalidad. Se cargan los textos publicados originalmente cuando todavía no se han editado. Se pueden añadir y eliminar apartados.
- **Icono y foto principal > Subir icono**: sustituye el pictograma que aparece en las tarjetas y fichas del censo. Admite PNG, JPG, WEBP y GIF de hasta 8 MB; se recomienda PNG transparente. Si no se selecciona un archivo, se conserva el icono actual.
- **Foto principal**: imagen de cabecera de la ficha pública.

Para los párrafos, utiliza una línea en blanco entre ellos. En una lista, escribe un elemento por línea y utiliza `**texto**` para destacar una parte en negrita. Guarda con **Guardar modalidad** y comprueba el resultado con **Ver ficha**.

La descripción larga y la información adicional se conservan como datos editoriales; los textos de la ficha pública se gestionan en su apartado propio.

## Despliegue

1. Ejecutar `database/009_add_modality_page_content.sql` en la base de datos del servidor, una sola vez y antes de subir el código actualizado.
2. Subir los archivos modificados de `app/` y `public/assets/js/admin.js`.
3. Comprobar que PHP puede escribir en `public/uploads/` y que están habilitadas `imagick` y `fileinfo`.
4. Abrir la edición de una modalidad existente y comprobar que aparecen los textos actuales. Guardar una corrección y verificarla en la ficha pública.

La migración añade dos columnas vacías y conserva las descripciones existentes. Mientras no se guarde una ficha, la web utiliza sus textos originales. No requiere rellenar manualmente los nuevos campos.

## Verificación local

`composer test:modality-editor` prueba la edición, publicación, subidas y reversión ante errores mediante peticiones HTTP contra una base SQLite temporal. Necesita las extensiones `pdo_sqlite`, `curl` e `imagick`. No utiliza `.env` ni una base de datos real y limpia sus archivos al terminar.
