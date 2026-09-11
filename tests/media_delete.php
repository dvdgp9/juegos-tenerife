<?php

declare(strict_types=1);

/**
 * Prueba del borrado físico de imágenes de MediaUploadService (Paso C).
 *
 * Uso: php tests/media_delete.php
 *
 * Trabaja dentro de public/uploads/__test__ y limpia al terminar.
 * Comprueba tanto el borrado normal como los intentos de salir de la carpeta de subidas.
 */

use JuegosTenerife\Services\MediaUploadService;

require dirname(__DIR__) . '/vendor/autoload.php';

$root = dirname(__DIR__);
$uploadsRoot = MediaUploadService::uploadsRoot();
$testFolder = '__test__';
$testDir = $uploadsRoot . '/' . $testFolder;

if (!is_dir($testDir) && !mkdir($testDir, 0755, true)) {
    fwrite(STDERR, "No se pudo crear la carpeta de pruebas.\n");
    exit(1);
}

$failures = [];
$checks = 0;
$service = new MediaUploadService();

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

function makeFile(string $path, string $contents = 'contenido'): void
{
    file_put_contents($path, $contents);
}

echo "=== 1. Borrado normal: imagen y miniatura ===\n";

$publicPath = '/uploads/' . $testFolder . '/foto.jpg';
$diskPath = $uploadsRoot . '/' . $testFolder . '/foto.jpg';
$thumbDiskPath = $uploadsRoot . '/' . $testFolder . '/foto-thumb.jpg';
makeFile($diskPath);
makeFile($thumbDiskPath);

$result = $service->deleteStoredImage($publicPath);
check('Devuelve true', $result);
check('Borra la imagen', !is_file($diskPath));
check('Borra también la miniatura', !is_file($thumbDiskPath));

echo "\n=== 2. Es idempotente ===\n";

check('Volver a borrar lo ya borrado no falla', $service->deleteStoredImage($publicPath));

echo "\n=== 3. Rechaza rutas fuera de uploads ===\n";

$outsidePath = $root . '/public/victima-test.txt';
makeFile($outsidePath, 'no debe borrarse');

check(
    'Rechaza una ruta que no empieza por /uploads/',
    !$service->deleteStoredImage('/assets/images/logo-dxt-tenerife.png')
);
check(
    'Rechaza el recorrido con ..',
    !$service->deleteStoredImage('/uploads/' . $testFolder . '/../../victima-test.txt')
);
check('El archivo de fuera sigue estando', is_file($outsidePath));

check('Rechaza una ruta vacía', !$service->deleteStoredImage(''));
check('Rechaza solo el prefijo', !$service->deleteStoredImage('/uploads/'));
check('Rechaza segmentos vacíos', !$service->deleteStoredImage('/uploads//foto.jpg'));
check('Rechaza el punto como segmento', !$service->deleteStoredImage('/uploads/./foto.jpg'));

echo "\n=== 4. Rechaza enlaces simbólicos que apuntan fuera ===\n";

$linkPath = $uploadsRoot . '/' . $testFolder . '/enlace.jpg';
@unlink($linkPath);
$linkCreated = @symlink($outsidePath, $linkPath);

if ($linkCreated) {
    check(
        'Rechaza un enlace que sale de la carpeta de subidas',
        !$service->deleteStoredImage('/uploads/' . $testFolder . '/enlace.jpg')
    );
    check('El archivo apuntado sigue estando', is_file($outsidePath));
    @unlink($linkPath);
} else {
    echo "  (omitido: el sistema no permite crear enlaces simbólicos)\n";
}

echo "\n=== 5. No se lleva por delante a los vecinos ===\n";

$keepPath = $uploadsRoot . '/' . $testFolder . '/otra.jpg';
$targetPath = $uploadsRoot . '/' . $testFolder . '/borrar.jpg';
makeFile($keepPath);
makeFile($targetPath);

$service->deleteStoredImage('/uploads/' . $testFolder . '/borrar.jpg');
check('Borra la indicada', !is_file($targetPath));
check('Deja intacta la de al lado', is_file($keepPath));

// Limpieza.
@unlink($outsidePath);
foreach ((array) glob($testDir . '/*') as $file) {
    @unlink((string) $file);
}
@rmdir($testDir);

check('La carpeta de pruebas queda limpia', !is_dir($testDir));

echo "\n=== Resultado ===\n";
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
