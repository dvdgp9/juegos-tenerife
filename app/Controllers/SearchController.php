<?php

declare(strict_types=1);

namespace JuegosTenerife\Controllers;

use JuegosTenerife\Core\Response;
use JuegosTenerife\Core\View;

final class SearchController
{
    public function index(): Response
    {
        return View::render('search-results', [
            'title' => 'Resultados de búsqueda',
        ]);
    }
}

