-- Fix para entornos donde 001 pudo fallar por el nombre row_number.
-- Ejecutar si la tabla import_rows no existe o si existe con row_number.

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS import_rows;

CREATE TABLE import_rows (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    import_id BIGINT UNSIGNED NOT NULL,
    source_row_number INT UNSIGNED NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'pending',
    entity_id BIGINT UNSIGNED NULL,
    raw_data JSON NOT NULL,
    normalized_data JSON NULL,
    warnings JSON NULL,
    errors JSON NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_import_rows_import_row (import_id, source_row_number),
    KEY idx_import_rows_status (status),
    KEY idx_import_rows_entity (entity_id),
    CONSTRAINT fk_import_rows_import FOREIGN KEY (import_id) REFERENCES imports (id) ON DELETE CASCADE,
    CONSTRAINT fk_import_rows_entity FOREIGN KEY (entity_id) REFERENCES entities (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

