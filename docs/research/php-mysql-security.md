# PHP, MySQL y seguridad base

Uso previsto: autenticación, sesiones, consultas SQL y protección de formularios.

Fuentes principales:

- https://www.php.net/manual/en/function.password-hash.php
- https://www.php.net/manual/en/ref.password.php
- https://www.php.net/manual/en/pdo.prepare.php
- https://dev.mysql.com/doc/refman/8.0/en/sql-prepared-statements.html

Notas:

- Usar `password_hash` para crear hashes de contraseñas.
- Usar `password_verify` para validar contraseñas.
- Guardar hashes en columna suficientemente amplia, por ejemplo `VARCHAR(255)`.
- Usar PDO con prepared statements para datos de usuario.
- Implementar CSRF en formularios admin y formularios públicos.
- Validar uploads por extensión, MIME real, tamaño y destino.

Decisiones:

- Roles iniciales: `superadmin` y `admin`.
- Login por usuario/correo y contraseña.
- Sesiones PHP con regeneración de ID tras login.
- Todas las consultas con entrada de usuario deben pasar por prepared statements.
