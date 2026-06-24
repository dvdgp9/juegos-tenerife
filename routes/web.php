<?php

declare(strict_types=1);

use JuegosTenerife\Controllers\HomeController;
use JuegosTenerife\Controllers\EntityController;
use JuegosTenerife\Controllers\ModalityController;
use JuegosTenerife\Controllers\SearchController;
use JuegosTenerife\Controllers\Admin\AuthController;
use JuegosTenerife\Controllers\Admin\DashboardController;
use JuegosTenerife\Controllers\Admin\EntityController as AdminEntityController;
use JuegosTenerife\Controllers\Admin\FacilityController as AdminFacilityController;
use JuegosTenerife\Controllers\Admin\ImportController;
use JuegosTenerife\Controllers\Admin\ModalityController as AdminModalityController;
use JuegosTenerife\Controllers\Admin\UserController as AdminUserController;

$router->get('/', [HomeController::class, 'index']);
$router->get('/busqueda', [SearchController::class, 'index']);
$router->get('/modalidades/{slug}', [ModalityController::class, 'show']);
$router->get('/entidades/{slug}', [EntityController::class, 'show']);
$router->get('/admin/login', [AuthController::class, 'login']);
$router->post('/admin/login', [AuthController::class, 'authenticate']);
$router->post('/admin/logout', [AuthController::class, 'logout']);
$router->get('/admin', [DashboardController::class, 'index']);
$router->get('/admin/entities', [AdminEntityController::class, 'index']);
$router->get('/admin/entities/new', [AdminEntityController::class, 'create']);
$router->post('/admin/entities', [AdminEntityController::class, 'store']);
$router->get('/admin/entities/{id}/edit', [AdminEntityController::class, 'edit']);
$router->post('/admin/entities/{id}', [AdminEntityController::class, 'update']);
$router->get('/admin/facilities', [AdminFacilityController::class, 'index']);
$router->get('/admin/facilities/new', [AdminFacilityController::class, 'create']);
$router->post('/admin/facilities', [AdminFacilityController::class, 'store']);
$router->get('/admin/facilities/{id}/edit', [AdminFacilityController::class, 'edit']);
$router->post('/admin/facilities/{id}', [AdminFacilityController::class, 'update']);
$router->get('/admin/modalities', [AdminModalityController::class, 'index']);
$router->get('/admin/modalities/new', [AdminModalityController::class, 'create']);
$router->post('/admin/modalities', [AdminModalityController::class, 'store']);
$router->get('/admin/modalities/{id}/edit', [AdminModalityController::class, 'edit']);
$router->post('/admin/modalities/{id}', [AdminModalityController::class, 'update']);
$router->get('/admin/users', [AdminUserController::class, 'index']);
$router->get('/admin/users/new', [AdminUserController::class, 'create']);
$router->post('/admin/users', [AdminUserController::class, 'store']);
$router->get('/admin/users/{id}/edit', [AdminUserController::class, 'edit']);
$router->post('/admin/users/{id}', [AdminUserController::class, 'update']);
$router->get('/admin/import', [ImportController::class, 'index']);
$router->post('/admin/import/preview', [ImportController::class, 'preview']);
$router->post('/admin/import/confirm', [ImportController::class, 'confirm']);
