<?php

declare(strict_types=1);

namespace JuegosTenerife\Controllers\Admin;

use JuegosTenerife\Core\Csrf;
use JuegosTenerife\Core\Database;
use JuegosTenerife\Core\Response;
use JuegosTenerife\Core\View;
use PDO;

final class UserController extends AdminController
{
    public function index(): Response
    {
        $redirect = $this->requireSuperadmin();
        if ($redirect !== null) {
            return $redirect;
        }

        return View::render('admin/users/index', [
            'title' => 'Usuarias',
            'activeNav' => 'users',
            'user' => $this->user(),
            'csrf' => Csrf::token(),
            'flash' => $this->consumeFlash(),
            'users' => $this->users(),
        ]);
    }

    public function create(): Response
    {
        $redirect = $this->requireSuperadmin();
        if ($redirect !== null) {
            return $redirect;
        }

        return $this->renderForm($this->blankUser(), [], 'Crear usuaria', '/admin/users');
    }

    public function store(): Response
    {
        $redirect = $this->requireSuperadmin();
        if ($redirect !== null) {
            return $redirect;
        }

        $user = $this->userFromPost();
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            return $this->renderForm($user, ['La sesión ha caducado. Vuelve a intentarlo.'], 'Crear usuaria', '/admin/users');
        }

        $errors = $this->validateUser($user, null, true);
        if ($errors !== []) {
            return $this->renderForm($user, $errors, 'Crear usuaria', '/admin/users');
        }

        $statement = Database::connection()->prepare(
            'INSERT INTO users (role, username, email, password_hash, full_name, is_active)
             VALUES (:role, :username, :email, :password_hash, :full_name, :is_active)'
        );
        $statement->execute([
            'role' => $user['role'],
            'username' => $user['username'],
            'email' => $user['email'],
            'password_hash' => password_hash((string) $_POST['password'], PASSWORD_DEFAULT),
            'full_name' => $this->nullable($user['full_name'] ?? null),
            'is_active' => (int) $user['is_active'],
        ]);

        $this->flash('success', 'Usuaria creada correctamente.');

