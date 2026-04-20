<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use PDO;

class UserRepository implements UserRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                IdClient as id,
                Email as email,
                MDP as password,
                NomClient as nom,
                PrenomClient as prenom,
                NumTel as telephone,
                Adresse as adresse
            FROM client 
            WHERE IdClient = ?
        ");

        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $this->hydrate($row, 'client');
    }

    public function findByEmail(string $email): ?User
    {
        // Chercher d'abord dans les clients
        $stmt = $this->pdo->prepare("
            SELECT 
                IdClient as id,
                Email as email,
                MDP as password,
                NomClient as nom,
                PrenomClient as prenom,
                NumTel as telephone,
                Adresse as adresse
            FROM client 
            WHERE Email = ?
        ");

        $stmt->execute([$email]);
        $row = $stmt->fetch();

        if ($row) {
            return $this->hydrate($row, 'client');
        }

        // Si pas trouvé → chercher dans les admins
        $stmt = $this->pdo->prepare("
            SELECT 
                IdAdmin as id,
                Email as email,
                MotDePasse as password
            FROM admin 
            WHERE Email = ?
        ");

        $stmt->execute([$email]);
        $row = $stmt->fetch();

        if ($row) {
            // Admin n'a pas de nom/prenom dans la DB
            $row['nom']       = 'Administrateur';
            $row['prenom']    = 'Système';
            $row['telephone'] = '';
            $row['adresse']   = '';
            return $this->hydrate($row, 'admin');
        }

        return null;
    }

    public function save(User $user): void
    {
        if ($user->getId() === 0) {
            // Nouvel utilisateur → INSERT
            $stmt = $this->pdo->prepare("
                INSERT INTO client 
                    (NomClient, PrenomClient, Email, MDP, Adresse, NumTel, DateInscription)
                VALUES 
                    (?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $user->getNom(),
                $user->getPrenom(),
                $user->getEmail(),
                $user->getPassword(),
                $user->getAdresse(),
                $user->getTelephone(),
            ]);
        } else {
            // Utilisateur existant → UPDATE
            $stmt = $this->pdo->prepare("
                UPDATE client SET
                    NomClient     = ?,
                    PrenomClient  = ?,
                    Email         = ?,
                    Adresse       = ?,
                    NumTel        = ?
                WHERE IdClient = ?
            ");

            $stmt->execute([
                $user->getNom(),
                $user->getPrenom(),
                $user->getEmail(),
                $user->getAdresse(),
                $user->getTelephone(),
                $user->getId(),
            ]);
        }
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM client WHERE IdClient = ?
        ");
        $stmt->execute([$id]);
    }

    // Transforme un tableau DB en objet User
    private function hydrate(array $row, string $role): User
    {
        return new User(
            (int)$row['id'],
            $row['email'],
            $row['password'],
            $row['nom'],
            $row['prenom'],
            $role,
            $row['telephone'] ?? '',
            $row['adresse']   ?? ''
        );
    }
}