<?php
// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    echo "Vous devez être connecté en tant que client pour accéder à cette page.";
    exit();
}

// Inclure la connexion à la base de données
require_once 'db.php';

// Récupérer l'ID du client depuis la session
$clientId = $_SESSION['user_id'];

try {
    // Récupérer la structure de la table client
    $stmt = $pdo->prepare("DESCRIBE client");
    $stmt->execute();
    $structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Structure de la table client</h2>";
    echo "<pre>";
    print_r($structure);
    echo "</pre>";
    
    // Récupérer les informations du client
    $stmt = $pdo->prepare("SELECT * FROM client WHERE IdClient = :id");
    $stmt->bindParam(':id', $clientId, PDO::PARAM_INT);
    $stmt->execute();
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h2>Données du client</h2>";
    echo "<pre>";
    print_r($client);
    echo "</pre>";
    
    echo "<h2>Données de session</h2>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<h2>Erreur</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
