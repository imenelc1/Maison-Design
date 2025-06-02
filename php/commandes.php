<?php
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if (isset($_GET['details']) && $_GET['details'] == 'true') {
            getOrderDetails();
        } else {
            getOrders();
        }
        break;
    case 'PUT':
        updateOrderStatus();
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée']);
        break;
}

function getOrders() {
    global $pdo;
    
    try {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 4;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        $offset = ($page - 1) * $limit;
        
        // Requête avec jointure pour récupérer le nom du client
        $sql = "SELECT c.IdCommande as id, 
                CONCAT(cl.PrenomClient, ' ', cl.NomClient) as client,
                DATE_FORMAT(c.DateCommande, '%Y-%m-%d') as date,
                c.Status as statut, c.TotalPrix as total
                FROM commande c 
                LEFT JOIN client cl ON c.IdClient = cl.IdClient";
        
        $params = [];
        
        // Ajouter la recherche si nécessaire
        if (!empty($search)) {
            $sql .= " WHERE cl.NomClient LIKE :search OR cl.PrenomClient LIKE :search 
                     OR c.Status LIKE :search OR c.IdCommande LIKE :search";
            $params[':search'] = "%$search%";
        }
        
        // Compter le total
        $countSql = str_replace("SELECT c.IdCommande as id, CONCAT(cl.PrenomClient, ' ', cl.NomClient) as client, DATE_FORMAT(c.DateCommande, '%Y-%m-%d') as date, c.Status as statut, c.TotalPrix as total", "SELECT COUNT(*)", $sql);
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();
        
        // Ajouter la pagination - CORRECTION ICI
        $sql .= " ORDER BY c.DateCommande DESC LIMIT $limit OFFSET $offset";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();
        
        // Convertir les statuts pour correspondre au JS
        foreach ($orders as &$order) {
            switch ($order['statut']) {
                case 'en attente':
                    $order['statut'] = 'En attente';
                    break;
                case 'expe?die?':
                    $order['statut'] = 'En cours';
                    break;
                case 'livre?':
                    $order['statut'] = 'Livré';
                    break;
                case 'annule?':
                    $order['statut'] = 'Annulé';
                    break;
            }
        }
        
        echo json_encode([
            'success' => true,
            'data' => $orders,
            'total' => $total,
            'page' => $page,
            'totalPages' => ceil($total / $limit)
        ]);
        
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la récupération des commandes: ' . $e->getMessage()]);
    }
}

function getOrderDetails() {
    global $pdo;
    
    try {
        $orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($orderId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID commande invalide']);
            return;
        }
        
        // Récupérer les détails de la commande
        $sql = "SELECT p.NomProduit as produit, pa.Qtt as quantite, 
                p.Prix as prix, (pa.Qtt * p.Prix) as total
                FROM panier pa
                JOIN produit p ON pa.IdProd = p.IdProduit
                WHERE pa.IdCom = :order_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':order_id' => $orderId]);
        $details = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => $details
        ]);
        
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la récupération des détails: ' . $e->getMessage()]);
    }
}

function updateOrderStatus() {
    global $pdo;
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $id = $input['id'] ?? 0;
        $statut = $input['statut'] ?? '';
        
        if ($id <= 0 || empty($statut)) {
            http_response_code(400);
            echo json_encode(['error' => 'Données invalides']);
            return;
        }
        
        // Convertir le statut pour la base de données
        $dbStatut = '';
        switch ($statut) {
            case 'En attente':
                $dbStatut = 'en attente';
                break;
            case 'En cours':
                $dbStatut = 'expe?die?';
                break;
            case 'Livré':
                $dbStatut = 'livre?';
                break;
            case 'Annulé':
                $dbStatut = 'annule?';
                break;
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Statut invalide']);
                return;
        }
        
        // Mettre à jour le statut
        $stmt = $pdo->prepare("UPDATE commande SET Status = :statut WHERE IdCommande = :id");
        $stmt->execute([':statut' => $dbStatut, ':id' => $id]);
        
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Commande non trouvée']);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Statut de la commande #' . $id . ' modifié avec succès'
        ]);
        
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la modification: ' . $e->getMessage()]);
    }
}
?>