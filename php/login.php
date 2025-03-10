<?php
// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db.php'; // Inclure la connexion à la base de données

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier si les champs sont remplis
    if (isset($_POST['email'], $_POST['password']) && !empty($_POST['email']) && !empty($_POST['password'])) {
        $email = htmlspecialchars(trim($_POST['email']));
        $password = htmlspecialchars(trim($_POST['password']));

        // Vérifier dans la table admin
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE Email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user && $user['MotDePasse'] === $password) {
            // Utilisateur admin trouvé
            session_start();
            $_SESSION['user_id'] = $user['IdAdmin'];
            $_SESSION['email'] = $user['Email'];
            $_SESSION['role'] = 'admin'; // Enregistrer le rôle

            header('Location: ../admin.html');
            exit();
        } else {
            // Vérifier dans la table client si pas trouvé dans admin
            $stmt = $pdo->prepare("SELECT * FROM client WHERE Email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $client = $stmt->fetch();

            if ($client && $client['MDP'] === $password) {
                // Utilisateur client trouvé
                session_start();
                $_SESSION['user_id'] = $client['IdClient'];
                $_SESSION['email'] = $client['Email'];
                $_SESSION['role'] = 'client'; // Enregistrer le rôle

                header('Location: ../produits.html');
                exit();
            } else {
                // Redirection avec message d'erreur
                header('Location: ../connexion.html?error=invalid');
                exit();
            }
        }
    } else {
        // Redirection avec message d'erreur pour champs vides
        header('Location: ../connexion.html?error=empty');
        exit();
    }
} else {
    // Redirection si accès direct à login.php
    header('Location: ../connexion.html');
    exit();
}
