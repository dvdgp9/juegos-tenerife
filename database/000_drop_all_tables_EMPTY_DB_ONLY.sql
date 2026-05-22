-- ATENCION:
-- Ejecutar SOLO si la base de datos esta vacia o si la migracion inicial fallo a medias.
-- Este script elimina todas las tablas del proyecto Juegos Tenerife.
-- No usar si ya hay datos reales cargados.

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS import_rows;
DROP TABLE IF EXISTS imports;
DROP TABLE IF EXISTS media_files;
DROP TABLE IF EXISTS entity_age_ranges;
DROP TABLE IF EXISTS entity_facilities;
DROP TABLE IF EXISTS facilities;
DROP TABLE IF EXISTS entity_social_links;
DROP TABLE IF EXISTS entity_contacts;
DROP TABLE IF EXISTS entity_modalities;
DROP TABLE IF EXISTS entities;
DROP TABLE IF EXISTS modalities;
DROP TABLE IF EXISTS entity_types;
DROP TABLE IF EXISTS municipalities;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

