<?php
// Version corrigée pour éviter les doublons et la duplication
ob_clean();
ini_set('display_errors', 0);
error_reporting(0);

session_start();

try {
    require_once 'db.php';
} catch (Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données']);
    exit();
}

// Initialiser le panier s'il n'existe pas
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Fonction pour ajouter un produit au panier - VERSION CORRIGÉE ANTI-DUPLICATION
function ajouterAuPanier($produitId, $quantite = 1) {
    global $pdo;
    
    try {
        // CORRECTION 1: S'assurer que $produitId est un entier
        $produitId = (int)$produitId;
        $quantite = (int)$quantite;
        
        if ($produitId <= 0 || $quantite <= 0) {
            return ['success' => false, 'message' => 'Paramètres invalides'];
        }
        
        // Vérifier si le produit existe et récupérer ses informations
        $stmt = $pdo->prepare("
            SELECT p.*, i.URL as image
            FROM produit p
            LEFT JOIN (
                SELECT IdProduit, MIN(URL) as URL
                FROM imageprod
                GROUP BY IdProduit
            ) i ON p.IdProduit = i.IdProduit
            WHERE p.IdProduit = ?
        ");
        $stmt->execute([$produitId]);
        $produit = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$produit) {
            return ['success' => false, 'message' => 'Produit non trouvé'];
        }
        
        // Vérification du stock
        $stockDisponible = (int)$produit['Stock'];
        if ($stockDisponible <= 0) {
            return ['success' => false, 'message' => 'Ce produit n\'est plus disponible en stock'];
        }

        // CORRECTION 2: Vérifier si le produit est déjà dans le panier avec une comparaison stricte
        $produitTrouve = false;
        $indexProduit = -1;
        
        for ($i = 0; $i < count($_SESSION['panier']); $i++) {
            // CORRECTION 3: Comparaison stricte avec conversion en entier
            if ((int)$_SESSION['panier'][$i]['id'] === $produitId) {
                $produitTrouve = true;
                $indexProduit = $i;
                break;
            }
        }
        
        if ($produitTrouve) {
            // Le produit existe déjà, augmenter la quantité
            $quantiteActuelle = (int)$_SESSION['panier'][$indexProduit]['quantite'];
            $nouvelleQuantite = $quantiteActuelle + $quantite;
            
            // Vérifier si la nouvelle quantité ne dépasse pas le stock
            if ($nouvelleQuantite > $stockDisponible) {
                $quantiteRestante = $stockDisponible - $quantiteActuelle;
                if ($quantiteRestante <= 0) {
                    return ['success' => false, 'message' => 'Vous avez déjà le maximum disponible de ce produit dans votre panier'];
                } else {
                    return ['success' => false, 'message' => "Stock insuffisant. Il ne reste que {$quantiteRestante} exemplaire(s) disponible(s)"];
                }
            }
            
            // CORRECTION 4: Mise à jour directe par index
            $_SESSION['panier'][$indexProduit]['quantite'] = $nouvelleQuantite;
            
        } else {
            // Le produit n'existe pas dans le panier, l'ajouter
            
            // Vérifier si la quantité demandée ne dépasse pas le stock
            if ($quantite > $stockDisponible) {
                return ['success' => false, 'message' => "Stock insuffisant. Il ne reste que {$stockDisponible} exemplaire(s) disponible(s)"];
            }
            
            // Traiter l'image
            $imageUrl = $produit['image'] ?? 'images/placeholder.jpeg';
            if (!empty($imageUrl) && strpos($imageUrl, 'images/') !== 0) {
                $imageUrl = 'images/' . basename($imageUrl);
            }
            
            // CORRECTION 5: Ajouter le nouveau produit avec des types cohérents
            $_SESSION['panier'][] = [
                'id' => $produitId, // Entier
                'nom' => $produit['NomProduit'],
                'prix' => (float)$produit['Prix'], // Float
                'image' => $imageUrl,
                'quantite' => $quantite // Entier
            ];
        }
        
        // CORRECTION 6: Calculer le total avec vérification
        $totalItems = 0;
        foreach ($_SESSION['panier'] as $item) {
            $totalItems += (int)$item['quantite'];
        }
        
        // Debug pour vérifier l'état du panier
        error_log("Panier après ajout: " . json_encode($_SESSION['panier']));
        error_log("Total items: " . $totalItems);
        
        return [
            'success' => true, 
            'message' => 'Produit ajouté au panier avec succès',
            'cartCount' => $totalItems,
            'debug' => [
                'produitId' => $produitId,
                'quantiteAjoutee' => $quantite,
                'produitExistait' => $produitTrouve,
                'totalItems' => $totalItems
            ]
        ];
        
    } catch (Exception $e) {
        error_log("Erreur ajouterAuPanier: " . $e->getMessage());
        return ['success' => false, 'message' => 'Une erreur est survenue lors de l\'ajout au panier'];
    }
}

