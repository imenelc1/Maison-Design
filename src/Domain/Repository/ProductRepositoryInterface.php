<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Product;

interface ProductRepositoryInterface
{
    public function findById(int $id): ?Product;

    public function findAll(): array;

    public function findByCategorie(string $categorie): array;

    public function save(Product $product): int;

    public function saveImage(int $productId, string $imagePath): void;

    public function delete(int $id): void;

    public function updateStock(int $id, int $quantite): void;
}
