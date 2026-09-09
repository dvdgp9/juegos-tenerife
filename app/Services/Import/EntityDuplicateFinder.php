<?php

declare(strict_types=1);

namespace JuegosTenerife\Services\Import;

use JuegosTenerife\Core\Database;
use JuegosTenerife\Services\Support\Slugger;
use PDO;

/**
 * Detección de entidades ya existentes.
 *
 * La usan tanto la previsualización como la importación, para que lo que
 * anuncia el preview sea exactamente lo que después hará la importación.
 */
final class EntityDuplicateFinder
{
    private const MUNICIPALITY_ALIASES = [
        'la-laguna' => 'San Cristóbal de La Laguna',
    ];

    public function normalizeMunicipalityName(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return '';
        }

        return self::MUNICIPALITY_ALIASES[Slugger::slug($name)] ?? $name;
    }

    /**
     * Slug que tendría la entidad. Si otro registro distinto ya ocupa ese
     * slug, se desambigua con el municipio.
     */
    public function slugFor(PDO $pdo, string $name, string $municipality): string
    {
        $slug = Slugger::slug($name);
        $duplicate = $pdo->prepare('SELECT COUNT(*) FROM entities WHERE slug = :slug AND name <> :name');
        $duplicate->execute(['slug' => $slug, 'name' => $name]);

        if ((int) $duplicate->fetchColumn() === 0) {
            return $slug;
        }

        return Slugger::slug($name . '-' . $municipality);
    }

    public function findIdBySlug(PDO $pdo, string $slug): ?int
    {
        $statement = $pdo->prepare('SELECT id FROM entities WHERE slug = :slug LIMIT 1');
        $statement->execute(['slug' => $slug]);
        $id = $statement->fetchColumn();

        return $id === false ? null : (int) $id;
    }

    /**
     * Busca primero por slug y, si no aparece, por nombre exacto dentro del
     * mismo municipio.
     */
    public function findExistingId(PDO $pdo, string $name, string $municipality, string $slug): ?int
    {
        $existingBySlug = $this->findIdBySlug($pdo, $slug);
        if ($existingBySlug !== null) {
            return $existingBySlug;
        }

        $municipality = $this->normalizeMunicipalityName($municipality);
        $municipalitySlug = $municipality !== '' ? Slugger::slug($municipality) : null;

        $statement = $pdo->prepare(
            'SELECT e.id
             FROM entities e
             LEFT JOIN municipalities m ON m.id = e.municipality_id
             WHERE LOWER(TRIM(e.name)) = LOWER(TRIM(:name))
               AND (:municipality_slug_empty = 1 OR m.slug = :municipality_slug)
               AND e.deleted_at IS NULL
             ORDER BY e.id DESC
             LIMIT 1'
        );
        $statement->execute([
            'name' => $name,
            'municipality_slug_empty' => $municipalitySlug === null ? 1 : 0,
            'municipality_slug' => $municipalitySlug,
        ]);
        $id = $statement->fetchColumn();

        return $id === false ? null : (int) $id;
    }

    /**
     * Clasifica las filas de un Excel antes de importarlo.
     *
     * Contempla también los repetidos dentro del propio archivo: si un nombre
     * aparece dos veces, la primera fila se crearía y la segunda quedaría
     * como repetida, que es lo que hará la importación real.
     *
     * @param list<array{row:int,name:string,municipality:string}> $rows
     * @return array{
     *     new:int,
     *     duplicate:int,
     *     skipped:int,
     *     duplicates:list<array{row:int,name:string,municipality:string,reason:string}>,
     *     newEntities:list<array{row:int,name:string,municipality:string}>
     * }
     */
    public function classify(array $rows): array
    {
        $pdo = Database::connection();

        $result = [
            'new' => 0,
            'duplicate' => 0,
            'skipped' => 0,
            'duplicates' => [],
            'newEntities' => [],
        ];

        $seenSlugs = [];

        foreach ($rows as $row) {
            $name = trim($row['name']);
            if ($name === '') {
                $result['skipped']++;
                continue;
            }

            $municipality = $this->normalizeMunicipalityName($row['municipality']);
            $slug = $this->slugFor($pdo, $name, $municipality);

            if (isset($seenSlugs[$slug])) {
                $result['duplicate']++;
                $result['duplicates'][] = [
                    'row' => $row['row'],
                    'name' => $name,
                    'municipality' => $municipality,
                    'reason' => 'repetida dentro del propio Excel (fila ' . $seenSlugs[$slug] . ')',
                ];
                continue;
            }

            if ($this->findExistingId($pdo, $name, $municipality, $slug) !== null) {
                $result['duplicate']++;
                $result['duplicates'][] = [
                    'row' => $row['row'],
                    'name' => $name,
                    'municipality' => $municipality,
                    'reason' => 'ya existe en el censo',
                ];
                continue;
            }

            $seenSlugs[$slug] = $row['row'];
            $result['new']++;
            $result['newEntities'][] = [
                'row' => $row['row'],
                'name' => $name,
                'municipality' => $municipality,
            ];
        }

        return $result;
    }
}
