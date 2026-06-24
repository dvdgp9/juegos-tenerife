<?php

declare(strict_types=1);

namespace JuegosTenerife\Controllers;

use JuegosTenerife\Content\ModalityContent;
use JuegosTenerife\Core\Database;
use JuegosTenerife\Core\Response;
use JuegosTenerife\Core\View;
use PDO;

final class ModalityController
{
    public function show(string $slug): Response
    {
        $modality = ModalityContent::find($slug);

        if ($modality === null) {
            return new Response('Modalidad no encontrada', 404);
        }

        try {
            $adminMedia = $this->adminMediaFor($slug);
        } catch (\Throwable) {
            $adminMedia = null;
        }
        if ($adminMedia !== null && !empty($adminMedia['image'])) {
            $modality['image'] = $adminMedia['image'];
            $modality['image_alt'] = $adminMedia['image_alt'] ?: ($modality['image_alt'] ?? $modality['name']);
        }

        return View::render('modality-show', [
            'title' => $modality['name'] . ' | Juegos Tradicionales de Tenerife',
            'slug' => $slug,
            'modality' => $modality,
        ]);
    }

    /**
     * @return array{image: string, image_alt: string}|null
     */
    private function adminMediaFor(string $slug): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT mf.file_path, mf.alt_text
             FROM modalities mo
             INNER JOIN media_files mf ON mf.owner_type = :owner_type
                AND mf.owner_id = mo.id
                AND mf.media_type = :media_type
             WHERE mo.slug = :slug
             LIMIT 1'
        );
        $statement->execute([
            'owner_type' => 'modality',
            'media_type' => 'modality_main_image',
            'slug' => $slug,
        ]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if (!is_array($row)) {
            return null;
        }

        return [
            'image' => (string) $row['file_path'],
            'image_alt' => (string) ($row['alt_text'] ?? ''),
        ];
    }
}
