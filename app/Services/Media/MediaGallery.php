<?php

declare(strict_types=1);

namespace JuegosTenerife\Services\Media;

use JuegosTenerife\Core\Database;
use JuegosTenerife\Services\MediaUploadService;
use PDO;
use RuntimeException;
use Throwable;

/**
 * Galería de fotos de un elemento del censo, guardada en media_files.
 *
 * La tabla es polimórfica, así que la misma lógica sirve para entidades y para
 * instalaciones: solo cambian el tipo de propietario y la carpeta de destino.
 */
final class MediaGallery
{
    private const UPLOAD_FIELD = 'gallery_images';

    public function __construct(
        private readonly string $ownerType,
        private readonly string $folderPrefix,
    ) {
    }

    public static function forEntities(): self
    {
        return new self('entity', 'entities');
    }

    public static function forFacilities(): self
    {
        return new self('facility', 'facilities');
    }

    /**
     * Fotos de la galería, con la destacada siempre en primer lugar.
     *
     * @return list<array<string, mixed>>
     */
    public function photosFor(int $ownerId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, media_type, original_name, file_path, mime_type, file_size, alt_text, caption, sort_order
             FROM media_files
             WHERE owner_type = :owner_type
               AND owner_id = :owner_id
               AND media_type IN (:photo_type, :featured_type)
             ORDER BY media_type = :featured_order DESC, sort_order ASC, id ASC'
        );
        $statement->execute([
            'owner_type' => $this->ownerType,
            'owner_id' => $ownerId,
            'photo_type' => $this->photoType(),
            'featured_type' => $this->featuredType(),
            'featured_order' => $this->featuredType(),
        ]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Aplica de una vez los cambios enviados desde el formulario: edición de las fotos que ya
     * había, borrado de las marcadas y alta de las nuevas.
     *
     * @param array<string, mixed> $post  Normalmente $_POST.
     * @param array<string, mixed> $files Normalmente $_FILES.
     */
    public function sync(int $ownerId, array $post, array $files): MediaGalleryResult
    {
        [$deleted, $orphaned] = $this->updateExisting($ownerId, $post);
        $uploaded = $this->storeUploads($ownerId, $files);

        return new MediaGalleryResult($deleted, $uploaded, $orphaned);
    }

    /**
     * Borra todas las fotos de un propietario, archivos incluidos.
     * Pensado para cuando se elimina de verdad, no para un archivado reversible.
     *
     * @return list<string> Archivos que no se pudieron borrar del disco.
     */
    public function deleteAllFor(int $ownerId): array
    {
        $photos = $this->photosFor($ownerId);
        if ($photos === []) {
            return [];
        }

        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'DELETE FROM media_files
             WHERE owner_type = :owner_type
               AND owner_id = :owner_id
               AND media_type IN (:photo_type, :featured_type)'
        );
        $statement->execute([
            'owner_type' => $this->ownerType,
            'owner_id' => $ownerId,
            'photo_type' => $this->photoType(),
            'featured_type' => $this->featuredType(),
        ]);

        return $this->deleteFiles(array_map(
            static fn (array $photo): string => (string) $photo['file_path'],
            $photos
        ));
    }

