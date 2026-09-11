<?php

declare(strict_types=1);

/**
 * Prueba de las fotos de instalaciones en la ficha pública de entidad (Paso F).
 *
 * Uso: php tests/entity_facility_media.php [nombre_base_de_datos]
 *
 * Crea una entidad y dos instalaciones de prueba, comprueba el agrupado y limpia al terminar.
 */

use JuegosTenerife\Core\Database;
use JuegosTenerife\Models\EntityRepository;

require dirname(__DIR__) . '/vendor/autoload.php';

$database = $argv[1] ?? 'juegos_tenerife_test';
$_ENV['DB_DATABASE'] = $database;
$_ENV['DB_HOST'] = getenv('DB_HOST') ?: 'localhost';
$_ENV['DB_USERNAME'] = getenv('DB_USERNAME') ?: (string) getenv('USER');
$_ENV['DB_PASSWORD'] = getenv('DB_PASSWORD') ?: '';

if (!str_contains($database, 'test')) {
    fwrite(STDERR, "Por seguridad, la base de datos debe llevar 'test' en el nombre.\n");
    exit(1);
}

try {
    $pdo = Database::connection();
} catch (Throwable $throwable) {
    fwrite(STDERR, 'No se pudo conectar a ' . $database . ': ' . $throwable->getMessage() . "\n");
    exit(1);
}

const ENTITY_SLUG = 'zzz-test-entidad-fotos';
const FACILITY_A_SLUG = 'zzz-test-terrero';
const FACILITY_B_SLUG = 'zzz-test-campo-sin-fotos';
const FACILITY_C_SLUG = 'zzz-test-pabellon';

$failures = [];
$checks = 0;

function check(string $description, bool $condition, string $detail = ''): void
{
    global $failures, $checks;
    $checks++;

    if ($condition) {
        echo "  OK    $description" . ($detail === '' ? '' : "  ($detail)") . "\n";

        return;
    }

    echo "  FALLA $description" . ($detail === '' ? '' : "  ($detail)") . "\n";
    $failures[] = $description;
}

function cleanUp(PDO $pdo): void
{
    $slugs = [ENTITY_SLUG, FACILITY_A_SLUG, FACILITY_B_SLUG, FACILITY_C_SLUG];

    $entityIds = $pdo->query("SELECT id FROM entities WHERE slug = '" . ENTITY_SLUG . "'")->fetchAll(PDO::FETCH_COLUMN);
    $facilityIds = $pdo->query(
        "SELECT id FROM facilities WHERE slug IN ('" . implode("','", array_slice($slugs, 1)) . "')"
    )->fetchAll(PDO::FETCH_COLUMN);

    foreach ($entityIds as $id) {
        $pdo->prepare('DELETE FROM media_files WHERE owner_type = ? AND owner_id = ?')->execute(['entity', $id]);
        $pdo->prepare('DELETE FROM entity_facilities WHERE entity_id = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM entities WHERE id = ?')->execute([$id]);
    }

    foreach ($facilityIds as $id) {
        $pdo->prepare('DELETE FROM media_files WHERE owner_type = ? AND owner_id = ?')->execute(['facility', $id]);
        $pdo->prepare('DELETE FROM entity_facilities WHERE facility_id = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM facilities WHERE id = ?')->execute([$id]);
    }
}

