<?php

declare(strict_types=1);

namespace JuegosTenerife\Core;

final class Router
{
    /**
     * @var array<string, array<string, callable|array{0: class-string, 1: string}>>
     */
    private array $routes = [];

    /**
     * @param callable|array{0: class-string, 1: string} $handler
     */
    public function get(string $path, callable|array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    /**
     * @param callable|array{0: class-string, 1: string} $handler
     */
    public function post(string $path, callable|array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    /**
     * @param callable|array{0: class-string, 1: string} $handler
     */
    private function add(string $method, string $path, callable|array $handler): void
    {
        $this->routes[$method][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): Response
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $lookupMethod = $method === 'HEAD' ? 'GET' : $method;
        $handler = $this->routes[$lookupMethod][$path] ?? null;

        if ($handler === null) {
            return new Response('Página no encontrada', 404);
        }

        if (is_array($handler)) {
            [$className, $methodName] = $handler;
            $controller = new $className();

            return $controller->{$methodName}();
        }

        return $handler();
    }
}
