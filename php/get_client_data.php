<?php
// Version corrigée basée sur la vraie structure de votre base de données
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'not_authenticated']);
    exit();
}

try {
    require_once 'db.php';
    $clientId = $_SESSION['user_id'];
    
    // Récupérer les informations du client
    $stmtClient = $pdo->prepare("
        SELECT 
            IdClient,
            NomClient as nom,
            PrenomClient as prenom,
            Email as email,
            NumTel as telephone,
            Adresse as adresse,
            DateInscription as dateInscription
        FROM client 
        WHERE IdClient = ?
    ");
    $stmtClient->execute([$clientId]);
    $client = $stmtClient->fetch(PDO::FETCH_ASSOC);
    
    if (!$client) {
        echo json_encode(['error' => 'client_not_found']);
        exit();
    }
    
    // Récupérer les commandes
    $stmtCommandes = $pdo->prepare("
        SELECT 
            IdCommande as id,
            TotalPrix as montant_total,
            Status as statut,
            DateCommande as date
        FROM commande 
        WHERE IdClient = ? 
        ORDER BY DateCommande DESC
    ");
    $stmtCommandes->execute([$clientId]);
    $commandes = $stmtCommandes->fetchAll(PDO::FETCH_ASSOC);
    
    // Traiter chaque commande
    foreach ($commandes as &$commande) {
        // Formater la date
        $commande['date'] = date('d/m/Y à H:i', strtotime($commande['date']));
        
        // Formater le montant (diviser par 1000 car stocké en millièmes)
        $montant = floatval($commande['montant_total']) / 1000;
        $commande['montant_total'] = number_format($montant, 0, ',', ' ');
        
        // Traduire le statut
        switch ($commande['statut']) {
            case 'en attente':
                $commande['statut'] = 'En attente';
                break;
            case 'expe?die?':
            case 'expedie':
            case 'expédié':
                $commande['statut'] = 'Expédié';
                break;
            case 'livre?':
            case 'livre':
            case 'livré':
                $commande['statut'] = 'Livré';
                break;
            case 'annule?':
            case 'annule':
            case 'annulé':
                $commande['statut'] = 'Annulé';
                break;
            default:
                $commande['statut'] = ucfirst($commande['statut']);
        }
        
        // Ajouter l'adresse de livraison
        $commande['adresse_livraison'] = $client['adresse'];
        
        // Récupérer les produits de cette commande depuis la table `panier`
        $stmtProduits = $pdo->prepare("
            SELECT 
                p.NomProduit as nom,
                pa.Qtt as quantite,
                p.Prix as prix_unitaire,
                i.URL as image
            FROM panier pa
            JOIN produit p ON pa.IdProd = p.IdProduit
            LEFT JOIN imageprod i ON p.IdProduit = i.IdProduit
            WHERE pa.IdCom = ?
        ");
        $stmtProduits->execute([$commande['id']]);
        $produits = $stmtProduits->fetchAll(PDO::FETCH_ASSOC);
        
        // Traiter les produits
        foreach ($produits as &$produit) {
            // Formater le prix unitaire (diviser par 1000)
            $prix = floatval($produit['prix_unitaire']) / 1000;
            $produit['prix_unitaire'] = number_format($prix, 0, ',', ' ');
            
            // Corriger le chemin de l'image
            if (empty($produit['image'])) {
                $produit['image'] = 'images/placeholder.jpeg';
            } else {
                // S'assurer que l'image a une extension
                $image = $produit['image'];
                if (!str_contains($image, '.')) {
                    // Ajouter .jpg par défaut si pas d'extension
                    $image .= '.jpg';
                }
                
                // S'assurer que le chemin commence par images/
                if (!str_starts_with($image, 'images/')) {
                    $image = 'images/' . basename($image);
                }
                
                $produit['image'] = $image;
            }
        }
        
        // Si pas de produits trouvés, créer un produit générique
        if (empty($produits)) {
            $produits = [
                [
                    'nom' => 'Commande #' . $commande['id'],
                    'quantite' => 'Plusieurs',
                    'prix_unitaire' => $commande['montant_total'],
                    'image' => 'images/package-icon.png'
                ]
            ];
        }
        
        $commande['produits'] = $produits;
    }
    
    // Adresses
    $adresses = [];
    if (!empty($client['adresse'])) {
        $adresses = [
            [
                'id' => 1,
                'titre' => 'Adresse principale',
                'adresse' => $client['adresse'],
                'isPrimary' => true
            ]
        ];
    }
    
    $response = [
        'success' => true,
        'client' => $client,
        'commandes' => $commandes,
        'adresses' => $adresses
    ];
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    error_log("Erreur base de données dans get_client_data.php: " . $e->getMessage());
    echo json_encode(['error' => 'database_error', 'message' => 'Erreur de base de données']);
} catch (Exception $e) {
    error_log("Erreur générale dans get_client_data.php: " . $e->getMessage());
    echo json_encode(['error' => 'general_error', 'message' => 'Erreur serveur']);
}
?>
