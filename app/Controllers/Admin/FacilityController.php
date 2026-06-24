<?php

declare(strict_types=1);

namespace JuegosTenerife\Controllers\Admin;

use JuegosTenerife\Core\Csrf;
use JuegosTenerife\Core\Database;
use JuegosTenerife\Core\Response;
use JuegosTenerife\Core\View;
use JuegosTenerife\Models\MunicipalityRepository;
use JuegosTenerife\Services\Support\Slugger;
use PDO;
use RuntimeException;

final class FacilityController extends AdminController
{
    public function index(): Response
    {
        $redirect = $this->requireAdmin();
        if ($redirect !== null) {
            return $redirect;
        }

        return View::render('admin/facilities/index', [
            'title' => 'Instalaciones',
            'activeNav' => 'facilities',
            'user' => $this->user(),
            'csrf' => Csrf::token(),
            'flash' => $this->consumeFlash(),
            'facilities' => $this->facilities(),
        ]);
    }

    public function create(): Response
    {
        $redirect = $this->requireAdmin();
        if ($redirect !== null) {
            return $redirect;
        }

        return $this->renderForm($this->blankFacility(), [], 'Crear instalación', '/admin/facilities');
    }

    public function store(): Response
    {
        $redirect = $this->requireAdmin();
        if ($redirect !== null) {
            return $redirect;
        }

        $facility = $this->facilityFromPost();
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            return $this->renderForm($facility, ['La sesión ha caducado. Vuelve a intentarlo.'], 'Crear instalación', '/admin/facilities');
        }

        $errors = $this->validateFacility($facility, null);
        if ($errors !== []) {
            return $this->renderForm($facility, $errors, 'Crear instalación', '/admin/facilities');
        }

        try {
            $id = $this->saveFacility($facility, null);
        } catch (RuntimeException $exception) {
            return $this->renderForm($facility, [$exception->getMessage()], 'Crear instalación', '/admin/facilities');
        }

        $this->flash('success', 'Instalación creada correctamente.');

