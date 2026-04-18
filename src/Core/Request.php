<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    private array $getParams;
    private array $postParams;
    private array $serverParams;
    private array $sessionParams;

    public function __construct()
    {
        $this->getParams    = $_GET    ?? [];
        $this->postParams   = $_POST   ?? [];
        $this->serverParams = $_SERVER ?? [];
        $this->sessionParams = isset($_SESSION) ? $_SESSION : [];
    }

    // Récupérer un paramètre GET
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->getParams[$key] ?? $default;
    }

    // Récupérer un paramètre POST
    public function post(string $key, mixed $default = null): mixed
    {
        return $this->postParams[$key] ?? $default;
    }

    // Récupérer la méthode HTTP (GET, POST...)
    public function getMethod(): string
    {
        return strtoupper($this->serverParams['REQUEST_METHOD'] ?? 'GET');
    }

    // Récupérer l'URI
    public function getUri(): string
    {
        return $this->serverParams['REQUEST_URI'] ?? '/';
    }

    // Vérifier si c'est une requête POST
    public function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }

    // Vérifier si c'est une requête AJAX
    public function isAjax(): bool
    {
        return ($this->serverParams['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    // Récupérer une variable de session
    public function session(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    // Définir une variable de session
    public function setSession(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    // Supprimer une variable de session
    public function removeSession(string $key): void
    {
        unset($_SESSION[$key]);
    }

    // Vérifier si l'utilisateur est connecté
    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    // Récupérer l'ID de l'utilisateur connecté
    public function getUserId(): ?int
    {
        return isset($_SESSION['user_id']) 
            ? (int)$_SESSION['user_id'] 
            : null;
    }

    // Récupérer le rôle de l'utilisateur connecté
    public function getUserRole(): string
    {
        return $_SESSION['role'] ?? 'guest';
    }

    // Nettoyer et valider une valeur string
    public function getString(string $key, string $from = 'post'): string
    {
        $value = $from === 'post' 
            ? $this->post($key, '') 
            : $this->get($key, '');
            
        return htmlspecialchars(trim((string)$value));
    }

    // Récupérer un entier
    public function getInt(string $key, string $from = 'get'): int
    {
        $value = $from === 'post'
            ? $this->post($key, 0)
            : $this->get($key, 0);

        return (int)$value;
    }
}