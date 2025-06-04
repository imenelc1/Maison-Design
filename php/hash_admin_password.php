<?php
// Inclure la connexion à la base de données
require_once 'db.php'; 

$current_password = 'adminpass'; 

// Hacher le mot de passe
$hashed_password = password_hash($current_password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("UPDATE admin SET MotDePasse = :password WHERE Email = 'admin.pass@maison-design.com'");
    $stmt->bindParam(':password', $hashed_password);
    $result = $stmt->execute();
    
    if ($result) {
        echo "Le mot de passe admin a été mis à jour avec succès!<br>";
        echo "Vous pouvez maintenant vous connecter avec admin@example.com et votre mot de passe.";
    } else {
        echo "Erreur lors de la mise à jour du mot de passe.";
    }
} catch(PDOException $e) {
    echo "Erreur: " . $e->getMessage();
}
?>