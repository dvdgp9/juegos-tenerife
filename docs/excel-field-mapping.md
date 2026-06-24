# Mapeo del Excel de entidades

Archivo revisado inicialmente: `Listado Entidades Prueba 20260522.xlsx`.
Archivo definitivo revisado: `Listado Entidades Def.xlsx`.

## Resumen

- Hoja: `ENTIDADES`.
- Registros con datos en la muestra inicial: 11.
- Registros con datos en el archivo definitivo: 41.
- Columnas del archivo definitivo: 69.
- No contiene coordenadas.
- No contiene logos ni fotos.
- Contiene enlaces cortos de Google Maps dentro de columnas `Instalaciones1` a `Instalaciones9`.

## Cabeceras detectadas

1. Tipo Entidad1
2. Tipo Entidad2
3. Modalidad1
4. Modalidad2
5. Modalidad3
6. Modalidad4
7. Nombre Entidad
8. Domicilio
9. Localidad
10. Municipio
11. Código Postal
12. Teléfono1
13. Teléfono2
14. Email1
15. Email2
16. Web
17. Facebook
18. Instagram
19. X
20. TikTok
21. Youtube
22. Persona Contacto
23. Cargo Contacto
24. Teléfono1
25. Teléfono2
26. Email
27. Directiva
28. Miembros Directiva
29. Directivos/Hombres
30. Directivas/Mujeres
31. Asambleas Anuales
32. Socios/as
33. Número Total Socios/as
34. Socios/Hombres
35. Socias/Mujeres
36. Equipos
37. Equipos por Género
38. Equipos por Edad
39. Total Deportistas/Practicantes
40. Mujeres/Niñas
41. Hombres/Niños
42. Edades: De 0 a 5 años
43. Edades: De 6 a 11 años
44. Edades: De 12 a 17 años
45. Edades: De 18 a 29 años
46. Edades: De 30 a 45 años
47. Edades: De 46 a 59 años
48. Edades: 60 años y más
49. Instalaciones1
50. Instalaciones2
51. Instalaciones3
52. Instalaciones4
53. Instalaciones5
54. Instalaciones6
55. Instalaciones7
56. Instalaciones8
57. Instalaciones9
58. Entrenamientos/Prácticas
59. Días
60. Horarios
61. Breve Historia
62. Principios Corporativos
63. Valores Deportivos
64. Protocolo Igualdad
65. Protocolo Violencia
66. LOPIVI
67. Necesidades Educativas
68. Discapacidad
69. Educar Entrenando

## Mapeo propuesto

- `Tipo Entidad` o `Tipo Entidad1` -> `entity_types.name`.
- `Tipo Entidad2` -> se conserva en el dato bruto de importación como tipo complementario; no cambia el filtro público principal porque el modelo actual tiene un único tipo de entidad.
- `Nombre Entidad` -> `entities.name`.
- `Domicilio`, `Localidad`, `Municipio`, `Código Postal` -> campos de dirección de `entities`.
- `Modalidad1` a `Modalidad4` -> `modalities` + `entity_modalities`.
- `Teléfono1`, `Teléfono2`, `Email1`, `Email2` en columnas 11-14 -> `entity_contacts` con tipo `phone` o `email`.
- `Web` -> `entities.website_url`.
- `Facebook`, `Instagram`, `X`, `TikTok`, `Youtube` -> `entity_social_links`.
- `Persona Contacto`, `Cargo Contacto`, `Teléfono1`, `Teléfono2`, `Email` en el segundo bloque de teléfonos -> `entity_contacts` con tipo `person`. El importador crea aliases internos `Teléfono Contacto1` y `Teléfono Contacto2` para no depender de la posición exacta de las cabeceras duplicadas.
- `Directiva`, `Miembros Directiva`, `Directivos/Hombres`, `Directivas/Mujeres` -> campos de directiva en `entities`.
- `Asambleas Anuales` -> `entities.holds_annual_assemblies`.
- `Socios/as`, `Número Total Socios/as`, `Socios/Hombres`, `Socias/Mujeres` -> campos de socios en `entities`.
- `Equipos`, `Equipos por Género`, `Equipos por Edad` -> campos de equipos en `entities`.
- `Total Deportistas/Practicantes`, `Mujeres/Niñas`, `Hombres/Niños` -> campos de practicantes en `entities`.
- `Edades: ...` -> `entity_age_ranges`, conservando valor bruto y número si se puede parsear.
- `Instalaciones1` a `Instalaciones9` -> `facilities` + `entity_facilities`, parseando nombre, municipio y URL Maps cuando sea posible.
- `Entrenamientos/Prácticas`, `Días`, `Horarios` -> campos de entrenamientos en `entities`.
- `Breve Historia`, `Principios Corporativos`, `Valores Deportivos` -> campos largos de contenido en `entities`.
- `Protocolo Igualdad`, `Protocolo Violencia`, `LOPIVI` -> estados de protocolo, no booleanos simples.
- `Necesidades Educativas`, `Discapacidad`, `Educar Entrenando` -> booleanos sí/no.

## Riesgos de importación

- Cabeceras duplicadas: el importador no puede mapear solo por nombre; debe mapear por nombre + posición o por grupos.
- Enlaces Maps cortos: útiles como enlace, pero no proporcionan coordenadas directas fiables.
- Instalaciones mezclan nombre y URL en una sola celda. En el archivo definitivo, muchas no incluyen municipio explícito; el importador usa el municipio de la entidad por defecto y solo interpreta un prefijo antes de `:` como municipio si coincide con un municipio conocido.
- El archivo definitivo trae `La Laguna` en una fila; se normaliza a `San Cristóbal de La Laguna` durante la importación.
- Algunos campos numéricos incluyen texto, por ejemplo socios `291 en activo`.
- El municipio `Agüimes` confirma que no todos los registros son estrictamente de municipios de Tenerife.

## Regla de importaciones posteriores

Después de la carga inicial, las importaciones Excel deben funcionar en modo solo altas:

- Si una fila corresponde a una entidad nueva, se crea junto con sus modalidades, contactos, redes, instalaciones y tramos de edad.
- Si una fila corresponde a una entidad ya existente, se registra como `duplicate` en `import_rows.status`.
- Las filas repetidas no actualizan `entities` ni reemplazan relaciones hijas como contactos, redes, instalaciones, modalidades o edades.
- La pantalla de importación muestra cuántas filas fueron creadas, repetidas, omitidas o terminaron con error.
- La detección de repetidos usa el slug derivado del nombre y, como apoyo, la combinación de nombre normalizado y municipio.
