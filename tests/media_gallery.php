<?php

declare(strict_types=1);

/**
 * Prueba del servicio compartido de galerías y del aviso de envío demasiado grande
 * (Pasos D e I).
 *
 * Uso: php tests/media_gallery.php [nombre_base_de_datos]
 *
 * Trabaja contra una base de pruebas, nunca contra producción, y usa identificadores de
 * propietario altísimos para no cruzarse con datos reales. Limpia todo al terminar.
 */

use JuegosTenerife\Controllers\Admin\EntityController;
use JuegosTenerife\Core\Database;
use JuegosTenerife\Services\Media\MediaGallery;
use JuegosTenerife\Services\MediaUploadService;

require dirname(__DIR__) . '/vendor/autoload.php';

$database = $argv[1] ?? 'juegos_tenerife_test';
$_ENV['DB_DATABASE'] = $database;
// "localhost" hace que PDO use el socket de MariaDB, que es por donde autentica el usuario
// del sistema en una instalación local de Homebrew. Por TCP pediría contraseña.
$_ENV['DB_HOST'] = getenv('DB_HOST') ?: 'localhost';
$_ENV['DB_USERNAME'] = getenv('DB_USERNAME') ?: (string) getenv('USER');
$_ENV['DB_PASSWORD'] = getenv('DB_PASSWORD') ?: '';

if (str_contains($database, 'test') === false) {
    fwrite(STDERR, "Por seguridad, la base de datos debe llevar 'test' en el nombre.\n");
    exit(1);
}

try {
    $pdo = Database::connection();
} catch (Throwable $throwable) {
    fwrite(STDERR, 'No se pudo conectar a ' . $database . ': ' . $throwable->getMessage() . "\n");
    exit(1);
}

const OWNER_ENTITY = 999901;
const OWNER_FACILITY = 999902;

$uploadsRoot = MediaUploadService::uploadsRoot();
$testFolder = '__test_gallery__';
$testDir = $uploadsRoot . '/' . $testFolder;
@mkdir($testDir, 0755, true);

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

