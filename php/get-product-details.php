<?php
// Désactiver l'affichage des erreurs pour éviter de corrompre le JSON
ini_set('display_errors', 0);
error_reporting(0);

// Inclure le fichier de connexion à la base de données
require_once 'db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID du produit non fourni']);
    exit;
}

$productId = intval($_GET['id']);

try {
    // Récupérer les détails du produit
    $query = "SELECT p.IdProduit, p.NomProduit, p.Description, p.Prix, p.Stock, 
                     c.IdCategorie, c.NomCategorie
              FROM produit p
              LEFT JOIN categorie c ON p.IdCat = c.IdCategorie
              WHERE p.IdProduit = :productId";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
    $stmt->execute();
    
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Produit non trouvé']);
        exit;
    }
    
    // Récupérer les images du produit
    $imageQuery = "SELECT URL FROM imageprod WHERE IdProduit = :productId";
    $imageStmt = $pdo->prepare($imageQuery);
    $imageStmt->bindParam(':productId', $productId, PDO::PARAM_INT);
    $imageStmt->execute();
    
    $images = [];
    
    while ($imageRow = $imageStmt->fetch(PDO::FETCH_ASSOC)) {
        $imageUrl = $imageRow['URL'];
        
        if ($imageUrl) {
            // Si c'est un chemin Windows complet
            if (strpos($imageUrl, 'C:\\') === 0 || strpos($imageUrl, '\\') !== false) {
                // Extraire le nom du fichier du chemin complet
                $pathParts = explode('\\', $imageUrl);
                $fileName = end($pathParts);
                
                // Vérifier si le fichier a déjà une extension
                $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                
                if (empty($extension)) {
                    // Ajouter une extension par défaut
                    $imageUrl = 'images/' . $fileName . '.jpeg';
                } else {
                    // Le fichier a déjà une extension
                    $imageUrl = 'images/' . $fileName;
                }
            } 
            // Si c'est déjà un chemin relatif, le laisser tel quel
            else if (strpos($imageUrl, 'images/') === 0) {
                // Ne rien faire, c'est déjà au bon format
            }
            // Sinon, supposer que c'est juste un nom de fichier
            else {
                $imageUrl = 'images/' . $imageUrl;
                
                // Vérifier si le fichier a déjà une extension
                $extension = pathinfo($imageUrl, PATHINFO_EXTENSION);
                if (empty($extension)) {
                    // Ajouter une extension par défaut
                    $imageUrl .= '.jpeg';
                }
            }
            
            $images[] = $imageUrl;
        }
    }
    
    // Si aucune image n'est trouvée, ajouter une image par défaut
    if (empty($images)) {
        $images[] = '../images/placeholder.jpeg';
    }
    
    // Récupérer des produits similaires (même catégorie)
    $similarQuery = "SELECT p.IdProduit, p.NomProduit, p.Description, p.Prix, p.Stock, 
                           c.NomCategorie, i.URL as ImageURL
                    FROM produit p
                    LEFT JOIN categorie c ON p.IdCat = c.IdCategorie
                    LEFT JOIN imageprod i ON p.IdProduit = i.IdProduit
                    WHERE p.IdCat = :categoryId AND p.IdProduit != :productId
                    ORDER BY RAND()
                    LIMIT 4";
    
    $similarStmt = $pdo->prepare($similarQuery);
    $similarStmt->bindParam(':categoryId', $product['IdCategorie'], PDO::PARAM_INT);
    $similarStmt->bindParam(':productId', $productId, PDO::PARAM_INT);
    $similarStmt->execute();
    
    $similarProducts = [];
    
    while ($row = $similarStmt->fetch(PDO::FETCH_ASSOC)) {
        // Traitement de l'URL de l'image
        $imageUrl = $row['ImageURL'];
        
        if ($imageUrl) {
            // Si c'est un chemin Windows complet
            if (strpos($imageUrl, 'C:\\') === 0 || strpos($imageUrl, '\\') !== false) {
                // Extraire le nom du fichier du chemin complet
                $pathParts = explode('\\', $imageUrl);
                $fileName = end($pathParts);
                
                // Vérifier si le fichier a déjà une extension
                $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                
                if (empty($extension)) {
                    // Ajouter une extension par défaut
                    $imageUrl = 'images/' . $fileName . '.jpeg';
                } else {
                    // Le fichier a déjà une extension
                    $imageUrl = 'images/' . $fileName;
                }
            } 
            // Si c'est déjà un chemin relatif, le laisser tel quel
            else if (strpos($imageUrl, 'images/') === 0) {
                // Ne rien faire, c'est déjà au bon format
            }
            // Sinon, supposer que c'est juste un nom de fichier
            else {
                $imageUrl = 'images/' . $imageUrl;
                
                // Vérifier si le fichier a déjà une extension
                $extension = pathinfo($imageUrl, PATHINFO_EXTENSION);
                if (empty($extension)) {
                    // Ajouter une extension par défaut
                    $imageUrl .= '.jpeg';
                }
            }
        } else {
            // Image par défaut si aucune image n'est trouvée
            $imageUrl = '../images/placeholder.jpeg';
        }
        
        $similarProducts[] = [
            'id' => $row['IdProduit'],
            'name' => $row['NomProduit'],
            'description' => $row['Description'],
            'price' => $row['Prix'],
            'stock' => $row['Stock'],
            'categoryId' => $row['NomCategorie'] ? $row['NomCategorie'] : 'autre',
            'image' => $imageUrl
        ];
    }
    
    // Préparer les données du produit
    $productData = [
        'id' => $product['IdProduit'],
        'name' => $product['NomProduit'],
        'description' => $product['Description'],
        'price' => $product['Prix'],
        'stock' => $product['Stock'],
        'category' => $product['NomCategorie'],
        'images' => $images,
        'similarProducts' => $similarProducts
    ];
    
    // Renvoyer les données au format JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'product' => $productData]);
    
} catch (PDOException $e) {
    // En cas d'erreur, renvoyer un message d'erreur
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des détails du produit: ' . $e->getMessage()]);
}
?>