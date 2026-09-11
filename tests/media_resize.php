<?php

declare(strict_types=1);

/**
 * Prueba del procesado de imágenes de MediaUploadService (Paso B).
 *
 * Uso: php tests/media_resize.php
 *
 * Genera sus propias imágenes de muestra, las pasa por el procesado real y comprueba
 * el resultado. No toca la base de datos ni public/uploads.
 */

use JuegosTenerife\Services\MediaUploadService;

require dirname(__DIR__) . '/vendor/autoload.php';

if (!extension_loaded('imagick')) {
    fwrite(STDERR, "Falta la extensión imagick.\n");
    exit(1);
}

$workDir = sys_get_temp_dir() . '/media-resize-' . bin2hex(random_bytes(4));
$sourceDir = $workDir . '/origen';
$outputDir = $workDir . '/salida';
mkdir($sourceDir, 0755, true);
mkdir($outputDir, 0755, true);

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

/**
 * Inserta una cabecera EXIF mínima con la orientación indicada.
 * ImageMagick no escribe esa etiqueta al generar la imagen, así que hay que ponerla a mano
 * para poder reproducir el caso de la foto vertical hecha con móvil.
 */
function withExifOrientation(string $jpegPath, int $orientation): void
{
    $jpeg = (string) file_get_contents($jpegPath);
    if (substr($jpeg, 0, 2) !== "\xFF\xD8") {
        throw new RuntimeException('El archivo de muestra no es un JPEG.');
    }

    $tiff = "MM\x00\x2A\x00\x00\x00\x08";
    $entry = "\x01\x12\x00\x03\x00\x00\x00\x01" . pack('n', $orientation) . "\x00\x00";
    $ifd = "\x00\x01" . $entry . "\x00\x00\x00\x00";
    $body = "Exif\x00\x00" . $tiff . $ifd;
    $app1 = "\xFF\xE1" . pack('n', strlen($body) + 2) . $body;

    file_put_contents($jpegPath, "\xFF\xD8" . $app1 . substr($jpeg, 2));
}

function makeImage(string $path, int $width, int $height, string $pseudo, int $quality = 92): void
{
    $image = new Imagick();
    $image->newPseudoImage($width, $height, $pseudo);
    $image->setImageFormat(pathinfo($path, PATHINFO_EXTENSION) === 'png' ? 'png' : 'jpeg');
    $image->setImageCompressionQuality($quality);
    $image->writeImage($path);
    $image->clear();
}

/** Invoca el procesado real del servicio, que es privado por no formar parte de su interfaz. */
function process(string $source, string $target, bool $withThumbnail = true): void
{
    $method = new ReflectionMethod(MediaUploadService::class, 'process');
    $method->setAccessible(true);
    $method->invoke(new MediaUploadService(), $source, $target, $withThumbnail);
}

function dimensions(string $path): array
{
    $image = new Imagick($path);
    $result = [$image->getImageWidth(), $image->getImageHeight()];
    $image->clear();

    return $result;
}

echo "=== 1. Foto grande: se reescala y adelgaza ===\n";

$source = $sourceDir . '/grande.jpg';
makeImage($source, 4000, 3000, 'gradient:navy-orange');
$target = $outputDir . '/grande.jpg';
process($source, $target);

[$width, $height] = dimensions($target);
check('La versión web limita el lado mayor a 1600 px', $width === 1600 && $height === 1200, "{$width}x{$height}");

$thumb = MediaUploadService::thumbnailPath($target);
check('Se genera la miniatura', is_file($thumb));
if (is_file($thumb)) {
    [$tw, $th] = dimensions($thumb);
    check('La miniatura limita el lado mayor a 400 px', $tw === 400 && $th === 300, "{$tw}x{$th}");
}

$originalBytes = (int) filesize($source);
$webBytes = (int) filesize($target);
check('La versión web pesa menos que el original', $webBytes < $originalBytes, round($originalBytes / 1024) . ' KB -> ' . round($webBytes / 1024) . ' KB');
check('La versión web baja de 400 KB', $webBytes < 409600, round($webBytes / 1024) . ' KB');

echo "\n=== 2. Foto vertical de móvil: orientación EXIF ===\n";

$source = $sourceDir . '/vertical.jpg';
makeImage($source, 1200, 800, 'gradient:red-yellow');
withExifOrientation($source, 6);

