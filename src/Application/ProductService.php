<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Entity\Product;
use App\Domain\Repository\ProductRepositoryInterface;

class ProductService
{
    private ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Récupère tous les produits
     */
    public function getTousLesProduits(): array
    {
        return $this->productRepository->findAll();
    }

    /**
     * Récupère les produits d'une catégorie
     */
    public function getProduitsByCategorie(string $categorie): array
    {
        return $this->productRepository->findByCategorie($categorie);
    }

    /**
     * Récupère un produit par son ID
     */
    public function getProduit(int $id): ?Product
    {
        return $this->productRepository->findById($id);
    }

    /**
     * Vérifie si un produit est disponible en quantité demandée
     */
    public function isDisponible(int $id, int $quantite = 1): bool
    {
        $product = $this->productRepository->findById($id);

        if ($product === null) {
            return false;
        }

        return $product->getStock() >= $quantite;
    }

    /**
     * Réduit le stock après une commande
     */
    public function reduireStock(int $id, int $quantite): void
    {
        $product = $this->productRepository->findById($id);

        if ($product === null) {
            throw new \RuntimeException("Produit {$id} introuvable");
        }

        if ($product->getStock() < $quantite) {
            throw new \RuntimeException(
                "Stock insuffisant pour {$product->getNom()}. 
                 Disponible: {$product->getStock()}, 
                 Demandé: {$quantite}"
            );
        }

        $this->productRepository->updateStock($id, $quantite);
    }
}