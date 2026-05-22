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

final class SearchController
{
    public function index(): Response
    {
        $filters = [
            'q' => (string) ($_GET['q'] ?? ''),
            'municipio' => (string) ($_GET['municipio'] ?? ''),
            'tipo' => (string) ($_GET['tipo'] ?? ''),
            'modalidad' => (string) ($_GET['modalidad'] ?? ''),
        ];

        $results = [];
        $modalities = [];
        $municipalities = [];
        $entityTypes = [];
        $dbError = null;

        $mapPoints = [];
        try {
            $repo = new EntityRepository();
            $results = $repo->search($filters);
            $modalities = (new ModalityRepository())->all();
            $municipalities = (new MunicipalityRepository())->filterable();
            $entityTypes = (new EntityTypeRepository())->all();

            $entityIds = array_map(static fn($r) => (int) $r['id'], $results);
            $mapPoints = $repo->facilityMapPointsForEntities($entityIds);
        } catch (RuntimeException $exception) {
            $dbError = $exception->getMessage();
        }

        return View::render('search-results', [
            'title' => 'Resultados de búsqueda',
            'filters' => $filters,
            'results' => $results,
            'modalities' => $modalities,
            'municipalities' => $municipalities,
            'entityTypes' => $entityTypes,
            'mapPoints' => $mapPoints,
            'dbError' => $dbError,
        ]);
    }
}
