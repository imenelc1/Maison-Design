<?php
// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../php/db.php'; // Chemin correct vers db.php

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
            header('Location: ../inscription.html?error=password_mismatch');
            exit();
        }

        $passwordHash = $password;

        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM client WHERE Email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $emailExists = $stmt->fetchColumn();

        if ($emailExists) {
            header('Location: ../inscription.html?error=email_exists');
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
                header('Location: ../connexion.html?success=registered');
                exit();
            } else {
                header('Location: ../inscription.html?error=insert_failed');
                exit();
            }
        }
    } else {
        header('Location: ../inscription.html?error=empty');
        exit();
    }
} else {
    header('Location: ../inscription.html');
    exit();
}
