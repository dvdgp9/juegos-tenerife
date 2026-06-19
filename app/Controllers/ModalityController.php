<?php

declare(strict_types=1);

namespace JuegosTenerife\Controllers;

use JuegosTenerife\Content\ModalityContent;
use JuegosTenerife\Core\Response;
use JuegosTenerife\Core\View;

final class ModalityController
{
    public function show(string $slug): Response
    {
        $modality = ModalityContent::find($slug);

        if ($modality === null) {
            return new Response('Modalidad no encontrada', 404);
        }

        return View::render('modality-show', [
            'title' => $modality['name'] . ' | Juegos Tradicionales de Tenerife',
            'slug' => $slug,
            'modality' => $modality,
        ]);
    }
}
