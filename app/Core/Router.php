<?php

declare(strict_types=1);

namespace JuegosTenerife\Core;

final class Router
{
    /**
     * @var array<string, list<array{pattern: string, params: list<string>, handler: callable|array{0: class-string, 1: string}}>>
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
        $params = [];
        $regex = preg_replace_callback(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#',
            static function (array $match) use (&$params): string {
                $params[] = $match[1];
                return '([^/]+)';
            },
            $path
        );

        $this->routes[$method][] = [
            'pattern' => '#^' . $regex . '$#',
            'params' => $params,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): Response
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $lookupMethod = $method === 'HEAD' ? 'GET' : $method;

        foreach ($this->routes[$lookupMethod] ?? [] as $route) {
            if (preg_match($route['pattern'], $path, $matches) !== 1) {
                continue;
            }

            $args = array_slice($matches, 1);
            $handler = $route['handler'];

            if (is_array($handler)) {
                [$className, $methodName] = $handler;
                $controller = new $className();

                return $controller->{$methodName}(...$args);
            }

            return $handler(...$args);
        }

        return new Response('Página no encontrada', 404);
    }
}
