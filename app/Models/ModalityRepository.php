<?php

declare(strict_types=1);

namespace JuegosTenerife\Models;

use JuegosTenerife\Core\Database;
use PDO;

final class ModalityRepository
{
    /**
     * @return list<array<string, mixed>>
     */
    public function featured(): array
    {
        $statement = Database::connection()->query(
            'SELECT id, name, slug, short_description, icon_path
             FROM modalities
             WHERE is_featured = 1
             ORDER BY sort_order ASC, name ASC'
        );

        return $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function all(): array
    {
        $statement = Database::connection()->query(
            'SELECT id, name, slug
             FROM modalities
             ORDER BY is_featured DESC, sort_order ASC, name ASC'
        );

        return $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
    }
}
