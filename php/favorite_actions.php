<?php
session_start();
header('Content-Type: application/json');

// Debug amélioré
error_log("=== FAVORITES ACTION DEBUG ===");
error_log("Session ID: " . session_id());
error_log("Session data: " . print_r($_SESSION, true));
error_log("POST data: " . print_r($_POST, true));
error_log("Cookies: " . print_r($_COOKIE, true));

// Vérifier si l'utilisateur est connecté avec plus de détails
if (!isset($_SESSION['client_id']) || empty($_SESSION['client_id'])) {
    error_log("ERREUR: Utilisateur non connecté");
    error_log("client_id isset: " . (isset($_SESSION['client_id']) ? 'oui' : 'non'));
    error_log("client_id empty: " . (empty($_SESSION['client_id']) ? 'oui' : 'non'));
    error_log("client_id value: " . (isset($_SESSION['client_id']) ? $_SESSION['client_id'] : 'non défini'));
    
    echo json_encode([
        'success' => false, 
        'message' => 'Utilisateur non connecté',
        'debug' => [
            'session_id' => session_id(),
            'client_id_isset' => isset($_SESSION['client_id']),
            'client_id_value' => $_SESSION['client_id'] ?? 'non défini'
        ]
    ]);
    exit;
}

require_once 'db.php';

$clientId = $_SESSION['client_id'];
$action = $_POST['action'] ?? '';
$produitId = intval($_POST['produitId'] ?? 0);

error_log("Client ID: $clientId, Action: $action, Produit ID: $produitId");

if ($produitId <= 0) {
    error_log("ERREUR: ID produit invalide: $produitId");
    echo json_encode(['success' => false, 'message' => 'ID produit invalide']);
    exit;
}

try {
    if ($action === 'toggle') {
        // Vérifier si le produit existe
        $stmtProduct = $pdo->prepare("SELECT COUNT(*) FROM produit WHERE IdProduit = ?");
        $stmtProduct->execute([$produitId]);
        if ($stmtProduct->fetchColumn() == 0) {
            error_log("ERREUR: Produit inexistant: $produitId");
            echo json_encode(['success' => false, 'message' => 'Produit inexistant']);
            exit;
        }
        
        // Vérifier si le client existe
        $stmtClient = $pdo->prepare("SELECT COUNT(*) FROM client WHERE IdClient = ?");
        $stmtClient->execute([$clientId]);
        if ($stmtClient->fetchColumn() == 0) {
            error_log("ERREUR: Client inexistant: $clientId");
            echo json_encode(['success' => false, 'message' => 'Client inexistant']);
            exit;
        }
        
        // Vérifier si le produit est déjà dans les favoris
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM favoris WHERE IdClient = ? AND IdProduit = ?");
        $stmt->execute([$clientId, $produitId]);
        $exists = $stmt->fetchColumn() > 0;
        
        error_log("Produit existe dans favoris: " . ($exists ? 'oui' : 'non'));
        
        if ($exists) {
            // Retirer des favoris
            $stmt = $pdo->prepare("DELETE FROM favoris WHERE IdClient = ? AND IdProduit = ?");
            $result = $stmt->execute([$clientId, $produitId]);
            error_log("Résultat suppression: " . ($result ? 'succès' : 'échec'));
            error_log("Lignes affectées: " . $stmt->rowCount());
            
            echo json_encode([
                'success' => true, 
                'action' => 'removed', 
                'message' => 'Produit retiré des favoris',
                'debug' => ['rows_affected' => $stmt->rowCount()]
            ]);
        } else {
            // Ajouter aux favoris
            $stmt = $pdo->prepare("INSERT INTO favoris (IdClient, IdProduit, DateAjout) VALUES (?, ?, NOW())");
            $result = $stmt->execute([$clientId, $produitId]);
            error_log("Résultat insertion: " . ($result ? 'succès' : 'échec'));
            error_log("Lignes affectées: " . $stmt->rowCount());
            
            echo json_encode([
                'success' => true, 
                'action' => 'added', 
                'message' => 'Produit ajouté aux favoris',
                'debug' => ['rows_affected' => $stmt->rowCount()]
            ]);
        }
    } else {
        error_log("ERREUR: Action non reconnue: $action");
        echo json_encode(['success' => false, 'message' => 'Action non reconnue: ' . $action]);
    }
    
} catch (PDOException $e) {
    error_log("ERREUR PDO: " . $e->getMessage());
    error_log("Code erreur: " . $e->getCode());
    echo json_encode([
        'success' => false, 
        'message' => 'Erreur de base de données: ' . $e->getMessage(),
        'debug' => ['error_code' => $e->getCode()]
    ]);
}
?>
