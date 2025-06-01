<?php
// Démarrer la session et vérifier si l'utilisateur est connecté
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Non autorisé']);
    exit();
}

// Connexion à la base de données
require_once 'db.php';

// Récupérer les données JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Données invalides']);
    exit();
}

$clientId = $_SESSION['user_id'];
$prenom = trim($data['prenom'] ?? '');
$nom = trim($data['nom'] ?? '');
$email = trim($data['email'] ?? '');
$telephone = trim($data['telephone'] ?? '');

// Validation
if (empty($prenom) || empty($nom) || empty($email)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Tous les champs obligatoires doivent être remplis']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Adresse email invalide']);
    exit();
}

try {
    // Vérifier si l'email n'est pas déjà utilisé par un autre client
    $stmt = $pdo->prepare("SELECT IdClient FROM client WHERE Email = ? AND IdClient != ?");
    $stmt->execute([$email, $clientId]);
    
    if ($stmt->fetch()) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Cette adresse email est déjà utilisée']);
        exit();
    }

    // Mettre à jour le profil
    $stmt = $pdo->prepare("
        UPDATE client 
        SET PrenomClient = ?, NomClient = ?, Email = ?, NumTel = ?
        WHERE IdClient = ?
    ");
    
    $stmt->execute([$prenom, $nom, $email, $telephone, $clientId]);

    // Mettre à jour les variables de session
    $_SESSION['prenom'] = $prenom;
    $_SESSION['nom'] = $nom;
    $_SESSION['email'] = $email;
    $_SESSION['telephone'] = $telephone;

    // Retourner le succès
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Profil mis à jour avec succès'
    ]);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()]);
}
?>
