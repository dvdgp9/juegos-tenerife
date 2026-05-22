<?php

declare(strict_types=1);

namespace JuegosTenerife\Models;

use JuegosTenerife\Core\Database;
use PDO;

final class EntityTypeRepository
{
    /**
     * @return list<array<string, mixed>>
     */
    public function all(): array
    {
        $statement = Database::connection()->query(
            'SELECT id, name, slug
             FROM entity_types
             ORDER BY name ASC'
        );

        return $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
    }
}