function addPhoto(PDO $pdo, string $ownerType, int $ownerId, string $mediaType, string $file, int $sortOrder, ?string $caption = null): void
{
    $pdo->prepare(
        'INSERT INTO media_files (owner_type, owner_id, media_type, original_name, file_path, sort_order, caption)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    )->execute([$ownerType, $ownerId, $mediaType, $file, '/uploads/__test__/' . $file, $sortOrder, $caption]);
}

cleanUp($pdo);

// Entidad publicada con tres instalaciones: dos con fotos y una sin ninguna.
$pdo->prepare('INSERT INTO entities (name, slug, is_published) VALUES (?, ?, 1)')
    ->execute(['Club de prueba fotos', ENTITY_SLUG]);
$entityId = (int) $pdo->lastInsertId();

$facilityIds = [];
foreach ([
    FACILITY_A_SLUG => 'Terrero de prueba',
    FACILITY_B_SLUG => 'Campo sin fotos',
    FACILITY_C_SLUG => 'Pabellón de prueba',
] as $slug => $name) {
    $pdo->prepare('INSERT INTO facilities (name, slug) VALUES (?, ?)')->execute([$name, $slug]);
    $facilityIds[$slug] = (int) $pdo->lastInsertId();
}

// El orden de vinculación es el que debe respetar la ficha.
$link = $pdo->prepare('INSERT INTO entity_facilities (entity_id, facility_id, label, sort_order) VALUES (?, ?, ?, ?)');
$link->execute([$entityId, $facilityIds[FACILITY_A_SLUG], null, 10]);
$link->execute([$entityId, $facilityIds[FACILITY_B_SLUG], null, 20]);
$link->execute([$entityId, $facilityIds[FACILITY_C_SLUG], 'Pabellón municipal (cedido)', 30]);

addPhoto($pdo, 'entity', $entityId, 'entity_photo', 'equipo.jpg', 10, 'El equipo en 2026');

addPhoto($pdo, 'facility', $facilityIds[FACILITY_A_SLUG], 'facility_photo', 'terrero-b.jpg', 20);
addPhoto($pdo, 'facility', $facilityIds[FACILITY_A_SLUG], 'facility_featured_photo', 'terrero-destacada.jpg', 90);
addPhoto($pdo, 'facility', $facilityIds[FACILITY_A_SLUG], 'facility_photo', 'terrero-a.jpg', 10);
addPhoto($pdo, 'facility', $facilityIds[FACILITY_C_SLUG], 'facility_photo', 'pabellon.jpg', 10);

$entity = (new EntityRepository())->findBySlug(ENTITY_SLUG);

echo "=== 1. La ficha carga los dos orígenes por separado ===\n";

check('Encuentra la entidad', $entity !== null);
check('Trae sus fotos propias', count($entity['media_files'] ?? []) === 1);
check('Trae las fotos de instalaciones', isset($entity['facility_media']));
check(
    'Las fotos propias no incluyen las de instalación',
    ($entity['media_files'][0]['file_path'] ?? '') === '/uploads/__test__/equipo.jpg'
);

echo "\n=== 2. Agrupado por instalación ===\n";

$groups = $entity['facility_media'] ?? [];
check('Solo aparecen las instalaciones con fotos', count($groups) === 2, 'grupos: ' . count($groups));
check('Respeta el orden de vinculación', ($groups[0]['facility_name'] ?? '') === 'Terrero de prueba', (string) ($groups[0]['facility_name'] ?? ''));
check(
    'Usa la etiqueta de la vinculación cuando existe',
    ($groups[1]['facility_name'] ?? '') === 'Pabellón municipal (cedido)',
    (string) ($groups[1]['facility_name'] ?? '')
);

echo "\n=== 3. Orden dentro de cada instalación ===\n";

$photos = $groups[0]['photos'] ?? [];
check('El terrero trae sus tres fotos', count($photos) === 3, 'fotos: ' . count($photos));
check(
    'La destacada va primero pese a tener el orden más alto',
    ($photos[0]['file_path'] ?? '') === '/uploads/__test__/terrero-destacada.jpg',
    (string) ($photos[0]['file_path'] ?? '')
);
check(
    'El resto respeta sort_order',
    ($photos[1]['file_path'] ?? '') === '/uploads/__test__/terrero-a.jpg'
        && ($photos[2]['file_path'] ?? '') === '/uploads/__test__/terrero-b.jpg'
);

echo "\n=== 4. Entidad sin instalaciones con fotos ===\n";

$pdo->prepare('DELETE FROM media_files WHERE owner_type = ? AND owner_id IN (?, ?)')
    ->execute(['facility', $facilityIds[FACILITY_A_SLUG], $facilityIds[FACILITY_C_SLUG]]);

$entity = (new EntityRepository())->findBySlug(ENTITY_SLUG);
check('Devuelve lista vacía, no un grupo vacío', ($entity['facility_media'] ?? null) === []);

echo "\n=== 5. Entidad sin instalaciones ===\n";

$pdo->prepare('DELETE FROM entity_facilities WHERE entity_id = ?')->execute([$entityId]);
$entity = (new EntityRepository())->findBySlug(ENTITY_SLUG);
check('No falla sin instalaciones vinculadas', ($entity['facility_media'] ?? null) === []);
check('Sigue trayendo sus fotos propias', count($entity['media_files'] ?? []) === 1);

cleanUp($pdo);

echo "\n=== Resultado ===\n";
echo "Base de datos: $database\n";
echo "Comprobaciones: $checks\n";

if ($failures === []) {
    echo "Todas correctas.\n";
    exit(0);
}

echo 'Fallos: ' . count($failures) . "\n";
foreach ($failures as $failure) {
    echo "  - $failure\n";
}

exit(1);
