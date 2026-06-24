<?php

declare(strict_types=1);

namespace JuegosTenerife\Controllers\Admin;

use JuegosTenerife\Core\Response;
use JuegosTenerife\Core\Csrf;
use JuegosTenerife\Core\View;

final class DashboardController extends AdminController
{
    public function index(): Response
    {
        $redirect = $this->requireAdmin();
        if ($redirect !== null) {
            return $redirect;
        }

        return View::render('admin/dashboard', [
            'title' => 'Panel de administración',
            'activeNav' => 'dashboard',
            'user' => $this->user(),
            'csrf' => Csrf::token(),
            'flash' => $this->consumeFlash(),
        ]);
    }
}
