# Dompdf

Uso previsto: generación de PDF descargable para la ficha de entidad.

Fuente principal: https://packagist.org/packages/dompdf/dompdf

Notas:

- Dompdf convierte HTML/CSS a PDF.
- Es adecuado para documentos visualmente claros pero no necesariamente idénticos a la ficha web.
- Soporta muchas reglas CSS 2.1, algunas CSS3, imágenes PNG/JPEG/GIF/BMP y SVG básico.
- Requiere extensiones PHP como DOM y MBString.

Decisiones:

- Usar una plantilla HTML específica para PDF, no reutilizar directamente la ficha web completa.
- Mantener CSS de PDF simple y compatible.
- Validar imágenes y rutas locales para evitar fallos de renderizado en Plesk.
