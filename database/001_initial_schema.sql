-- Esquema inicial para Juegos Tenerife.
-- Ejecutar en una base de datos vacía.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role VARCHAR(30) NOT NULL DEFAULT 'admin',
    username VARCHAR(80) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(190) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE municipalities (
    id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(140) NOT NULL,
    island VARCHAR(120) NULL,
    is_tenerife TINYINT(1) NOT NULL DEFAULT 1,
    is_filterable TINYINT(1) NOT NULL DEFAULT 1,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 100,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_municipalities_name (name),
    UNIQUE KEY uq_municipalities_slug (slug),
    KEY idx_municipalities_filter (is_filterable, sort_order, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE entity_types (
    id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(140) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_entity_types_name (name),
    UNIQUE KEY uq_entity_types_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE modalities (
    id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL,
    slug VARCHAR(180) NOT NULL,
    short_description TEXT NULL,
    full_description MEDIUMTEXT NULL,
    extra_info MEDIUMTEXT NULL,
    icon_path VARCHAR(255) NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 100,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_modalities_name (name),
    UNIQUE KEY uq_modalities_slug (slug),
    KEY idx_modalities_featured_sort (is_featured, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE entities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity_type_id SMALLINT UNSIGNED NULL,
    municipality_id SMALLINT UNSIGNED NULL,
    name VARCHAR(220) NOT NULL,
    slug VARCHAR(240) NOT NULL,
    logo_path VARCHAR(255) NULL,
    address VARCHAR(255) NULL,
    locality VARCHAR(160) NULL,
    postal_code VARCHAR(20) NULL,
    google_maps_url TEXT NULL,
    latitude DECIMAL(10, 7) NULL,
    longitude DECIMAL(10, 7) NULL,
    geocoding_status VARCHAR(30) NOT NULL DEFAULT 'pending',
    website_url TEXT NULL,
    history MEDIUMTEXT NULL,
    corporate_principles MEDIUMTEXT NULL,
    sports_values MEDIUMTEXT NULL,
    total_teams SMALLINT UNSIGNED NULL,
    teams_by_gender VARCHAR(255) NULL,
    teams_by_age VARCHAR(255) NULL,
    total_practitioners SMALLINT UNSIGNED NULL,
    female_practitioners SMALLINT UNSIGNED NULL,
    male_practitioners SMALLINT UNSIGNED NULL,
    training_practices MEDIUMTEXT NULL,
    training_days VARCHAR(255) NULL,
    training_hours VARCHAR(255) NULL,
    has_board TINYINT(1) NULL,
    board_members SMALLINT UNSIGNED NULL,
    board_male SMALLINT UNSIGNED NULL,
    board_female SMALLINT UNSIGNED NULL,
    holds_annual_assemblies TINYINT(1) NULL,
    has_members TINYINT(1) NULL,
    total_members VARCHAR(120) NULL,
    male_members VARCHAR(120) NULL,
    female_members VARCHAR(120) NULL,
    equality_protocol_status VARCHAR(30) NULL,
    violence_protocol_status VARCHAR(30) NULL,
    lopivi_status VARCHAR(30) NULL,
    joined_educar_entrenando TINYINT(1) NULL,
    supports_educational_needs TINYINT(1) NULL,
    supports_disability TINYINT(1) NULL,
    source_reference VARCHAR(190) NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    UNIQUE KEY uq_entities_slug (slug),
    KEY idx_entities_name (name),
    KEY idx_entities_municipality (municipality_id),
    KEY idx_entities_type (entity_type_id),
    KEY idx_entities_geo (latitude, longitude),
    KEY idx_entities_published (is_published, deleted_at),
    CONSTRAINT fk_entities_entity_type FOREIGN KEY (entity_type_id) REFERENCES entity_types (id) ON DELETE SET NULL,
    CONSTRAINT fk_entities_municipality FOREIGN KEY (municipality_id) REFERENCES municipalities (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE entity_modalities (
    entity_id BIGINT UNSIGNED NOT NULL,
    modality_id SMALLINT UNSIGNED NOT NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 100,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (entity_id, modality_id),
    KEY idx_entity_modalities_modality (modality_id),
    CONSTRAINT fk_entity_modalities_entity FOREIGN KEY (entity_id) REFERENCES entities (id) ON DELETE CASCADE,
    CONSTRAINT fk_entity_modalities_modality FOREIGN KEY (modality_id) REFERENCES modalities (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE entity_contacts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity_id BIGINT UNSIGNED NOT NULL,
    contact_type VARCHAR(30) NOT NULL,
    label VARCHAR(120) NULL,
    person_name VARCHAR(190) NULL,
    role_title VARCHAR(190) NULL,
    phone VARCHAR(80) NULL,
    email VARCHAR(190) NULL,
    value VARCHAR(255) NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 100,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_entity_contacts_entity (entity_id),
    KEY idx_entity_contacts_type (contact_type),
    CONSTRAINT fk_entity_contacts_entity FOREIGN KEY (entity_id) REFERENCES entities (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE entity_social_links (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity_id BIGINT UNSIGNED NOT NULL,
    platform VARCHAR(30) NOT NULL,
    label VARCHAR(120) NULL,
    url TEXT NOT NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 100,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_entity_social_links_entity (entity_id),
    CONSTRAINT fk_entity_social_links_entity FOREIGN KEY (entity_id) REFERENCES entities (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE facilities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    municipality_id SMALLINT UNSIGNED NULL,
    name VARCHAR(220) NOT NULL,
    slug VARCHAR(240) NOT NULL,
    address VARCHAR(255) NULL,
    locality VARCHAR(160) NULL,
    postal_code VARCHAR(20) NULL,
    google_maps_url TEXT NULL,
    latitude DECIMAL(10, 7) NULL,
    longitude DECIMAL(10, 7) NULL,
    geocoding_status VARCHAR(30) NOT NULL DEFAULT 'pending',
    notes MEDIUMTEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    UNIQUE KEY uq_facilities_slug (slug),
    KEY idx_facilities_municipality (municipality_id),
    KEY idx_facilities_geo (latitude, longitude),
    CONSTRAINT fk_facilities_municipality FOREIGN KEY (municipality_id) REFERENCES municipalities (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE entity_facilities (
    entity_id BIGINT UNSIGNED NOT NULL,
    facility_id BIGINT UNSIGNED NOT NULL,
    label VARCHAR(120) NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 100,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (entity_id, facility_id),
    KEY idx_entity_facilities_facility (facility_id),
    CONSTRAINT fk_entity_facilities_entity FOREIGN KEY (entity_id) REFERENCES entities (id) ON DELETE CASCADE,
    CONSTRAINT fk_entity_facilities_facility FOREIGN KEY (facility_id) REFERENCES facilities (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE entity_age_ranges (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity_id BIGINT UNSIGNED NOT NULL,
    age_range_key VARCHAR(80) NOT NULL,
    label VARCHAR(160) NOT NULL,
    practitioners_count SMALLINT UNSIGNED NULL,
    raw_value VARCHAR(120) NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 100,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_entity_age_ranges_entity_key (entity_id, age_range_key),
    KEY idx_entity_age_ranges_entity (entity_id),
    CONSTRAINT fk_entity_age_ranges_entity FOREIGN KEY (entity_id) REFERENCES entities (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE media_files (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_type VARCHAR(30) NOT NULL,
    owner_id BIGINT UNSIGNED NOT NULL,
    media_type VARCHAR(30) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    mime_type VARCHAR(120) NULL,
    file_size INT UNSIGNED NULL,
    alt_text VARCHAR(255) NULL,
    caption VARCHAR(255) NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 100,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_media_owner (owner_type, owner_id),
    KEY idx_media_type (media_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE imports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    original_filename VARCHAR(255) NOT NULL,
    stored_path VARCHAR(255) NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'uploaded',
    total_rows INT UNSIGNED NOT NULL DEFAULT 0,
    created_rows INT UNSIGNED NOT NULL DEFAULT 0,
    updated_rows INT UNSIGNED NOT NULL DEFAULT 0,
    skipped_rows INT UNSIGNED NOT NULL DEFAULT 0,
    error_rows INT UNSIGNED NOT NULL DEFAULT 0,
    summary JSON NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    KEY idx_imports_user (user_id),
    KEY idx_imports_status (status),
    CONSTRAINT fk_imports_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE import_rows (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    import_id BIGINT UNSIGNED NOT NULL,
    row_number INT UNSIGNED NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'pending',
    entity_id BIGINT UNSIGNED NULL,
    raw_data JSON NOT NULL,
    normalized_data JSON NULL,
    warnings JSON NULL,
    errors JSON NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_import_rows_import_row (import_id, row_number),
    KEY idx_import_rows_status (status),
    KEY idx_import_rows_entity (entity_id),
    CONSTRAINT fk_import_rows_import FOREIGN KEY (import_id) REFERENCES imports (id) ON DELETE CASCADE,
    CONSTRAINT fk_import_rows_entity FOREIGN KEY (entity_id) REFERENCES entities (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(80) NOT NULL,
    subject_type VARCHAR(80) NOT NULL,
    subject_id BIGINT UNSIGNED NULL,
    metadata JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_audit_user (user_id),
    KEY idx_audit_subject (subject_type, subject_id),
    KEY idx_audit_action (action),
    CONSTRAINT fk_audit_logs_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

