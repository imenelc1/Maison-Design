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
    // Requête pour récupérer les produits similaires (même catégorie, mais pas le même produit)
    $stmt = $pdo->prepare("
        SELECT p.IdProduit as id, p.NomProduit as nom, p.Prix as prix, p.Stock as stock, 
               c.NomCategorie as categorie, i.URL as image
        FROM produit p
        LEFT JOIN categorie c ON p.IdCategorie = c.IdCategorie
        LEFT JOIN (
            SELECT IdProduit, MIN(URL) as URL
            FROM imageprod
            GROUP BY IdProduit
        ) i ON p.IdProduit = i.IdProduit
        WHERE c.NomCategorie = ? AND p.IdProduit != ?
        ORDER BY RAND()
        LIMIT ?
    ");
    $stmt->execute([$category, $excludeId, $limit]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Si aucun produit n'est trouvé, essayer de récupérer des produits aléatoires
    if (empty($products)) {
        $stmt = $pdo->prepare("
            SELECT p.IdProduit as id, p.NomProduit as nom, p.Prix as prix, p.Stock as stock, 
                   c.NomCategorie as categorie, i.URL as image
            FROM produit p
            LEFT JOIN categorie c ON p.IdCategorie = c.IdCategorie
            LEFT JOIN (
                SELECT IdProduit, MIN(URL) as URL
                FROM imageprod
                GROUP BY IdProduit
            ) i ON p.IdProduit = i.IdProduit
            WHERE p.IdProduit != ?
            ORDER BY RAND()
            LIMIT ?
        ");
        $stmt->execute([$excludeId, $limit]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Pour chaque produit, s'assurer qu'il y a une image
    foreach ($products as &$product) {
        if (empty($product['image'])) {
            $product['image'] = '../images/placeholder.jpeg';
        }
    }
    
    // Renvoyer les données au format JSON
    header('Content-Type: application/json');
    echo json_encode($products);
    
} catch (PDOException $e) {
    // Renvoyer une erreur en cas de problème avec la base de données
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Erreur de base de données: ' . $e->getMessage()]);
}
?>
