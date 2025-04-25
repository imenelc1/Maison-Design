<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
    header('Content-Type: application/json');
    echo json_encode(['error' => 'not_authenticated']);
    exit();
}

// Inclure la connexion à la base de données
require_once 'db.php';

// Récupérer l'ID du client depuis la session
$clientId = $_SESSION['user_id'];

try {
    // Récupérer les informations du client
    $stmt = $pdo->prepare("SELECT * FROM client WHERE IdClient = :id");
    $stmt->bindParam(':id', $clientId);
    $stmt->execute();
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        throw new Exception("Client non trouvé");
    }

    // Récupérer les commandes du client
    $stmtCommandes = $pdo->prepare("
        SELECT c.*, s.Libelle as StatutLibelle 
        FROM commande c
        LEFT JOIN statut s ON c.IdStatut = s.IdStatut
        WHERE c.IdClient = :id
        ORDER BY c.DateCommande DESC
    ");
    $stmtCommandes->bindParam(':id', $clientId);
    $stmtCommandes->execute();
    $commandes = $stmtCommandes->fetchAll(PDO::FETCH_ASSOC);

    // Pour chaque commande, récupérer les détails (produits)
    foreach ($commandes as &$commande) {
        $stmtDetails = $pdo->prepare("
            SELECT d.*, p.Nom as NomProduit, p.Prix, p.Image 
            FROM detailcommande d
            JOIN produit p ON d.IdProduit = p.IdProduit
            WHERE d.IdCommande = :idCommande
        ");
        $stmtDetails->bindParam(':idCommande', $commande['IdCommande']);
        $stmtDetails->execute();
        $commande['produits'] = $stmtDetails->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer les adresses du client
    $stmtAdresses = $pdo->prepare("
        SELECT * FROM adresse 
        WHERE IdClient = :id
        ORDER BY EstPrincipale DESC
    ");
    $stmtAdresses->bindParam(':id', $clientId);
    $stmtAdresses->execute();
    $adresses = $stmtAdresses->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les favoris du client
    $stmtFavoris = $pdo->prepare("
        SELECT f.*, p.Nom, p.Prix, p.Image, p.Description, c.Nom as Categorie
        FROM favoris f
        JOIN produit p ON f.produit_id = p.IdProduit
        LEFT JOIN categorie c ON p.IdCategorie = c.IdCategorie
        WHERE f.utilisateur_id = :id
    ");
    $stmtFavoris->bindParam(':id', $clientId);
    $stmtFavoris->execute();
    $favoris = $stmtFavoris->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les produits dans le panier du client
    $stmtPanier = $pdo->prepare("
        SELECT p.*, c.Quantite
        FROM panier c
        JOIN produit p ON c.IdProduit = p.IdProduit
        WHERE c.IdClient = :id
    ");
    $stmtPanier->bindParam(':id', $clientId);
    $stmtPanier->execute();
    $panier = $stmtPanier->fetchAll(PDO::FETCH_ASSOC);

    // Calculer le total du panier
    $totalPanier = 0;
    $nombreArticles = 0;
    foreach ($panier as $article) {
        $totalPanier += $article['Prix'] * $article['Quantite'];
        $nombreArticles += $article['Quantite'];
    }

    // Préparer les données à renvoyer
    $data = [
        'client' => [
            'id' => $client['IdClient'],
            'prenom' => $client['Prenom'],
            'nom' => $client['Nom'],
            'email' => $client['Email'],
            'telephone' => $client['Telephone'],
            'dateInscription' => $client['DateInscription']
        ],
        'commandes' => $commandes,
        'adresses' => $adresses,
        'favoris' => $favoris,
        'panier' => [
            'articles' => $panier,
            'total' => $totalPanier,
            'nombreArticles' => $nombreArticles
        ]
    ];

    // Renvoyer les données au format JSON
    header('Content-Type: application/json');
    echo json_encode($data);

} catch (Exception $e) {
    // En cas d'erreur, renvoyer un message d'erreur
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>