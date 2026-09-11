<?php

declare(strict_types=1);

namespace JuegosTenerife\Services\Media;

/**
 * Resultado de sincronizar una galería. El servicio no registra auditoría por su cuenta:
 * devuelve lo ocurrido y es el controlador, que sabe quién está en sesión, quien lo anota.
 */
final class MediaGalleryResult
{
    /**
     * @param list<string> $orphanedPaths Archivos que no se pudieron borrar del disco.
     */
    public function __construct(
        public readonly int $deleted,
        public readonly int $uploaded,
        public readonly array $orphanedPaths = [],
    ) {
    }
}
