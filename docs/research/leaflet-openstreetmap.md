# Leaflet + OpenStreetMap

Uso previsto: mapa interactivo público con marcadores de entidades/instalaciones y filtros sincronizados con el buscador.

Fuentes principales:

- https://leafletjs.com/reference
- https://instalacionesdeportivastenerife.es/
- https://instalacionesdeportivastenerife.es/mapa

Notas:

- Leaflet soporta marcadores, popups, capas y GeoJSON.
- Encaja bien con PHP vanilla porque se puede alimentar con JSON desde endpoints propios.
- OpenStreetMap requiere atribución visible.
- La web de referencia del Cabildo usa una estructura de búsqueda + mapa + filtros que conviene adaptar al censo de entidades.

Decisiones:

- Usar Leaflet para el mapa principal.
- Guardar coordenadas `lat` y `lng` en MySQL cuando estén disponibles.
- Exponer endpoint JSON filtrable para marcadores.
- Diseñar popups útiles: nombre, municipio, modalidades y enlace a ficha.
