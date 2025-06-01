<?php
// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
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
    $stmt->bindParam(':id', $clientId, PDO::PARAM_INT);
    $stmt->execute();
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        throw new Exception("Client non trouvé avec l'ID: $clientId");
    }

    // Préparer les données à renvoyer
    $data = [
        'client' => [
            'id' => $client['IdClient'],
            'prenom' => isset($client['PrenomClient']) ? $client['PrenomClient'] : '',
            'nom' => isset($client['NomClient']) ? $client['NomClient'] : '',
            'email' => $client['Email'],
            'telephone' => isset($client['NumTel']) ? $client['NumTel'] : '',
            'dateInscription' => isset($client['DateInscription']) ? $client['DateInscription'] : date('Y-m-d H:i:s')
        ],
        'commandes' => [],
        'adresses' => [],
        'favoris' => [],
        'panier' => [
            'articles' => [],
            'total' => 0,
            'nombreArticles' => 0
        ]
    ];

    // Récupérer les commandes
    try {
        $stmtCommandes = $pdo->prepare("
            SELECT c.*, DATE_FORMAT(c.DateCommande, '%d/%m/%Y à %H:%i') as date_formatee
            FROM commande c
            WHERE c.IdClient = :id
            ORDER BY c.DateCommande DESC
        ");
        $stmtCommandes->bindParam(':id', $clientId, PDO::PARAM_INT);
        $stmtCommandes->execute();
        $commandes = $stmtCommandes->fetchAll(PDO::FETCH_ASSOC);

        // Pour chaque commande, récupérer les produits
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
            
            $data['commandes'][] = [
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
    } catch (PDOException $e) {
        error_log("Erreur commandes: " . $e->getMessage());
    }

    // Récupérer les adresses
    try {
        $stmtAdresses = $pdo->prepare("
            SELECT * FROM adresse 
            WHERE IdClient = :id
            ORDER BY EstPrincipale DESC
        ");
        $stmtAdresses->bindParam(':id', $clientId, PDO::PARAM_INT);
        $stmtAdresses->execute();
        $adresses = $stmtAdresses->fetchAll(PDO::FETCH_ASSOC);
        
        $data['adresses'] = array_map(function($a) {
            return [
                'id' => $a['IdAdresse'],
                'titre' => $a['Titre'] ?? 'Adresse',
                'adresse' => $a['Adresse'],
                'isPrimary' => isset($a['EstPrincipale']) && $a['EstPrincipale'] == 1
            ];
        }, $adresses);
    } catch (PDOException $e) {
        error_log("Erreur adresses: " . $e->getMessage());
    }

    // Récupérer les favoris
    try {
        $stmtFavoris = $pdo->prepare("
            SELECT f.*, p.NomProduit, p.Prix, p.Description, i.URL as image
            FROM favoris f
            JOIN produit p ON f.IdProduit = p.IdProduit
            LEFT JOIN imageprod i ON p.IdProduit = i.IdProduit
            WHERE f.IdClient = :id
            GROUP BY f.IdProduit
        ");
        $stmtFavoris->bindParam(':id', $clientId, PDO::PARAM_INT);
        $stmtFavoris->execute();
        $favoris = $stmtFavoris->fetchAll(PDO::FETCH_ASSOC);
        
        $data['favoris'] = array_map(function($f) {
            return [
                'id' => $f['IdProduit'],
                'nom' => $f['NomProduit'],
                'prix' => floatval($f['Prix']),
                'image' => $f['image'] ?? 'images/placeholder.jpeg',
                'description' => $f['Description'] ?? ''
            ];
        }, $favoris);
    } catch (PDOException $e) {
        error_log("Erreur favoris: " . $e->getMessage());
    }

    // Récupérer le panier
    try {
        $stmtPanier = $pdo->prepare("
            SELECT p.*, c.Qtt as Quantite
            FROM panier c
            JOIN produit p ON c.IdProd = p.IdProduit
            WHERE c.IdClient = :id AND c.IdCom IS NULL
        ");
        $stmtPanier->bindParam(':id', $clientId, PDO::PARAM_INT);
        $stmtPanier->execute();
        $panier = $stmtPanier->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculer le total du panier
        $totalPanier = 0;
        $nombreArticles = 0;
        foreach ($panier as $article) {
            $totalPanier += $article['Prix'] * $article['Quantite'];
            $nombreArticles += $article['Quantite'];
        }
        
        $data['panier']['articles'] = $panier;
        $data['panier']['total'] = $totalPanier;
        $data['panier']['nombreArticles'] = $nombreArticles;
    } catch (PDOException $e) {
        error_log("Erreur panier: " . $e->getMessage());
    }

    // Renvoyer les données au format JSON
    header('Content-Type: application/json');
    echo json_encode($data);

} catch (Exception $e) {
    // Renvoyer un message d'erreur
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
