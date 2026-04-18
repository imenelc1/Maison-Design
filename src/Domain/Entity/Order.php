<?php

declare(strict_types=1);

namespace App\Domain\Entity;

class Order
{
    private int $id;
    private int $userId;
    private float $totalPrix;
    private string $status;
    private string $dateCommande;
    private array $items;

    public function __construct(
        int $id,
        int $userId,
        float $totalPrix,
        string $status = 'en attente',
        string $dateCommande = '',
        array $items = []
    ) {
        $this->id            = $id;
        $this->userId        = $userId;
        $this->totalPrix     = $totalPrix;
        $this->status        = $status;
        $this->dateCommande  = $dateCommande;
        $this->items         = $items;
    }

    // Getters
    public function getId(): int             { return $this->id; }
    public function getUserId(): int         { return $this->userId; }
    public function getTotalPrix(): float    { return $this->totalPrix; }
    public function getStatus(): string      { return $this->status; }
    public function getDateCommande(): string { return $this->dateCommande; }
    public function getItems(): array        { return $this->items; }

    // Méthodes métier
    public function isEnAttente(): bool
    {
        return $this->status === 'en attente';
    }

    public function isLivree(): bool
    {
        return $this->status === 'livré';
    }

    public function isAnnulee(): bool
    {
        return $this->status === 'annulé';
    }

    public function getTotalFormate(): string
    {
        return number_format($this->totalPrix, 2, ',', ' ') . ' DA';
    }
}