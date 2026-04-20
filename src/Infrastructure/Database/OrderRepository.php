<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use App\Domain\Entity\Order;
use App\Domain\Repository\OrderRepositoryInterface;
use PDO;

class OrderRepository implements OrderRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?Order
    {
        $stmt = $this->pdo->prepare("
            SELECT IdCommande as id, IdClient as userId, 
                   TotalPrix as totalPrix, Status as status,
                   DateCommande as dateCommande
            FROM commande WHERE IdCommande = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return $this->hydrate($row);
    }

    public function findByUserId(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT IdCommande as id, IdClient as userId,
                   TotalPrix as totalPrix, Status as status,
                   DateCommande as dateCommande
            FROM commande WHERE IdClient = ?
            ORDER BY DateCommande DESC
        ");
        $stmt->execute([$userId]);

        return array_map(
            fn($row) => $this->hydrate($row),
            $stmt->fetchAll()
        );
    }

   public function save(Order $order): int
{
    error_log("=== OrderRepository::save ===");
    error_log("userId: " . $order->getUserId());
    error_log("total: " . $order->getTotalPrix());

    // Vérifier que le client existe
    $checkClient = $this->pdo->prepare("SELECT IdClient FROM client WHERE IdClient = ?");
    $checkClient->execute([$order->getUserId()]);
    
    if (!$checkClient->fetch()) {
        throw new \RuntimeException(
            "Client ID {$order->getUserId()} introuvable en base de données"
        );
    }

    $this->pdo->beginTransaction();

    try {
        // 1. Créer la commande
        $stmt = $this->pdo->prepare("
            INSERT INTO commande (IdClient, TotalPrix, Status, DateCommande)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([
            $order->getUserId(),
            $order->getTotalPrix(),
            $order->getStatus(),
        ]);

        $commandeId = (int)$this->pdo->lastInsertId();
        error_log("CommandeId créé: " . $commandeId);

        // 2. Insérer les articles du panier
        foreach ($order->getItems() as $item) {
            $stmtPanier = $this->pdo->prepare("
                INSERT INTO panier (IdProd, IdCom, Qtt, DatePanier)
                VALUES (?, ?, ?, NOW())
            ");
            $stmtPanier->execute([
                (int)$item['id'],
                $commandeId,
                (int)$item['quantite'],
            ]);
        }

        $this->pdo->commit();

        return $commandeId;

    } catch (\Exception $e) {
        $this->pdo->rollBack();
        error_log("Erreur save commande: " . $e->getMessage());
        throw new \RuntimeException("Erreur lors de la création de la commande: " . $e->getMessage());
    }
}

    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE commande SET Status = ? WHERE IdCommande = ?
        ");
        $stmt->execute([$status, $id]);
    }

    private function hydrate(array $row): Order
    {
        return new Order(
            (int)$row['id'],
            (int)$row['userId'],
            (float)$row['totalPrix'],
            $row['status'] ?? 'en attente',
            $row['dateCommande'] ?? ''
        );
    }
    public function saveLivraison(int $commandeId, string $adresse): void
{
    $stmt = $this->pdo->prepare("
        INSERT INTO livraison (Adresse, DateLivraison, StatutLivraison, Frais, IdComm)
        VALUES (?, NOW(), 'En attente', 1000, ?)
    ");
    $stmt->execute([$adresse, $commandeId]);
}
}