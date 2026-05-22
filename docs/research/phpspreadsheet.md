# PhpSpreadsheet

Estado: descartado para este proyecto.

Fuente principal: https://phpspreadsheet.readthedocs.io/en/latest/

Notas:

- Librería PHP para leer y escribir formatos de hoja de cálculo, incluyendo `.xlsx`, `.xls`, `.ods` y `.csv`.
- Instalación recomendada con Composer: `composer require phpoffice/phpspreadsheet`.
- La documentación actual indica PHP 8.1 o superior para desarrollo.
- Para este proyecto debe usarse en un importador con previsualización antes de persistir.
- No se debe asumir que las columnas del Excel definitivo coinciden exactamente con los nombres indicados en la descripción hasta recibir muestra real.

Revisión 2026-05-22:

- Se instaló temporalmente `phpoffice/phpspreadsheet` para evaluar el importador.
- `composer audit` reportó avisos críticos/altos vigentes sobre la versión instalada.
- Se eliminó la dependencia y se sustituyó por `openspout/openspout`.

Decisión:

- No usar PhpSpreadsheet mientras existan avisos críticos/altos sin versión corregida disponible para la rama estable usada por Composer.
