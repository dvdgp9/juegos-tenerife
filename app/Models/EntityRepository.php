<?php

declare(strict_types=1);

namespace JuegosTenerife\Models;

use JuegosTenerife\Core\Database;
use PDO;

final class EntityRepository
{
    /**
     * One entity per featured modality (most recently updated), no entity repeated.
     * Modalities without any associated entity are omitted.
     *
     * @return list<array{modality: array<string, mixed>, entity: array<string, mixed>}>
     */
    public function featuredByModality(): array
    {
        $pdo = Database::connection();
        $statement = $pdo->query(
            'SELECT mo.id AS modality_id, mo.name AS modality_name, mo.slug AS modality_slug,
                    mo.icon_path AS modality_icon, mo.sort_order AS modality_sort
             FROM modalities mo
             WHERE mo.is_featured = 1
             ORDER BY mo.sort_order ASC, mo.name ASC'
        );

        $modalities = $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
        if ($modalities === []) {
            return [];
        }

        $usedEntityIds = [];
        $result = [];

        $entityStatement = $pdo->prepare(
            'SELECT e.id, e.name, e.slug, e.locality,
                    et.name AS entity_type,
                    m.name AS municipality
             FROM entities e
             INNER JOIN entity_modalities em ON em.entity_id = e.id
             LEFT JOIN entity_types et ON et.id = e.entity_type_id
             LEFT JOIN municipalities m ON m.id = e.municipality_id
             WHERE em.modality_id = :modality_id
               AND e.is_published = 1
               AND e.deleted_at IS NULL
             ORDER BY e.updated_at DESC, e.name ASC'
        );

        foreach ($modalities as $modality) {
            $entityStatement->execute(['modality_id' => $modality['modality_id']]);
            $candidates = $entityStatement->fetchAll(PDO::FETCH_ASSOC);

            foreach ($candidates as $candidate) {
                $id = (int) $candidate['id'];
                if (in_array($id, $usedEntityIds, true)) {
                    continue;
                }
                $usedEntityIds[] = $id;
                $result[] = [
                    'modality' => [
                        'name' => $modality['modality_name'],
                        'slug' => $modality['modality_slug'],
                        'icon_path' => $modality['modality_icon'],
                    ],
                    'entity' => $candidate,
                ];
                break;
            }
        }

        return $result;
    }

    /**
     * @param array{q?: string, municipio?: string, tipo?: string, modalidad?: string} $filters
     * @return list<array<string, mixed>>
     */
    public function search(array $filters, int $limit = 100): array
    {
        $pdo = Database::connection();

        $where = ['e.is_published = 1', 'e.deleted_at IS NULL'];
        $params = [];

        $q = trim($filters['q'] ?? '');
        if ($q !== '') {
            $where[] = 'e.name LIKE :q';
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q);
            $params['q'] = '%' . $escaped . '%';
        }

        $municipio = trim($filters['municipio'] ?? '');
        if ($municipio !== '') {
            $where[] = 'm.slug = :municipio_slug';
            $params['municipio_slug'] = $municipio;
        }

        $tipo = trim($filters['tipo'] ?? '');
        if ($tipo !== '') {
            $where[] = 'et.slug = :tipo_slug';
            $params['tipo_slug'] = $tipo;
        }

        $modalidad = trim($filters['modalidad'] ?? '');
        $modalityJoin = '';
        if ($modalidad !== '') {
            $modalityJoin = ' INNER JOIN entity_modalities em_f ON em_f.entity_id = e.id'
                . ' INNER JOIN modalities mo_f ON mo_f.id = em_f.modality_id AND mo_f.slug = :modalidad_slug';
            $params['modalidad_slug'] = $modalidad;
        }

