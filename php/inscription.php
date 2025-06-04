<?php
session_start();

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db.php';

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et nettoyer les données
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $numtel = trim($_POST['numtel'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm-password'] ?? '';
    
    // Validation des données
    $errors = [];
    
    // Vérifier que tous les champs sont remplis
    if (empty($nom) || empty($prenom) || empty($email) || empty($numtel) || empty($adresse) || empty($password) || empty($confirmPassword)) {
        $errors[] = 'empty';
    }
    
    // Vérifier que les mots de passe correspondent
    if ($password !== $confirmPassword) {
        $errors[] = 'password_mismatch';
    }
    
    // Valider l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'invalid_email';
    }
    
    // Valider le numéro de téléphone (format algérien)
    if (!preg_match('/^(0[5-7][0-9]{8}|(\+213|0213)[5-7][0-9]{8})$/', $numtel)) {
        $errors[] = 'invalid_phone';
    }
    
    // Valider la longueur du mot de passe
    if (strlen($password) < 6) {
        $errors[] = 'password_too_short';
    }
    
    // Valider les noms (lettres, espaces, tirets, apostrophes uniquement)
    if (!preg_match('/^[A-Za-zÀ-ÿ\s\-\']+$/', $nom)) {
        $errors[] = 'invalid_nom';
    }
    
    if (!preg_match('/^[A-Za-zÀ-ÿ\s\-\']+$/', $prenom)) {
        $errors[] = 'invalid_prenom';
    }
    
    // Si pas d'erreurs, procéder à l'inscription
    if (empty($errors)) {
        try {
            // Vérifier si l'email existe déjà
            $checkEmail = $pdo->prepare("SELECT IdClient FROM client WHERE Email = ?");
            $checkEmail->execute([$email]);
            
            if ($checkEmail->fetch()) {
                $errors[] = 'email_exists';
            } else {
                // Hasher le mot de passe
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // ADAPTATION: Utiliser les noms de colonnes de votre base de données
                $stmt = $pdo->prepare("
                    INSERT INTO client (NomClient, PrenomClient, Email, MDP, Adresse, DateInscription, NumTel) 
                    VALUES (?, ?, ?, ?, ?, NOW(), ?)
                ");
                
                $result = $stmt->execute([
                    htmlspecialchars($nom),
                    htmlspecialchars($prenom), 
                    htmlspecialchars($email),
                    $hashedPassword,
                    htmlspecialchars($adresse),
                    htmlspecialchars($numtel)
                ]);
                
                if ($result) {
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
                    $_SESSION['role'] = 'client'; // Ajouter le rôle
                    
                    // Rediriger vers la page client avec un message de succès
                    header('Location: ../client.php?success=inscription');
                    exit();
                } else {
                    $errors[] = 'insert_failed';
                }
            }
        } catch (PDOException $e) {
            // Log l'erreur pour debug
            error_log("Erreur inscription: " . $e->getMessage());
            
            // Vérifier le type d'erreur
            if (strpos($e->getMessage(), 'client_chk_1') !== false) {
                $errors[] = 'constraint_violation';
            } elseif (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $errors[] = 'email_exists';
            } elseif (strpos($e->getMessage(), 'Data too long') !== false) {
                $errors[] = 'data_too_long';
            } else {
                $errors[] = 'database_error';
            }
        }
    }
    
    // S'il y a des erreurs, rediriger avec les erreurs et les données
    if (!empty($errors)) {
        $queryParams = [
            'error' => implode(',', $errors),
            'nom' => urlencode($nom),
            'prenom' => urlencode($prenom),
            'email' => urlencode($email),
            'numtel' => urlencode($numtel),
            'adresse' => urlencode($adresse)
        ];
        
        $queryString = http_build_query($queryParams);
        header("Location: ../inscription.php?$queryString");
        exit();
    }
}

// Si accès direct au fichier, rediriger
header('Location: ../inscription.php');
exit();
?>
