<?php
// Désactiver l'affichage des erreurs pour éviter de corrompre le JSON
ini_set('display_errors', 0);
error_reporting(0);

// Inclure le fichier de connexion à la base de données
require_once 'db.php';

// Récupérer les produits avec leurs catégories et images
try {
    $query = "SELECT p.IdProduit, p.NomProduit, p.Description, p.Prix, p.Stock, 
                     c.IdCategorie, c.NomCategorie, 
                     i.URL as ImageURL
              FROM produit p
              LEFT JOIN categorie c ON p.IdCat = c.IdCategorie
              LEFT JOIN imageprod i ON p.IdProduit = i.IdProduit
              ORDER BY p.DateAjout DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $products = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
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
                    // Chercher le fichier avec différentes extensions possibles
                    $possibleExtensions = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
                    $foundExtension = '';
                    
                    foreach ($possibleExtensions as $ext) {
                        if (file_exists('images/' . $fileName . '.' . $ext)) {
                            $foundExtension = $ext;
                            break;
                        }
                    }
                    
                    // Si une extension a été trouvée, l'utiliser, sinon utiliser jpeg par défaut
                    $imageUrl = 'images/' . $fileName . ($foundExtension ? '.' . $foundExtension : '.jpeg');
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
            $imageUrl = 'images/placeholder.jpeg';
        }
        
        // Normaliser le nom de la catégorie pour le filtrage
        $categoryId = 'autre';
        if ($row['NomCategorie']) {
            // Convertir en minuscules, supprimer les accents et les espaces
            $categoryId = strtolower($row['NomCategorie']);
            $categoryId = preg_replace('/\s+/', '', $categoryId); // Supprimer les espaces
            
            // Remplacer les caractères accentués
            $accents = array('é', 'è', 'ê', 'ë', 'à', 'â', 'ä', 'î', 'ï', 'ô', 'ö', 'ù', 'û', 'ü', 'ç');
            $sans = array('e', 'e', 'e', 'e', 'a', 'a', 'a', 'i', 'i', 'o', 'o', 'u', 'u', 'u', 'c');
            $categoryId = str_replace($accents, $sans, $categoryId);
            
            // Correspondance spécifique pour les catégories courantes
            $mapping = [
                'lits' => 'lit',
                'lit' => 'lit',
                'chaises' => 'chaise',
                'chaise' => 'chaise',
                'tables' => 'table',
                'table' => 'table',
                'canapes' => 'canapé',
                'canape' => 'canapé',
                'armoires' => 'armoire',
                'armoire' => 'armoire'
            ];
            
            if (array_key_exists($categoryId, $mapping)) {
                $categoryId = $mapping[$categoryId];
            }
        }
        
        $products[] = [
            'id' => $row['IdProduit'],
            'name' => $row['NomProduit'],
            'description' => $row['Description'],
            'price' => $row['Prix'],
            'stock' => $row['Stock'],
            'categoryId' => $categoryId,
            'categoryName' => $row['NomCategorie'] ? $row['NomCategorie'] : 'Autre',
            'image' => $imageUrl
        ];
    }
    
    // Renvoyer les produits au format JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'products' => $products]);
    
} catch (PDOException $e) {
    // En cas d'erreur, renvoyer un message d'erreur
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des produits: ' . $e->getMessage()]);
}
?>