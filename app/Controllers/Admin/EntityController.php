<?php

declare(strict_types=1);

namespace JuegosTenerife\Controllers\Admin;

use JuegosTenerife\Core\Csrf;
use JuegosTenerife\Core\Database;
use JuegosTenerife\Core\Response;
use JuegosTenerife\Core\View;
use JuegosTenerife\Models\EntityTypeRepository;
use JuegosTenerife\Models\ModalityRepository;
use JuegosTenerife\Models\MunicipalityRepository;
use JuegosTenerife\Services\MediaUploadService;
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
            $this->syncEntityMedia($id);
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
            $this->syncEntityMedia($entityId);
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
            'modalities' => (new ModalityRepository())->all(),
            'mediaFiles' => !empty($entity['id']) ? $this->mediaFilesForEntity((int) $entity['id']) : [],
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

        if (!is_array($entity)) {
            return null;
        }

        return $this->withRelations($entity);
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
            'modality_ids' => [],
            'phone_1' => '',
            'phone_2' => '',
            'email_1' => '',
            'email_2' => '',
            'contact_person' => '',
            'contact_role' => '',
            'contact_phone' => '',
            'contact_email' => '',
            'facebook_url' => '',
            'instagram_url' => '',
            'youtube_url' => '',
            'x_url' => '',
            'tiktok_url' => '',
            'age_ranges' => $this->blankAgeRanges(),
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
        $entity['modality_ids'] = array_values(array_filter(array_map('intval', (array) ($_POST['modality_ids'] ?? []))));
        $entity['age_ranges'] = [];
        foreach ($this->ageRangeLabels() as $key => $label) {
            $entity['age_ranges'][$key] = [
                'label' => $label,
                'raw_value' => trim((string) ($_POST['age_ranges'][$key] ?? '')),
            ];
        }

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

            $this->replaceEntityRelations($pdo, $entityId, $entity);
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
    private function withRelations(array $entity): array
    {
        $pdo = Database::connection();
        $entityId = (int) $entity['id'];

        $statement = $pdo->prepare('SELECT modality_id FROM entity_modalities WHERE entity_id = :id ORDER BY sort_order ASC');
        $statement->execute(['id' => $entityId]);
        $entity['modality_ids'] = array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));

        $statement = $pdo->prepare(
            'SELECT contact_type, label, person_name, role_title, phone, email, value
             FROM entity_contacts
             WHERE entity_id = :id
             ORDER BY sort_order ASC, id ASC'
        );
        $statement->execute(['id' => $entityId]);
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $contact) {
            $label = (string) ($contact['label'] ?? '');
            if ($contact['contact_type'] === 'phone' && $label === 'Teléfono1') {
                $entity['phone_1'] = $contact['phone'] ?? $contact['value'] ?? '';
            }
            if ($contact['contact_type'] === 'phone' && $label === 'Teléfono2') {
                $entity['phone_2'] = $contact['phone'] ?? $contact['value'] ?? '';
            }
            if ($contact['contact_type'] === 'email' && $label === 'Email1') {
                $entity['email_1'] = $contact['email'] ?? $contact['value'] ?? '';
            }
            if ($contact['contact_type'] === 'email' && $label === 'Email2') {
                $entity['email_2'] = $contact['email'] ?? $contact['value'] ?? '';
            }
            if ($contact['contact_type'] === 'person') {
                $entity['contact_person'] = $contact['person_name'] ?? '';
                $entity['contact_role'] = $contact['role_title'] ?? '';
                $entity['contact_phone'] = $contact['phone'] ?? '';
                $entity['contact_email'] = $contact['email'] ?? '';
            }
        }

        $statement = $pdo->prepare('SELECT platform, url FROM entity_social_links WHERE entity_id = :id');
        $statement->execute(['id' => $entityId]);
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $link) {
            $entity[(string) $link['platform'] . '_url'] = $link['url'] ?? '';
        }

        $statement = $pdo->prepare(
            'SELECT age_range_key, label, raw_value
             FROM entity_age_ranges
             WHERE entity_id = :id
             ORDER BY sort_order ASC, id ASC'
        );
        $statement->execute(['id' => $entityId]);
        $ageRanges = $this->blankAgeRanges();
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $range) {
            $key = (string) $range['age_range_key'];
            if (!isset($ageRanges[$key])) {
                continue;
            }
            $ageRanges[$key]['raw_value'] = (string) ($range['raw_value'] ?? '');
        }
        $entity['age_ranges'] = $ageRanges;

        return array_merge($this->blankEntity(), $entity);
    }

    /**
     * @param array<string, mixed> $entity
     */
    private function replaceEntityRelations(PDO $pdo, int $entityId, array $entity): void
    {
        $pdo->prepare('DELETE FROM entity_modalities WHERE entity_id = :id')->execute(['id' => $entityId]);
        $insertModality = $pdo->prepare('INSERT INTO entity_modalities (entity_id, modality_id, sort_order) VALUES (:entity_id, :modality_id, :sort_order)');
        foreach (array_values(array_unique(array_map('intval', (array) ($entity['modality_ids'] ?? [])))) as $index => $modalityId) {
            if ($modalityId <= 0) {
                continue;
            }
            $insertModality->execute([
                'entity_id' => $entityId,
                'modality_id' => $modalityId,
                'sort_order' => ($index + 1) * 10,
            ]);
        }

        $pdo->prepare('DELETE FROM entity_contacts WHERE entity_id = :id')->execute(['id' => $entityId]);
        $insertContact = $pdo->prepare(
            'INSERT INTO entity_contacts (entity_id, contact_type, label, person_name, role_title, phone, email, value, is_primary, sort_order)
             VALUES (:entity_id, :contact_type, :label, :person_name, :role_title, :phone, :email, :value, :is_primary, :sort_order)'
        );
        $contactRows = [
            ['phone', 'Teléfono1', null, null, $this->nullable($entity['phone_1'] ?? null), null, $this->nullable($entity['phone_1'] ?? null), 1, 10],
            ['phone', 'Teléfono2', null, null, $this->nullable($entity['phone_2'] ?? null), null, $this->nullable($entity['phone_2'] ?? null), 0, 20],
            ['email', 'Email1', null, null, null, $this->nullable($entity['email_1'] ?? null), $this->nullable($entity['email_1'] ?? null), 1, 30],
            ['email', 'Email2', null, null, null, $this->nullable($entity['email_2'] ?? null), $this->nullable($entity['email_2'] ?? null), 0, 40],
        ];
        foreach ($contactRows as [$type, $label, $person, $role, $phone, $email, $value, $primary, $sort]) {
            if ($value === null) {
                continue;
            }
            $insertContact->execute(['entity_id' => $entityId, 'contact_type' => $type, 'label' => $label, 'person_name' => $person, 'role_title' => $role, 'phone' => $phone, 'email' => $email, 'value' => $value, 'is_primary' => $primary, 'sort_order' => $sort]);
        }

        if ($this->nullable($entity['contact_person'] ?? null) !== null) {
            $insertContact->execute([
                'entity_id' => $entityId,
                'contact_type' => 'person',
                'label' => 'Persona de contacto',
                'person_name' => $this->nullable($entity['contact_person'] ?? null),
                'role_title' => $this->nullable($entity['contact_role'] ?? null),
                'phone' => $this->nullable($entity['contact_phone'] ?? null),
                'email' => $this->nullable($entity['contact_email'] ?? null),
                'value' => $this->nullable($entity['contact_person'] ?? null),
                'is_primary' => 1,
                'sort_order' => 50,
            ]);
        }

        $pdo->prepare('DELETE FROM entity_social_links WHERE entity_id = :id')->execute(['id' => $entityId]);
        $insertSocial = $pdo->prepare('INSERT INTO entity_social_links (entity_id, platform, label, url, sort_order) VALUES (:entity_id, :platform, :label, :url, :sort_order)');
        $socials = [
            'facebook' => ['Facebook', 'facebook_url'],
            'instagram' => ['Instagram', 'instagram_url'],
            'youtube' => ['YouTube', 'youtube_url'],
            'x' => ['X', 'x_url'],
            'tiktok' => ['TikTok', 'tiktok_url'],
        ];
        $sort = 10;
        foreach ($socials as $platform => [$label, $field]) {
            $url = $this->nullable($entity[$field] ?? null);
            if ($url === null) {
                continue;
            }
            $insertSocial->execute(['entity_id' => $entityId, 'platform' => $platform, 'label' => $label, 'url' => $url, 'sort_order' => $sort]);
            $sort += 10;
        }

        $pdo->prepare('DELETE FROM entity_age_ranges WHERE entity_id = :id')->execute(['id' => $entityId]);
        $insertAgeRange = $pdo->prepare(
            'INSERT INTO entity_age_ranges (entity_id, age_range_key, label, practitioners_count, raw_value, sort_order)
             VALUES (:entity_id, :age_range_key, :label, :practitioners_count, :raw_value, :sort_order)'
        );
        $sort = 10;
        foreach ($this->ageRangeLabels() as $key => $label) {
            $rawValue = trim((string) (($entity['age_ranges'][$key]['raw_value'] ?? $entity['age_ranges'][$key] ?? '')));
            if ($rawValue === '') {
                continue;
            }
            $insertAgeRange->execute([
                'entity_id' => $entityId,
                'age_range_key' => $key,
                'label' => $label,
                'practitioners_count' => $this->intFromText($rawValue),
                'raw_value' => $rawValue,
                'sort_order' => $sort,
            ]);
            $sort += 10;
        }
    }

    /**
     * @return array<string, string>
     */
    private function ageRangeLabels(): array
    {
        return [
            'age_0_5' => 'Edades: De 0 a 5 años',
            'age_6_11' => 'Edades: De 6 a 11 años',
            'age_12_17' => 'Edades: De 12 a 17 años',
            'age_18_29' => 'Edades: De 18 a 29 años',
            'age_30_45' => 'Edades: De 30 a 45 años',
            'age_46_59' => 'Edades: De 46 a 59 años',
            'age_60_plus' => 'Edades: 60 años y más',
        ];
    }

    /**
     * @return array<string, array{label: string, raw_value: string}>
     */
    private function blankAgeRanges(): array
    {
        $ranges = [];
        foreach ($this->ageRangeLabels() as $key => $label) {
            $ranges[$key] = [
                'label' => $label,
                'raw_value' => '',
            ];
        }

        return $ranges;
    }

    private function intFromText(string $value): ?int
    {
        if (preg_match('/\d+/', $value, $matches) !== 1) {
            return null;
        }

        return (int) $matches[0];
    }

    private function syncEntityMedia(int $entityId): void
    {
        $this->updateExistingMedia($entityId);
        $this->storeLogoUpload($entityId);
        $this->storeGalleryUploads($entityId);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mediaFilesForEntity(int $entityId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, media_type, original_name, file_path, mime_type, file_size, alt_text, caption, sort_order
             FROM media_files
             WHERE owner_type = :owner_type
               AND owner_id = :owner_id
             ORDER BY media_type = \'entity_featured_photo\' DESC, sort_order ASC, id ASC'
        );
        $statement->execute([
            'owner_type' => 'entity',
            'owner_id' => $entityId,
        ]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function updateExistingMedia(int $entityId): void
    {
        $mediaInput = is_array($_POST['media'] ?? null) ? $_POST['media'] : [];
        $deleteIds = array_map('intval', (array) ($_POST['delete_media_ids'] ?? []));
        $featuredId = (int) ($_POST['featured_media_id'] ?? 0);

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            foreach ($deleteIds as $deleteId) {
                $statement = $pdo->prepare('DELETE FROM media_files WHERE id = :id AND owner_type = \'entity\' AND owner_id = :owner_id');
                $statement->execute(['id' => $deleteId, 'owner_id' => $entityId]);
            }

            if ($mediaInput !== []) {
                $statement = $pdo->prepare(
                    'UPDATE media_files
                     SET alt_text = :alt_text,
                         caption = :caption,
                         sort_order = :sort_order
                     WHERE id = :id
                       AND owner_type = \'entity\'
                       AND owner_id = :owner_id'
                );
                foreach ($mediaInput as $id => $data) {
                    if (!is_array($data)) {
                        continue;
                    }
                    $statement->execute([
                        'alt_text' => $this->nullable($data['alt_text'] ?? null),
                        'caption' => $this->nullable($data['caption'] ?? null),
                        'sort_order' => $this->intOrNull($data['sort_order'] ?? null) ?? 100,
                        'id' => (int) $id,
                        'owner_id' => $entityId,
                    ]);
                }
            }

            $pdo->prepare('UPDATE media_files SET media_type = \'entity_photo\' WHERE owner_type = \'entity\' AND owner_id = :owner_id AND media_type = \'entity_featured_photo\'')->execute(['owner_id' => $entityId]);
            if ($featuredId > 0 && !in_array($featuredId, $deleteIds, true)) {
                $statement = $pdo->prepare('UPDATE media_files SET media_type = \'entity_featured_photo\' WHERE id = :id AND owner_type = \'entity\' AND owner_id = :owner_id');
                $statement->execute(['id' => $featuredId, 'owner_id' => $entityId]);
            }

            $pdo->commit();
        } catch (\Throwable $throwable) {
            $pdo->rollBack();
            throw new RuntimeException('No se pudieron guardar las fotos existentes: ' . $throwable->getMessage(), 0, $throwable);
        }
    }

    private function storeLogoUpload(int $entityId): void
    {
        if (!isset($_FILES['logo']) || !is_array($_FILES['logo']) || (int) ($_FILES['logo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return;
        }

        $uploaded = (new MediaUploadService())->storeImage($_FILES['logo'], 'entities/' . $entityId);
        $statement = Database::connection()->prepare('UPDATE entities SET logo_path = :logo_path WHERE id = :id');
        $statement->execute([
            'logo_path' => $uploaded['file_path'],
            'id' => $entityId,
        ]);
    }

    private function storeGalleryUploads(int $entityId): void
    {
        if (!isset($_FILES['gallery_images']) || !is_array($_FILES['gallery_images'])) {
            return;
        }

        $files = $this->normalizeMultipleUpload($_FILES['gallery_images']);
        if ($files === []) {
            return;
        }

        $upload = new MediaUploadService();
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'INSERT INTO media_files (owner_type, owner_id, media_type, original_name, file_path, mime_type, file_size, alt_text, caption, sort_order)
             VALUES (:owner_type, :owner_id, :media_type, :original_name, :file_path, :mime_type, :file_size, :alt_text, :caption, :sort_order)'
        );

        foreach ($files as $file) {
            if ((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $uploaded = $upload->storeImage($file, 'entities/' . $entityId);
            $statement->execute([
                'owner_type' => 'entity',
                'owner_id' => $entityId,
                'media_type' => 'entity_photo',
                'original_name' => $uploaded['original_name'],
                'file_path' => $uploaded['file_path'],
                'mime_type' => $uploaded['mime_type'],
                'file_size' => $uploaded['file_size'],
                'alt_text' => null,
                'caption' => null,
                'sort_order' => 100,
            ]);
        }
    }

    /**
     * @param array<string, mixed> $files
     * @return list<array<string, mixed>>
     */
    private function normalizeMultipleUpload(array $files): array
    {
        $names = (array) ($files['name'] ?? []);
        $normalized = [];

        foreach ($names as $index => $name) {
            $normalized[] = [
                'name' => $name,
                'type' => $files['type'][$index] ?? null,
                'tmp_name' => $files['tmp_name'][$index] ?? null,
                'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                'size' => $files['size'][$index] ?? 0,
            ];
        }

        return $normalized;
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
