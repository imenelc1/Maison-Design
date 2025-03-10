<?php
// Utilisation de PDO pour la connexion à la base de données
$host = 'localhost';
$user = 'root';
$password = '';  // Par défaut, c'est vide sur WampServer
$database = 'maisondesign';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connexion réussie à la base de données";
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>