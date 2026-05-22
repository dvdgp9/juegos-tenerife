# Geocoding y enlaces de Google Maps

Uso previsto: convertir o gestionar ubicaciones recibidas desde Excel, previsiblemente como enlaces de Google Maps.

Fuente principal:

- https://operations.osmfoundation.org/policies/nominatim/

Notas:

- Un enlace corto como `https://maps.app.goo.gl/5NANy6NfJ8sKJ8yc7` es viable como enlace externo para "ver en Google Maps".
- Para mostrar marcadores en Leaflet se necesitan coordenadas `lat`/`lng`.
- A veces un enlace de Google Maps expandido contiene coordenadas, pero no debe asumirse que siempre será extraíble de forma estable.
- El servicio público Nominatim de OpenStreetMap limita el uso a un máximo de 1 petición por segundo, exige User-Agent/Referer identificable y desaconseja geocodificación masiva recurrente.

Decisiones:

- Pedir, si es posible, que el Excel final incluya columnas `Latitud` y `Longitud`.
- Si solo llega enlace de Maps, intentar extraer coordenadas durante importación cuando sea razonable.
- Si no se pueden extraer coordenadas, guardar el enlace y marcar la ubicación como pendiente de revisión.
- No hacer geocodificación masiva contra Nominatim público sin revisar volumen y condiciones.

## Prueba con enlace real

Enlace probado:

- `https://maps.app.goo.gl/5NANy6NfJ8sKJ8yc7`

Resultado con `curl -L`:

- URL final: `https://www.google.es/maps/search/28.438214,+-16.456722?...`
- Coordenadas extraíbles: `28.438214`, `-16.456722`.

Implementación:

- `JuegosTenerife\Services\Maps\GoogleMapsCoordinateExtractor` intenta expandir el enlace y extraer coordenadas desde patrones habituales de URL.
- En el sandbox local, PHP/cURL no pudo resolver DNS para `maps.app.goo.gl`, aunque `curl` de terminal sí lo resolvió. En servidor Plesk habrá que confirmar salida HTTPS/DNS desde PHP.
- Si la extracción falla, se guarda la instalación con `geocoding_status = 'pending'`.
