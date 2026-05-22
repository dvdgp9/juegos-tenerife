<?php

declare(strict_types=1);

namespace JuegosTenerife\Controllers;

use JuegosTenerife\Core\Response;
use JuegosTenerife\Core\View;

final class HomeController
{
    public function index(): Response
    {
        return View::render('home', [
            'title' => 'Censo de Deportes y Juegos Tradicionales de Tenerife',
        ]);
    }
}

