<?php
// Démarrer la session pour pouvoir accéder aux variables de session
session_start();

// Connexion à la base de données
require_once '../php/db.php';

// Initialiser le panier s'il n'existe pas
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Fonction pour ajouter un produit au panier
function ajouterAuPanier($produitId, $quantite = 1) {
    global $pdo;
    
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
    
    // Vérifier si le produit est déjà dans le panier
    $produitExiste = false;
    foreach ($_SESSION['panier'] as &$item) {
        if ($item['id'] == $produitId) {
            $item['quantite'] += $quantite;
            $produitExiste = true;
            break;
        }
    }
    
    // Si le produit n'est pas dans le panier, l'ajouter
    if (!$produitExiste) {
        $_SESSION['panier'][] = [
            'id' => $produit['IdProduit'],
            'nom' => $produit['NomProduit'],
            'prix' => $produit['Prix'],
            'image' => $produit['image'] ?? 'images/placeholder.jpeg',
            'quantite' => $quantite
        ];
    }
    
    return ['success' => true, 'message' => 'Produit ajouté au panier'];
}

// Fonction pour modifier la quantité d'un produit dans le panier
function modifierQuantite($produitId, $delta) {
    $produitExiste = false;
    
    foreach ($_SESSION['panier'] as $key => &$item) {
        if ($item['id'] == $produitId) {
            $item['quantite'] += $delta;
            
            // Supprimer le produit si la quantité est 0 ou moins
            if ($item['quantite'] <= 0) {
                unset($_SESSION['panier'][$key]);
                $_SESSION['panier'] = array_values($_SESSION['panier']); // Réindexer le tableau
            }
            
            $produitExiste = true;
            break;
        }
    }
    
    if (!$produitExiste) {
        return ['success' => false, 'message' => 'Produit non trouvé dans le panier'];
    }
    
    return ['success' => true, 'message' => 'Quantité modifiée'];
}

// Fonction pour supprimer un produit du panier
function supprimerDuPanier($produitId) {
    foreach ($_SESSION['panier'] as $key => $item) {
        if ($item['id'] == $produitId) {
            unset($_SESSION['panier'][$key]);
            $_SESSION['panier'] = array_values($_SESSION['panier']); // Réindexer le tableau
            return ['success' => true, 'message' => 'Produit supprimé du panier'];
        }
    }
    
    return ['success' => false, 'message' => 'Produit non trouvé dans le panier'];
}

// Fonction pour vider le panier
function viderPanier() {
    $_SESSION['panier'] = [];
    return ['success' => true, 'message' => 'Panier vidé'];
}

// Fonction pour obtenir le contenu du panier
function getPanier() {
    return $_SESSION['panier'];
}

// Fonction pour calculer le total du panier
function calculerTotal() {
    $sousTotal = 0;
    
    foreach ($_SESSION['panier'] as $item) {
        $sousTotal += $item['prix'] * $item['quantite'];
    }
    
    $fraisLivraison = 1000; // Frais de livraison fixes
    $total = $sousTotal + $fraisLivraison;
    
    return [
        'sousTotal' => $sousTotal,
        'fraisLivraison' => $fraisLivraison,
        'total' => $total
    ];
}

// Traiter les actions en fonction de la méthode HTTP et des paramètres
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $response = ['success' => false, 'message' => 'Action non reconnue'];
    
    switch ($action) {
        case 'ajouter':
            $produitId = isset($_POST['produitId']) ? intval($_POST['produitId']) : 0;
            $quantite = isset($_POST['quantite']) ? intval($_POST['quantite']) : 1;
            $response = ajouterAuPanier($produitId, $quantite);
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
    
    // Rediriger vers la page précédente après l'action
    if (isset($_SERVER['HTTP_REFERER'])) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    } else {
        // Renvoyer la réponse au format JSON si pas de référent
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = isset($_GET['action']) ? $_GET['action'] : 'get';
    
    switch ($action) {
        case 'ajouter':
            $produitId = isset($_GET['produitId']) ? intval($_GET['produitId']) : 0;
            $quantite = isset($_GET['quantite']) ? intval($_GET['quantite']) : 1;
            $response = ajouterAuPanier($produitId, $quantite);
            
            // Rediriger vers la page précédente après l'action
            if (isset($_SERVER['HTTP_REFERER'])) {
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit();
            } else {
                // Rediriger vers la page du panier
                header('Location: ../cart.php');
                exit();
            }
            break;
            
        case 'modifier':
            $produitId = isset($_GET['produitId']) ? intval($_GET['produitId']) : 0;
            $delta = isset($_GET['delta']) ? intval($_GET['delta']) : 0;
            $response = modifierQuantite($produitId, $delta);
            
            // Rediriger vers la page précédente après l'action
            if (isset($_SERVER['HTTP_REFERER'])) {
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit();
            } else {
                // Rediriger vers la page du panier
                header('Location: ../cart.php');
                exit();
            }
            break;
            
        case 'supprimer':
            $produitId = isset($_GET['produitId']) ? intval($_GET['produitId']) : 0;
            $response = supprimerDuPanier($produitId);
            
            // Rediriger vers la page précédente après l'action
            if (isset($_SERVER['HTTP_REFERER'])) {
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit();
            } else {
                // Rediriger vers la page du panier
                header('Location: ../cart.php');
                exit();
            }
            break;
            
        case 'vider':
            $response = viderPanier();
            
            // Rediriger vers la page précédente après l'action
            if (isset($_SERVER['HTTP_REFERER'])) {
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit();
            } else {
                // Rediriger vers la page du panier
                header('Location: ../cart.php');
                exit();
            }
            break;
            
        case 'get':
            $panier = getPanier();
            $totaux = calculerTotal();
            $response = [
                'panier' => $panier,
                'sousTotal' => $totaux['sousTotal'],
                'fraisLivraison' => $totaux['fraisLivraison'],
                'total' => $totaux['total']
            ];
            
            // Renvoyer la réponse au format JSON
            header('Content-Type: application/json');
            echo json_encode($response);
            break;
            
        default:
            $response = ['success' => false, 'message' => 'Action non reconnue'];
            
            // Renvoyer la réponse au format JSON
            header('Content-Type: application/json');
            echo json_encode($response);
            break;
    }
}
?>
