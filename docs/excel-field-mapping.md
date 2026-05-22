# Mapeo del Excel de entidades

Archivo revisado: `Listado Entidades Prueba 20260522.xlsx`.

## Resumen

- Hoja: `ENTIDADES`.
- Registros con datos: 11.
- Columnas: 68.
- No contiene coordenadas.
- No contiene logos ni fotos.
- Contiene enlaces cortos de Google Maps dentro de columnas `Instalaciones1` a `Instalaciones9`.

## Cabeceras detectadas

1. Tipo Entidad
2. Modalidad1
3. Modalidad2
4. Modalidad3
5. Modalidad4
6. Nombre Entidad
7. Domicilio
8. Localidad
9. Municipio
10. Código Postal
11. Teléfono1
12. Teléfono2
13. Email1
14. Email2
15. Web
16. Facebook
17. Instagram
18. X
19. TikTok
20. Youtube
21. Persona Contacto
22. Cargo Contacto
23. Teléfono1
24. Teléfono2
25. Email
26. Directiva
27. Miembros Directiva
28. Directivos/Hombres
29. Directivas/Mujeres
30. Asambleas Anuales
31. Socios/as
32. Número Total Socios/as
33. Socios/Hombres
34. Socias/Mujeres
35. Equipos
36. Equipos por Género
37. Equipos por Edad
38. Total Deportistas/Practicantes
39. Mujeres/Niñas
40. Hombres/Niños
41. Edades: De 0 a 5 años
42. Edades: De 6 a 11 años
43. Edades: De 12 a 17 años
44. Edades: De 18 a 29 años
45. Edades: De 30 a 45 años
46. Edades: De 46 a 59 años
47. Edades: 60 años y más
48. Instalaciones1
49. Instalaciones2
50. Instalaciones3
51. Instalaciones4
52. Instalaciones5
53. Instalaciones6
54. Instalaciones7
55. Instalaciones8
56. Instalaciones9
57. Entrenamientos/Prácticas
58. Días
59. Horarios
60. Breve Historia
61. Principios Corporativos
62. Valores Deportivos
63. Protocolo Igualdad
64. Protocolo Violencia
65. LOPIVI
66. Necesidades Educativas
67. Discapacidad
68. Educar Entrenando

## Mapeo propuesto

- `Tipo Entidad` -> `entity_types.name`.
- `Nombre Entidad` -> `entities.name`.
- `Domicilio`, `Localidad`, `Municipio`, `Código Postal` -> campos de dirección de `entities`.
- `Modalidad1` a `Modalidad4` -> `modalities` + `entity_modalities`.
- `Teléfono1`, `Teléfono2`, `Email1`, `Email2` en columnas 11-14 -> `entity_contacts` con tipo `phone` o `email`.
- `Web` -> `entities.website_url`.
- `Facebook`, `Instagram`, `X`, `TikTok`, `Youtube` -> `entity_social_links`.
- `Persona Contacto`, `Cargo Contacto`, `Teléfono1`, `Teléfono2`, `Email` en columnas 21-25 -> `entity_contacts` con tipo `person`.
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
- Instalaciones mezclan municipio, nombre y URL en una sola celda.
- Algunos campos numéricos incluyen texto, por ejemplo socios `291 en activo`.
- El municipio `Agüimes` confirma que no todos los registros son estrictamente de municipios de Tenerife.