        return Response::redirect('/admin/facilities/' . $id . '/edit');
    }

    public function edit(string $id): Response
    {
        $redirect = $this->requireAdmin();
        if ($redirect !== null) {
            return $redirect;
        }

        $facility = $this->findFacility((int) $id);
        if ($facility === null) {
            return new Response('Instalación no encontrada', 404);
        }

        return $this->renderForm($facility, [], 'Editar instalación', '/admin/facilities/' . (int) $id);
    }

    public function update(string $id): Response
    {
        $redirect = $this->requireAdmin();
        if ($redirect !== null) {
            return $redirect;
        }

        $facilityId = (int) $id;
        $existing = $this->findFacility($facilityId);
        if ($existing === null) {
            return new Response('Instalación no encontrada', 404);
        }

        $facility = array_merge($existing, $this->facilityFromPost());
        $facility['id'] = $facilityId;
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            return $this->renderForm($facility, ['La sesión ha caducado. Vuelve a intentarlo.'], 'Editar instalación', '/admin/facilities/' . $facilityId);
        }

        $errors = $this->validateFacility($facility, $facilityId);
        if ($errors !== []) {
            return $this->renderForm($facility, $errors, 'Editar instalación', '/admin/facilities/' . $facilityId);
        }

        try {
            $this->saveFacility($facility, $facilityId);
        } catch (RuntimeException $exception) {
            return $this->renderForm($facility, [$exception->getMessage()], 'Editar instalación', '/admin/facilities/' . $facilityId);
        }

        $this->flash('success', 'Instalación guardada correctamente.');

        return Response::redirect('/admin/facilities/' . $facilityId . '/edit');
    }

    /**
     * @param array<string, mixed> $facility
     * @param list<string> $errors
     */
    private function renderForm(array $facility, array $errors, string $title, string $action): Response
    {
        return View::render('admin/facilities/form', [
            'title' => $title,
            'activeNav' => 'facilities',
            'user' => $this->user(),
            'csrf' => Csrf::token(),
            'flash' => $this->consumeFlash(),
            'facility' => $facility,
            'errors' => $errors,
            'action' => $action,
            'municipalities' => (new MunicipalityRepository())->allForAdmin(),
            'entities' => $this->allEntitiesForAdmin(),
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function facilities(): array
    {
        $statement = Database::connection()->query(
            "SELECT f.id, f.name, f.slug, f.locality, f.latitude, f.longitude, f.geocoding_status,
                    m.name AS municipality,
                    COUNT(ef.entity_id) AS entities_count,
                    GROUP_CONCAT(e.name ORDER BY e.name SEPARATOR ', ') AS entities
             FROM facilities f
             LEFT JOIN municipalities m ON m.id = f.municipality_id
             LEFT JOIN entity_facilities ef ON ef.facility_id = f.id
             LEFT JOIN entities e ON e.id = ef.entity_id AND e.deleted_at IS NULL
             WHERE f.deleted_at IS NULL
             GROUP BY f.id, f.name, f.slug, f.locality, f.latitude, f.longitude, f.geocoding_status, m.name
             ORDER BY f.updated_at DESC, f.name ASC
             LIMIT 300"
        );

        return $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findFacility(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT *
             FROM facilities
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $facility = $statement->fetch(PDO::FETCH_ASSOC);

        if (!is_array($facility)) {
            return null;
        }

        $facility['entity_ids'] = $this->entityIdsForFacility($id);

        return array_merge($this->blankFacility(), $facility);
    }

    /**
     * @return array<string, mixed>
     */
    private function blankFacility(): array
    {
        return [
            'id' => null,
            'municipality_id' => null,
            'name' => '',
            'slug' => '',
            'address' => '',
            'locality' => '',
            'postal_code' => '',
            'google_maps_url' => '',
            'latitude' => '',
            'longitude' => '',
            'geocoding_status' => 'pending',
            'notes' => '',
            'entity_ids' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function facilityFromPost(): array
    {
        $facility = $this->blankFacility();
        foreach (array_keys($facility) as $field) {
            if ($field === 'id' || $field === 'entity_ids') {
                continue;
            }
            $facility[$field] = $_POST[$field] ?? '';
        }
        $facility['slug'] = Slugger::slug((string) ($facility['slug'] ?: $facility['name']));
        $facility['entity_ids'] = array_values(array_filter(array_map('intval', (array) ($_POST['entity_ids'] ?? []))));

        return $facility;
    }

    /**
     * @param array<string, mixed> $facility
     * @return list<string>
     */
    private function validateFacility(array $facility, ?int $facilityId): array
    {
        $errors = [];
        $name = trim((string) ($facility['name'] ?? ''));
        $slug = Slugger::slug((string) ($facility['slug'] ?: $name));

        if ($name === '') {
            $errors[] = 'El nombre de la instalación es obligatorio.';
        }

        if ($this->slugExists($slug, $facilityId)) {
            $errors[] = 'Ya existe una instalación con ese slug.';
        }

        foreach (['latitude' => 'latitud', 'longitude' => 'longitud'] as $field => $label) {
            $value = trim((string) ($facility[$field] ?? ''));
            if ($value !== '' && !is_numeric(str_replace(',', '.', $value))) {
                $errors[] = 'La ' . $label . ' debe ser un número decimal.';
            }
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $facility
     */
    private function saveFacility(array $facility, ?int $facilityId): int
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $data = $this->normalizeFacilityForStorage($facility);
            if ($facilityId === null) {
                $statement = $pdo->prepare(
                    'INSERT INTO facilities (municipality_id, name, slug, address, locality, postal_code, google_maps_url, latitude, longitude, geocoding_status, notes)
                     VALUES (:municipality_id, :name, :slug, :address, :locality, :postal_code, :google_maps_url, :latitude, :longitude, :geocoding_status, :notes)'
                );
                $statement->execute($data);
                $facilityId = (int) $pdo->lastInsertId();
            } else {
                $data['id'] = $facilityId;
                $statement = $pdo->prepare(
                    'UPDATE facilities
                     SET municipality_id = :municipality_id,
                         name = :name,
                         slug = :slug,
                         address = :address,
                         locality = :locality,
                         postal_code = :postal_code,
                         google_maps_url = :google_maps_url,
                         latitude = :latitude,
                         longitude = :longitude,
                         geocoding_status = :geocoding_status,
                         notes = :notes
                     WHERE id = :id
                       AND deleted_at IS NULL'
                );
                $statement->execute($data);
            }

            $this->replaceEntityLinks($pdo, $facilityId, (array) ($facility['entity_ids'] ?? []));
            $pdo->commit();

            return $facilityId;
        } catch (\Throwable $throwable) {
            $pdo->rollBack();
            throw new RuntimeException('No se pudo guardar la instalación: ' . $throwable->getMessage(), 0, $throwable);
        }
    }

    /**
     * @param array<string, mixed> $facility
     * @return array<string, mixed>
     */
    private function normalizeFacilityForStorage(array $facility): array
    {
        return [
            'municipality_id' => $this->intOrNull($facility['municipality_id'] ?? null),
            'name' => trim((string) $facility['name']),
            'slug' => Slugger::slug((string) ($facility['slug'] ?: $facility['name'])),
            'address' => $this->nullable($facility['address'] ?? null),
            'locality' => $this->nullable($facility['locality'] ?? null),
            'postal_code' => $this->nullable($facility['postal_code'] ?? null),
            'google_maps_url' => $this->nullable($facility['google_maps_url'] ?? null),
            'latitude' => $this->decimalOrNull($facility['latitude'] ?? null),
            'longitude' => $this->decimalOrNull($facility['longitude'] ?? null),
            'geocoding_status' => $this->nullable($facility['geocoding_status'] ?? null) ?? 'pending',
            'notes' => $this->nullable($facility['notes'] ?? null),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function allEntitiesForAdmin(): array
    {
        $statement = Database::connection()->query(
            'SELECT e.id, e.name, e.slug, m.name AS municipality
             FROM entities e
             LEFT JOIN municipalities m ON m.id = e.municipality_id
             WHERE e.deleted_at IS NULL
             ORDER BY e.name ASC'
        );

        return $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * @return list<int>
     */
    private function entityIdsForFacility(int $facilityId): array
    {
        $statement = Database::connection()->prepare('SELECT entity_id FROM entity_facilities WHERE facility_id = :id');
        $statement->execute(['id' => $facilityId]);

        return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    /**
     * @param list<int> $entityIds
     */
    private function replaceEntityLinks(PDO $pdo, int $facilityId, array $entityIds): void
    {
        $pdo->prepare('DELETE FROM entity_facilities WHERE facility_id = :id')->execute(['id' => $facilityId]);
        $insert = $pdo->prepare('INSERT INTO entity_facilities (entity_id, facility_id, label, sort_order) VALUES (:entity_id, :facility_id, :label, :sort_order)');
        foreach (array_values(array_unique(array_map('intval', $entityIds))) as $index => $entityId) {
            if ($entityId <= 0) {
                continue;
            }
            $insert->execute([
                'entity_id' => $entityId,
                'facility_id' => $facilityId,
                'label' => 'Instalación',
                'sort_order' => ($index + 1) * 10,
            ]);
        }
    }

    private function slugExists(string $slug, ?int $facilityId): bool
    {
        $statement = Database::connection()->prepare(
            'SELECT COUNT(*)
             FROM facilities
             WHERE slug = :slug
               AND deleted_at IS NULL
               AND (:ignore_current = 1 OR id <> :facility_id)'
        );
        $statement->execute([
            'slug' => $slug,
            'ignore_current' => $facilityId === null ? 1 : 0,
            'facility_id' => $facilityId ?? 0,
        ]);

        return (int) $statement->fetchColumn() > 0;
    }

    private function nullable(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function intOrNull(mixed $value): ?int
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' || !ctype_digit($value) ? null : (int) $value;
    }

    private function decimalOrNull(mixed $value): ?string
    {
        $value = trim(str_replace(',', '.', (string) ($value ?? '')));

        return $value === '' || !is_numeric($value) ? null : $value;
    }
}
