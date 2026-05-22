<?php

declare(strict_types=1);

namespace JuegosTenerife\Controllers;

use JuegosTenerife\Core\Response;
use JuegosTenerife\Core\View;

final class EntityController
{
    public function show(): Response
    {
        return View::render('entity-show', [
            'title' => 'Federación de Arrastre Canario',
        ]);
    }
}

