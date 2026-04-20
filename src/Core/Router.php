<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function get(string $path, string $controller, string $method): self
    {
        $this->routes['GET'][$path] = [$controller, $method];
        return $this;
    }

    public function post(string $path, string $controller, string $method): self
    {
        $this->routes['POST'][$path] = [$controller, $method];
        return $this;
    }

    public function dispatch(string $httpMethod, string $uri): void
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/');

        if (empty($uri)) {
            $uri = '/';
        }

        $routes = $this->routes[$httpMethod] ?? [];

        foreach ($routes as $pattern => $handler) {
            $regex = $this->patternToRegex($pattern);

            if (preg_match($regex, $uri, $matches)) {
                $params = array_filter(
                    $matches,
                    fn($key) => !is_int($key),
                    ARRAY_FILTER_USE_KEY
                );

                [$controllerClass, $method] = $handler;

                // Utiliser le Container pour créer le Controller
                $controller = $this->container->make($controllerClass);
                $controller->$method($params);
                return;
            }
        }

        $this->notFound();
    }

    private function patternToRegex(string $pattern): string
    {
        $regex = preg_replace(
            '/\{(\w+)\}/',
            '(?P<$1>[^/]+)',
            $pattern
        );

        return '#^' . $regex . '$#';
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo "404 — Page introuvable";
    }
}