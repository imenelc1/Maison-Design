<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();

session_start();

// Charger le container
$container = require ROOT_PATH . '/config/container.php';

// Charger le router en lui passant le container
$router = new App\Core\Router($container);

// Charger les routes
$routeLoader = require ROOT_PATH . '/config/routes.php';
$routeLoader($router);

// Dispatcher
$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI']
);