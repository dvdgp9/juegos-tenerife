<?php

declare(strict_types=1);

namespace JuegosTenerife\Controllers;

use JuegosTenerife\Core\Response;
use JuegosTenerife\Core\View;
use JuegosTenerife\Models\EntityRepository;
use RuntimeException;

final class EntityController
{
    public function show(string $slug): Response
    {
        try {
            $entity = (new EntityRepository())->findBySlug($slug);
        } catch (RuntimeException $exception) {
            return new Response('Error consultando la base de datos.', 500);
        }

        if ($entity === null) {
            return new Response('Entidad no encontrada', 404);
        }

        $mapPoints = [];
        if ($entity['latitude'] !== null && $entity['longitude'] !== null) {
            $modalityNames = [];
            foreach ($entity['modalities'] as $m) {
                $modalityNames[] = $m['name'];
            }
            $mapPoints[] = [
                'title' => $entity['name'],
                'municipality' => $entity['municipality'] ?? '',
                'modalities' => implode(', ', $modalityNames),
                'lat' => (float) $entity['latitude'],
                'lng' => (float) $entity['longitude'],
                'url' => '#',
            ];
        }
        foreach ($entity['facilities'] as $facility) {
            if ($facility['latitude'] === null || $facility['longitude'] === null) {
                continue;
            }
            $mapPoints[] = [
                'title' => $facility['name'],
                'municipality' => $facility['locality'] ?? '',
                'modalities' => $facility['label'] ?? '',
                'lat' => (float) $facility['latitude'],
                'lng' => (float) $facility['longitude'],
                'url' => '#',
            ];
        }

        return View::render('entity-show', [
            'title' => $entity['name'],
            'entity' => $entity,
            'mapPoints' => $mapPoints,
        ]);
    }
}
