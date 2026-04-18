<?php
session_start();
header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(['error' => 'not_authenticated', 'message' => 'Utilisateur non connecté']);
    exit;
}

require_once 'db.php';

$clientId = $_SESSION['user_id'];

// Traiter les requêtes GET (récupération des favoris)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Récupérer les favoris avec les informations des produits et leurs images
        $stmt = $pdo->prepare("
            SELECT 
                p.IdProduit,
                p.NomProduit,
                p.Description,
                p.Prix,
                p.Stock,
                i.URL as image_url,
                f.DateAjout
            FROM favoris f
            INNER JOIN produit p ON f.IdProduit = p.IdProduit
            LEFT JOIN imageprod i ON p.IdProduit = i.IdProduit
            WHERE f.IdClient = ?
            ORDER BY f.DateAjout DESC
        ");
        
        $stmt->execute([$clientId]);
        $favoris = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Traiter les données pour l'affichage
        $favorisFormates = [];
        foreach ($favoris as $favori) {
            // Formater le prix (diviser par 1000 si nécessaire)
            $prix = $favori['Prix'];
            
            // Gérer l'image
            $image = $favori['image_url'];
            if ($image) {
                // Ajouter l'extension si manquante
                if (!pathinfo($image, PATHINFO_EXTENSION)) {
                    $image .= '.jpg';
                }
                // S'assurer que le chemin commence par images/
                if (!str_starts_with($image, 'images/')) {
                    $image = 'images/' . $image;
                }
            } else {
                $image = 'images/placeholder.jpeg';
            }
            
            $favorisFormates[] = [
                'IdProduit' => $favori['IdProduit'],
                'NomProduit' => $favori['NomProduit'],
                'Description' => $favori['Description'],
                'Prix' => $favori['Prix'],
                'Stock' => $favori['Stock'],
                'image' => $image,
                'DateAjout' => $favori['DateAjout']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'favoris' => $favorisFormates,
            'count' => count($favorisFormates)
        ]);
        
    } catch (PDOException $e) {
        error_log("Erreur dans favorites_actions.php (GET): " . $e->getMessage());
        echo json_encode([
            'error' => 'database_error',
            'message' => 'Erreur lors de la récupération des favoris'
        ]);
    }
}
// Traiter les requêtes POST (ajouter/supprimer des favoris)
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier les paramètres requis
    if (!isset($_POST['action']) || !isset($_POST['produitId'])) {
        echo json_encode(['error' => 'missing_parameters', 'message' => 'Paramètres manquants']);
        exit;
    }

    $action = $_POST['action'];
    $produitId = (int)$_POST['produitId'];

    try {
        // Vérifier si la table favoris existe
        $stmt = $pdo->prepare("SHOW TABLES LIKE 'favoris'");
        $stmt->execute();
        if ($stmt->rowCount() === 0) {
            // La table n'existe pas, on la crée
            $pdo->exec("
                CREATE TABLE favoris (
                    IdFavori INT AUTO_INCREMENT PRIMARY KEY,
                    IdClient INT NOT NULL,
                    IdProduit INT NOT NULL,
                    DateAjout DATETIME DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_favori (IdClient, IdProduit),
                    FOREIGN KEY (IdClient) REFERENCES client(IdClient) ON DELETE CASCADE,
                    FOREIGN KEY (IdProduit) REFERENCES produit(IdProduit) ON DELETE CASCADE
                )
            ");
        }
        
        switch ($action) {
            case 'toggle':
                // Vérifier si le favori existe déjà
                $stmt = $pdo->prepare("SELECT IdFavori FROM favoris WHERE IdClient = ? AND IdProduit = ?");
                $stmt->execute([$clientId, $produitId]);
                $favori = $stmt->fetch();
                
                if ($favori) {
                    // Le favori existe, on le supprime
                    $stmt = $pdo->prepare("DELETE FROM favoris WHERE IdClient = ? AND IdProduit = ?");
                    $stmt->execute([$clientId, $produitId]);
                    echo json_encode(['success' => true, 'action' => 'removed', 'message' => 'Produit retiré des favoris']);
                } else {
                    // Le favori n'existe pas, on l'ajoute
                    $stmt = $pdo->prepare("INSERT INTO favoris (IdClient, IdProduit) VALUES (?, ?)");
                    $stmt->execute([$clientId, $produitId]);
                    echo json_encode(['success' => true, 'action' => 'added', 'message' => 'Produit ajouté aux favoris']);
                }
                break;
                
            case 'check':
                // Vérifier si le produit est dans les favoris
                $stmt = $pdo->prepare("SELECT IdFavori FROM favoris WHERE IdClient = ? AND IdProduit = ?");
                $stmt->execute([$clientId, $produitId]);
                $favori = $stmt->fetch();
                
                echo json_encode(['success' => true, 'isFavorite' => (bool)$favori]);
                break;
                
            default:
                echo json_encode(['error' => 'invalid_action', 'message' => 'Action non valide']);
                break;
        }
        
    } catch (PDOException $e) {
        error_log("Erreur base de données dans favorites_actions.php (POST): " . $e->getMessage());
        echo json_encode(['error' => 'database_error', 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
    } catch (Exception $e) {
        error_log("Erreur générale dans favorites_actions.php (POST): " . $e->getMessage());
        echo json_encode(['error' => 'general_error', 'message' => 'Erreur serveur: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'method_not_allowed', 'message' => 'Méthode non autorisée']);
}
?>
