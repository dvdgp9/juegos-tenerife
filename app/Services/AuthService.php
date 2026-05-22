<?php

declare(strict_types=1);

namespace JuegosTenerife\Services;

use JuegosTenerife\Core\Database;
use JuegosTenerife\Core\Session;
use PDO;

final class AuthService
{
    /**
     * @return array<string, mixed>|null
     */
    public function attempt(string $identifier, string $password): ?array
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'SELECT id, role, username, email, password_hash, full_name
             FROM users
             WHERE is_active = 1 AND (email = :email OR username = :username)
             LIMIT 1'
        );
        $statement->execute(['email' => $identifier, 'username' => $identifier]);
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        if (!is_array($user) || !password_verify($password, (string) $user['password_hash'])) {
            return null;
        }

        $update = $pdo->prepare('UPDATE users SET last_login_at = CURRENT_TIMESTAMP WHERE id = :id');
        $update->execute(['id' => $user['id']]);

        unset($user['password_hash']);

        Session::regenerate();
        Session::put('user', $user);

        return $user;
    }

    public function logout(): void
    {
        Session::destroy();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function user(): ?array
    {
        $user = Session::get('user');

        return is_array($user) ? $user : null;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }
}

