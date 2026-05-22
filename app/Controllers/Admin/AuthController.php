<?php

declare(strict_types=1);

namespace JuegosTenerife\Controllers\Admin;

use JuegosTenerife\Core\Csrf;
use JuegosTenerife\Core\Response;
use JuegosTenerife\Core\View;
use JuegosTenerife\Services\AuthService;
use RuntimeException;

final class AuthController
{
    public function login(): Response
    {
        if ((new AuthService())->check()) {
            return Response::redirect('/admin');
        }

        return View::render('admin/login', [
            'title' => 'Acceso administración',
            'csrf' => Csrf::token(),
            'error' => null,
        ]);
    }

    public function authenticate(): Response
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            return $this->loginWithError('La sesión ha caducado. Vuelve a intentarlo.');
        }

        $identifier = trim((string) ($_POST['identifier'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($identifier === '' || $password === '') {
            return $this->loginWithError('Introduce usuario o correo y contraseña.');
        }

        try {
            $user = (new AuthService())->attempt($identifier, $password);
        } catch (RuntimeException) {
            return $this->loginWithError('No se pudo conectar con la base de datos. Revisa la configuración.');
        }

        if ($user === null) {
            return $this->loginWithError('Credenciales no válidas.');
        }

        return Response::redirect('/admin');
    }

    public function logout(): Response
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            return Response::redirect('/admin');
        }

        (new AuthService())->logout();

        return Response::redirect('/admin/login');
    }

    private function loginWithError(string $error): Response
    {
        return View::render('admin/login', [
            'title' => 'Acceso administración',
            'csrf' => Csrf::token(),
            'error' => $error,
        ]);
    }
}
