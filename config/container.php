<?php

declare(strict_types=1);

use App\Core\Container;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Repository\ProductRepositoryInterface;
use App\Domain\Repository\OrderRepositoryInterface;
use App\Infrastructure\Database\PDOConnection;
use App\Infrastructure\Database\UserRepository;
use App\Infrastructure\Database\ProductRepository;
use App\Application\AuthService;
use App\Application\ProductService;
use App\Application\CartService;
use App\Application\OrderService;
use PDO;

$container = new Container();

// PDO — singleton, une seule connexion
$container->singleton(PDO::class, function() {
    return PDOConnection::getInstance()->getConnection();
});

// Repositories — on lie l'interface à l'implémentation concrète
$container->bind(UserRepositoryInterface::class, function($c) {
    return new UserRepository($c->make(PDO::class));
});

$container->bind(ProductRepositoryInterface::class, function($c) {
    return new ProductRepository($c->make(PDO::class));
});

// Services
$container->bind(AuthService::class, function($c) {
    return new AuthService(
        $c->make(UserRepositoryInterface::class)
    );
});

$container->bind(ProductService::class, function($c) {
    return new ProductService(
        $c->make(ProductRepositoryInterface::class)
    );
});

$container->bind(CartService::class, function($c) {
    return new CartService(
        $c->make(ProductRepositoryInterface::class)
    );
});

$container->bind(OrderService::class, function($c) {
    return new OrderService(
        $c->make(OrderRepositoryInterface::class),
        $c->make(ProductRepositoryInterface::class)
    );
});

return $container;