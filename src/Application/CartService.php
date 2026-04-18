<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Entity\Product;
use App\Domain\Repository\ProductRepositoryInterface;

class CartService
{
    private ProductRepositoryInterface $productRepository;

    private const SESSION_KEY = 'panier';

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;

        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }
    }

    /**
     * Ajoute un produit au panier
     */
    public function ajouter(int $produitId, int $quantite = 1): void
    {
        $product = $this->productRepository->findById($produitId);

        if ($product === null) {
            throw new \RuntimeException("Produit introuvable");
        }

        if (!$product->isDisponible()) {
            throw new \RuntimeException(
                "Le produit {$product->getNom()} n'est plus disponible"
            );
        }

        $panier = $_SESSION[self::SESSION_KEY];

        // Chercher si le produit est déjà dans le panier
        foreach ($panier as $index => $item) {
            if ((int)$item['id'] === $produitId) {
                // Produit déjà présent — augmenter la quantité
                $panier[$index]['quantite'] += $quantite;
                $_SESSION[self::SESSION_KEY] = $panier;
                return;
            }
        }

        // Produit pas encore dans le panier — l'ajouter
        $_SESSION[self::SESSION_KEY][] = [
            'id'       => $produitId,
            'nom'      => $product->getNom(),
            'prix'     => $product->getPrix(),
            'image'    => $product->getImage(),
            'quantite' => $quantite,
        ];
    }

    /**
     * Supprime un produit du panier
     */
    public function supprimer(int $produitId): void
    {
        $_SESSION[self::SESSION_KEY] = array_values(
            array_filter(
                $_SESSION[self::SESSION_KEY],
                fn($item) => (int)$item['id'] !== $produitId
            )
        );
    }

    /**
     * Modifie la quantité d'un produit
     */
    public function modifierQuantite(int $produitId, int $delta): void
    {
        foreach ($_SESSION[self::SESSION_KEY] as $index => $item) {
            if ((int)$item['id'] === $produitId) {
                $nouvelleQuantite = $item['quantite'] + $delta;

                if ($nouvelleQuantite <= 0) {
                    $this->supprimer($produitId);
                    return;
                }

                $_SESSION[self::SESSION_KEY][$index]['quantite'] = $nouvelleQuantite;
                return;
            }
        }
    }

    /**
     * Vide le panier
     */
    public function vider(): void
    {
        $_SESSION[self::SESSION_KEY] = [];
    }

    /**
     * Retourne tous les articles du panier
     */
    public function getItems(): array
    {
        return $_SESSION[self::SESSION_KEY] ?? [];
    }

    /**
     * Calcule le sous-total
     */
    public function getSousTotal(): float
    {
        $total = 0.0;
        foreach ($this->getItems() as $item) {
            $total += $item['prix'] * $item['quantite'];
        }
        return $total;
    }

    /**
     * Retourne le nombre total d'articles
     */
    public function getNombreArticles(): int
    {
        $total = 0;
        foreach ($this->getItems() as $item) {
            $total += $item['quantite'];
        }
        return $total;
    }

    /**
     * Vérifie si le panier est vide
     */
    public function isEmpty(): bool
    {
        return empty($this->getItems());
    }
}