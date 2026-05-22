<?php

declare(strict_types=1);

namespace JuegosTenerife\Controllers;

use JuegosTenerife\Core\Response;
use JuegosTenerife\Core\View;
use JuegosTenerife\Models\EntityRepository;
use JuegosTenerife\Models\EntityTypeRepository;
use JuegosTenerife\Models\ModalityRepository;
use JuegosTenerife\Models\MunicipalityRepository;
use RuntimeException;

final class HomeController
{
    public function index(): Response
    {
        $modalities = [];
        $municipalities = [];
        $entityTypes = [];
        $featuredEntities = [];
        $dbError = null;

        try {
            $modalities = (new ModalityRepository())->featured();
            $municipalities = (new MunicipalityRepository())->filterable();
            $entityTypes = (new EntityTypeRepository())->all();
            $featuredEntities = (new EntityRepository())->featuredByModality();
        } catch (RuntimeException $exception) {
            $dbError = $exception->getMessage();
        }

        return View::render('home', [
            'title' => 'Censo de Deportes y Juegos Tradicionales de Tenerife',
            'modalities' => $modalities,
            'municipalities' => $municipalities,
            'entityTypes' => $entityTypes,
            'featuredEntities' => $featuredEntities,
            'dbError' => $dbError,
        ]);
    }
}
