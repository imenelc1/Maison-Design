<?php
// Démarrer la session pour pouvoir accéder aux variables de session
session_start();

// Connexion à la base de données
require_once 'db.php';

// Initialiser le panier s'il n'existe pas
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Fonction pour ajouter un produit au panier
function ajouterAuPanier($produitId, $quantite = 1) {
    global $pdo;
    
    error_log("DEBUG - Ajout au panier: produitId=$produitId, quantite=$quantite");
    
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
        error_log("DEBUG - Produit non trouvé: $produitId");
        return ['success' => false, 'message' => 'Produit non trouvé'];
    }
    
    error_log("DEBUG - Produit trouvé: " . $produit['NomProduit'] . ", Stock: " . $produit['Stock']);
    
    // NOUVELLE VÉRIFICATION DU STOCK
    $stockDisponible = (int)$produit['Stock'];
    if ($stockDisponible <= 0) {
        return ['success' => false, 'message' => 'Ce produit n\'est plus disponible en stock'];
    }

    // Vérifier si le produit est déjà dans le panier
    $quantiteActuelle = 0;
    foreach ($_SESSION['panier'] as $item) {
        if ($item['id'] == $produitId) {
            $quantiteActuelle = $item['quantite'];
            break;
        }
    }

    // Vérifier si la quantité demandée ne dépasse pas le stock
    if (($quantiteActuelle + $quantite) > $stockDisponible) {
        $quantiteRestante = $stockDisponible - $quantiteActuelle;
        if ($quantiteRestante <= 0) {
            return ['success' => false, 'message' => 'Vous avez déjà le maximum disponible de ce produit dans votre panier'];
        } else {
            return ['success' => false, 'message' => "Stock insuffisant. Il ne reste que {$quantiteRestante} exemplaire(s) disponible(s)"];
        }
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
    global $pdo;
    
    // Vérifier le stock disponible
    $stmt = $pdo->prepare("SELECT Stock FROM produit WHERE IdProduit = ?");
    $stmt->execute([$produitId]);
    $produit = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$produit) {
        return ['success' => false, 'message' => 'Produit non trouvé'];
    }
    
    $stockDisponible = (int)$produit['Stock'];
    $produitExiste = false;
    
    foreach ($_SESSION['panier'] as $key => &$item) {
        if ($item['id'] == $produitId) {
            $nouvelleQuantite = $item['quantite'] + $delta;
            
            // Vérifier le stock si on augmente la quantité
            if ($delta > 0 && $nouvelleQuantite > $stockDisponible) {
                return ['success' => false, 'message' => "Stock insuffisant. Il ne reste que {$stockDisponible} exemplaire(s) disponible(s)"];
            }
            
            $item['quantite'] = $nouvelleQuantite;
            
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

// Fonction pour rediriger après une action
function redirectAfterAction() {
    if (isset($_SERVER['HTTP_REFERER'])) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    } else {
        header('Location: ../cart.php');
    }
    exit();
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
    
    // Pour les requêtes AJAX, renvoyer JSON
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
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
            $panier = getPanier();
            $totaux = calculerTotal();
            $response = [
                'success' => true,
                'panier' => $panier,
                'sousTotal' => $totaux['sousTotal'],
                'fraisLivraison' => $totaux['fraisLivraison'],
                'total' => $totaux['total']
            ];
            
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
            break;
            
        default:
            $response = ['success' => false, 'message' => 'Action non reconnue'];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
            break;
    }
}
?>
