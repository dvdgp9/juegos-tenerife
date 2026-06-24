<?php

declare(strict_types=1);

namespace JuegosTenerife\Services;

use RuntimeException;

final class MediaUploadService
{
    private const MAX_BYTES = 8388608;

    /** @var array<string, string> */
    private const ALLOWED_MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    /**
     * @param array<string, mixed> $file
     * @return array{original_name: string, file_path: string, mime_type: string, file_size: int}
     */
    public function storeImage(array $file, string $folder): array
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

        if (@getimagesize($tmpName) === false) {
            throw new RuntimeException('El archivo no parece ser una imagen válida.');
        }

        $folder = trim($folder, '/');
        if ($folder === '' || str_contains($folder, '..')) {
            throw new RuntimeException('Carpeta de destino no válida.');
        }

        $publicRoot = dirname(__DIR__, 2) . '/public';
        $targetDir = $publicRoot . '/uploads/' . $folder;
        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
            throw new RuntimeException('No se pudo crear la carpeta de subidas.');
        }

        $extension = self::ALLOWED_MIME_EXTENSIONS[$mimeType];
        $filename = date('Ymd-His') . '-' . bin2hex(random_bytes(8)) . '.' . $extension;
        $targetPath = $targetDir . '/' . $filename;

        if (!move_uploaded_file($tmpName, $targetPath)) {
            throw new RuntimeException('No se pudo guardar la imagen subida.');
        }

        chmod($targetPath, 0644);

        return [
            'original_name' => (string) ($file['name'] ?? $filename),
            'file_path' => '/uploads/' . $folder . '/' . $filename,
            'mime_type' => $mimeType,
            'file_size' => $size,
        ];
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
