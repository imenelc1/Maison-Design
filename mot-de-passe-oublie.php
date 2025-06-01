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

// Initialiser les variables
$message = '';
$messageType = '';

// Traiter le formulaire de récupération de mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    require_once 'php/db.php';
    
    $email = htmlspecialchars(trim($_POST['email']));
    
    // Vérifier si l'email existe dans la base de données
    $stmt = $pdo->prepare("SELECT * FROM client WHERE Email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $client = $stmt->fetch();
    
    if ($client) {
        // Générer un token unique
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Stocker le token dans la base de données
        $stmt = $pdo->prepare("UPDATE client SET reset_token = :token, reset_expiry = :expiry WHERE Email = :email");
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expiry', $expiry);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        // Dans un environnement de production, vous enverriez un email avec le lien de réinitialisation
        // Pour cet exemple, nous affichons simplement un message
        $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/reinitialiser-mot-de-passe.php?token=" . $token;
        
        $message = "Un lien de réinitialisation a été envoyé à votre adresse email. Veuillez vérifier votre boîte de réception.";
        $messageType = "success";
        
        // Pour le débogage, afficher le lien (à supprimer en production)
        $debugLink = "<div class='mt-2 p-2 bg-gray-100 rounded text-xs'>Lien de réinitialisation (pour débogage): <a href='$resetLink' class='text-accent'>$resetLink</a></div>";
    } else {
        $message = "Aucun compte n'est associé à cette adresse email.";
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - Maison Design</title>
    <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwind.config.js"></script>

    <link rel="stylesheet" href="css/style.css">
</head>
<body class="font-cormorant bg-background text-textColor">
    <!--header -->
    <header class="fixed top-0 left-0 w-full h-20 bg-white/50 backdrop-blur-sm flex items-center justify-center z-50 shadow-md">
        <div class="w-[90%] max-w-[1200px]">
            <nav class="flex items-center justify-between">
                <div class="w-[20%] flex items-center">
                    <a href="index.html" class="logo-content flex items-center">
                        <img src="images/Logo3_1_1.png" alt="Logo Maison Design" class="w-[60px] md:w-[70px] h-auto">
                    </a>
                </div>

                <div class="w-[60%] flex justify-center">
                    <!-- Menu desktop -->
                    <ul class="hidden md:flex flex-row gap-8">
                        <li class="relative"><a href="index.html" class="text-textColor font-normal text-lg hover:text-accent transition-colors">Accueil</a></li>
                        <li class="relative"><a href="index.html#apropos" class="text-textColor font-normal text-lg hover:text-accent transition-colors">A propos</a></li>
                        <li class="relative group">
                            <a href="categories.html" class="text-textColor font-normal text-lg hover:text-accent transition-colors">Catégories</a>
                            <ul class="absolute top-full left-0 bg-white list-none py-2.5 px-0 m-0 min-w-[100px] rounded-lg shadow-md z-10 hidden group-hover:block">
                                <li class="px-5 py-2.5 hover:bg-secondary"><a href="categories.html?category=lit" class="no-underline text-textColor text-base block">Lits</a></li>
                                <li class="px-5 py-2.5 hover:bg-secondary"><a href="categories.html?category=chaise" class="no-underline text-textColor text-base block">Chaises</a></li>
                                <li class="px-5 py-2.5 hover:bg-secondary"><a href="categories.html?category=table" class="no-underline text-textColor text-base block">Tables</a></li>
                                <li class="px-5 py-2.5 hover:bg-secondary"><a href="categories.html?category=canapé" class="no-underline text-textColor text-base block">Canapés</a></li>
                                <li class="px-5 py-2.5 hover:bg-secondary"><a href="categories.html?category=armoire" class="no-underline text-textColor text-base block">Armoires</a></li>
                            </ul>
                        </li>
                        <li class="relative"><a href="index.html#contact" class="text-textColor font-normal text-lg hover:text-accent transition-colors">Contact</a></li>
                    </ul>
                </div>
                
                <div class="w-[20%] flex items-center justify-end gap-4">
                    <!--  barre de recherche  -->
                    <div class="search-container relative">
                        <button id="search-toggle" class="search-toggle-btn bg-transparent border-none cursor-pointer text-textColor text-2xl flex items-center justify-center hover:text-accent transition-colors p-2 rounded-full hover:bg-primary/30" title="Rechercher">
                            <i class='bx bx-search'></i>
                        </button>
                        <div id="search-dropdown" class="absolute right-0 top-full mt-2 bg-white rounded-lg shadow-lg p-3 w-[300px] z-50 hidden opacity-0 -translate-y-2.5 transition-all duration-300">
                            <div class="search-bar flex items-center bg-primary/20 rounded-full px-4 py-2 border border-accent/20 focus-within:border-accent transition-all duration-300">
                                <input type="text" placeholder="Rechercher un produit..." class="border-none outline-none bg-transparent text-base w-full">
                                <button class="search-button bg-transparent border-none cursor-pointer text-accent text-lg flex items-center justify-center hover:text-accent/70 transition-colors" title="Rechercher">
                                    <i class='bx bx-search'></i>
                                </button>
                            </div>
                            <div class="search-suggestions mt-3">
                                <p class="text-sm text-textColor/70 mb-2">Suggestions populaires :</p>
                                <div class="flex flex-wrap gap-2">
                                    <a href="#" class="text-xs bg-primary/20 px-2 py-1 rounded-full text-textColor hover:bg-accent hover:text-white transition-colors">Chaises design</a>
                                    <a href="#" class="text-xs bg-primary/20 px-2 py-1 rounded-full text-textColor hover:bg-accent hover:text-white transition-colors">Tables basses</a>
                                    <a href="#" class="text-xs bg-primary/20 px-2 py-1 rounded-full text-textColor hover:bg-accent hover:text-white transition-colors">Canapés d'angle</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="hidden md:flex cote-droit items-center gap-4 text-lg text-textColor">
                        <a href="connexion.php" class="flex items-center justify-center px-4 py-1.5 bg-accent text-white rounded-full hover:bg-accent/80 transition-colors text-sm font-medium" title="Connexion">
                            Se connecter
                        </a>
                    </div>
                    
                    <!-- Icône pour ouvrir menu uniquement sur mobile -->
                    <button id="open-menu" class="md:hidden bg-transparent border-none text-2xl cursor-pointer text-textColor hover:text-accent transition-colors ml-2" title="ouvrir menu">
                        <i class='bx bx-menu'></i>
                    </button>
                </div>

                <div id="mobile-menu" class="fixed top-0 right-[-250px] md:hidden bg-primary p-6 z-[1000] h-screen w-[250px] transition-all duration-300 shadow-lg">           
                     <button id="close-menu" class="absolute top-4 left-4 bg-transparent border-none text-2xl cursor-pointer text-textColor hover:text-accent transition-colors" title="fermer menu">
                    <i class='bx bx-x'></i>
                </button>

                    <!-- Logo dans le menu mobile -->
                    <div class="flex justify-center mt-4 mb-8">
                        <img src="images/Logo3_1_1.png" alt="Logo Maison Design" class="w-[60px] h-auto">
                    </div>

                    <ul class="menu-list flex flex-col items-start gap-8 w-full">
                        <li class="relative w-full"><a href="index.html" class="text-textColor font-normal text-lg hover:text-accent transition-colors">Accueil</a></li>
                        <li class="relative w-full"><a href="index.html#apropos" class="text-textColor font-normal text-lg hover:text-accent transition-colors">A propos</a></li>
                        <li class="relative w-full mobile-dropdown">
                            <a href="categories.html" class="text-textColor font-normal text-lg hover:text-accent transition-colors flex justify-between items-center">
                                Catégories
                                <i class='bx bx-chevron-down text-lg'></i>
                            </a>
                            <ul class="bg-white/10 list-none py-2 px-0 m-0 rounded-lg mt-2 hidden mobile-submenu">
                                <li class="px-5 py-2 hover:bg-white/20"><a href="categories.html?category=lit" class="no-underline text-textColor text-base block">Lits</a></li>
                                <li class="px-5 py-2 hover:bg-white/20"><a href="categories.html?category=chaise" class="no-underline text-textColor text-base block">Chaises</a></li>
                                <li class="px-5 py-2 hover:bg-white/20"><a href="categories.html?category=table" class="no-underline text-textColor text-base block">Tables</a></li>
                                <li class="px-5 py-2 hover:bg-white/20"><a href="categories.html?category=canapé" class="no-underline text-textColor text-base block">Canapés</a></li>
                                <li class="px-5 py-2 hover:bg-white/20"><a href="categories.html?category=armoire" class="no-underline text-textColor text-base block">Armoires</a></li>
                            </ul>
                        </li>
                        <li class="relative w-full"><a href="index.html#contact" class="text-textColor font-normal text-lg hover:text-accent transition-colors">Contact</a></li>
                    </ul>

                    <!-- Bouton Se connecter dans le menu mobile -->
                    <div class="mt-8">
                        <a href="connexion.php" class="flex items-center justify-center w-full px-4 py-2 bg-accent text-white rounded-full hover:bg-accent/80 transition-colors text-base font-medium">
                            Se connecter
                        </a>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <main class="min-h-screen py-24 px-4 bg-background flex items-center justify-center">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-3xl shadow-lg p-8 flex flex-col items-center">
                <h2 class="text-3xl font-frunchy text-textColor mb-8">Mot de passe oublié</h2>
                
                <?php if (!empty($message)): ?>
                <div class="w-full mb-4 p-3 <?php echo $messageType === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?> rounded-lg">
                    <?php echo $message; ?>
                    <?php if (isset($debugLink) && $messageType === 'success'): ?>
                        <?php echo $debugLink; ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <p class="text-center text-gray-600 mb-6">Entrez votre adresse email pour recevoir un lien de réinitialisation de mot de passe.</p>
                
                <form class="w-full space-y-6" action="mot-de-passe-oublie.php" method="POST">
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
                            >
                        </div>
                    </div>
                    
                    <button 
                        type="submit" 
                        class="w-full py-3 bg-accent text-white rounded-full hover:shadow-md hover:-translate-y-0.5 transition-all duration-300"
                    >
                        Envoyer le lien
                    </button>
                    
                    <div class="text-center text-sm text-textColor">
                        <a href="connexion.php" class="text-accent hover:underline">Retour à la connexion</a>
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
