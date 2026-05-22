<?php

declare(strict_types=1);

namespace JuegosTenerife\Services\Support;

final class Slugger
{
    public static function slug(string $value): string
    {
        $value = trim($value);
        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $slug = strtolower((string) $transliterated);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : 'sin-nombre';
    }
}

