<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'not_authenticated']);
    exit();
}

// Inclure la connexion à la base de données
require_once 'db.php';

// Vérifier si les données du formulaire sont présentes
if (isset($_POST['prenom'], $_POST['nom'], $_POST['email'], $_POST['telephone'])) {
    // Récupérer et nettoyer les données
    $prenom = htmlspecialchars(trim($_POST['prenom']));
    $nom = htmlspecialchars(trim($_POST['nom']));
    $email = htmlspecialchars(trim($_POST['email']));
    $telephone = htmlspecialchars(trim($_POST['telephone']));
    $clientId = $_SESSION['user_id'];

    try {
        // Vérifier si l'email existe déjà pour un autre client
        $stmt = $pdo->prepare("SELECT IdClient FROM client WHERE Email = :email AND IdClient != :id");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $clientId);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // L'email existe déjà pour un autre client
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Cet email est déjà utilisé par un autre compte.']);
            exit();
        }

        // Mettre à jour les informations du client
        $stmt = $pdo->prepare("UPDATE client SET PrenomClient = :prenom, NomClient = :nom, Email = :email, Telephone = :telephone WHERE IdClient = :id");
        $stmt->bindParam(':prenom', $prenom);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telephone', $telephone);
        $stmt->bindParam(':id', $clientId);
        $stmt->execute();

        // Mettre à jour l'email dans la session
        $_SESSION['email'] = $email;

        // Renvoyer une réponse de succès
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);

    } catch (PDOException $e) {
        // En cas d'erreur, renvoyer un message d'erreur
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    // Données manquantes
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Données manquantes']);
}
?>