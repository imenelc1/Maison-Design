<?php
session_start();

// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION['user_id'])) {
    // Rediriger selon le rôle s'il existe, sinon vers l'accueil
    $role = isset($_SESSION['role']) ? $_SESSION['role'] : 'client';
    
    if ($role === 'admin') {
        header('Location: admin.php');
    } else {
        header('Location: client.php');
    }
    exit();
}

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'php/db.php';
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs";
    } else {
        try {
            // Vérifier d'abord dans la table client - CORRECTION DES NOMS DE COLONNES
            $stmt = $pdo->prepare("SELECT IdClient as id, Email, MDP as MotDePasse, NomClient as Nom, PrenomClient as Prenom, 'client' as role FROM client WHERE Email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Si pas trouvé dans client, vérifier dans admin - SANS COLONNES NOM/PRENOM
            if (!$user) {
                $stmt = $pdo->prepare("SELECT IdAdmin as id, Email, MotDePasse, 'admin' as role FROM admin WHERE Email = ?");
                $stmt->execute([$email]);
                $adminUser = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($adminUser) {
                    // Ajouter des valeurs par défaut pour nom et prénom pour l'admin
                    $user = $adminUser;
                    $user['Nom'] = 'Administrateur';
                    $user['Prenom'] = 'Système';
                }
            }
            
            if ($user && password_verify($password, $user['MotDePasse'])) {
                // Connexion réussie
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['Email'];
                $_SESSION['user_nom'] = $user['Nom'];
                $_SESSION['user_prenom'] = $user['Prenom'];
                $_SESSION['role'] = $user['role'];
                
                // CORRECTION: Utiliser les bonnes clés de session pour le header
                $_SESSION['nom'] = $user['Nom'];
                $_SESSION['prenom'] = $user['Prenom'];
                $_SESSION['email'] = $user['Email'];
                
                // Redirection selon le rôle
                if ($user['role'] === 'admin') {
                    header('Location: admin/dashboard.php');
                } else {
                    header('Location: index.php');
                }
                exit();
            } else {
                $error = "Email ou mot de passe incorrect";
            }
        } catch (PDOException $e) {
            $error = "Erreur de connexion à la base de données";
            error_log("Erreur connexion: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Maison Design</title>
    <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwind.config.js"></script>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="font-cormorant bg-background">
    <!-- HEADER -->
    <?php include 'header.php'; ?>

    <main class="min-h-screen pt-28 pb-16 px-4 md:px-[10%] bg-background">
        <div class="max-w-md mx-auto">
            <div class="bg-white rounded-xl shadow-md overflow-hidden p-8">
                <h1 class="text-3xl font-frunchy text-textColor text-center mb-8">Connexion</h1>
                
                <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" class="space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" id="email" name="email" required
                               value="<?php echo htmlspecialchars($email ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-accent">
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Mot de passe</label>
                        <input type="password" id="password" name="password" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-accent">
                    </div>
                    
                    <button type="submit" 
                            class="w-full px-6 py-3 bg-accent text-white rounded-full hover:bg-accent/90 transition-colors">
                        Se connecter
                    </button>
                </form>
                
                <div class="text-center mt-6">
                    <p class="text-gray-600">Pas encore de compte ?</p>
                    <a href="inscription.php" class="text-accent hover:underline">S'inscrire</a>
                </div>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <?php include 'footer.php'; ?>
</body>
</html>
