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
        try {
            $row = $this->findModality($slug);
            $modality = $row === null ? ModalityContent::find($slug) : ModalityContent::published($row);
        } catch (\Throwable) {
            $modality = ModalityContent::find($slug);
        }

        if ($modality === null) {
            return new Response('Modalidad no encontrada', 404);
        }

        return View::render('modality-show', [
            'title' => $modality['name'] . ' | Juegos Tradicionales de Tenerife',
            'slug' => $slug,
            'modality' => $modality,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findModality(string $slug): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT mo.*, mf.file_path AS main_image_path, mf.alt_text AS main_image_alt
             FROM modalities mo
             LEFT JOIN media_files mf ON mf.owner_type = :owner_type
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

        return $row;
    }
}
