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
            // star
            'star' => '<path d="M11.5 3.2a.6.6 0 0 1 1 0l2.3 4.7 5.2.8a.6.6 0 0 1 .3 1l-3.7 3.6.9 5.1a.6.6 0 0 1-.9.7L12 16.7l-4.6 2.4a.6.6 0 0 1-.9-.7l.9-5.1-3.7-3.6a.6.6 0 0 1 .3-1l5.2-.8z"/>',
            // image-up
            'image-up' => '<path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7"/>'
                . '<path d="m3 17 5-5c.9-.9 2.1-.9 3 0l5 5"/><circle cx="9" cy="8" r="1.5"/>'
                . '<path d="M18 9V3"/><path d="m15.5 5.5 2.5-2.5 2.5 2.5"/>',
            // trash-2
            'trash' => '<path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/>'
                . '<path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/>',
            // arrow-up
            'arrow-up' => '<path d="M12 19V5"/><path d="m5 12 7-7 7 7"/>',
            // arrow-down
            'arrow-down' => '<path d="M12 5v14"/><path d="m19 12-7 7-7-7"/>',
            // rotate-ccw
            'undo' => '<path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/>',
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
