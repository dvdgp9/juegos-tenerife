<?php

declare(strict_types=1);

namespace JuegosTenerife\Controllers\Admin;

use JuegosTenerife\Core\Database;
use JuegosTenerife\Core\Response;
use JuegosTenerife\Core\View;
use JuegosTenerife\Services\AuthService;
use PDO;
use RuntimeException;

final class EntityController
{
    public function index(): Response
    {
        if (!(new AuthService())->check()) {
            return Response::redirect('/admin/login');
        }

        try {
            $entities = $this->entities();
            $error = null;
        } catch (RuntimeException $exception) {
            $entities = [];
            $error = $exception->getMessage();
        }

        return View::render('admin/entities/index', [
            'title' => 'Entidades',
            'entities' => $entities,
            'error' => $error,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function entities(): array
    {
        $pdo = Database::connection();
        $statement = $pdo->query(
            "SELECT
                e.id,
                e.name,
                e.slug,
                e.is_published,
                e.geocoding_status,
                et.name AS entity_type,
                m.name AS municipality,
                GROUP_CONCAT(DISTINCT mo.name ORDER BY em.sort_order SEPARATOR ', ') AS modalities
             FROM entities e
             LEFT JOIN entity_types et ON et.id = e.entity_type_id
             LEFT JOIN municipalities m ON m.id = e.municipality_id
             LEFT JOIN entity_modalities em ON em.entity_id = e.id
             LEFT JOIN modalities mo ON mo.id = em.modality_id
             WHERE e.deleted_at IS NULL
             GROUP BY e.id, e.name, e.slug, e.is_published, e.geocoding_status, et.name, m.name
             ORDER BY e.updated_at DESC, e.name ASC
             LIMIT 200"
        );

        if (!$statement) {
            return [];
        }

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}

