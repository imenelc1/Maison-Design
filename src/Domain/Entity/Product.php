<?php

declare(strict_types=1);

namespace App\Domain\Entity;

class Product
{
    private int $id;
    private string $nom;
    private string $description;
    private float $prix;
    private int $stock;
    private string $categorie;
    private string $image;

    public function __construct(
        int $id,
        string $nom,
        string $description,
        float $prix,
        int $stock,
        string $categorie = '',
        string $image = ''
    ) {
        $this->id          = $id;
        $this->nom         = $nom;
        $this->description = $description;
        $this->prix        = $prix;
        $this->stock       = $stock;
        $this->categorie   = $categorie;
        $this->image       = $image;
    }

    // Getters
    public function getId(): int            { return $this->id; }
    public function getNom(): string        { return $this->nom; }
    public function getDescription(): string { return $this->description; }
    public function getPrix(): float        { return $this->prix; }
    public function getStock(): int         { return $this->stock; }
    public function getCategorie(): string  { return $this->categorie; }
    public function getImage(): string      { return $this->image; }

    // Méthodes métier
    public function isDisponible(): bool
    {
        return $this->stock > 0;
    }

    public function getPrixFormate(): string
    {
        return number_format($this->prix, 2, ',', ' ') . ' DA';
    }
}