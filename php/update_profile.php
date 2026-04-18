<?php
// Démarrer la session
session_start();

// Définir le type de contenu JSON
header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'not_authenticated']);
    exit();
}

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'method_not_allowed']);
    exit();
}

try {
    // Récupérer les données JSON
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        echo json_encode(['error' => 'invalid_json']);
        exit();
    }
    
    // Valider les données
    $prenom = trim($data['prenom'] ?? '');
    $nom = trim($data['nom'] ?? '');
    $email = trim($data['email'] ?? '');
    $telephone = trim($data['telephone'] ?? '');
    
    if (empty($prenom) || empty($nom) || empty($email)) {
        echo json_encode(['error' => 'missing_required_fields']);
        exit();
    }
    
    // Valider l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['error' => 'invalid_email']);
        exit();
    }
    
    // Connexion à la base de données
    require_once 'db.php';
    
    $clientId = $_SESSION['user_id'];
    
    // Vérifier si l'email n'est pas déjà utilisé par un autre utilisateur
    $stmtCheck = $pdo->prepare("SELECT IdClient FROM client WHERE Email = ? AND IdClient != ?");
    $stmtCheck->execute([$email, $clientId]);
    
    if ($stmtCheck->fetch()) {
        echo json_encode(['error' => 'email_already_exists']);
        exit();
    }
    
    // Mettre à jour le profil
    $stmtUpdate = $pdo->prepare("
        UPDATE client 
        SET PrenomClient = ?, NomClient = ?, Email = ?, NumTel = ?
        WHERE IdClient = ?
    ");
    
    $result = $stmtUpdate->execute([$prenom, $nom, $email, $telephone, $clientId]);
    
    if ($result) {
        // Mettre à jour les variables de session
        $_SESSION['prenom'] = $prenom;
        $_SESSION['nom'] = $nom;
        $_SESSION['email'] = $email;
        $_SESSION['telephone'] = $telephone;
        
        echo json_encode(['success' => true, 'message' => 'Profil mis à jour avec succès']);
    } else {
        echo json_encode(['error' => 'update_failed']);
    }
    
} catch (PDOException $e) {
    error_log("Erreur base de données dans update_profile.php: " . $e->getMessage());
    echo json_encode(['error' => 'database_error']);
} catch (Exception $e) {
    error_log("Erreur générale dans update_profile.php: " . $e->getMessage());
    echo json_encode(['error' => 'general_error']);
}
?>
