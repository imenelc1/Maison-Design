<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

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
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        
        if (isset($_GET['all']) && $_GET['all'] == 'true') {
            $limit = null;
        }
        
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        $sql = "SELECT DISTINCT c.IdCommande as id, 
                CONCAT(COALESCE(cl.PrenomClient, ''), ' ', COALESCE(cl.NomClient, '')) as client,
                DATE_FORMAT(c.DateCommande, '%Y-%m-%d') as date,
                c.Status as statut, 
                c.TotalPrix as total,
                l.StatutLivraison as statut_livraison
                FROM commande c 
                LEFT JOIN client cl ON c.IdClient = cl.IdClient
                LEFT JOIN livraison l ON c.IdCommande = l.IdComm";
        
        $params = [];
        
        if (!empty($search)) {
            $sql .= " WHERE (cl.NomClient LIKE :search OR cl.PrenomClient LIKE :search 
                     OR c.Status LIKE :search OR c.IdCommande LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        $countSql = "SELECT COUNT(DISTINCT c.IdCommande) FROM commande c LEFT JOIN client cl ON c.IdClient = cl.IdClient";
        if (!empty($search)) {
            $countSql .= " WHERE (cl.NomClient LIKE :search OR cl.PrenomClient LIKE :search 
                         OR c.Status LIKE :search OR c.IdCommande LIKE :search)";
        }
        
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();
        
        $sql .= " ORDER BY c.DateCommande DESC";
        if ($limit !== null) {
            $offset = ($page - 1) * $limit;
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($orders as &$order) {
            $order['client'] = trim($order['client']);
            if (empty($order['client'])) {
                $order['client'] = 'Client inconnu';
            }
            
            switch (strtolower(trim($order['statut']))) {
                case 'en attente':
                case 'en_attente':
                case '':
                    $order['statut'] = 'En attente';
                    break;
                case 'expe?die?':
                case 'expedie':
                case 'expédié':
                    $order['statut'] = 'En cours';
                    break;
                case 'livre?':
                case 'livre':
                case 'livré':
                    $order['statut'] = 'Livré';
                    break;
                case 'annule?':
                case 'annule':
                case 'annulé':
                    $order['statut'] = 'Annulé';
                    break;
                default:
                    $order['statut'] = 'En attente';
            }
        }
        
        echo json_encode([
            'success' => true,
            'data' => $orders,
            'total' => $total,
            'page' => $page,
            'totalPages' => $limit ? ceil($total / $limit) : 1,
            'showing' => count($orders)
        ]);
        
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la récupération des commandes: ' . $e->getMessage()]);
    }
}

function getOrderDetails() {
    global $pdo;
    
    try {
        $orderId = (int)($_GET['id'] ?? 0);
        if ($orderId <= 0) {
            throw new Exception('ID de commande invalide');
        }

        $checkStmt = $pdo->prepare("SELECT IdCommande FROM commande WHERE IdCommande = ?");
        $checkStmt->execute([$orderId]);
        if (!$checkStmt->fetch()) {
            throw new Exception('Commande introuvable');
        }

        $sql = "SELECT DISTINCT
            p.NomProduit as produit,
            pa.Qtt as quantite,
            p.Prix as prix,
            (pa.Qtt * p.Prix) as total,
            p.IdProduit,
            pa.IdCom
            FROM panier pa
            INNER JOIN produit p ON pa.IdProd = p.IdProduit
            WHERE pa.IdCom = ?
            ORDER BY p.NomProduit";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$orderId]);
        $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $productIds = [];
        $cleanDetails = [];
        
        foreach ($details as $detail) {
            $productId = $detail['IdProduit'];
            
            if (in_array($productId, $productIds)) {
                error_log("DOUBLON DÉTECTÉ: Produit ID $productId dans commande $orderId");
                continue;
            }
            
            $productIds[] = $productId;
            
            $cleanDetail = [
                'produit' => htmlspecialchars($detail['produit']),
                'quantite' => (int)$detail['quantite'],
                'prix' => (float)$detail['prix'],
                'total' => (float)$detail['total']
            ];
            
            $cleanDetails[] = $cleanDetail;
        }

        $orderSql = "SELECT 
            c.IdCommande as id,
            CONCAT(COALESCE(cl.PrenomClient, ''), ' ', COALESCE(cl.NomClient, '')) as client,
            DATE_FORMAT(c.DateCommande, '%d/%m/%Y à %H:%i') as date,
            c.Status as statut,
            c.TotalPrix as total,
            l.Adresse as adresse_livraison,
            l.StatutLivraison as statut_livraison
            FROM commande c
            LEFT JOIN client cl ON c.IdClient = cl.IdClient
            LEFT JOIN livraison l ON c.IdCommande = l.IdComm
            WHERE c.IdCommande = ?";
        
        $orderStmt = $pdo->prepare($orderSql);
        $orderStmt->execute([$orderId]);
        $orderInfo = $orderStmt->fetch(PDO::FETCH_ASSOC);

        if (!$orderInfo) {
            throw new Exception('Informations de commande introuvables');
        }

        $orderInfo['client'] = trim($orderInfo['client']);
        if (empty($orderInfo['client'])) {
            $orderInfo['client'] = 'Client inconnu';
        }

        $totalCalcule = array_sum(array_column($cleanDetails, 'total'));
        
        $debug = [
            'produits_trouves' => count($cleanDetails),
            'total_commande' => (float)$orderInfo['total'],
            'total_calcule' => $totalCalcule,
            'difference' => abs((float)$orderInfo['total'] - $totalCalcule)
        ];

        echo json_encode([
            'success' => true,
            'data' => $cleanDetails,
            'orderInfo' => $orderInfo,
            'debug' => $debug
        ]);

    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Erreur lors de la récupération des détails',
            'message' => $e->getMessage(),
            'orderId' => $orderId ?? 'non défini'
        ]);
    }
}

