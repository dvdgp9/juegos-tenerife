-- Ejecutar antes de desplegar el editor de fichas de modalidades.
-- NULL conserva el contenido original; un texto vacío o [] permite borrarlo desde el panel.
SET NAMES utf8mb4;

ALTER TABLE modalities
    ADD COLUMN page_lead TEXT NULL AFTER extra_info,
    ADD COLUMN page_sections MEDIUMTEXT NULL AFTER page_lead;
