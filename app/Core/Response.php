<?php

declare(strict_types=1);

namespace JuegosTenerife\Core;

final class Response
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        public readonly string $body,
        public readonly int $statusCode = 200,
        public readonly array $headers = ['Content-Type' => 'text/html; charset=UTF-8']
    ) {
    }

    public static function redirect(string $to, int $statusCode = 302): self
    {
        return new self('', $statusCode, ['Location' => $to]);
    }
}
