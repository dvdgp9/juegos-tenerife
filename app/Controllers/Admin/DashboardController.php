<?php

declare(strict_types=1);

namespace JuegosTenerife\Controllers\Admin;

use JuegosTenerife\Core\Response;
use JuegosTenerife\Core\Csrf;
use JuegosTenerife\Core\View;
use JuegosTenerife\Services\AuthService;

final class DashboardController
{
    public function index(): Response
    {
        $auth = new AuthService();

        if (!$auth->check()) {
            return Response::redirect('/admin/login');
        }

        return View::render('admin/dashboard', [
            'title' => 'Panel de administración',
            'user' => $auth->user(),
            'csrf' => Csrf::token(),
        ]);
    }
}