/** Inserta una foto ya "subida", con su archivo real en el disco. */
function seedPhoto(PDO $pdo, string $ownerType, int $ownerId, string $mediaType, string $name, int $sortOrder = 100): int
{
    global $testFolder, $uploadsRoot;

    $publicPath = '/uploads/' . $testFolder . '/' . $name;
    file_put_contents($uploadsRoot . '/' . $testFolder . '/' . $name, 'imagen');
    file_put_contents($uploadsRoot . '/' . $testFolder . '/' . basename($name, '.jpg') . '-thumb.jpg', 'miniatura');

    $statement = $pdo->prepare(
        'INSERT INTO media_files (owner_type, owner_id, media_type, original_name, file_path, mime_type, file_size, sort_order)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $statement->execute([$ownerType, $ownerId, $mediaType, $name, $publicPath, 'image/jpeg', 6, $sortOrder]);

    return (int) $pdo->lastInsertId();
}

function cleanUp(PDO $pdo): void
{
    global $testDir;

    $pdo->prepare('DELETE FROM media_files WHERE owner_id IN (?, ?)')
        ->execute([OWNER_ENTITY, OWNER_FACILITY]);

    foreach ((array) glob($testDir . '/*') as $file) {
        @unlink((string) $file);
    }
}

cleanUp($pdo);

$entityGallery = MediaGallery::forEntities();
$facilityGallery = MediaGallery::forFacilities();

echo "=== 1. Galería vacía ===\n";
check('Sin fotos devuelve lista vacía', $entityGallery->photosFor(OWNER_ENTITY) === []);

echo "\n=== 2. Orden: la destacada primero, después por orden ===\n";

$tercera = seedPhoto($pdo, 'entity', OWNER_ENTITY, 'entity_photo', 'tercera.jpg', 30);
$primera = seedPhoto($pdo, 'entity', OWNER_ENTITY, 'entity_photo', 'primera.jpg', 10);
$destacada = seedPhoto($pdo, 'entity', OWNER_ENTITY, 'entity_featured_photo', 'destacada.jpg', 90);

$photos = $entityGallery->photosFor(OWNER_ENTITY);
check('Devuelve las tres', count($photos) === 3, 'devueltas: ' . count($photos));
check('La destacada va primero', (int) $photos[0]['id'] === $destacada);
check('Después manda sort_order', (int) $photos[1]['id'] === $primera && (int) $photos[2]['id'] === $tercera);

echo "\n=== 3. Aislamiento entre entidades e instalaciones ===\n";

$deInstalacion = seedPhoto($pdo, 'facility', OWNER_FACILITY, 'facility_photo', 'instalacion.jpg', 10);

check('La galería de entidad no ve las de instalación', count($entityGallery->photosFor(OWNER_ENTITY)) === 3);
check('La galería de instalación ve solo la suya', count($facilityGallery->photosFor(OWNER_FACILITY)) === 1);
check(
    'Los tipos de instalación usan su propio prefijo',
    (string) $facilityGallery->photosFor(OWNER_FACILITY)[0]['media_type'] === 'facility_photo'
);

echo "\n=== 4. Edición de título, alt y orden ===\n";

$result = $entityGallery->sync(OWNER_ENTITY, [
    'media' => [
        (string) $primera => ['caption' => 'Terrero al atardecer', 'alt_text' => 'Vista del terrero', 'sort_order' => '5'],
    ],
], []);

$photos = $entityGallery->photosFor(OWNER_ENTITY);
$edited = null;
foreach ($photos as $photo) {
    if ((int) $photo['id'] === $primera) {
        $edited = $photo;
    }
}

check('Guarda el título', $edited !== null && $edited['caption'] === 'Terrero al atardecer');
check('Guarda el texto alternativo', $edited !== null && $edited['alt_text'] === 'Vista del terrero');
check('Guarda el orden', $edited !== null && (int) $edited['sort_order'] === 5);
check('No borra ni sube nada', $result->deleted === 0 && $result->uploaded === 0);

echo "\n=== 5. Cambio de foto destacada ===\n";

$entityGallery->sync(OWNER_ENTITY, ['featured_media_id' => (string) $tercera], []);
$photos = $entityGallery->photosFor(OWNER_ENTITY);

check('La nueva destacada va primero', (int) $photos[0]['id'] === $tercera);
check('Solo hay una destacada', count(array_filter(
    $photos,
    static fn (array $p): bool => $p['media_type'] === 'entity_featured_photo'
)) === 1);

echo "\n=== 6. Borrado: fila y archivos ===\n";

$pathBorrada = $uploadsRoot . '/' . $testFolder . '/primera.jpg';
$thumbBorrada = $uploadsRoot . '/' . $testFolder . '/primera-thumb.jpg';
check('El archivo existe antes de borrar', is_file($pathBorrada));

$result = $entityGallery->sync(OWNER_ENTITY, ['delete_media_ids' => [(string) $primera]], []);

check('Informa de una borrada', $result->deleted === 1, 'deleted: ' . $result->deleted);
check('Queda una foto menos', count($entityGallery->photosFor(OWNER_ENTITY)) === 2);
check('Borra el archivo', !is_file($pathBorrada));
check('Borra la miniatura', !is_file($thumbBorrada));
check('Sin archivos huérfanos', $result->orphanedPaths === []);

echo "\n=== 7. No se puede borrar lo que es de otro ===\n";

$result = $entityGallery->sync(OWNER_ENTITY, ['delete_media_ids' => [(string) $deInstalacion]], []);
check('No cuenta como borrada', $result->deleted === 0, 'deleted: ' . $result->deleted);
check('La foto de la instalación sigue ahí', count($facilityGallery->photosFor(OWNER_FACILITY)) === 1);

echo "\n=== 8. Borrado completo ===\n";

$orphaned = $entityGallery->deleteAllFor(OWNER_ENTITY);
check('No quedan fotos', $entityGallery->photosFor(OWNER_ENTITY) === []);
check('Sin huérfanos', $orphaned === []);
check('No toca la instalación', count($facilityGallery->photosFor(OWNER_FACILITY)) === 1);

echo "\n=== 9. Aviso de envío demasiado grande (Paso I) ===\n";

$controller = (new ReflectionClass(EntityController::class))->newInstanceWithoutConstructor();
$detect = new ReflectionMethod(EntityController::class, 'postExceededLimit');
$detect->setAccessible(true);

function scenario(ReflectionMethod $detect, object $controller, string $method, int $contentLength, array $post, array $files): bool
{
    $_SERVER['REQUEST_METHOD'] = $method;
    $_SERVER['CONTENT_LENGTH'] = (string) $contentLength;
    $_POST = $post;
    $_FILES = $files;

    return (bool) $detect->invoke($controller);
}

check('Una petición GET no se marca', !scenario($detect, $controller, 'GET', 0, [], []));
check('Un POST normal no se marca', !scenario($detect, $controller, 'POST', 500, ['_csrf' => 'x'], []));
check('Un POST sin cuerpo no se marca', !scenario($detect, $controller, 'POST', 0, [], []));
check('Un POST con archivos no se marca', !scenario($detect, $controller, 'POST', 900, [], ['logo' => []]));
check('Un POST descartado sí se marca', scenario($detect, $controller, 'POST', 90000000, [], []));

$message = new ReflectionMethod(EntityController::class, 'uploadLimitMessage');
$message->setAccessible(true);
$text = (string) $message->invoke($controller);
check('El aviso menciona el límite', str_contains($text, (string) ini_get('post_max_size')), $text);
check('El aviso aclara que no se ha guardado nada', str_contains($text, 'No se ha guardado'));

cleanUp($pdo);
@rmdir($testDir);

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
