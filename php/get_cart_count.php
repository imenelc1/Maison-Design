<?php
// Fichier pour récupérer le nombre d'articles dans le panier
session_start();

// Initialiser le panier s'il n'existe pas
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Calculer le nombre total d'articles
$totalItems = 0;
foreach ($_SESSION['panier'] as $item) {
    $totalItems += $item['quantite'];
}

// Renvoyer la réponse JSON
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success' => true,
    'count' => $totalItems
]);
?>
