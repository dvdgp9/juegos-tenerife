-- Usuario superadmin inicial.
-- Contraseña temporal comunicada por canal privado. Cambiar tras el primer acceso.

SET NAMES utf8mb4;

INSERT INTO users (role, username, email, password_hash, full_name, is_active)
VALUES (
    'superadmin',
    'it',
    'it@ebone.es',
    '$2y$12$qCeEcLG.8Qed5qpts.FzbOyxBWQl./p3IK13fbd9p04S/NZ5ZR13q',
    'Administrador',
    1
)
ON DUPLICATE KEY UPDATE
    role = VALUES(role),
    password_hash = VALUES(password_hash),
    full_name = VALUES(full_name),
    is_active = VALUES(is_active);

