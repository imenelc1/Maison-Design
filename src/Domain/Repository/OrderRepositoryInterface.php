<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Order;

interface OrderRepositoryInterface
{
    public function findById(int $id): ?Order;

    public function findByUserId(int $userId): array;

    public function save(Order $order): int;

    public function updateStatus(int $id, string $status): void;

    public function saveLivraison(int $commandeId, string $adresse): void;

    /**
     * Récupère toutes les commandes avec info client pour admin
     */
    public function findAllWithClient(): array;
}