function updateOrderStatus() {
    global $pdo;
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $id = (int)($input['id'] ?? 0);
        $statut = trim($input['statut'] ?? '');
        
        if ($id <= 0 || empty($statut)) {
            http_response_code(400);
            echo json_encode(['error' => 'Données invalides']);
            return;
        }
        
        $statusMapping = [
            'En attente' => 'en attente',
            'En cours' => 'expe?die?',
            'Livré' => 'livre?',
            'Annulé' => 'annule?'
        ];
        
        if (!isset($statusMapping[$statut])) {
            http_response_code(400);
            echo json_encode(['error' => 'Statut invalide: ' . $statut]);
            return;
        }
        
        $dbStatut = $statusMapping[$statut];
        
        // Vérifier que la commande existe
        $checkStmt = $pdo->prepare("SELECT IdCommande FROM commande WHERE IdCommande = ?");
        $checkStmt->execute([$id]);
        if (!$checkStmt->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Commande non trouvée']);
            return;
        }
        
        // Utiliser une transaction pour s'assurer que tout se passe bien
        $pdo->beginTransaction();
        
        try {
            // Mettre à jour le statut de la commande
            $stmt = $pdo->prepare("UPDATE commande SET Status = ? WHERE IdCommande = ?");
            $result1 = $stmt->execute([$dbStatut, $id]);
            
            if (!$result1) {
                throw new Exception("Erreur lors de la mise à jour du statut de commande");
            }
            
            //Vérifier d'abord si une entrée de livraison existe
            $checkLivraison = $pdo->prepare("SELECT IdComm FROM livraison WHERE IdComm = ?");
            $checkLivraison->execute([$id]);
            $livraisonExists = $checkLivraison->fetch();
            
            if ($livraisonExists) {
                // Mettre à jour le statut de livraison selon le statut de commande
                $statutLivraison = '';
                switch ($statut) {
                    case 'En attente':
                        $statutLivraison = 'En attente';
                        break;
                    case 'En cours':
                        $statutLivraison = 'En route';
                        break;
                    case 'Livré':
                        $statutLivraison = 'livre';
                        break;
                    case 'Annulé':
                        $statutLivraison = 'Annulé';
                        break;
                }
                
                if (!empty($statutLivraison)) {
                    $updateLivraison = $pdo->prepare("UPDATE livraison SET StatutLivraison = ? WHERE IdComm = ?");
                    $result2 = $updateLivraison->execute([$statutLivraison, $id]);
                    
                    if (!$result2) {
                        throw new Exception("Erreur lors de la mise à jour du statut de livraison");
                    }
                    
                    // Log pour debug
                    error_log("Statut de livraison mis à jour: Commande $id -> $statutLivraison");
                }
            } else {
                //  Créer une entrée de livraison si elle n'existe pas
                $createLivraison = $pdo->prepare("
                    INSERT INTO livraison (Adresse, DateLivraison, StatutLivraison, Frais, IdComm) 
                    VALUES ('Adresse non spécifiée', NOW(), ?, 1000, ?)
                ");
                
                $statutLivraison = '';
                switch ($statut) {
                    case 'En attente':
                        $statutLivraison = 'En attente';
                        break;
                    case 'En cours':
                        $statutLivraison = 'En route';
                        break;
                    case 'Livré':
                        $statutLivraison = 'livre';
                        break;
                    case 'Annulé':
                        $statutLivraison = 'Annulé';
                        break;
                }
                
                if (!empty($statutLivraison)) {
                    $result3 = $createLivraison->execute([$statutLivraison, $id]);
                    
                    if (!$result3) {
                        throw new Exception("Erreur lors de la création de l'entrée de livraison");
                    }
                    
                    error_log("Entrée de livraison créée: Commande $id -> $statutLivraison");
                }
            }
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Statut de la commande #' . $id . ' modifié avec succès vers "' . $statut . '"',
                'debug' => [
                    'commande_updated' => $result1,
                    'livraison_exists' => $livraisonExists ? true : false,
                    'statut_livraison' => $statutLivraison ?? 'non défini'
                ]
            ]);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
        
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la modification: ' . $e->getMessage()]);
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur: ' . $e->getMessage()]);
    }
}
?>
