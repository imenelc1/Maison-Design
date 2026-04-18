<?php
// Commencer la session et vérifier si l'utilisateur est connecté
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    // Renvoyer une erreur en JSON
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Non autorisé']);
    exit();
}

// Connexion à la base de données
require_once 'db.php';

$clientId = $_SESSION['user_id'];

try {
    // Récupérer les commandes de l'utilisateur
    $stmt = $pdo->prepare("
        SELECT c.*, DATE_FORMAT(c.DateCommande, '%d/%m/%Y à %H:%i') as date_formatee
        FROM commande c
        WHERE c.IdClient = ?
        ORDER BY c.DateCommande DESC
    ");
    $stmt->execute([$clientId]);
    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pour chaque commande, récupérer les produits associés
    $commandesAvecProduits = [];

    foreach ($commandes as $commande) {
        $stmtProduits = $pdo->prepare("
            SELECT p.IdProd, p.Qtt, pr.NomProduit, pr.Prix, i.URL as image
            FROM panier p
            JOIN produit pr ON p.IdProd = pr.IdProduit
            LEFT JOIN imageprod i ON pr.IdProduit = i.IdProduit
            WHERE p.IdCom = ?
            GROUP BY p.IdProd
        ");
        $stmtProduits->execute([$commande['IdCommande']]);
        $produits = $stmtProduits->fetchAll(PDO::FETCH_ASSOC);
        
        // Récupérer les informations de livraison
        $stmtLivraison = $pdo->prepare("SELECT * FROM livraison WHERE IdComm = ?");
        $stmtLivraison->execute([$commande['IdCommande']]);
        $livraison = $stmtLivraison->fetch(PDO::FETCH_ASSOC);
        
        $commandesAvecProduits[] = [
            'id' => $commande['IdCommande'],
            'date' => $commande['date_formatee'],
            'statut' => $commande['Status'],
            'montant_total' => number_format($commande['TotalPrix'], 2, ',', ' '),
            'adresse_livraison' => $livraison ? $livraison['Adresse'] : '',
            'produits' => array_map(function($p) {
                return [
                    'id' => $p['IdProd'],
                    'nom' => $p['NomProduit'],
                    'quantite' => $p['Qtt'],
                    'prix_unitaire' => number_format($p['Prix'], 2, ',', ' '),
                    'image' => $p['image'] ?? 'images/placeholder.jpeg'
                ];
            }, $produits)
        ];
    }

    // Renvoyer les commandes en JSON
    header('Content-Type: application/json');
    echo json_encode($commandesAvecProduits);

} catch (Exception $e) {
    // Renvoyer une erreur en JSON
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
