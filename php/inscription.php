<?php
// Démarrer la session
session_start();

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db.php'; 

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier si tous les champs sont remplis
    if (
        isset($_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['password'], $_POST['adresse'], $_POST['numtel'], $_POST['confirm-password']) &&
        !empty($_POST['nom']) && !empty($_POST['prenom']) && !empty($_POST['email']) &&
        !empty($_POST['password']) && !empty($_POST['adresse']) && !empty($_POST['numtel']) && !empty($_POST['confirm-password'])
    ) {
        $nom = htmlspecialchars(trim($_POST['nom']));
        $prenom = htmlspecialchars(trim($_POST['prenom']));
        $email = htmlspecialchars(trim($_POST['email']));
        $adresse = htmlspecialchars(trim($_POST['adresse']));
        $numtel = htmlspecialchars(trim($_POST['numtel']));
        $password = trim($_POST['password']);
        $confirmPassword = trim($_POST['confirm-password']);

        // Vérifier la correspondance des mots de passe
        if ($password !== $confirmPassword) {
            // Préserver les données du formulaire
            $params = http_build_query([
                'error' => 'password_mismatch',
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'adresse' => $adresse,
                'numtel' => $numtel
            ]);
            header('Location: ../inscription.php?' . $params);
            exit();
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM client WHERE Email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $emailExists = $stmt->fetchColumn();

        if ($emailExists) {
            // Préserver les données du formulaire
            $params = http_build_query([
                'error' => 'email_exists',
                'nom' => $nom,
                'prenom' => $prenom,
                'adresse' => $adresse,
                'numtel' => $numtel
            ]);
            header('Location: ../inscription.php?' . $params);
            exit();
        } else {
            // Insérer le nouvel utilisateur dans la base de données
            $stmt = $pdo->prepare("INSERT INTO client (NomClient, PrenomClient, Email, MDP, Adresse, DateInscription, NumTel) VALUES (:nom, :prenom, :email, :password, :adresse, NOW(), :numtel)");
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $passwordHash);
            $stmt->bindParam(':adresse', $adresse);
            $stmt->bindParam(':numtel', $numtel);

            if ($stmt->execute()) {
                // NOUVEAU : Récupérer l'ID du client nouvellement créé
                $clientId = $pdo->lastInsertId();
                
                // NOUVEAU : Connecter automatiquement l'utilisateur
                $_SESSION['user_id'] = $clientId;
                $_SESSION['nom'] = $nom;
                $_SESSION['prenom'] = $prenom;
                $_SESSION['email'] = $email;
                $_SESSION['telephone'] = $numtel;
                $_SESSION['adresse'] = $adresse;
                $_SESSION['date_inscription'] = date('Y-m-d H:i:s');
                
                // Rediriger vers la page client avec un message de succès
                header('Location: ../client.php?success=inscription');
                exit();
            } else {
                // Préserver les données du formulaire
                $params = http_build_query([
                    'error' => 'insert_failed',
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email,
                    'adresse' => $adresse,
                    'numtel' => $numtel
                ]);
                header('Location: ../inscription.php?' . $params);
                exit();
            }
        }
    } else {
        header('Location: ../inscription.php?error=empty');
        exit();
    }
} else {
    header('Location: ../inscription.php');
    exit();
}
?>
