<?php

declare(strict_types=1);

namespace JuegosTenerife\Controllers\Admin;

use JuegosTenerife\Core\Csrf;
use JuegosTenerife\Core\Database;
use JuegosTenerife\Core\Response;
use JuegosTenerife\Core\View;
use JuegosTenerife\Services\MediaUploadService;
use JuegosTenerife\Services\Support\Slugger;
use PDO;
use RuntimeException;

final class ModalityController extends AdminController
{
    public function index(): Response
    {
        $redirect = $this->requireAdmin();
        if ($redirect !== null) {
            return $redirect;
        }

        return View::render('admin/modalities/index', [
            'title' => 'Modalidades',
            'activeNav' => 'modalities',
            'user' => $this->user(),
            'csrf' => Csrf::token(),
            'flash' => $this->consumeFlash(),
            'modalities' => $this->modalities(),
        ]);
    }

    public function create(): Response
    {
        $redirect = $this->requireAdmin();
        if ($redirect !== null) {
            return $redirect;
        }

        return $this->renderForm($this->blankModality(), [], 'Crear modalidad', '/admin/modalities');
    }

    public function store(): Response
    {
        $redirect = $this->requireAdmin();
        if ($redirect !== null) {
            return $redirect;
        }

        $modality = $this->modalityFromPost();
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            return $this->renderForm($modality, ['La sesión ha caducado. Vuelve a intentarlo.'], 'Crear modalidad', '/admin/modalities');
        }

        $errors = $this->validateModality($modality, null);
        if ($errors !== []) {
            return $this->renderForm($modality, $errors, 'Crear modalidad', '/admin/modalities');
        }

        try {
            $id = $this->saveModality($modality, null);
            $this->storeMainImageUpload($id);
        } catch (RuntimeException $exception) {
            return $this->renderForm($modality, [$exception->getMessage()], 'Crear modalidad', '/admin/modalities');
        }

        $this->flash('success', 'Modalidad creada correctamente.');

