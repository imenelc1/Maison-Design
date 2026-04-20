<?php

declare(strict_types=1);

namespace App\Domain\Entity;

class User
{
    private int $id;
    private string $email;
    private string $password;
    private string $nom;
    private string $prenom;
    private string $role;
    private string $telephone;
    private string $adresse;

    public function __construct(
        int $id,
        string $email,
        string $password,
        string $nom,
        string $prenom,
        string $role = 'client',
        string $telephone = '',
        string $adresse = ''
    ) {
        $this->id        = $id;
        $this->email     = $email;
        $this->password  = $password;
        $this->nom       = $nom;
        $this->prenom    = $prenom;
        $this->role      = $role;
        $this->telephone = $telephone;
        $this->adresse   = $adresse;
    }

    // Getters — pour lire les données
    public function getId(): int            { return $this->id; }
    public function getEmail(): string      { return $this->email; }
    public function getPassword(): string   { return $this->password; }
    public function getNom(): string        { return $this->nom; }
    public function getPrenom(): string     { return $this->prenom; }
    public function getRole(): string       { return $this->role; }
    public function getTelephone(): string  { return $this->telephone; }
    public function getAdresse(): string    { return $this->adresse; }

    // Méthodes métier — des questions sur l'objet
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function getFullName(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }
}