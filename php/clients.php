<?php
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        getClients();
        break;
    case 'DELETE':
        deleteClient();
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée']);
        break;
}

function getClients() {
    global $pdo;
    
    try {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 4;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        $offset = ($page - 1) * $limit;
        
        // Requête de base
        $sql = "SELECT IdClient as id, NomClient as nom, PrenomClient as prenom, 
                Email as email, NumTel as telephone, Adresse as adresse 
                FROM client";
        
        $params = [];
        
        // Ajouter la recherche si nécessaire
        if (!empty($search)) {
            $sql .= " WHERE NomClient LIKE :search OR PrenomClient LIKE :search 
                     OR Email LIKE :search OR NumTel LIKE :search OR Adresse LIKE :search";
            $params[':search'] = "%$search%";
        }
        
        // Compter le total
        $countSql = str_replace("SELECT IdClient as id, NomClient as nom, PrenomClient as prenom, Email as email, NumTel as telephone, Adresse as adresse", "SELECT COUNT(*)", $sql);
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();
        
        // Ajouter la pagination - CORRECTION ICI
        $sql .= " ORDER BY IdClient DESC LIMIT $limit OFFSET $offset";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $clients = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => $clients,
            'total' => $total,
            'page' => $page,
            'totalPages' => ceil($total / $limit)
        ]);
        
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la récupération des clients: ' . $e->getMessage()]);
    }
}

function deleteClient() {
    global $pdo;
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = isset($input['id']) ? (int)$input['id'] : 0;
        
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID client invalide']);
            return;
        }
        
        // Vérifier si le client existe
        $checkStmt = $pdo->prepare("SELECT NomClient FROM client WHERE IdClient = :id");
        $checkStmt->execute([':id' => $id]);
        $client = $checkStmt->fetch();
        
        if (!$client) {
            http_response_code(404);
            echo json_encode(['error' => 'Client non trouvé']);
            return;
        }
        
        // Supprimer le client
        $stmt = $pdo->prepare("DELETE FROM client WHERE IdClient = :id");
        $stmt->execute([':id' => $id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Client ' . $client['NomClient'] . ' supprimé avec succès'
        ]);
        
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
    }
}
?>