        $sql = 'SELECT e.id, e.name, e.slug, e.latitude, e.longitude,
                       et.name AS entity_type, et.slug AS entity_type_slug,
                       m.name AS municipality, m.slug AS municipality_slug,
                       GROUP_CONCAT(DISTINCT mo.name ORDER BY em.sort_order SEPARATOR \', \') AS modalities,
                       GROUP_CONCAT(DISTINCT mo.icon_path ORDER BY em.sort_order SEPARATOR \'|\') AS modality_icons
                FROM entities e
                LEFT JOIN entity_types et ON et.id = e.entity_type_id
                LEFT JOIN municipalities m ON m.id = e.municipality_id
                LEFT JOIN entity_modalities em ON em.entity_id = e.id
                LEFT JOIN modalities mo ON mo.id = em.modality_id'
                . $modalityJoin
                . ' WHERE ' . implode(' AND ', $where)
                . ' GROUP BY e.id, e.name, e.slug, e.latitude, e.longitude, et.name, et.slug, m.name, m.slug'
                . ' ORDER BY e.name ASC'
                . ' LIMIT ' . (int) $limit;

        $statement = $pdo->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Map points (one per geocoded facility) for a given list of entity IDs.
     *
     * @param list<int> $entityIds
     * @return list<array{title: string, municipality: string, modalities: string, lat: float, lng: float, url: string}>
     */
    public function facilityMapPointsForEntities(array $entityIds): array
    {
        if ($entityIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($entityIds), '?'));
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'SELECT e.id AS entity_id, e.name AS entity_name, e.slug AS entity_slug,
                    f.name AS facility_name, f.locality AS facility_locality,
                    f.latitude, f.longitude,
                    m.name AS municipality
             FROM entity_facilities ef
             INNER JOIN entities e ON e.id = ef.entity_id
             INNER JOIN facilities f ON f.id = ef.facility_id
             LEFT JOIN municipalities m ON m.id = f.municipality_id
             WHERE ef.entity_id IN (' . $placeholders . ')
               AND f.latitude IS NOT NULL
               AND f.longitude IS NOT NULL
               AND f.deleted_at IS NULL
               AND e.deleted_at IS NULL
             ORDER BY e.name ASC, ef.sort_order ASC'
        );
        $statement->execute($entityIds);

        $points = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $points[] = [
                'title' => $row['entity_name'] . ' — ' . $row['facility_name'],
                'municipality' => (string) ($row['municipality'] ?? $row['facility_locality'] ?? ''),
                'modalities' => '',
                'lat' => (float) $row['latitude'],
                'lng' => (float) $row['longitude'],
                'url' => '/entidades/' . $row['entity_slug'],
            ];
        }

        return $points;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findBySlug(string $slug): ?array
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'SELECT e.*,
                    et.name AS entity_type, et.slug AS entity_type_slug,
                    m.name AS municipality, m.slug AS municipality_slug
             FROM entities e
             LEFT JOIN entity_types et ON et.id = e.entity_type_id
             LEFT JOIN municipalities m ON m.id = e.municipality_id
             WHERE e.slug = :slug
               AND e.deleted_at IS NULL
             LIMIT 1'
        );
        $statement->execute(['slug' => $slug]);
        $entity = $statement->fetch(PDO::FETCH_ASSOC);

        if ($entity === false) {
            return null;
        }

        $entityId = (int) $entity['id'];

        $entity['modalities'] = $this->modalitiesFor($pdo, $entityId);
        $entity['contacts'] = $this->contactsFor($pdo, $entityId);
        $entity['social_links'] = $this->socialLinksFor($pdo, $entityId);
        $entity['facilities'] = $this->facilitiesFor($pdo, $entityId);
        $entity['age_ranges'] = $this->ageRangesFor($pdo, $entityId);

        return $entity;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function modalitiesFor(PDO $pdo, int $entityId): array
    {
        $statement = $pdo->prepare(
            'SELECT mo.id, mo.name, mo.slug, mo.icon_path
             FROM entity_modalities em
             INNER JOIN modalities mo ON mo.id = em.modality_id
             WHERE em.entity_id = :id
             ORDER BY em.sort_order ASC, mo.name ASC'
        );
        $statement->execute(['id' => $entityId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function contactsFor(PDO $pdo, int $entityId): array
    {
        $statement = $pdo->prepare(
            'SELECT contact_type, label, person_name, role_title, phone, email, value, is_primary
             FROM entity_contacts
             WHERE entity_id = :id
             ORDER BY is_primary DESC, sort_order ASC, id ASC'
        );
        $statement->execute(['id' => $entityId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function socialLinksFor(PDO $pdo, int $entityId): array
    {
        $statement = $pdo->prepare(
            'SELECT platform, label, url
             FROM entity_social_links
             WHERE entity_id = :id
             ORDER BY sort_order ASC, id ASC'
        );
        $statement->execute(['id' => $entityId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function facilitiesFor(PDO $pdo, int $entityId): array
    {
        $statement = $pdo->prepare(
            'SELECT f.id, f.name, f.slug, f.address, f.locality, f.google_maps_url,
                    f.latitude, f.longitude, ef.label
             FROM entity_facilities ef
             INNER JOIN facilities f ON f.id = ef.facility_id
             WHERE ef.entity_id = :id
               AND f.deleted_at IS NULL
             ORDER BY ef.sort_order ASC, f.name ASC'
        );
        $statement->execute(['id' => $entityId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function ageRangesFor(PDO $pdo, int $entityId): array
    {
        $statement = $pdo->prepare(
            'SELECT age_range_key, label, practitioners_count, raw_value
             FROM entity_age_ranges
             WHERE entity_id = :id
             ORDER BY sort_order ASC, id ASC'
        );
        $statement->execute(['id' => $entityId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
