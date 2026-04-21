<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Request;

abstract class Controller
{
    protected Request $request;

    public function __construct()
    {
        $this->request = new Request();
    }

    /**
     * Afficher une vue
     */
    protected function render(string $view, array $data = []): void
    {
        // Rendre les variables disponibles dans la vue
        extract($data);

        $viewPath = ROOT_PATH . '/src/View/' . $view . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("Vue introuvable : {$view}");
        }

        // Charger le layout avec la vue dedans
        require ROOT_PATH . '/src/View/layouts/header.php';
        require $viewPath;
        require ROOT_PATH . '/src/View/layouts/footer.php';
    }

    /**
     * Rediriger vers une URL
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit();
    }

    /**
     * Réponse JSON pour les requêtes AJAX
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    /**
     * Vérifier si l'utilisateur est connecté
     * Sinon rediriger vers /connexion
     */
    protected function requireAuth(): void
    {
        if (!$this->request->isLoggedIn()) {
            $this->redirect('/connexion');
        }
    }

    /**
     * Vérifier si l'utilisateur est admin
     */
    protected function requireAdmin(): void
    {
        if (!$this->request->isLoggedIn() || 
            $this->request->getUserRole() !== 'admin') {
            $this->redirect('/');
        }
    }

    protected function requireClient(): void
    {
        if (!$this->request->isLoggedIn()) {
            $this->redirect('/connexion');
        }

        if ($this->request->getUserRole() === 'admin') {
            $this->setFlash('error', 'Cette section est reservee aux clients');
            $this->redirect('/admin');
        }
    }
    protected function requireCsrf(): void
{
    if (!$this->request->verifyCsrf()) {
        $this->setFlash('error', 'Token de sécurité invalide. Réessayez.');
        // Retourner en arrière
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }
}

    /**
     * Stocker un message flash
     * Affiché une seule fois puis supprimé
     */
    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type'    => $type,
            'message' => $message,
        ];
    }

    /**
     * Récupérer et supprimer le message flash
     */
    protected function getFlash(): ?array
    {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}
