<?php

declare(strict_types=1);

namespace JuegosTenerife\Core;

final class Application
{
    public function __construct(private readonly string $basePath)
    {
        $this->loadEnvironment();
    }

    public function basePath(string $path = ''): string
    {
        return $this->basePath . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }

    public function send(Response $response): void
    {
        http_response_code($response->statusCode);

        foreach ($response->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $response->body;
    }

    private function loadEnvironment(): void
    {
        $envPath = $this->basePath('.env');

        if (!is_file($envPath)) {
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            $_ENV[$key] = $value;
            putenv($key . '=' . $value);
        }
    }
}