    /**
     * @param array<string, mixed> $post
     * @return array{0: int, 1: list<string>}
     */
    private function updateExisting(int $ownerId, array $post): array
    {
        $mediaInput = is_array($post['media'] ?? null) ? $post['media'] : [];
        $deleteIds = array_values(array_filter(array_map('intval', (array) ($post['delete_media_ids'] ?? []))));
        $featuredId = (int) ($post['featured_media_id'] ?? 0);

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            // Se leen las rutas antes de borrar las filas: después ya no habría forma de
            // saber qué archivos quedaron sueltos en el disco.
            $deletedPaths = $this->pathsFor($pdo, $ownerId, $deleteIds);

            if ($deleteIds !== []) {
                $statement = $pdo->prepare(
                    'DELETE FROM media_files
                     WHERE id = :id
                       AND owner_type = :owner_type
                       AND owner_id = :owner_id'
                );
                foreach ($deleteIds as $deleteId) {
                    $statement->execute([
                        'id' => $deleteId,
                        'owner_type' => $this->ownerType,
                        'owner_id' => $ownerId,
                    ]);
                }
            }

            if ($mediaInput !== []) {
                $statement = $pdo->prepare(
                    'UPDATE media_files
                     SET alt_text = :alt_text,
                         caption = :caption,
                         sort_order = :sort_order
                     WHERE id = :id
                       AND owner_type = :owner_type
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
                        'owner_type' => $this->ownerType,
                        'owner_id' => $ownerId,
                    ]);
                }
            }

            // Solo puede haber una destacada: se baja la actual antes de subir la nueva.
            $statement = $pdo->prepare(
                'UPDATE media_files
                 SET media_type = :photo_type
                 WHERE owner_type = :owner_type
                   AND owner_id = :owner_id
                   AND media_type = :featured_type'
            );
            $statement->execute([
                'photo_type' => $this->photoType(),
                'owner_type' => $this->ownerType,
                'owner_id' => $ownerId,
                'featured_type' => $this->featuredType(),
            ]);

            if ($featuredId > 0 && !in_array($featuredId, $deleteIds, true)) {
                $statement = $pdo->prepare(
                    'UPDATE media_files
                     SET media_type = :featured_type
                     WHERE id = :id
                       AND owner_type = :owner_type
                       AND owner_id = :owner_id'
                );
                $statement->execute([
                    'featured_type' => $this->featuredType(),
                    'id' => $featuredId,
                    'owner_type' => $this->ownerType,
                    'owner_id' => $ownerId,
                ]);
            }

            $pdo->commit();
        } catch (Throwable $throwable) {
            $pdo->rollBack();

            throw new RuntimeException('No se pudieron guardar las fotos existentes: ' . $throwable->getMessage(), 0, $throwable);
        }

        // Los archivos se borran una vez confirmada la transacción. Al revés, una reversión
        // dejaría filas en base de datos apuntando a imágenes que ya no existen.
        $orphaned = $this->deleteFiles($deletedPaths);

        return [count($deletedPaths), $orphaned];
    }

    /**
     * @param array<string, mixed> $files
     */
    private function storeUploads(int $ownerId, array $files): int
    {
        if (!isset($files[self::UPLOAD_FIELD]) || !is_array($files[self::UPLOAD_FIELD])) {
            return 0;
        }

        $uploads = $this->normalizeMultipleUpload($files[self::UPLOAD_FIELD]);
        if ($uploads === []) {
            return 0;
        }

        $service = new MediaUploadService();
        $statement = Database::connection()->prepare(
            'INSERT INTO media_files (owner_type, owner_id, media_type, original_name, file_path, mime_type, file_size, alt_text, caption, sort_order)
             VALUES (:owner_type, :owner_id, :media_type, :original_name, :file_path, :mime_type, :file_size, :alt_text, :caption, :sort_order)'
        );

        $stored = 0;

        foreach ($uploads as $upload) {
            if ((int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $uploaded = $service->storeImage($upload, $this->folderPrefix . '/' . $ownerId);
            $statement->execute([
                'owner_type' => $this->ownerType,
                'owner_id' => $ownerId,
                'media_type' => $this->photoType(),
                'original_name' => $uploaded['original_name'],
                'file_path' => $uploaded['file_path'],
                'mime_type' => $uploaded['mime_type'],
                'file_size' => $uploaded['file_size'],
                'alt_text' => null,
                'caption' => null,
                'sort_order' => 100,
            ]);
            $stored++;
        }

        return $stored;
    }

    /**
     * Rutas de las fotos indicadas que pertenecen realmente a este propietario.
     *
     * @param list<int> $mediaIds
     * @return list<string>
     */
    private function pathsFor(PDO $pdo, int $ownerId, array $mediaIds): array
    {
        if ($mediaIds === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($mediaIds), '?'));
        $statement = $pdo->prepare(
            'SELECT file_path
             FROM media_files
             WHERE owner_type = ?
               AND owner_id = ?
               AND id IN (' . $placeholders . ')'
        );
        $statement->execute(array_merge([$this->ownerType, $ownerId], $mediaIds));

        return array_map('strval', $statement->fetchAll(PDO::FETCH_COLUMN, 0));
    }

    /**
     * @param list<string> $paths
     * @return list<string> Los que no se pudieron borrar.
     */
    private function deleteFiles(array $paths): array
    {
        if ($paths === []) {
            return [];
        }

        $service = new MediaUploadService();
        $failed = [];

        foreach ($paths as $path) {
            if (!$service->deleteStoredImage($path)) {
                $failed[] = $path;
            }
        }

        return $failed;
    }

    /**
     * PHP entrega una subida múltiple como columnas paralelas; aquí se vuelve a montar
     * cada archivo por separado para poder tratarlos de uno en uno.
     *
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

    private function photoType(): string
    {
        return $this->ownerType . '_photo';
    }

    private function featuredType(): string
    {
        return $this->ownerType . '_featured_photo';
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