        return Response::redirect('/admin/modalities/' . $id . '/edit');
    }

    public function edit(string $id): Response
    {
        $redirect = $this->requireAdmin();
        if ($redirect !== null) {
            return $redirect;
        }

        $modality = $this->findModality((int) $id);
        if ($modality === null) {
            return new Response('Modalidad no encontrada', 404);
        }

        return $this->renderForm($modality, [], 'Editar modalidad', '/admin/modalities/' . (int) $id);
    }

    public function update(string $id): Response
    {
        $redirect = $this->requireAdmin();
        if ($redirect !== null) {
            return $redirect;
        }

        $modalityId = (int) $id;
        $existing = $this->findModality($modalityId);
        if ($existing === null) {
            return new Response('Modalidad no encontrada', 404);
        }

        $modality = array_merge($existing, $this->modalityFromPost());
        $modality['id'] = $modalityId;
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            return $this->renderForm($modality, ['La sesión ha caducado. Vuelve a intentarlo.'], 'Editar modalidad', '/admin/modalities/' . $modalityId);
        }

        $errors = $this->validateModality($modality, $modalityId);
        if ($errors !== []) {
            return $this->renderForm($modality, $errors, 'Editar modalidad', '/admin/modalities/' . $modalityId);
        }

        try {
            $this->saveModality($modality, $modalityId);
            $this->storeMainImageUpload($modalityId);
        } catch (RuntimeException $exception) {
            return $this->renderForm($modality, [$exception->getMessage()], 'Editar modalidad', '/admin/modalities/' . $modalityId);
        }

        $this->flash('success', 'Modalidad guardada correctamente.');

        return Response::redirect('/admin/modalities/' . $modalityId . '/edit');
    }

    /**
     * @param array<string, mixed> $modality
     * @param list<string> $errors
     */
    private function renderForm(array $modality, array $errors, string $title, string $action): Response
    {
        return View::render('admin/modalities/form', [
            'title' => $title,
            'activeNav' => 'modalities',
            'user' => $this->user(),
            'csrf' => Csrf::token(),
            'flash' => $this->consumeFlash(),
            'modality' => $modality,
            'errors' => $errors,
            'action' => $action,
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function modalities(): array
    {
        $statement = Database::connection()->query(
            'SELECT mo.*,
                    mf.file_path AS main_image_path
             FROM modalities mo
             LEFT JOIN media_files mf ON mf.owner_type = \'modality\'
                AND mf.owner_id = mo.id
                AND mf.media_type = \'modality_main_image\'
             ORDER BY mo.is_featured DESC, mo.sort_order ASC, mo.name ASC'
        );

        return $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findModality(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT mo.*,
                    mf.file_path AS main_image_path,
                    mf.id AS main_image_id,
                    mf.alt_text AS main_image_alt
             FROM modalities mo
             LEFT JOIN media_files mf ON mf.owner_type = \'modality\'
                AND mf.owner_id = mo.id
                AND mf.media_type = \'modality_main_image\'
             WHERE mo.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $modality = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($modality) ? array_merge($this->blankModality(), $modality) : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function blankModality(): array
    {
        return [
            'id' => null,
            'name' => '',
            'slug' => '',
            'short_description' => '',
            'full_description' => '',
            'extra_info' => '',
            'icon_path' => '',
            'is_featured' => 0,
            'sort_order' => 100,
            'main_image_path' => '',
            'main_image_alt' => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function modalityFromPost(): array
    {
        $modality = $this->blankModality();
        foreach (array_keys($modality) as $field) {
            if (in_array($field, ['id', 'main_image_path', 'main_image_alt'], true)) {
                continue;
            }
            $modality[$field] = $_POST[$field] ?? '';
        }
        $modality['slug'] = Slugger::slug((string) ($modality['slug'] ?: $modality['name']));
        $modality['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;
        $modality['main_image_alt'] = trim((string) ($_POST['main_image_alt'] ?? ''));

        return $modality;
    }

    /**
     * @param array<string, mixed> $modality
     * @return list<string>
     */
    private function validateModality(array $modality, ?int $modalityId): array
    {
        $errors = [];
        $name = trim((string) ($modality['name'] ?? ''));
        $slug = Slugger::slug((string) ($modality['slug'] ?: $name));

        if ($name === '') {
            $errors[] = 'El nombre de la modalidad es obligatorio.';
        }

        if ($this->slugExists($slug, $modalityId)) {
            $errors[] = 'Ya existe una modalidad con ese slug.';
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $modality
     */
    private function saveModality(array $modality, ?int $modalityId): int
    {
        $data = [
            'name' => trim((string) $modality['name']),
            'slug' => Slugger::slug((string) ($modality['slug'] ?: $modality['name'])),
            'short_description' => $this->nullable($modality['short_description'] ?? null),
            'full_description' => $this->nullable($modality['full_description'] ?? null),
            'extra_info' => $this->nullable($modality['extra_info'] ?? null),
            'icon_path' => $this->nullable($modality['icon_path'] ?? null),
            'is_featured' => (int) ($modality['is_featured'] ?? 0) === 1 ? 1 : 0,
            'sort_order' => $this->intOrNull($modality['sort_order'] ?? null) ?? 100,
        ];

        $pdo = Database::connection();
        if ($modalityId === null) {
            $statement = $pdo->prepare(
                'INSERT INTO modalities (name, slug, short_description, full_description, extra_info, icon_path, is_featured, sort_order)
                 VALUES (:name, :slug, :short_description, :full_description, :extra_info, :icon_path, :is_featured, :sort_order)'
            );
            $statement->execute($data);

            return (int) $pdo->lastInsertId();
        }

        $data['id'] = $modalityId;
        $statement = $pdo->prepare(
            'UPDATE modalities
             SET name = :name,
                 slug = :slug,
                 short_description = :short_description,
                 full_description = :full_description,
                 extra_info = :extra_info,
                 icon_path = :icon_path,
                 is_featured = :is_featured,
                 sort_order = :sort_order
             WHERE id = :id'
        );
        $statement->execute($data);

        return $modalityId;
    }

    private function storeMainImageUpload(int $modalityId): void
    {
        $pdo = Database::connection();

        if (isset($_POST['main_image_alt'])) {
            $statement = $pdo->prepare(
                'UPDATE media_files
                 SET alt_text = :alt_text
                 WHERE owner_type = \'modality\'
                   AND owner_id = :owner_id
                   AND media_type = \'modality_main_image\''
            );
            $statement->execute([
                'alt_text' => $this->nullable($_POST['main_image_alt'] ?? null),
                'owner_id' => $modalityId,
            ]);
        }

        if (!isset($_FILES['main_image']) || !is_array($_FILES['main_image']) || (int) ($_FILES['main_image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return;
        }

        $uploaded = (new MediaUploadService())->storeImage($_FILES['main_image'], 'modalities/' . $modalityId);
        $pdo->beginTransaction();
        try {
            $pdo->prepare('DELETE FROM media_files WHERE owner_type = \'modality\' AND owner_id = :owner_id AND media_type = \'modality_main_image\'')->execute(['owner_id' => $modalityId]);
            $statement = $pdo->prepare(
                'INSERT INTO media_files (owner_type, owner_id, media_type, original_name, file_path, mime_type, file_size, alt_text, caption, sort_order)
                 VALUES (:owner_type, :owner_id, :media_type, :original_name, :file_path, :mime_type, :file_size, :alt_text, :caption, :sort_order)'
            );
            $statement->execute([
                'owner_type' => 'modality',
                'owner_id' => $modalityId,
                'media_type' => 'modality_main_image',
                'original_name' => $uploaded['original_name'],
                'file_path' => $uploaded['file_path'],
                'mime_type' => $uploaded['mime_type'],
                'file_size' => $uploaded['file_size'],
                'alt_text' => $this->nullable($_POST['main_image_alt'] ?? null),
                'caption' => null,
                'sort_order' => 10,
            ]);
            $pdo->commit();
        } catch (\Throwable $throwable) {
            $pdo->rollBack();
            throw new RuntimeException('No se pudo guardar la foto principal: ' . $throwable->getMessage(), 0, $throwable);
        }
    }

    private function slugExists(string $slug, ?int $modalityId): bool
    {
        $statement = Database::connection()->prepare(
            'SELECT COUNT(*)
             FROM modalities
             WHERE slug = :slug
               AND (:ignore_current = 1 OR id <> :id)'
        );
        $statement->execute([
            'slug' => $slug,
            'ignore_current' => $modalityId === null ? 1 : 0,
            'id' => $modalityId ?? 0,
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
}
