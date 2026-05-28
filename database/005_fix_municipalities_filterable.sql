-- Saneamiento: dejar como filtrables únicamente los 31 municipios canónicos de Tenerife.
-- Cualquier municipio "colado" por importación (por ejemplo, nombres de instalaciones)
-- o de fuera de Tenerife dejará de aparecer en el desplegable de búsqueda.

UPDATE municipalities
   SET is_filterable = 0
 WHERE slug NOT IN (
        'adeje','arafo','arico','arona','buenavista-del-norte','candelaria',
        'el-rosario','el-sauzal','el-tanque','fasnia','garachico',
        'granadilla-de-abona','guia-de-isora','guimar','icod-de-los-vinos',
        'la-guancha','la-matanza-de-acentejo','la-orotava','la-victoria-de-acentejo',
        'los-realejos','los-silos','puerto-de-la-cruz','san-cristobal-de-la-laguna',
        'san-juan-de-la-rambla','san-miguel-de-abona','santa-cruz-de-tenerife',
        'santa-ursula','santiago-del-teide','tacoronte','tegueste','vilaflor-de-chasna'
   );

-- Actualizar pictograma de Lucha Canaria a la versión negativa (en línea con el resto).
UPDATE modalities
   SET icon_path = '/assets/images/pictogramas/LUCHA_CANARIA_2.png'
 WHERE slug = 'lucha-canaria';
