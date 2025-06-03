<?php
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        getProducts();
        break;
    case 'POST':
        addProduct();
        break;
    case 'PUT':
        updateProduct();
        break;
    case 'DELETE':
        deleteProduct();
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée']);
        break;
}
function getProducts() {
    global $pdo;
    
    try {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 4;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        $offset = ($page - 1) * $limit;
        
        // Requête principale avec LEFT JOIN pour l'image
        $sql = "SELECT 
                p.IdProduit as id, 
                p.NomProduit as nom, 
                c.NomCategorie as categorie, 
                p.Prix as prix, 
                p.Stock as stock,
                i.URL as image
                FROM produit p 
                LEFT JOIN categorie c ON p.IdCat = c.IdCategorie
                LEFT JOIN imageprod i ON p.IdProduit = i.IdProduit";
        
        $params = [];
        
        if (!empty($search)) {
            $sql .= " WHERE p.NomProduit LIKE :search OR c.NomCategorie LIKE :search";
            $params[':search'] = "%$search%";
        }
        
        // Requête de comptage
        $countSql = "SELECT COUNT(*) FROM produit p";
        if (!empty($search)) {
            $countSql .= " WHERE p.NomProduit LIKE :search";
        }
        
        // Exécution
        $stmt = $pdo->prepare($sql . " LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Comptage total
        $countStmt = $pdo->prepare($countSql);
        foreach ($params as $key => $value) {
            if (strpos($countSql, $key) !== false) {
                $countStmt->bindValue($key, $value);
            }
        }
        $countStmt->execute();
        $total = $countStmt->fetchColumn();
        
        echo json_encode([
            'success' => true,
            'data' => $products,
            'total' => $total,
            'page' => $page,
            'totalPages' => ceil($total / $limit)
        ]);
        
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur SQL: ' . $e->getMessage()]);
    }
}

function addProduct() {
    global $pdo;
    
    try {
        $nom = $_POST['nom'] ?? '';
        $categorie = $_POST['categorie'] ?? '';
        $prix = $_POST['prix'] ?? 0;
        $stock = $_POST['stock'] ?? 0;
        $description = $_POST['description'] ?? '';
        
        if (empty($nom) || empty($categorie) || $prix <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Données invalides']);
            return;
        }
        
        $pdo->beginTransaction();
        
        // Récupérer l'ID de la catégorie
        $catStmt = $pdo->prepare("SELECT IdCategorie FROM categorie WHERE NomCategorie = :categorie");
        $catStmt->execute([':categorie' => $categorie]);
        $catId = $catStmt->fetchColumn();
        
        if (!$catId) {
            // Créer la catégorie si elle n'existe pas
            $maxCatStmt = $pdo->query("SELECT MAX(IdCategorie) FROM categorie");
            $maxCatId = $maxCatStmt->fetchColumn();
            $catId = $maxCatId + 1;
            
            $insertCatStmt = $pdo->prepare("INSERT INTO categorie (IdCategorie, NomCategorie) VALUES (:id, :nom)");
            $insertCatStmt->execute([':id' => $catId, ':nom' => $categorie]);
        }
        
        // Insérer le produit
        $stmt = $pdo->prepare("INSERT INTO produit (NomProduit, Description, Prix, Stock, IdCat) 
                              VALUES (:nom, :description, :prix, :stock, :cat_id)");
        $stmt->execute([
            ':nom' => $nom,
            ':description' => $description,
            ':prix' => $prix,
            ':stock' => $stock,
            ':cat_id' => $catId
        ]);
        
        $productId = $pdo->lastInsertId();
        
        // Gérer l'upload d'image si présente
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = time() . '_' . $_FILES['image']['name'];
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $imageStmt = $pdo->prepare("INSERT INTO imageprod (URL, IdProduit) VALUES (:url, :product_id)");
                $imageStmt->execute([':url' => 'images/' . $fileName, ':product_id' => $productId]);
            }
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Produit ' . $nom . ' ajouté avec succès',
            'id' => $productId
        ]);
        
    } catch(PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de l\'ajout: ' . $e->getMessage()]);
    }
}

function updateProduct() {
    global $pdo;
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $id = $input['id'] ?? 0;
        $nom = $input['nom'] ?? '';
        $categorie = $input['categorie'] ?? '';
        $prix = $input['prix'] ?? 0;
        $stock = $input['stock'] ?? 0;
        
        if ($id <= 0 || empty($nom) || empty($categorie) || $prix <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Données invalides']);
            return;
        }
        
        // Récupérer l'ID de la catégorie
        $catStmt = $pdo->prepare("SELECT IdCategorie FROM categorie WHERE NomCategorie = :categorie");
        $catStmt->execute([':categorie' => $categorie]);
        $catId = $catStmt->fetchColumn();
        
        if (!$catId) {
            http_response_code(400);
            echo json_encode(['error' => 'Catégorie non trouvée']);
            return;
        }
        
        // Mettre à jour le produit
        $stmt = $pdo->prepare("UPDATE produit SET NomProduit = :nom, Prix = :prix, 
                              Stock = :stock, IdCat = :cat_id WHERE IdProduit = :id");
        $stmt->execute([
            ':nom' => $nom,
            ':prix' => $prix,
            ':stock' => $stock,
            ':cat_id' => $catId,
            ':id' => $id
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Produit modifié avec succès'
        ]);
        
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la modification: ' . $e->getMessage()]);
    }
}

function deleteProduct() {
    global $pdo;
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = isset($input['id']) ? (int)$input['id'] : 0;
        
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID produit invalide']);
            return;
        }
        
        // Vérifier si le produit existe
        $checkStmt = $pdo->prepare("SELECT NomProduit FROM produit WHERE IdProduit = :id");
        $checkStmt->execute([':id' => $id]);
        $product = $checkStmt->fetch();
        
        if (!$product) {
            http_response_code(404);
            echo json_encode(['error' => 'Produit non trouvé']);
            return;
        }
        
        $pdo->beginTransaction();
        
        // Supprimer les images associées
        $imageStmt = $pdo->prepare("DELETE FROM imageprod WHERE IdProduit = :id");
        $imageStmt->execute([':id' => $id]);
        
        // Supprimer le produit
        $stmt = $pdo->prepare("DELETE FROM produit WHERE IdProduit = :id");
        $stmt->execute([':id' => $id]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Produit ' . $product['NomProduit'] . ' supprimé avec succès'
        ]);
        
    } catch(PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
    }
}
?>