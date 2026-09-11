<?php

declare(strict_types=1);

/**
 * Comprobación del entorno para la subida y el redimensionado de imágenes.
 *
 * Uso: php tests/env_check.php
 *
 * No modifica nada: solo lee configuración de PHP y comprueba permisos.
 */

$root = dirname(__DIR__);
$problems = [];
$warnings = [];

function bytesFromIni(string $value): int
{
    $value = trim($value);
    if ($value === '' || $value === '-1') {
        return -1;
    }

    $unit = strtolower($value[strlen($value) - 1]);
    $number = (int) $value;

    return match ($unit) {
        'g' => $number * 1024 * 1024 * 1024,
        'm' => $number * 1024 * 1024,
        'k' => $number * 1024,
        default => $number,
    };
}

function human(int $bytes): string
{
    if ($bytes < 0) {
        return 'sin límite';
    }

    return round($bytes / 1048576, 1) . ' MB';
}

echo "=== Entorno PHP ===\n";
echo 'Versión PHP: ' . PHP_VERSION . "\n";
echo 'Sistema: ' . PHP_OS_FAMILY . "\n";
if (version_compare(PHP_VERSION, '8.1', '<')) {
    $problems[] = 'PHP es anterior a 8.1.';
}

echo "\n=== Extensiones necesarias ===\n";

$required = [
    'imagick' => 'Redimensionado de imágenes. Es el motor elegido para las galerías.',
    'fileinfo' => 'Detección del tipo real de archivo en la subida.',
];

foreach ($required as $extension => $why) {
    $loaded = extension_loaded($extension);
    echo str_pad($extension, 12) . ($loaded ? 'OK' : 'FALTA') . '   ' . $why . "\n";
    if (!$loaded) {
        $problems[] = "Falta la extensión $extension.";
    }
}

echo "\n=== Extensiones opcionales ===\n";

$hasImagick = extension_loaded('imagick');
$optional = [
    'gd' => 'Alternativa a Imagick. No se usa mientras Imagick esté disponible.',
    'exif' => $hasImagick
        ? 'Innecesaria: Imagick lee la orientación de la foto por sí mismo.'
        : 'Sin Imagick es imprescindible, o las fotos verticales de móvil saldrán tumbadas.',
];

foreach ($optional as $extension => $why) {
    $loaded = extension_loaded($extension);
    echo str_pad($extension, 12) . ($loaded ? 'presente' : 'ausente') . '   ' . $why . "\n";
}

if (!$hasImagick && !extension_loaded('exif')) {
    $problems[] = 'Sin Imagick y sin exif no hay forma de corregir la orientación de las fotos.';
}

if ($hasImagick) {
    $formats = (new Imagick())->queryFormats();
    $supported = [];
    foreach (['JPEG', 'PNG', 'WEBP', 'GIF'] as $format) {
        if (in_array($format, $formats, true)) {
            $supported[] = $format;
        }
    }
    echo 'Formatos que Imagick puede procesar: ' . ($supported === [] ? 'ninguno' : implode(', ', $supported)) . "\n";

    foreach (['JPEG', 'PNG', 'WEBP'] as $format) {
        if (!in_array($format, $supported, true)) {
            $problems[] = "Imagick no puede procesar $format.";
        }
    }
}

echo "\n=== Límites de subida ===\n";
$uploadMax = bytesFromIni((string) ini_get('upload_max_filesize'));
$postMax = bytesFromIni((string) ini_get('post_max_size'));
$maxFiles = (int) ini_get('max_file_uploads');
$memory = bytesFromIni((string) ini_get('memory_limit'));

echo 'upload_max_filesize: ' . ini_get('upload_max_filesize') . '  (' . human($uploadMax) . ")\n";
echo 'post_max_size:       ' . ini_get('post_max_size') . '  (' . human($postMax) . ")\n";
echo 'max_file_uploads:    ' . $maxFiles . "\n";
echo 'memory_limit:        ' . ini_get('memory_limit') . '  (' . human($memory) . ")\n";
echo 'max_execution_time:  ' . ini_get('max_execution_time') . "\n";

// La aplicación acepta imágenes de hasta 8 MB por archivo.
$appLimit = 8 * 1024 * 1024;

if ($uploadMax >= 0 && $uploadMax < $appLimit) {
    $problems[] = 'upload_max_filesize (' . human($uploadMax) . ') es menor que el límite de 8 MB de la aplicación: las fotos grandes se rechazarán antes de llegar al código.';
}

if ($postMax >= 0 && $postMax < $appLimit * 2) {
    $warnings[] = 'post_max_size (' . human($postMax) . ') deja poco margen para subir varias fotos a la vez. Con el formulario de entidad completo conviene al menos 32 MB.';
}

if ($maxFiles > 0 && $maxFiles < 20) {
    $warnings[] = "max_file_uploads es $maxFiles: es el número máximo de archivos por envío, contando todos los campos del formulario.";
}

if ($memory >= 0 && $memory < 256 * 1024 * 1024 && !extension_loaded('imagick')) {
    $warnings[] = 'memory_limit (' . human($memory) . ') puede quedarse corto al redimensionar fotos grandes con GD. Una imagen de 4000x3000 ocupa unos 48 MB descomprimida.';
}

$executionTime = (int) ini_get('max_execution_time');
if ($executionTime > 0 && $executionTime < 120) {
    $warnings[] = "max_execution_time es $executionTime segundos: procesar varias fotos en un mismo envío puede agotarlo.";
}

echo "\n=== Permisos de escritura ===\n";
$paths = [
    'public/uploads',
    'storage/uploads',
    'storage/imports',
    'storage/logs',
    'storage/cache',
];

foreach ($paths as $relative) {
    $path = $root . '/' . $relative;
    if (!is_dir($path)) {
        echo str_pad($relative, 20) . "NO EXISTE\n";
        $problems[] = "La carpeta $relative no existe.";
        continue;
    }

    $writable = is_writable($path);
    echo str_pad($relative, 20) . ($writable ? 'escribible' : 'SIN PERMISO DE ESCRITURA') . '   (' . substr(sprintf('%o', fileperms($path)), -4) . ")\n";
    if (!$writable) {
        $problems[] = "La carpeta $relative no tiene permiso de escritura.";
    }
}

echo "\n=== Resultado ===\n";

foreach ($warnings as $warning) {
    echo "AVISO: $warning\n";
}

foreach ($problems as $problem) {
    echo "PROBLEMA: $problem\n";
}

if ($problems === []) {
    echo $warnings === []
        ? "Todo correcto. Se puede continuar con el Paso B.\n"
        : "Sin problemas bloqueantes. Revisa los avisos antes de continuar con el Paso B.\n";
    exit(0);
}

echo "\nHay que resolver los problemas antes de continuar con el Paso B.\n";
exit(1);
