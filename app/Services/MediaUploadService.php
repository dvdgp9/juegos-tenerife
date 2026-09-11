<?php

declare(strict_types=1);

namespace JuegosTenerife\Services;

use Imagick;
use ImagickException;
use RuntimeException;
use Throwable;

final class MediaUploadService
{
    private const MAX_BYTES = 8388608;

    /** Límite defensivo: una imagen puede ocupar poco comprimida y agotar la memoria al abrirse. */
    private const MAX_PIXELS = 50000000;

    /** Lado mayor de la versión que se publica en la web. */
    private const WEB_MAX_SIDE = 1600;

    /** Lado mayor de la miniatura que se usa en rejillas y listados. */
    private const THUMBNAIL_MAX_SIDE = 400;

    private const THUMBNAIL_SUFFIX = '-thumb';

    private const COMPRESSION_QUALITY = 82;

    /** @var array<string, string> */
    private const ALLOWED_MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    /**
     * Guarda una imagen subida: corrige su orientación, la reescala a tamaño web y,
     * si se pide, genera la miniatura. El archivo original no se conserva.
     *
     * @param array<string, mixed> $file Entrada de $_FILES.
     * @param string $folder Subcarpeta dentro de public/uploads, por ejemplo "entities/12".
     * @param bool $withThumbnail Las galerías la necesitan; un logo o un icono, no.
     * @return array{original_name: string, file_path: string, mime_type: string, file_size: int}
     */
    public function storeImage(array $file, string $folder, bool $withThumbnail = true): array
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_NO_FILE) {
            throw new RuntimeException('No se recibió ningún archivo.');
        }

        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('No se pudo subir la imagen. Código de error: ' . $error);
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new RuntimeException('La subida no es válida.');
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > self::MAX_BYTES) {
            throw new RuntimeException('La imagen debe pesar menos de 8 MB.');
        }

        $mimeType = $this->detectMimeType($tmpName);
        if (!isset(self::ALLOWED_MIME_EXTENSIONS[$mimeType])) {
            throw new RuntimeException('Formato no permitido. Usa JPG, PNG, WEBP o GIF.');
        }

        $dimensions = @getimagesize($tmpName);
        if ($dimensions === false) {
            throw new RuntimeException('El archivo no parece ser una imagen válida.');
        }

        if ((int) $dimensions[0] * (int) $dimensions[1] > self::MAX_PIXELS) {
            throw new RuntimeException('La imagen tiene demasiados píxeles. Reduce su tamaño antes de subirla.');
        }

        $folder = trim($folder, '/');
        if ($folder === '' || str_contains($folder, '..')) {
            throw new RuntimeException('Carpeta de destino no válida.');
        }

        $targetDir = $this->uploadsRoot() . '/' . $folder;
        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            throw new RuntimeException('No se pudo crear la carpeta de subidas.');
        }

        $extension = self::ALLOWED_MIME_EXTENSIONS[$mimeType];
        $filename = date('Ymd-His') . '-' . bin2hex(random_bytes(8)) . '.' . $extension;
        $targetPath = $targetDir . '/' . $filename;

        $this->process($tmpName, $targetPath, $withThumbnail);

        $finalSize = filesize($targetPath);

        return [
            'original_name' => (string) ($file['name'] ?? $filename),
            'file_path' => '/uploads/' . $folder . '/' . $filename,
            'mime_type' => $mimeType,
            'file_size' => $finalSize === false ? $size : $finalSize,
        ];
    }

    /**
     * Ruta de la miniatura que corresponde a una imagen ya guardada.
     * La miniatura no se registra en base de datos: se deriva del nombre.
     */
    public static function thumbnailPath(string $filePath): string
    {
        $extensionPosition = strrpos($filePath, '.');
        if ($extensionPosition === false) {
            return $filePath . self::THUMBNAIL_SUFFIX;
        }

        return substr($filePath, 0, $extensionPosition)
            . self::THUMBNAIL_SUFFIX
            . substr($filePath, $extensionPosition);
    }

    /**
     * Ruta que debe mostrarse en una rejilla: la miniatura si existe y, si no, la propia
     * imagen.
     *
     * El respaldo no es decorativo: las fotos subidas antes de que existiera el
     * redimensionado no tienen miniatura, y sin esto se verían rotas.
     */
    public static function displayThumbnail(string $filePath): string
    {
        if (!str_starts_with($filePath, '/uploads/')) {
            return $filePath;
        }

        $thumbnail = self::thumbnailPath($filePath);

        return is_file(self::publicRoot() . $thumbnail) ? $thumbnail : $filePath;
    }

    private static function publicRoot(): string
    {
        return dirname(__DIR__, 2) . '/public';
    }

    /**
     * Carpeta raíz de subidas, ya resuelta. Cualquier borrado debe comprobarse contra ella.
     */
    public static function uploadsRoot(): string
    {
        return self::publicRoot() . '/uploads';
    }

    /**
     * Borra del disco una imagen publicada y su miniatura.
     *
     * Es idempotente: si el archivo ya no está, se considera hecho. Devuelve false solo
     * cuando la ruta no es aceptable o el borrado falla de verdad, para que quien llama
     * pueda registrarlo sin interrumpir la operación principal.
     *
     * @param string $filePath Ruta pública tal y como se guarda en media_files, por
     *                         ejemplo "/uploads/entities/12/20260911-abc.jpg".
     */
    public function deleteStoredImage(string $filePath): bool
    {
        $mainPath = $this->resolveUploadPath($filePath);
        if ($mainPath === null) {
            return false;
        }

        $deleted = $this->unlinkIfPresent($mainPath);

        // La miniatura no está en base de datos: si no se borra aquí, no la borra nadie.
        $thumbnailPath = $this->resolveUploadPath(self::thumbnailPath($filePath));
        if ($thumbnailPath !== null) {
            $this->unlinkIfPresent($thumbnailPath);
        }

        return $deleted;
    }

    /**
     * Convierte una ruta pública en ruta de disco, rechazando todo lo que no esté
     * claramente dentro de public/uploads.
     *
     * Se comprueba dos veces a propósito: primero la ruta declarada, para poder rechazar
     * un valor manipulado aunque el archivo no exista, y después la ruta real, que además
     * descarta enlaces simbólicos que apunten fuera.
     */
    private function resolveUploadPath(string $filePath): ?string
    {
        $prefix = '/uploads/';
        if (!str_starts_with($filePath, $prefix)) {
            return null;
        }

        $relative = substr($filePath, strlen($prefix));
        if ($relative === '' || str_contains($relative, "\0")) {
            return null;
        }

        foreach (explode('/', $relative) as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                return null;
            }
        }

        $root = self::uploadsRoot();
        $candidate = $root . '/' . $relative;

        if (!is_file($candidate)) {
            // No existe: no hay nada que borrar, pero tampoco es un error.
            return $candidate;
        }

        $realRoot = realpath($root);
        $realCandidate = realpath($candidate);
        if ($realRoot === false || $realCandidate === false) {
            return null;
        }

        if (!str_starts_with($realCandidate, $realRoot . DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $realCandidate;
    }

    private function unlinkIfPresent(string $path): bool
    {
        if (!is_file($path)) {
            return true;
        }

        return @unlink($path);
    }

    private function process(string $sourcePath, string $targetPath, bool $withThumbnail): void
    {
        $image = null;

        try {
            $image = new Imagick($sourcePath);

            // Una imagen ya comprimida no debe recomprimirse por encima de su calidad real:
            // no ganaría tamaño y sí perdería definición en cada paso.
            $sourceQuality = $image->getImageCompressionQuality();

            // Rota físicamente según los metadatos y deja el resto de EXIF fuera: evita que
            // las fotos verticales de móvil salgan tumbadas y no publica la geolocalización.
            $image->autoOrient();
            $image->stripImage();

            $this->resizeToLongestSide($image, self::WEB_MAX_SIDE);
            $this->writeImage($image, $targetPath, $sourceQuality);

            if ($withThumbnail) {
                $this->writeThumbnail($image, self::thumbnailPath($targetPath), $sourceQuality);
            }
        } catch (ImagickException $exception) {
            $this->deleteQuietly($targetPath);
            $this->deleteQuietly(self::thumbnailPath($targetPath));

            throw new RuntimeException('No se pudo procesar la imagen: ' . $exception->getMessage(), 0, $exception);
        } finally {
            $image?->clear();
        }
    }

    /**
     * Reescala manteniendo la proporción. Nunca amplía: una imagen ya pequeña se deja igual.
     */
    private function resizeToLongestSide(Imagick $image, int $maxSide): void
    {
        $width = $image->getImageWidth();
        $height = $image->getImageHeight();
        $longest = max($width, $height);

        if ($longest <= $maxSide) {
            return;
        }

        $ratio = $maxSide / $longest;
        $targetWidth = max(1, (int) round($width * $ratio));
        $targetHeight = max(1, (int) round($height * $ratio));

        if ($image->getNumberImages() > 1) {
            // Un GIF animado hay que recorrerlo fotograma a fotograma o se pierde la animación.
            $image->setFirstIterator();
            foreach ($image as $frame) {
                $frame->resizeImage($targetWidth, $targetHeight, Imagick::FILTER_LANCZOS, 1);
            }
            $image->setFirstIterator();

            return;
        }

        $image->resizeImage($targetWidth, $targetHeight, Imagick::FILTER_LANCZOS, 1);
    }

    private function writeImage(Imagick $image, string $targetPath, int $sourceQuality = 0): void
    {
        $format = strtolower($image->getImageFormat());
        if ($format === 'jpeg' || $format === 'webp') {
            $image->setImageCompressionQuality($this->qualityFor($sourceQuality));
        }

        $written = $image->getNumberImages() > 1
            ? $image->writeImages($targetPath, true)
            : $image->writeImage($targetPath);

        if (!$written) {
            throw new RuntimeException('No se pudo guardar la imagen procesada.');
        }

        chmod($targetPath, 0644);
    }

    private function writeThumbnail(Imagick $image, string $targetPath, int $sourceQuality = 0): void
    {
        $thumbnail = clone $image;

        try {
            // La miniatura de un GIF animado se queda en el primer fotograma a propósito:
            // en una rejilla de galería no aporta nada animarla y pesa mucho más.
            $thumbnail->setFirstIterator();
            $single = $thumbnail->getNumberImages() > 1
                ? $thumbnail->getImage()
                : $thumbnail;

            $this->resizeToLongestSide($single, self::THUMBNAIL_MAX_SIDE);

            $format = strtolower($single->getImageFormat());
            if ($format === 'jpeg' || $format === 'webp') {
                $single->setImageCompressionQuality($this->qualityFor($sourceQuality));
            }

            if (!$single->writeImage($targetPath)) {
                throw new RuntimeException('No se pudo guardar la miniatura.');
            }

            chmod($targetPath, 0644);
        } finally {
            $thumbnail->clear();
        }
    }

    /**
     * Nunca recomprime por encima de la calidad que ya traía la imagen. Una foto guardada
     * al 70 % no mejora al reescribirla al 82 %: solo ocuparía más.
     */
    private function qualityFor(int $sourceQuality): int
    {
        if ($sourceQuality > 0 && $sourceQuality < self::COMPRESSION_QUALITY) {
            return $sourceQuality;
        }

        return self::COMPRESSION_QUALITY;
    }

    private function deleteQuietly(string $path): void
    {
        try {
            if (is_file($path)) {
                @unlink($path);
            }
        } catch (Throwable) {
            // Limpieza de un fallo previo: si tampoco se puede borrar, no hay nada mejor que hacer.
        }
    }

    private function detectMimeType(string $path): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            throw new RuntimeException('No se pudo validar el tipo de archivo.');
        }

        $mimeType = finfo_file($finfo, $path);
        finfo_close($finfo);

        if (!is_string($mimeType) || $mimeType === '') {
            throw new RuntimeException('No se pudo detectar el tipo de archivo.');
        }

        return $mimeType;
    }
}
