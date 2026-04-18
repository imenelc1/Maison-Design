<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    // Enregistrer une route GET
    public function get(string $path, string $controller, string $method): self
    {
        $this->routes['GET'][$path] = [$controller, $method];
        return $this;
    }

    // Enregistrer une route POST
    public function post(string $path, string $controller, string $method): self
    {
        $this->routes['POST'][$path] = [$controller, $method];
        return $this;
    }

    // Trouver et exécuter le bon controller
    public function dispatch(string $httpMethod, string $uri): void
    {
        // Nettoyer l'URI — enlever les paramètres GET
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/');

        if (empty($uri)) {
            $uri = '/';
        }

        $routes = $this->routes[$httpMethod] ?? [];

        foreach ($routes as $pattern => $handler) {
            // Convertir /produit/{id} en regex
            $regex = $this->patternToRegex($pattern);

            if (preg_match($regex, $uri, $matches)) {
                // Extraire les paramètres dynamiques
                $params = array_filter(
                    $matches,
                    fn($key) => !is_int($key),
                    ARRAY_FILTER_USE_KEY
                );

                [$controllerClass, $method] = $handler;

                // Créer le controller et appeler la méthode
                $controller = new $controllerClass();
                $controller->$method($params);
                return;
            }
        }

        // Aucune route trouvée → 404
        $this->notFound();
    }

    // Convertit /produit/{id} en regex
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