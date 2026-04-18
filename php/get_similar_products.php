<?php
// Démarrer la session pour pouvoir accéder aux variables de session
session_start();

// Connexion à la base de données
require_once 'db.php';

// Récupérer la catégorie et l'ID du produit à exclure depuis l'URL
$category = isset($_GET['category']) ? $_GET['category'] : '';
$excludeId = isset($_GET['exclude']) ? intval($_GET['exclude']) : 0;

// Limiter le nombre de produits similaires à afficher
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 4;

try {
    // Requête corrigée pour éviter les doublons
    $stmt = $pdo->prepare("
        SELECT DISTINCT p.IdProduit as id, p.NomProduit as nom, p.Prix as prix, p.Stock as stock, 
               c.NomCategorie as categorie, 
               (SELECT MIN(URL) FROM imageprod WHERE IdProduit = p.IdProduit) as image
        FROM produit p
        LEFT JOIN categorie c ON p.IdCategorie = c.IdCategorie
        WHERE c.NomCategorie = ? AND p.IdProduit != ?
        ORDER BY RAND()
        LIMIT ?
    ");
    $stmt->execute([$category, $excludeId, $limit]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Si aucun produit n'est trouvé, essayer de récupérer des produits aléatoires
    if (empty($products)) {
        $stmt = $pdo->prepare("
            SELECT DISTINCT p.IdProduit as id, p.NomProduit as nom, p.Prix as prix, p.Stock as stock, 
                   c.NomCategorie as categorie,
                   (SELECT MIN(URL) FROM imageprod WHERE IdProduit = p.IdProduit) as image
            FROM produit p
            LEFT JOIN categorie c ON p.IdCategorie = c.IdCategorie
            WHERE p.IdProduit != ?
            ORDER BY RAND()
            LIMIT ?
        ");
        $stmt->execute([$excludeId, $limit]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Supprimer les doublons basés sur l'ID du produit (sécurité supplémentaire)
    $uniqueProducts = [];
    $seenIds = [];
    
    foreach ($products as $product) {
        if (!in_array($product['id'], $seenIds)) {
            $seenIds[] = $product['id'];
            
            // S'assurer qu'il y a une image
            if (empty($product['image'])) {
                $product['image'] = '../images/placeholder.jpeg';
            }
            
            $uniqueProducts[] = $product;
        }
    }
    
    // Limiter à nouveau au cas où il y aurait encore des doublons
    $uniqueProducts = array_slice($uniqueProducts, 0, $limit);
    
    // Renvoyer les données au format JSON
    header('Content-Type: application/json');
    echo json_encode($uniqueProducts);
    
} catch (PDOException $e) {
    // Renvoyer une erreur en cas de problème avec la base de données
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Erreur de base de données: ' . $e->getMessage()]);
}
?>