$probe = new Imagick($source);
$readOrientation = $probe->getImageOrientation();
$probe->clear();
check('La muestra lleva orientación 6 (RightTop)', $readOrientation === Imagick::ORIENTATION_RIGHTTOP, 'valor leído: ' . $readOrientation);

$target = $outputDir . '/vertical.jpg';
process($source, $target);
[$width, $height] = dimensions($target);
check('La foto se guarda girada, más alta que ancha', $height > $width, "{$width}x{$height}");

$result = new Imagick($target);
$finalOrientation = $result->getImageOrientation();
$result->clear();
check(
    'La etiqueta de orientación ya no queda pendiente de aplicar',
    in_array($finalOrientation, [Imagick::ORIENTATION_UNDEFINED, Imagick::ORIENTATION_TOPLEFT], true),
    'valor final: ' . $finalOrientation
);

echo "\n=== 3. Imagen pequeña: no se amplía ===\n";

$source = $sourceDir . '/pequena.png';
makeImage($source, 200, 150, 'xc:teal');
$target = $outputDir . '/pequena.png';
process($source, $target);
[$width, $height] = dimensions($target);
check('Conserva su tamaño original', $width === 200 && $height === 150, "{$width}x{$height}");

echo "\n=== 4. PNG con transparencia ===\n";

$source = $sourceDir . '/transparente.png';
$canvas = new Imagick();
$canvas->newImage(900, 600, new ImagickPixel('transparent'));
$canvas->setImageFormat('png');
$draw = new ImagickDraw();
$draw->setFillColor(new ImagickPixel('rgba(0,128,255,0.8)'));
$draw->circle(450, 300, 450, 100);
$canvas->drawImage($draw);
$canvas->writeImage($source);
$canvas->clear();

$target = $outputDir . '/transparente.png';
process($source, $target);
$result = new Imagick($target);
$cornerIsTransparent = $result->getImagePixelColor(2, 2)->getColorValue(Imagick::COLOR_ALPHA) < 0.1;
$result->clear();
check('La transparencia sobrevive al procesado', $cornerIsTransparent);

echo "\n=== 5. GIF animado ===\n";

$source = $sourceDir . '/animado.gif';
$animation = new Imagick();
foreach (['red', 'green', 'blue'] as $color) {
    $frame = new Imagick();
    $frame->newImage(2000, 1500, new ImagickPixel($color));
    $frame->setImageFormat('gif');
    $frame->setImageDelay(20);
    $animation->addImage($frame);
}
$animation->setImageFormat('gif');
$animation->writeImages($source, true);
$animation->clear();

$target = $outputDir . '/animado.gif';
process($source, $target);

$result = new Imagick($target);
$frames = $result->getNumberImages();
$result->setFirstIterator();
[$width, $height] = [$result->getImageWidth(), $result->getImageHeight()];
$result->clear();
check('Conserva los tres fotogramas', $frames === 3, "fotogramas: $frames");
check('Los fotogramas se reescalan', $width === 1600 && $height === 1200, "{$width}x{$height}");

$thumb = MediaUploadService::thumbnailPath($target);
$thumbImage = new Imagick($thumb);
$thumbFrames = $thumbImage->getNumberImages();
$thumbImage->clear();
check('La miniatura se queda en un solo fotograma', $thumbFrames === 1, "fotogramas: $thumbFrames");

echo "\n=== 6. Sin miniatura cuando no se pide ===\n";

$source = $sourceDir . '/logo.png';
makeImage($source, 800, 400, 'xc:white');
$target = $outputDir . '/logo.png';
process($source, $target, false);
check('La imagen se guarda', is_file($target));
check('No se genera miniatura', !is_file(MediaUploadService::thumbnailPath($target)));

echo "\n=== 7. Convención de nombre de la miniatura ===\n";

check(
    'Inserta el sufijo antes de la extensión',
    MediaUploadService::thumbnailPath('/uploads/entities/3/20260911-abc.jpg') === '/uploads/entities/3/20260911-abc-thumb.jpg'
);
check(
    'Tolera rutas sin extensión',
    MediaUploadService::thumbnailPath('/uploads/entities/3/sinextension') === '/uploads/entities/3/sinextension-thumb'
);

// Limpieza.
foreach ([$sourceDir, $outputDir] as $dir) {
    foreach ((array) glob($dir . '/*') as $file) {
        @unlink((string) $file);
    }
    @rmdir($dir);
}
@rmdir($workDir);

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
