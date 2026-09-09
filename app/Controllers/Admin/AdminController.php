<?php

declare(strict_types=1);

namespace JuegosTenerife\Controllers\Admin;

use JuegosTenerife\Core\Response;
use JuegosTenerife\Core\Session;
use JuegosTenerife\Services\AuditLogger;
use JuegosTenerife\Services\AuthService;

abstract class AdminController
{
    protected AuthService $auth;

    public function __construct()
    {
        $this->auth = new AuthService();
    }

    protected function requireAdmin(): ?Response
    {
        if ($this->auth->check()) {
            return null;
        }

        return Response::redirect('/admin/login');
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function user(): ?array
    {
        return $this->auth->user();
    }

    /**
     * Registra una acción administrativa atribuida a la usuaria en sesión.
     *
     * @param array<string, mixed> $metadata
     */
    protected function audit(string $action, string $subjectType, ?int $subjectId = null, array $metadata = []): void
    {
        $user = $this->user();

        AuditLogger::log(
            $action,
            $subjectType,
            $subjectId,
            $metadata,
            isset($user['id']) ? (int) $user['id'] : null
        );
    }

    protected function flash(string $type, string $message): void
    {
        Session::put('flash', [
            'type' => $type,
            'message' => $message,
        ]);
    }

    /**
     * @return array{type:string,message:string}|null
     */
    protected function consumeFlash(): ?array
    {
        $flash = Session::get('flash');
        Session::forget('flash');

        if (!is_array($flash) || empty($flash['message'])) {
            return null;
        }

        return [
            'type' => (string) ($flash['type'] ?? 'info'),
            'message' => (string) $flash['message'],
        ];
    }
}
