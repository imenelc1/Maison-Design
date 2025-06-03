<?php
// Test simple pour vérifier que cart_actions.php fonctionne
session_start();

echo "<h1>Test simple du panier</h1>";

// Simuler une requête AJAX directement
$_POST['action'] = 'ajouter';
$_POST['produitId'] = 1;
$_POST['quantite'] = 1;
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';

// Capturer la sortie
ob_start();
include 'php/cart_actions.php';
$output = ob_get_clean();

echo "<h2>Sortie brute:</h2>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

echo "<h2>Test de décodage JSON:</h2>";
$json = json_decode($output, true);
if ($json === null) {
    echo "<p style='color: red;'>Erreur JSON: " . json_last_error_msg() . "</p>";
} else {
    echo "<p style='color: green;'>JSON valide:</p>";
    echo "<pre>" . print_r($json, true) . "</pre>";
}
?>
