<?php

declare(strict_types=1);

/**
 * Iconos del panel, en línea.
 *
 * Trazados tomados de Lucide (https://lucide.dev, licencia ISC). Se incrustan
 * como SVG en vez de cargar la librería para no añadir una dependencia ni una
 * petición externa por un puñado de iconos.
 */
if (!function_exists('admin_icon')) {
    function admin_icon(string $name): string
    {
        $paths = [
            // square-pen
            'edit' => '<path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>'
                . '<path d="M18.4 2.6a2 2 0 0 1 2.8 2.8L12 14.6l-3.6.9.9-3.6z"/>',
            // external-link
            'external' => '<path d="M15 3h6v6"/><path d="M10 14 21 3"/>'
                . '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>',
            // archive
            'archive' => '<rect width="20" height="5" x="2" y="3" rx="1"/>'
                . '<path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"/><path d="M10 12h4"/>',
        ];

        if (!isset($paths[$name])) {
            return '';
        }

        return '<svg class="admin-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"'
            . ' fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"'
            . ' stroke-linejoin="round" aria-hidden="true" focusable="false">'
            . $paths[$name]
            . '</svg>';
    }
}
