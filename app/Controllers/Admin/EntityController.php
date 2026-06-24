<?php

declare(strict_types=1);

namespace JuegosTenerife\Controllers\Admin;

use JuegosTenerife\Core\Csrf;
use JuegosTenerife\Core\Database;
use JuegosTenerife\Core\Response;
use JuegosTenerife\Core\View;
use JuegosTenerife\Models\EntityTypeRepository;
use JuegosTenerife\Models\MunicipalityRepository;
use JuegosTenerife\Services\Support\Slugger;
use PDO;
use RuntimeException;

final class EntityController extends AdminController
{
    public function index(): Response
    {
        $redirect = $this->requireAdmin();
        if ($redirect !== null) {
            return $redirect;
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
            'activeNav' => 'entities',
            'user' => $this->user(),
            'csrf' => Csrf::token(),
            'flash' => $this->consumeFlash(),
            'entities' => $entities,
            'error' => $error,
        ]);
    }

    public function create(): Response
    {
        $redirect = $this->requireAdmin();
        if ($redirect !== null) {
            return $redirect;
        }

        return $this->renderForm($this->blankEntity(), [], 'Crear entidad', '/admin/entities');
    }

    public function store(): Response
    {
        $redirect = $this->requireAdmin();
        if ($redirect !== null) {
            return $redirect;
        }

        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            return $this->renderForm($this->entityFromPost(), ['La sesión ha caducado. Vuelve a intentarlo.'], 'Crear entidad', '/admin/entities');
        }

        $entity = $this->entityFromPost();
        $errors = $this->validateEntity($entity, null);

        if ($errors !== []) {
            return $this->renderForm($entity, $errors, 'Crear entidad', '/admin/entities');
        }

        try {
            $id = $this->saveEntity($entity, null);
        } catch (RuntimeException $exception) {
            return $this->renderForm($entity, [$exception->getMessage()], 'Crear entidad', '/admin/entities');
        }

        $this->flash('success', 'Entidad creada correctamente.');

        return Response::redirect('/admin/entities/' . $id . '/edit');
    }

    public function edit(string $id): Response
    {
        $redirect = $this->requireAdmin();
        if ($redirect !== null) {
            return $redirect;
        }

        $entity = $this->findEntity((int) $id);
        if ($entity === null) {
            return new Response('Entidad no encontrada', 404);
        }

        return $this->renderForm($entity, [], 'Editar entidad', '/admin/entities/' . (int) $id);
    }

    public function update(string $id): Response
    {
        $redirect = $this->requireAdmin();
        if ($redirect !== null) {
            return $redirect;
        }

        $entityId = (int) $id;
        $existing = $this->findEntity($entityId);
        if ($existing === null) {
            return new Response('Entidad no encontrada', 404);
        }

        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            return $this->renderForm(array_merge($existing, $this->entityFromPost()), ['La sesión ha caducado. Vuelve a intentarlo.'], 'Editar entidad', '/admin/entities/' . $entityId);
        }

        $entity = array_merge($existing, $this->entityFromPost());
        $entity['id'] = $entityId;
        $errors = $this->validateEntity($entity, $entityId);

        if ($errors !== []) {
            return $this->renderForm($entity, $errors, 'Editar entidad', '/admin/entities/' . $entityId);
        }

        try {
            $this->saveEntity($entity, $entityId);
        } catch (RuntimeException $exception) {
            return $this->renderForm($entity, [$exception->getMessage()], 'Editar entidad', '/admin/entities/' . $entityId);
        }

        $this->flash('success', 'Cambios guardados correctamente.');

        return Response::redirect('/admin/entities/' . $entityId . '/edit');
    }

    /**
     * @param array<string, mixed> $entity
     * @param list<string> $errors
     */
    private function renderForm(array $entity, array $errors, string $title, string $action): Response
    {
        return View::render('admin/entities/form', [
            'title' => $title,
            'activeNav' => 'entities',
            'user' => $this->user(),
            'csrf' => Csrf::token(),
            'flash' => $this->consumeFlash(),
            'entity' => $entity,
            'errors' => $errors,
            'action' => $action,
            'entityTypes' => (new EntityTypeRepository())->all(),
            'municipalities' => (new MunicipalityRepository())->allForAdmin(),
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

    /**
     * @return array<string, mixed>|null
     */
    private function findEntity(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT *
             FROM entities
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $entity = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($entity) ? $entity : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function blankEntity(): array
    {
        return [
            'id' => null,
            'entity_type_id' => null,
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
            'website_url' => '',
            'history' => '',
            'corporate_principles' => '',
            'sports_values' => '',
            'total_teams' => '',
            'teams_by_gender' => '',
            'teams_by_age' => '',
            'total_practitioners' => '',
            'female_practitioners' => '',
            'male_practitioners' => '',
            'training_practices' => '',
            'training_days' => '',
            'training_hours' => '',
            'has_board' => '',
            'board_members' => '',
            'board_male' => '',
            'board_female' => '',
            'holds_annual_assemblies' => '',
            'has_members' => '',
            'total_members' => '',
            'male_members' => '',
            'female_members' => '',
            'equality_protocol_status' => '',
            'violence_protocol_status' => '',
            'lopivi_status' => '',
            'joined_educar_entrenando' => '',
            'supports_educational_needs' => '',
            'supports_disability' => '',
            'is_published' => 1,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function entityFromPost(): array
    {
        $fields = array_keys($this->blankEntity());
        $entity = [];

        foreach ($fields as $field) {
            if ($field === 'id') {
                continue;
            }

            $entity[$field] = $_POST[$field] ?? '';
        }

        $entity['slug'] = Slugger::slug((string) ($entity['slug'] ?: $entity['name']));
        $entity['is_published'] = isset($_POST['is_published']) ? 1 : 0;

        return $entity;
    }

    /**
     * @param array<string, mixed> $entity
     * @return list<string>
     */
    private function validateEntity(array $entity, ?int $entityId): array
    {
        $errors = [];
        $name = trim((string) ($entity['name'] ?? ''));
        $slug = Slugger::slug((string) ($entity['slug'] ?? $name));

        if ($name === '') {
            $errors[] = 'El nombre de la entidad es obligatorio.';
        }

        if ($this->slugExists($slug, $entityId)) {
            $errors[] = 'Ya existe una entidad con ese slug.';
        }

        foreach (['latitude' => 'latitud', 'longitude' => 'longitud'] as $field => $label) {
            $value = trim((string) ($entity[$field] ?? ''));
            if ($value !== '' && !is_numeric(str_replace(',', '.', $value))) {
                $errors[] = 'La ' . $label . ' debe ser un número decimal.';
            }
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $entity
     */
    private function saveEntity(array $entity, ?int $entityId): int
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $data = $this->normalizeEntityForStorage($entity);

            if ($entityId === null) {
                $statement = $pdo->prepare(
                    'INSERT INTO entities (
                        entity_type_id, municipality_id, name, slug, address, locality, postal_code,
                        google_maps_url, latitude, longitude, geocoding_status, website_url, history,
                        corporate_principles, sports_values, total_teams, teams_by_gender, teams_by_age,
                        total_practitioners, female_practitioners, male_practitioners, training_practices,
                        training_days, training_hours, has_board, board_members, board_male, board_female,
                        holds_annual_assemblies, has_members, total_members, male_members, female_members,
                        equality_protocol_status, violence_protocol_status, lopivi_status,
                        joined_educar_entrenando, supports_educational_needs, supports_disability,
                        source_reference, is_published
                    ) VALUES (
                        :entity_type_id, :municipality_id, :name, :slug, :address, :locality, :postal_code,
                        :google_maps_url, :latitude, :longitude, :geocoding_status, :website_url, :history,
                        :corporate_principles, :sports_values, :total_teams, :teams_by_gender, :teams_by_age,
                        :total_practitioners, :female_practitioners, :male_practitioners, :training_practices,
                        :training_days, :training_hours, :has_board, :board_members, :board_male, :board_female,
                        :holds_annual_assemblies, :has_members, :total_members, :male_members, :female_members,
                        :equality_protocol_status, :violence_protocol_status, :lopivi_status,
                        :joined_educar_entrenando, :supports_educational_needs, :supports_disability,
                        :source_reference, :is_published
                    )'
                );
                $statement->execute($data);
                $entityId = (int) $pdo->lastInsertId();
            } else {
                $data['id'] = $entityId;
                $statement = $pdo->prepare(
                    'UPDATE entities
                     SET entity_type_id = :entity_type_id,
                         municipality_id = :municipality_id,
                         name = :name,
                         slug = :slug,
                         address = :address,
                         locality = :locality,
                         postal_code = :postal_code,
                         google_maps_url = :google_maps_url,
                         latitude = :latitude,
                         longitude = :longitude,
                         geocoding_status = :geocoding_status,
                         website_url = :website_url,
                         history = :history,
                         corporate_principles = :corporate_principles,
                         sports_values = :sports_values,
                         total_teams = :total_teams,
                         teams_by_gender = :teams_by_gender,
                         teams_by_age = :teams_by_age,
                         total_practitioners = :total_practitioners,
                         female_practitioners = :female_practitioners,
                         male_practitioners = :male_practitioners,
                         training_practices = :training_practices,
                         training_days = :training_days,
                         training_hours = :training_hours,
                         has_board = :has_board,
                         board_members = :board_members,
                         board_male = :board_male,
                         board_female = :board_female,
                         holds_annual_assemblies = :holds_annual_assemblies,
                         has_members = :has_members,
                         total_members = :total_members,
                         male_members = :male_members,
                         female_members = :female_members,
                         equality_protocol_status = :equality_protocol_status,
                         violence_protocol_status = :violence_protocol_status,
                         lopivi_status = :lopivi_status,
                         joined_educar_entrenando = :joined_educar_entrenando,
                         supports_educational_needs = :supports_educational_needs,
                         supports_disability = :supports_disability,
                         source_reference = :source_reference,
                         is_published = :is_published
                     WHERE id = :id
                       AND deleted_at IS NULL'
                );
                $statement->execute($data);
            }

            $pdo->commit();

            return $entityId;
        } catch (\Throwable $throwable) {
            $pdo->rollBack();
            throw new RuntimeException('No se pudo guardar la entidad: ' . $throwable->getMessage(), 0, $throwable);
        }
    }

    /**
     * @param array<string, mixed> $entity
     * @return array<string, mixed>
     */
    private function normalizeEntityForStorage(array $entity): array
    {
        return [
            'entity_type_id' => $this->intOrNull($entity['entity_type_id'] ?? null),
            'municipality_id' => $this->intOrNull($entity['municipality_id'] ?? null),
            'name' => trim((string) $entity['name']),
            'slug' => Slugger::slug((string) ($entity['slug'] ?: $entity['name'])),
            'address' => $this->nullable($entity['address'] ?? null),
            'locality' => $this->nullable($entity['locality'] ?? null),
            'postal_code' => $this->nullable($entity['postal_code'] ?? null),
            'google_maps_url' => $this->nullable($entity['google_maps_url'] ?? null),
            'latitude' => $this->decimalOrNull($entity['latitude'] ?? null),
            'longitude' => $this->decimalOrNull($entity['longitude'] ?? null),
            'geocoding_status' => $this->nullable($entity['geocoding_status'] ?? null) ?? 'pending',
            'website_url' => $this->nullable($entity['website_url'] ?? null),
            'history' => $this->nullable($entity['history'] ?? null),
            'corporate_principles' => $this->nullable($entity['corporate_principles'] ?? null),
            'sports_values' => $this->nullable($entity['sports_values'] ?? null),
            'total_teams' => $this->intOrNull($entity['total_teams'] ?? null),
            'teams_by_gender' => $this->nullable($entity['teams_by_gender'] ?? null),
            'teams_by_age' => $this->nullable($entity['teams_by_age'] ?? null),
            'total_practitioners' => $this->intOrNull($entity['total_practitioners'] ?? null),
            'female_practitioners' => $this->intOrNull($entity['female_practitioners'] ?? null),
            'male_practitioners' => $this->intOrNull($entity['male_practitioners'] ?? null),
            'training_practices' => $this->nullable($entity['training_practices'] ?? null),
            'training_days' => $this->nullable($entity['training_days'] ?? null),
            'training_hours' => $this->nullable($entity['training_hours'] ?? null),
            'has_board' => $this->triState($entity['has_board'] ?? null),
            'board_members' => $this->intOrNull($entity['board_members'] ?? null),
            'board_male' => $this->intOrNull($entity['board_male'] ?? null),
            'board_female' => $this->intOrNull($entity['board_female'] ?? null),
            'holds_annual_assemblies' => $this->triState($entity['holds_annual_assemblies'] ?? null),
            'has_members' => $this->triState($entity['has_members'] ?? null),
            'total_members' => $this->nullable($entity['total_members'] ?? null),
            'male_members' => $this->nullable($entity['male_members'] ?? null),
            'female_members' => $this->nullable($entity['female_members'] ?? null),
            'equality_protocol_status' => $this->nullable($entity['equality_protocol_status'] ?? null),
            'violence_protocol_status' => $this->nullable($entity['violence_protocol_status'] ?? null),
            'lopivi_status' => $this->nullable($entity['lopivi_status'] ?? null),
            'joined_educar_entrenando' => $this->triState($entity['joined_educar_entrenando'] ?? null),
            'supports_educational_needs' => $this->triState($entity['supports_educational_needs'] ?? null),
            'supports_disability' => $this->triState($entity['supports_disability'] ?? null),
            'source_reference' => $this->nullable($entity['source_reference'] ?? null) ?? 'admin',
            'is_published' => (int) ($entity['is_published'] ?? 0) === 1 ? 1 : 0,
        ];
    }

    private function slugExists(string $slug, ?int $entityId): bool
    {
        $statement = Database::connection()->prepare(
            'SELECT COUNT(*)
             FROM entities
             WHERE slug = :slug
               AND deleted_at IS NULL
               AND (:ignore_current = 1 OR id <> :entity_id)'
        );
        $statement->execute([
            'slug' => $slug,
            'ignore_current' => $entityId === null ? 1 : 0,
            'entity_id' => $entityId ?? 0,
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

    private function triState(mixed $value): ?int
    {
        $value = trim((string) ($value ?? ''));
        if ($value === '') {
            return null;
        }

        return $value === '1' ? 1 : 0;
    }
}
