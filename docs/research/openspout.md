# OpenSpout

Uso previsto: lectura streaming de archivos `.xlsx` para previsualización e importación del censo.

Fuentes:

- https://packagist.org/packages/openspout/openspout
- https://github.com/openspout/openspout

Notas:

- OpenSpout es un fork mantenido de `box/spout`.
- `box/spout` está abandonado; no usar.
- OpenSpout lee y escribe CSV, XLSX y ODS de forma escalable.
- Versión instalada: `v5.7.0`.
- `composer audit` no reportó vulnerabilidades después de instalar OpenSpout.

Decisiones:

- Usar OpenSpout para el importador Excel.
- Mantener el importador en dos fases: previsualización y confirmación.
- Validar extensión, tamaño, cabeceras, filas obligatorias y avisos antes de persistir.
