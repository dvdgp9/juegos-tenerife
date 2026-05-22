<?php

declare(strict_types=1);

use JuegosTenerife\Controllers\HomeController;
use JuegosTenerife\Controllers\EntityController;
use JuegosTenerife\Controllers\SearchController;
use JuegosTenerife\Controllers\Admin\AuthController;
use JuegosTenerife\Controllers\Admin\DashboardController;
use JuegosTenerife\Controllers\Admin\EntityController as AdminEntityController;
use JuegosTenerife\Controllers\Admin\ImportController;

$router->get('/', [HomeController::class, 'index']);
$router->get('/busqueda', [SearchController::class, 'index']);
$router->get('/entidades/federacion-arrastre-canario', [EntityController::class, 'show']);
$router->get('/admin/login', [AuthController::class, 'login']);
$router->post('/admin/login', [AuthController::class, 'authenticate']);
$router->post('/admin/logout', [AuthController::class, 'logout']);
$router->get('/admin', [DashboardController::class, 'index']);
$router->get('/admin/entities', [AdminEntityController::class, 'index']);
$router->get('/admin/import', [ImportController::class, 'index']);
$router->post('/admin/import/preview', [ImportController::class, 'preview']);
$router->post('/admin/import/confirm', [ImportController::class, 'confirm']);
