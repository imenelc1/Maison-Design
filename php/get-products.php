<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../php/db.php';

$selectedCategory = isset($_GET['category']) ? $_GET['category'] : 'all';

try {
    if ($selectedCategory === 'all') {
        $stmtProduits = $pdo->query("
            SELECT p.*, c.NomCategorie as categorie, i.URL as image
            FROM produit p
            LEFT JOIN categorie c ON p.IdCat = c.IdCategorie
            LEFT JOIN (
                SELECT IdProduit, MIN(URL) as URL
                FROM imageprod
                GROUP BY IdProduit
            ) i ON p.IdProduit = i.IdProduit
            ORDER BY p.IdProduit DESC
        ");
        $produits = $stmtProduits->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmtProduits = $pdo->prepare("
            SELECT p.*, c.NomCategorie as categorie, i.URL as image
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
        $stmtProduits->execute([$selectedCategory]);
        $produits = $stmtProduits->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Traiter les images pour s'assurer qu'elles ont le bon chemin
    foreach ($produits as &$produit) {
        if (!empty($produit['image'])) {
            // Si l'image ne commence pas par 'images/', l'ajouter
            if (strpos($produit['image'], 'images/') !== 0) {
                $produit['image'] = 'images/' . basename($produit['image']);
            }
        } else {
            $produit['image'] = 'images/placeholder.jpeg';
        }
    }
    
    echo json_encode(['success' => true, 'products' => $produits]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
