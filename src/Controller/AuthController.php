<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\AuthService;
use App\Application\OrderService;

class AuthController extends Controller
{
    private AuthService $authService;
    private OrderService $orderService;

    public function __construct(AuthService $authService, OrderService $orderService)
    {
        parent::__construct();
        $this->authService = $authService;
        $this->orderService = $orderService;
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
    // POST /connexion
public function login(): void
{
    $this->requireCsrf();

    $email    = $this->request->getString('email');
    $password = $this->request->post('password', '');

    $v = new \App\Core\Validator();
    $v->required($email, 'email')
      ->email($email)
      ->required($password, 'mot de passe');

    if (!$v->isValid()) {
        $this->setFlash('error', $v->getFirstError());
        $this->redirect('/connexion');
        return;
    }

    $user = $this->authService->login($email, $password);

    if ($user === null) {
        $this->setFlash('error', 'Email ou mot de passe incorrect');
        $this->redirect('/connexion');
        return;
    }

    $_SESSION['user_id']   = $user->getId();
    $_SESSION['role']      = $user->getRole();
    $_SESSION['nom']       = $user->getNom();
    $_SESSION['prenom']    = $user->getPrenom();
    $_SESSION['email']     = $user->getEmail();
    $_SESSION['telephone'] = $user->getTelephone();
    $_SESSION['adresse']   = $user->getAdresse();

    $this->redirect($user->isAdmin() ? '/admin' : '/');
}

// POST /inscription
public function register(): void
{
    $this->requireCsrf();

    $email           = $this->request->getString('email');
    $password        = $this->request->post('password', '');
    $confirmPassword = $this->request->post('confirm-password', '');
    $nom             = $this->request->getString('nom');
    $prenom          = $this->request->getString('prenom');
    $telephone       = $this->request->getString('numtel');
    $adresse         = $this->request->getString('adresse');

    $v = new \App\Core\Validator();
    $v->required($nom, 'nom')
      ->maxLength($nom, 50, 'nom')
      ->required($prenom, 'prénom')
      ->maxLength($prenom, 50, 'prénom')
      ->required($email, 'email')
      ->email($email)
      ->required($password, 'mot de passe')
      ->minLength($password, 6, 'mot de passe')
      ->matches($password, $confirmPassword, 'confirmation')
      ->required($telephone, 'téléphone')
      ->phone($telephone)
      ->required($adresse, 'adresse')
      ->minLength($adresse, 3, 'adresse');

  if (!$v->isValid()) {
    $_SESSION['form_old'] = [
        'nom'       => $nom,
        'prenom'    => $prenom,
        'email'     => $email,
        'numtel'    => $telephone,
        'adresse'   => $adresse,
    ];
    $this->setFlash('error', $v->getFirstError());
    $this->redirect('/inscription');
    return;
}

if ($this->authService->emailExiste($email)) {
    $_SESSION['form_old'] = [
        'nom'       => $nom,
        'prenom'    => $prenom,
        'email'     => $email,
        'numtel'    => $telephone,
        'adresse'   => $adresse,
    ];
    $this->setFlash('error', 'Cette adresse email est déjà utilisée');
    $this->redirect('/inscription');
    return;
}
    $user = $this->authService->register(
        $email, $password, $nom, $prenom, $telephone, $adresse
    );

    $userFromDb = $this->authService->findByEmail($email);

    if ($userFromDb === null) {
        $this->setFlash('error', 'Erreur lors de la création du compte');
        $this->redirect('/inscription');
        return;
    }

    $_SESSION['user_id']   = $userFromDb->getId();
    $_SESSION['role']      = $userFromDb->getRole();
    $_SESSION['nom']       = $userFromDb->getNom();
    $_SESSION['prenom']    = $userFromDb->getPrenom();
    $_SESSION['email']     = $userFromDb->getEmail();
    $_SESSION['telephone'] = $userFromDb->getTelephone();
    $_SESSION['adresse']   = $userFromDb->getAdresse();

    $this->setFlash('success', 'Inscription réussie ! Bienvenue ' . $userFromDb->getPrenom());
    $this->redirect('/');
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

       // GET /deconnexion
    public function logout(): void
    {
        session_destroy();
        $this->redirect('/connexion');
    }

    // GET /compte
    public function compte(): void
    {
        $this->requireClient();

        $userId = $this->request->getUserId();
        $orders = $userId !== null
            ? $this->orderService->getCommandesUser($userId)
            : [];

        $this->render('pages/client', [
            'flash'     => $this->getFlash(),
            'activeTab' => $this->request->get('tab', 'profile'),
            'orders'    => $orders,
        ]);
    }
}
