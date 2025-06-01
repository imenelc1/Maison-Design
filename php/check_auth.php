<?php
// Démarrer la session
session_start();

// Préparer la réponse
$response = [
    'isLoggedIn' => false,
    'role' => 'guest',
    'prenom' => '',
    'nom' => ''
];

// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $response['isLoggedIn'] = true;
    $response['role'] = $_SESSION['role'];
    
    // Ajouter des informations supplémentaires si disponibles
    if (isset($_SESSION['prenom'])) {
        $response['prenom'] = $_SESSION['prenom'];
    }
    
    if (isset($_SESSION['nom'])) {
        $response['nom'] = $_SESSION['nom'];
    }
}

// Envoyer la réponse au format JSON
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