        return Response::redirect('/admin/users');
    }

    public function edit(string $id): Response
    {
        $redirect = $this->requireSuperadmin();
        if ($redirect !== null) {
            return $redirect;
        }

        $adminUser = $this->findUser((int) $id);
        if ($adminUser === null) {
            return new Response('Usuaria no encontrada', 404);
        }

        return $this->renderForm($adminUser, [], 'Editar usuaria', '/admin/users/' . (int) $id);
    }

    public function update(string $id): Response
    {
        $redirect = $this->requireSuperadmin();
        if ($redirect !== null) {
            return $redirect;
        }

        $userId = (int) $id;
        $existing = $this->findUser($userId);
        if ($existing === null) {
            return new Response('Usuaria no encontrada', 404);
        }

        $user = array_merge($existing, $this->userFromPost());
        $user['id'] = $userId;

        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            return $this->renderForm($user, ['La sesión ha caducado. Vuelve a intentarlo.'], 'Editar usuaria', '/admin/users/' . $userId);
        }

        $currentUser = $this->user();
        if (isset($currentUser['id']) && (int) $currentUser['id'] === $userId) {
            $user['is_active'] = 1;
            $user['role'] = 'superadmin';
        }

        $changePassword = trim((string) ($_POST['password'] ?? '')) !== '';
        $errors = $this->validateUser($user, $userId, $changePassword);
        if ($errors !== []) {
            return $this->renderForm($user, $errors, 'Editar usuaria', '/admin/users/' . $userId);
        }

        $params = [
            'role' => $user['role'],
            'username' => $user['username'],
            'email' => $user['email'],
            'full_name' => $this->nullable($user['full_name'] ?? null),
            'is_active' => (int) $user['is_active'],
            'id' => $userId,
        ];

        $passwordSql = '';
        if ($changePassword) {
            $passwordSql = ', password_hash = :password_hash';
            $params['password_hash'] = password_hash((string) $_POST['password'], PASSWORD_DEFAULT);
        }

        $statement = Database::connection()->prepare(
            'UPDATE users
             SET role = :role,
                 username = :username,
                 email = :email,
                 full_name = :full_name,
                 is_active = :is_active' . $passwordSql . '
             WHERE id = :id'
        );
        $statement->execute($params);

        $this->flash('success', 'Usuaria guardada correctamente.');

        return Response::redirect('/admin/users/' . $userId . '/edit');
    }

    private function requireSuperadmin(): ?Response
    {
        $redirect = $this->requireAdmin();
        if ($redirect !== null) {
            return $redirect;
        }

        $user = $this->user();
        if (!is_array($user) || (string) ($user['role'] ?? '') !== 'superadmin') {
            return new Response('No tienes permisos para gestionar usuarias.', 403);
        }

        return null;
    }

    /**
     * @param array<string, mixed> $adminUser
     * @param list<string> $errors
     */
    private function renderForm(array $adminUser, array $errors, string $title, string $action): Response
    {
        return View::render('admin/users/form', [
            'title' => $title,
            'activeNav' => 'users',
            'user' => $this->user(),
            'csrf' => Csrf::token(),
            'flash' => $this->consumeFlash(),
            'adminUser' => $adminUser,
            'errors' => $errors,
            'action' => $action,
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function users(): array
    {
        $statement = Database::connection()->query(
            'SELECT id, role, username, email, full_name, is_active, last_login_at, created_at, updated_at
             FROM users
             ORDER BY is_active DESC, role DESC, full_name ASC, username ASC'
        );

        return $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findUser(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, role, username, email, full_name, is_active, last_login_at, created_at, updated_at
             FROM users
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($user) ? array_merge($this->blankUser(), $user) : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function blankUser(): array
    {
        return [
            'id' => null,
            'role' => 'admin',
            'username' => '',
            'email' => '',
            'full_name' => '',
            'is_active' => 1,
            'last_login_at' => null,
            'created_at' => null,
            'updated_at' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function userFromPost(): array
    {
        return [
            'role' => in_array((string) ($_POST['role'] ?? 'admin'), ['admin', 'superadmin'], true) ? (string) $_POST['role'] : 'admin',
            'username' => trim((string) ($_POST['username'] ?? '')),
            'email' => strtolower(trim((string) ($_POST['email'] ?? ''))),
            'full_name' => trim((string) ($_POST['full_name'] ?? '')),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];
    }

    /**
     * @param array<string, mixed> $user
     * @return list<string>
     */
    private function validateUser(array $user, ?int $userId, bool $requirePassword): array
    {
        $errors = [];
        $username = (string) ($user['username'] ?? '');
        $email = (string) ($user['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');

        if ($username === '') {
            $errors[] = 'El usuario es obligatorio.';
        } elseif (!preg_match('/^[a-zA-Z0-9._-]{3,80}$/', $username)) {
            $errors[] = 'El usuario debe tener entre 3 y 80 caracteres y usar solo letras, números, punto, guion o guion bajo.';
        }

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Introduce un correo válido.';
        }

        if ($requirePassword || $password !== '' || $passwordConfirmation !== '') {
            if (strlen($password) < 10) {
                $errors[] = 'La contraseña debe tener al menos 10 caracteres.';
            }

            if ($password !== $passwordConfirmation) {
                $errors[] = 'Las contraseñas no coinciden.';
            }
        }

        if ($this->usernameExists($username, $userId)) {
            $errors[] = 'Ya existe una usuaria con ese nombre de usuario.';
        }

        if ($this->emailExists($email, $userId)) {
            $errors[] = 'Ya existe una usuaria con ese correo.';
        }

        return $errors;
    }

    private function usernameExists(string $username, ?int $userId): bool
    {
        $statement = Database::connection()->prepare(
            'SELECT COUNT(*)
             FROM users
             WHERE username = :username
               AND (:ignore_current = 1 OR id <> :id)'
        );
        $statement->execute([
            'username' => $username,
            'ignore_current' => $userId === null ? 1 : 0,
            'id' => $userId ?? 0,
        ]);

        return (int) $statement->fetchColumn() > 0;
    }

    private function emailExists(string $email, ?int $userId): bool
    {
        $statement = Database::connection()->prepare(
            'SELECT COUNT(*)
             FROM users
             WHERE email = :email
               AND (:ignore_current = 1 OR id <> :id)'
        );
        $statement->execute([
            'email' => $email,
            'ignore_current' => $userId === null ? 1 : 0,
            'id' => $userId ?? 0,
        ]);

        return (int) $statement->fetchColumn() > 0;
    }

    private function nullable(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
