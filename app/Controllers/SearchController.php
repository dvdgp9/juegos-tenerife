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

        try {
            $results = (new EntityRepository())->search($filters);
            $modalities = (new ModalityRepository())->all();
            $municipalities = (new MunicipalityRepository())->filterable();
            $entityTypes = (new EntityTypeRepository())->all();
        } catch (RuntimeException $exception) {
            $dbError = $exception->getMessage();
        }

        $mapPoints = [];
        foreach ($results as $row) {
            if ($row['latitude'] === null || $row['longitude'] === null) {
                continue;
            }
            $mapPoints[] = [
                'title' => $row['name'],
                'municipality' => $row['municipality'] ?? '',
                'modalities' => $row['modalities'] ?? '',
                'lat' => (float) $row['latitude'],
                'lng' => (float) $row['longitude'],
                'url' => '/entidades/' . $row['slug'],
            ];
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
