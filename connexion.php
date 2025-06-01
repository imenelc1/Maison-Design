<?php
// Démarrer la session
session_start();

// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION['user_id'])) {
    // Rediriger vers la page appropriée selon le rôle
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin.html');
        exit();
    } elseif ($_SESSION['role'] === 'client') {
        header('Location: client.php');
        exit();
    }
}

// Récupérer les messages d'erreur
$error = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid':
            $error = 'Email ou mot de passe incorrect.';
            break;
        case 'empty':
            $error = 'Veuillez remplir tous les champs.';
            break;
        default:
            $error = 'Une erreur est survenue. Veuillez réessayer.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Maison Design</title>
    <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwind.config.js"></script>

    <link rel="stylesheet" href="css/style.css">

</head>
<body class="font-cormorant bg-background text-textColor">
    <!--header -->
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
                        Pas encore de compte ? <a href="inscription.html" class="text-accent font-semibold hover:underline">S'inscrire</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- footer -->
    <footer class="bg-primary text-textColor">
        <div class="container mx-auto px-4 py-12 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div>
                <h3 class="text-[1.3rem] md:text-[1.5rem] text-accent mb-4 font-frunchy">Maison Design</h3>
                <p>Découvrez des meubles élégants et raffinés pour votre intérieur.</p>
            </div>
            
            <div>
                <h3 class="text-[1.3rem] md:text-[1.5rem] text-accent mb-4 font-frunchy">Liens</h3>
                <ul class="space-y-2">
                    <li><a href="index.html#home" class="hover:text-accent">Accueil</a></li>
                    <li><a href="index.html#apropos" class="hover:text-accent">A propos</a></li>
                    <li><a href="categories.html" class="hover:text-accent">Catégories</a></li>
                    <li><a href="connexion.php" class="hover:text-accent">Connexion</a></li>
                </ul>
            </div>
            
            <div id="contact">
                <h3 class="text-[1.3rem] md:text-[1.5rem] text-accent mb-4 font-frunchy">Contact</h3>
                <form action="#" method="POST" class="space-y-3">
                    <input 
                        type="text" 
                        name="nom" 
                        placeholder="Votre Nom" 
                        required 
                        class="w-full px-4 py-2 rounded-md border border-secondary focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                    <input 
                        type="email" 
                        name="email" 
                        placeholder="Votre Email" 
                        required 
                        class="w-full px-4 py-2 rounded-md border border-secondary focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                    <textarea 
                        name="message" 
                        placeholder="Votre Message" 
                        required 
                        class="w-full px-4 py-2 rounded-md border border-secondary focus:outline-none focus:ring-2 focus:ring-accent"
                        rows="3"
                    ></textarea>
                    <button 
                        type="submit" 
                        class="px-6 py-2 bg-accent text-white rounded-md hover:shadow-md transition"
                    >
                        Envoyer
                    </button>
                </form>
                <p class="mt-3">Email: contact@maisondesign.com</p>
                <p>Téléphone: +213 555 123 456</p>
            </div>
            
            <div>
                <h3 class="text-[1.3rem] md:text-[1.5rem] text-accent mb-4 font-frunchy">Suivez-nous</h3>
                <div class="flex space-x-4">
                    <a href="https://www.facebook.com/?locale=fr_FR" target="_blank" rel="noopener" class="text-accent hover:text-textColor">
                        <i class="bx bxl-facebook text-2xl"></i>
                    </a>
                    <a href="https://www.instagram.com/" target="_blank" rel="noopener" class="text-accent hover:text-textColor">
                        <i class="bx bxl-instagram text-2xl"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="py-4 text-center border-t border-secondary">
            <p>&copy; 2025 Maison Design - Tous droits réservés.</p>
        </div>
    </footer>
    <script src="js/script.js"></script>
</body>
</html>
