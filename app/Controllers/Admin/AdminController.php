<?php

declare(strict_types=1);

namespace JuegosTenerife\Controllers\Admin;

use JuegosTenerife\Core\Response;
use JuegosTenerife\Core\Session;
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
