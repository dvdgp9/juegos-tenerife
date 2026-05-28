<?php

declare(strict_types=1);

namespace JuegosTenerife\Models;

use JuegosTenerife\Core\Database;
use PDO;

final class MunicipalityRepository
{
    /**
     * @return list<array<string, mixed>>
     */
    public function filterable(): array
    {
        $statement = Database::connection()->query(
            'SELECT id, name, slug
             FROM municipalities
             WHERE is_filterable = 1
               AND is_tenerife = 1
               AND sort_order < 900
             ORDER BY sort_order ASC, name ASC'
        );

        return $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
    }
}
