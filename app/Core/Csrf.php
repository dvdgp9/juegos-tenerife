<?php

declare(strict_types=1);

namespace JuegosTenerife\Core;

final class Csrf
{
    public static function token(): string
    {
        $token = Session::get('_csrf');

        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::put('_csrf', $token);
        }

        return $token;
    }

    public static function verify(?string $token): bool
    {
        $sessionToken = Session::get('_csrf');

        return is_string($token)
            && is_string($sessionToken)
            && hash_equals($sessionToken, $token);
    }
}

