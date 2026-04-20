<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;

class AuthService
{
    // AuthService a besoin d'un UserRepository pour trouver les users
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Récupère tous les clients pour admin
     */
    public function getAllClients(): array
    {
        return $this->userRepository->findAll();
    }

    public function deleteClient(int $id): void
    {
        $this->userRepository->delete($id);
    }
    

    /**
     * Tente de connecter un utilisateur
     * Retourne le User si succès, null si échec
     */
    public function login(string $email, string $password): ?User
    {
        // 1. Trouver le user par email
        $user = $this->userRepository->findByEmail($email);

        // 2. Si pas trouvé → échec
        if ($user === null) {
            return null;
        }

        // 3. Vérifier le mot de passe
        if (!password_verify($password, $user->getPassword())) {
            return null;
        }

        // 4. Tout est bon → retourner le user
        return $user;
    }

    /**
     * Inscrit un nouvel utilisateur
     */
    public function register(
        string $email,
        string $password,
        string $nom,
        string $prenom,
        string $telephone,
        string $adresse
    ): User {
        // Hasher le mot de passe avant de sauvegarder
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $user = new User(
            0, 
            $email,
            $hashedPassword,
            $nom,
            $prenom,
            'client',
            $telephone,
            $adresse
        );

        // Sauvegarder via le repository
        $this->userRepository->save($user);

        return $user;
    }

    /**
     * Vérifie si un email existe déjà
     */
    public function emailExiste(string $email): bool
    {
        return $this->userRepository->findByEmail($email) !== null;
    }
    public function findByEmail(string $email): ?User
{
    return $this->userRepository->findByEmail($email);
}
}