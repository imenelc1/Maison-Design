<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/vendor/autoload.php';

// Charger les variables d'environnement
$dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();

// Démarrer la session
session_start();

// Charger le container
$container = require ROOT_PATH . '/config/container.php';

// Charger le router
$router = require ROOT_PATH . '/config/routes.php';

// Dispatcher la requête
$method = $_SERVER['REQUEST_METHOD'];
$uri    = $_SERVER['REQUEST_URI'];

$router->dispatch($method, $uri);