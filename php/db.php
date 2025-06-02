<?php
// Connexion à la base de données pour les pages web
$host = 'localhost';
$user = 'root';
$password = '';  
$database = 'maisondesign';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die('Erreur de connexion à la base de données: ' . $e->getMessage());
}
?>