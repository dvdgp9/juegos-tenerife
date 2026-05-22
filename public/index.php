<?php

declare(strict_types=1);

use JuegosTenerife\Core\Application;
use JuegosTenerife\Core\Router;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = new Application(dirname(__DIR__));
$router = new Router();

require dirname(__DIR__) . '/routes/web.php';

$response = $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
$app->send($response);

