<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use App\Domain\Entity\Product;
use App\Domain\Repository\ProductRepositoryInterface;
use PDO;

class ProductRepository implements ProductRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?Product
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                p.IdProduit as id,
                p.NomProduit as nom,
                p.Description as description,
                p.Prix as prix,
                p.Stock as stock,
                c.NomCategorie as categorie,
                i.URL as image
            FROM produit p
            LEFT JOIN categorie c ON p.IdCat = c.IdCategorie
            LEFT JOIN (
                SELECT IdProduit, MIN(URL) as URL
                FROM imageprod
                GROUP BY IdProduit
            ) i ON p.IdProduit = i.IdProduit
            WHERE p.IdProduit = ?
        ");

        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("
            SELECT 
                p.IdProduit as id,
                p.NomProduit as nom,
                p.Description as description,
                p.Prix as prix,
                p.Stock as stock,
                c.NomCategorie as categorie,
                i.URL as image
            FROM produit p
            LEFT JOIN categorie c ON p.IdCat = c.IdCategorie
            LEFT JOIN (
                SELECT IdProduit, MIN(URL) as URL
                FROM imageprod
                GROUP BY IdProduit
            ) i ON p.IdProduit = i.IdProduit
            ORDER BY p.IdProduit DESC
        ");

        return array_map(
            fn($row) => $this->hydrate($row),
            $stmt->fetchAll()
        );
    }

    public function findByCategorie(string $categorie): array
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                p.IdProduit as id,
                p.NomProduit as nom,
                p.Description as description,
                p.Prix as prix,
                p.Stock as stock,
                c.NomCategorie as categorie,
                i.URL as image
            FROM produit p
            LEFT JOIN categorie c ON p.IdCat = c.IdCategorie
            LEFT JOIN (
                SELECT IdProduit, MIN(URL) as URL
                FROM imageprod
                GROUP BY IdProduit
            ) i ON p.IdProduit = i.IdProduit
            WHERE LOWER(c.NomCategorie) = LOWER(?)
            ORDER BY p.IdProduit DESC
        ");

        $stmt->execute([$categorie]);

        return array_map(
            fn($row) => $this->hydrate($row),
            $stmt->fetchAll()
        );
    }

    public function save(Product $product): void
    {
        if ($product->getId() === 0) {
            $stmt = $this->pdo->prepare("
                INSERT INTO produit 
                    (NomProduit, Description, Prix, Stock, IdCat, DateAjout)
                SELECT ?, ?, ?, ?, IdCategorie, NOW()
                FROM categorie
                WHERE NomCategorie = ?
            ");

            $stmt->execute([
                $product->getNom(),
                $product->getDescription(),
                $product->getPrix(),
                $product->getStock(),
                $product->getCategorie(),
            ]);
        } else {
            $stmt = $this->pdo->prepare("
                UPDATE produit SET
                    NomProduit  = ?,
                    Description = ?,
                    Prix        = ?,
                    Stock       = ?
                WHERE IdProduit = ?
            ");

            $stmt->execute([
                $product->getNom(),
                $product->getDescription(),
                $product->getPrix(),
                $product->getStock(),
                $product->getId(),
            ]);
        }
    }

    public function delete(int $id): void
    {
        // Supprimer les images d'abord
        $stmt = $this->pdo->prepare("
            DELETE FROM imageprod WHERE IdProduit = ?
        ");
        $stmt->execute([$id]);

        // Supprimer le produit
        $stmt = $this->pdo->prepare("
            DELETE FROM produit WHERE IdProduit = ?
        ");
        $stmt->execute([$id]);
    }

    public function updateStock(int $id, int $quantite): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE produit 
            SET Stock = Stock - ?
            WHERE IdProduit = ? AND Stock >= ?
        ");

        $stmt->execute([$quantite, $id, $quantite]);

        if ($stmt->rowCount() === 0) {
            throw new \RuntimeException(
                "Impossible de mettre à jour le stock du produit {$id}"
            );
        }
    }

   private function hydrate(array $row): Product
{
    $image = $row['image'] ?? '';

    if (!empty($image)) {
        // Normaliser le chemin
        if (strpos($image, 'images/') !== 0) {
            $image = 'images/' . basename($image);
        }
        
        // Vérifier si le fichier existe vraiment
        $fullPath = ROOT_PATH . '/public/' . $image;
        if (!file_exists($fullPath)) {
            $image = 'images/placeholder.jpeg';
        }
    } else {
        $image = 'images/placeholder.jpeg';
    }

    return new Product(
        (int)$row['id'],
        $row['nom'],
        $row['description'] ?? '',
        (float)$row['prix'],
        (int)$row['stock'],
        $row['categorie'] ?? '',
        $image
    );
}
}