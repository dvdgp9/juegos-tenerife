<?php

declare(strict_types=1);

namespace JuegosTenerife\Core;

final class View
{
    /**
     * @param array<string, mixed> $data
     */
    public static function render(string $template, array $data = []): Response
    {
        $viewPath = dirname(__DIR__) . '/Views/' . ltrim($template, '/') . '.php';

        if (!is_file($viewPath)) {
            return new Response('Vista no encontrada: ' . htmlspecialchars($template, ENT_QUOTES, 'UTF-8'), 500);
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewPath;
        $body = (string) ob_get_clean();

        return new Response($body);
    }
}

