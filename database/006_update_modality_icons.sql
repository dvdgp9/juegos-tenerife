-- Asocia iconos a modalidades secundarias usadas en fichas/listados.
-- Ejecutar tras desplegar los assets de public/assets/images/iconos-deportes.

SET NAMES utf8mb4;

INSERT INTO modalities (name, slug, short_description, icon_path, is_featured, sort_order) VALUES
('Levantamiento de Arado', 'levantamiento-de-arado', NULL, '/assets/images/iconos-deportes/LEVANTAMIENTO_ARADO_2.png', 0, 100),
('Levantamiento y Pulseo de Piedra', 'levantamiento-y-pulseo-de-piedra', NULL, '/assets/images/iconos-deportes/LEVANTAMIENTO_PIEDRA_2.png', 0, 110),
('Petanca', 'petanca', NULL, '/assets/images/iconos-deportes/PETANCA_2.png', 0, 120),
('Billarda Canaria', 'billarda-canaria', NULL, '/assets/images/iconos-deportes/BILLARDA_CANARIA_2.png', 0, 130),
('Pina', 'pina', NULL, '/assets/images/iconos-deportes/PILA_CANARIA_2.png', 0, 140)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    icon_path = VALUES(icon_path),
    is_featured = VALUES(is_featured),
    sort_order = VALUES(sort_order);
