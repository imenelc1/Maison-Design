<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$pdo = new PDO(
    'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'] . ';charset=utf8mb4',
    $_ENV['DB_USER'],
    $_ENV['DB_PASS']
);

echo "=== CLIENTS ===\n";
$stmt = $pdo->query('SELECT IdClient, NomClient, PrenomClient, Email FROM client');
foreach ($stmt->fetchAll() as $row) {
    echo $row['IdClient'] . ' | ' . $row['PrenomClient'] . ' ' . $row['NomClient'] . ' | ' . $row['Email'] . "\n";
}

echo "\n=== SESSION user_id au moment du checkout ===\n";
echo "Vérifie que ton user_id de session correspond à un IdClient ci-dessus\n";