// Fonction pour modifier la quantité d'un produit dans le panier - CORRIGÉE
function modifierQuantite($produitId, $delta) {
    global $pdo;
    
    try {
        $produitId = (int)$produitId;
        $delta = (int)$delta;
        
        // Vérifier le stock disponible
        $stmt = $pdo->prepare("SELECT Stock FROM produit WHERE IdProduit = ?");
        $stmt->execute([$produitId]);
        $produit = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$produit) {
            return ['success' => false, 'message' => 'Produit non trouvé'];
        }
        
        $stockDisponible = (int)$produit['Stock'];
        $produitTrouve = false;
        
        for ($i = 0; $i < count($_SESSION['panier']); $i++) {
            if ((int)$_SESSION['panier'][$i]['id'] === $produitId) {
                $nouvelleQuantite = (int)$_SESSION['panier'][$i]['quantite'] + $delta;
                
                // Vérifier le stock si on augmente la quantité
                if ($delta > 0 && $nouvelleQuantite > $stockDisponible) {
                    return ['success' => false, 'message' => "Stock insuffisant. Il ne reste que {$stockDisponible} exemplaire(s) disponible(s)"];
                }
                
                if ($nouvelleQuantite <= 0) {
                    // Supprimer le produit
                    array_splice($_SESSION['panier'], $i, 1);
                } else {
                    // Mettre à jour la quantité
                    $_SESSION['panier'][$i]['quantite'] = $nouvelleQuantite;
                }
                
                $produitTrouve = true;
                break;
            }
        }
        
        if (!$produitTrouve) {
            return ['success' => false, 'message' => 'Produit non trouvé dans le panier'];
        }
        
        // Calculer le nouveau nombre total d'articles
        $totalItems = 0;
        foreach ($_SESSION['panier'] as $item) {
            $totalItems += (int)$item['quantite'];
        }
        
        return [
            'success' => true, 
            'message' => 'Quantité modifiée',
            'cartCount' => $totalItems
        ];
        
    } catch (Exception $e) {
        error_log("Erreur modifierQuantite: " . $e->getMessage());
        return ['success' => false, 'message' => 'Une erreur est survenue'];
    }
}

// Fonction pour supprimer un produit du panier - CORRIGÉE
function supprimerDuPanier($produitId) {
    $produitId = (int)$produitId;
    
    for ($i = 0; $i < count($_SESSION['panier']); $i++) {
        if ((int)$_SESSION['panier'][$i]['id'] === $produitId) {
            array_splice($_SESSION['panier'], $i, 1);
            
            // Calculer le nouveau nombre total d'articles
            $totalItems = 0;
            foreach ($_SESSION['panier'] as $item) {
                $totalItems += (int)$item['quantite'];
            }
            
            return [
                'success' => true, 
                'message' => 'Produit supprimé du panier',
                'cartCount' => $totalItems
            ];
        }
    }
    
    return ['success' => false, 'message' => 'Produit non trouvé dans le panier'];
}

// Fonction pour vider le panier
function viderPanier() {
    $_SESSION['panier'] = [];
    return [
        'success' => true, 
        'message' => 'Panier vidé',
        'cartCount' => 0
    ];
}

// Fonction pour rediriger après une action
function redirectAfterAction() {
    if (isset($_SERVER['HTTP_REFERER'])) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    } else {
        header('Location: ../cart.php');
    }
    exit();
}

// Fonction pour envoyer une réponse JSON propre
function sendJsonResponse($response) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit();
}

// Traiter les actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $response = ['success' => false, 'message' => 'Action non reconnue'];
    
    switch ($action) {
        case 'ajouter':
            $produitId = isset($_POST['produitId']) ? intval($_POST['produitId']) : 0;
            $quantite = isset($_POST['quantite']) ? intval($_POST['quantite']) : 1;
            
            if ($produitId <= 0) {
                $response = ['success' => false, 'message' => 'ID produit invalide'];
            } else {
                $response = ajouterAuPanier($produitId, $quantite);
            }
            break;
            
        case 'modifier':
            $produitId = isset($_POST['produitId']) ? intval($_POST['produitId']) : 0;
            $delta = isset($_POST['delta']) ? intval($_POST['delta']) : 0;
            $response = modifierQuantite($produitId, $delta);
            break;
            
        case 'supprimer':
            $produitId = isset($_POST['produitId']) ? intval($_POST['produitId']) : 0;
            $response = supprimerDuPanier($produitId);
            break;
            
        case 'vider':
            $response = viderPanier();
            break;
    }
    
    // Pour les requêtes AJAX, renvoyer JSON
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        sendJsonResponse($response);
    }
    
    // Sinon, rediriger
    redirectAfterAction();
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = isset($_GET['action']) ? $_GET['action'] : 'get';
    
    switch ($action) {
        case 'ajouter':
            $produitId = isset($_GET['produitId']) ? intval($_GET['produitId']) : 0;
            $quantite = isset($_GET['quantite']) ? intval($_GET['quantite']) : 1;
            $response = ajouterAuPanier($produitId, $quantite);
            redirectAfterAction();
            break;
            
        case 'modifier':
            $produitId = isset($_GET['produitId']) ? intval($_GET['produitId']) : 0;
            $delta = isset($_GET['delta']) ? intval($_GET['delta']) : 0;
            $response = modifierQuantite($produitId, $delta);
            redirectAfterAction();
            break;

        case 'supprimer':
            $produitId = isset($_GET['produitId']) ? intval($_GET['produitId']) : 0;
            $response = supprimerDuPanier($produitId);
            redirectAfterAction();
            break;
    
        case 'vider':
            $response = viderPanier();
            redirectAfterAction();
            break;
            
        case 'get':
            $panier = $_SESSION['panier'];
            $sousTotal = 0;
            foreach ($panier as $item) {
                $sousTotal += (float)$item['prix'] * (int)$item['quantite'];
            }
            $fraisLivraison = 1000;
            $total = $sousTotal + $fraisLivraison;
            
            $totalItems = 0;
            foreach ($panier as $item) {
                $totalItems += (int)$item['quantite'];
            }
            
            $response = [
                'success' => true,
                'panier' => $panier,
                'sousTotal' => $sousTotal,
                'fraisLivraison' => $fraisLivraison,
                'total' => $total,
                'cartCount' => $totalItems
            ];
            
            sendJsonResponse($response);
            break;
            
        default:
            $response = ['success' => false, 'message' => 'Action non reconnue'];
            sendJsonResponse($response);
            break;
    }
}

// Si on arrive ici, c'est une erreur
sendJsonResponse(['success' => false, 'message' => 'Méthode non autorisée']);
?>
