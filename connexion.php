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
  <main class="min-h-screen py-24 px-4 bg-background flex items-center justify-center">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-3xl shadow-lg p-8 flex flex-col items-center">
                <h2 class="text-3xl font-frunchy text-textColor mb-8">Connexion</h2>
                
                <?php if (!empty($error)): ?>
                <div id="error-message" class="w-full mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <form class="w-full space-y-6" action="php/login.php" method="POST">
                    <div class="space-y-2">
                        <label for="email" class="block text-textColor">Email</label>
                        <div class="relative">
                            <i class='bx bx-envelope absolute left-4 top-1/2 transform -translate-y-1/2 text-accent text-xl'></i>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                required 
                                placeholder="Votre email" 
                                class="w-full pl-12 pr-4 py-3 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition"
                                value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>"
                            >
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label for="password" class="block text-textColor">Mot de passe</label>
                        <div class="relative">
                            <i class='bx bx-lock-alt absolute left-4 top-1/2 transform -translate-y-1/2 text-accent text-xl'></i>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required 
                                placeholder="Votre mot de passe" 
                                class="w-full pl-12 pr-4 py-3 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition"
                            >
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center text-sm">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded text-accent focus:ring-accent">
                            <span class="text-textColor">Se souvenir de moi</span>
                        </label>
                        <a href="mot-de-passe-oublie.php" class="text-accent hover:underline">Mot de passe oublié ?</a>
                    </div>
                    
                    <button 
                        type="submit" 
                        class="w-full py-3 bg-accent text-white rounded-full hover:shadow-md hover:-translate-y-0.5 transition-all duration-300"
                    >
                        Se connecter
                    </button>
                    
                    <div class="text-center text-sm text-textColor">
                        Pas encore de compte ? <a href="inscription.php" class="text-accent font-semibold hover:underline">S'inscrire</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <!-- FOOTER -->
    <?php include 'footer.php'; ?>
</body>
</html>
