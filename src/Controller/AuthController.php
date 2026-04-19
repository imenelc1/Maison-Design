<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\AuthService;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        parent::__construct();
        $this->authService = $authService;
    }

    // GET /connexion
    public function showLogin(): void
    {
        // Si déjà connecté → rediriger
        if ($this->request->isLoggedIn()) {
            $role = $this->request->getUserRole();
            $this->redirect($role === 'admin' ? '/admin' : '/');
        }

        $this->render('pages/auth/login', [
            'flash' => $this->getFlash(),
        ]);
    }

    // POST /connexion
    public function login(): void
    {
        $email    = $this->request->getString('email');
        $password = $this->request->post('password', '');

        if (empty($email) || empty($password)) {
            $this->setFlash('error', 'Veuillez remplir tous les champs');
            $this->redirect('/connexion');
            return;
        }

        $user = $this->authService->login($email, $password);

        if ($user === null) {
            $this->setFlash('error', 'Email ou mot de passe incorrect');
            $this->redirect('/connexion');
            return;
        }

        // Connexion réussie — stocker en session
        $_SESSION['user_id']    = $user->getId();
        $_SESSION['role']       = $user->getRole();
        $_SESSION['nom']        = $user->getNom();
        $_SESSION['prenom']     = $user->getPrenom();
        $_SESSION['email']      = $user->getEmail();
        $_SESSION['telephone']  = $user->getTelephone();
        $_SESSION['adresse']    = $user->getAdresse();

        $this->redirect($user->isAdmin() ? '/admin' : '/');
    }

    // GET /inscription
    public function showRegister(): void
    {
        if ($this->request->isLoggedIn()) {
            $this->redirect('/');
        }

        $this->render('pages/auth/register', [
            'flash' => $this->getFlash(),
        ]);
    }

    // POST /inscription
    public function register(): void
    {
        $email           = $this->request->getString('email');
        $password        = $this->request->post('password', '');
        $confirmPassword = $this->request->post('confirm-password', '');
        $nom             = $this->request->getString('nom');
        $prenom          = $this->request->getString('prenom');
        $telephone       = $this->request->getString('numtel');
        $adresse         = $this->request->getString('adresse');

        // Validations
        if (empty($email) || empty($password) || empty($nom) || empty($prenom)) {
            $this->setFlash('error', 'Veuillez remplir tous les champs');
            $this->redirect('/inscription');
            return;
        }

        if ($password !== $confirmPassword) {
            $this->setFlash('error', 'Les mots de passe ne correspondent pas');
            $this->redirect('/inscription');
            return;
        }

        if ($this->authService->emailExiste($email)) {
            $this->setFlash('error', 'Cette adresse email est déjà utilisée');
            $this->redirect('/inscription');
            return;
        }

        $user = $this->authService->register(
            $email, $password, $nom, $prenom, $telephone, $adresse
        );

        // Connecter automatiquement après inscription
        $_SESSION['user_id']   = $user->getId();
        $_SESSION['role']      = $user->getRole();
        $_SESSION['nom']       = $user->getNom();
        $_SESSION['prenom']    = $user->getPrenom();
        $_SESSION['email']     = $user->getEmail();
        $_SESSION['telephone'] = $user->getTelephone();
        $_SESSION['adresse']   = $user->getAdresse();

        $this->setFlash('success', 'Inscription réussie ! Bienvenue ' . $user->getPrenom());
        $this->redirect('/');
    }

    // GET /deconnexion
    public function logout(): void
    {
        session_destroy();
        $this->redirect('/connexion');
    }

    // GET /compte
    public function compte(): void
    {
        $this->requireAuth();

        $this->render('pages/client', [
            'flash'     => $this->getFlash(),
            'activeTab' => $this->request->get('tab', 'profile'),
        ]);
    }
}