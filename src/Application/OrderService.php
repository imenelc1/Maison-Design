<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Entity\Order;
use App\Domain\Repository\OrderRepositoryInterface;
use App\Domain\Repository\ProductRepositoryInterface;

class OrderService
{
    private OrderRepositoryInterface $orderRepository;
    private ProductRepositoryInterface $productRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->orderRepository   = $orderRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * Récupère toutes les commandes d'un utilisateur
     */
    public function getCommandesUser(int $userId): array
    {
        return $this->orderRepository->findByUserId($userId);
    }

    /**
     * Récupère une commande par son ID
     */
    public function getCommande(int $id): ?Order
    {
        return $this->orderRepository->findById($id);
    }

    /**
     * Crée une nouvelle commande depuis le panier
     */
    public function creerCommande(
    int $userId,
    array $items,
    float $fraisLivraison = 1000.0
): int {


    foreach ($items as $item) {
        $product = $this->productRepository->findById((int)$item['id']);

        if ($product === null) {
            throw new \RuntimeException(
                "Le produit {$item['nom']} n'existe plus"
            );
        }

        if ($product->getStock() < $item['quantite']) {
            throw new \RuntimeException(
                "Stock insuffisant pour {$product->getNom()}. 
                 Disponible: {$product->getStock()}"
            );
        }
    }

    $sousTotal = 0.0;
    foreach ($items as $item) {
        $sousTotal += $item['prix'] * $item['quantite'];
    }
    $total = $sousTotal + $fraisLivraison;

    $order = new Order(
        0,
        $userId,
        $total,
        'en attente',
        date('Y-m-d H:i:s'),
        $items
    );

    return $this->orderRepository->save($order);
}

    /**
     * Change le statut d'une commande
     */
    public function changerStatut(int $id, string $statut): void
    {
        $statutsValides = ['en attente', 'expédié', 'livré', 'annulé'];

        if (!in_array($statut, $statutsValides)) {
            throw new \RuntimeException("Statut invalide : {$statut}");
        }

        $this->orderRepository->updateStatus($id, $statut);
    }
    public function ajouterLivraison(int $commandeId, string $adresse): void
{
    $this->orderRepository->saveLivraison($commandeId, $adresse);
}

    /**
     * Récupère toutes les commandes pour admin
     */
    public function getAllOrders(): array
    {
        return $this->orderRepository->findAllWithClient();
    }
}
