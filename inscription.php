<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Maison Design</title>
    <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
        <script src="tailwind.config.js"></script>

    <link rel="stylesheet" href="css/style.css">
</head>
<body class="font-cormorant bg-background text-textColor">
    <!-- Header -->
    <?php include 'header.php'; ?>

    <main class="min-h-screen py-24 px-4 bg-background flex items-center justify-center">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-3xl shadow-lg p-8">
                <h2 class="text-3xl font-frunchy text-textColor mb-8 text-center">Inscription</h2>
                
                <!-- Messages d'erreur -->
                <?php if (isset($_GET['error'])): ?>
                    <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                        <?php
                        switch ($_GET['error']) {
                            case 'password_mismatch':
                                echo "Les mots de passe ne correspondent pas.";
                                break;
                            case 'email_exists':
                                echo "Cette adresse email est déjà utilisée.";
                                break;
                            case 'empty':
                                echo "Veuillez remplir tous les champs.";
                                break;
                            case 'insert_failed':
                                echo "Erreur lors de l'inscription. Veuillez réessayer.";
                                break;
                            default:
                                echo "Une erreur est survenue.";
                        }
                        ?>
                    </div>
                <?php endif; ?>
                
                <form class="space-y-6" action="php/inscription.php" method="POST">
                    <div class="space-y-2">
                        <label for="nom" class="block text-textColor">Nom</label>
                        <div class="relative">
                            <i class='bx bx-user absolute left-4 top-1/2 transform -translate-y-1/2 text-accent text-xl'></i>
                            <input 
                                type="text" 
                                id="nom" 
                                name="nom" 
                                required 
                                placeholder="Votre nom" 
                                value="<?php echo isset($_GET['nom']) ? htmlspecialchars($_GET['nom']) : ''; ?>"
                                class="w-full pl-12 pr-4 py-3 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition"
                            >
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label for="prenom" class="block text-textColor">Prénom</label>
                        <div class="relative">
                            <i class='bx bx-user absolute left-4 top-1/2 transform -translate-y-1/2 text-accent text-xl'></i>
                            <input 
                                type="text" 
                                id="prenom" 
                                name="prenom" 
                                required 
                                placeholder="Votre prénom" 
                                value="<?php echo isset($_GET['prenom']) ? htmlspecialchars($_GET['prenom']) : ''; ?>"
                                class="w-full pl-12 pr-4 py-3 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition"
                            >
                        </div>
                    </div>
                    
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
                                value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>"
                                class="w-full pl-12 pr-4 py-3 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition"
                            >
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label for="numtel" class="block text-textColor">Numéro de téléphone</label>
                        <div class="relative">
                            <i class='bx bx-phone absolute left-4 top-1/2 transform -translate-y-1/2 text-accent text-xl'></i>
                            <input 
                                type="tel" 
                                id="numtel" 
                                name="numtel" 
                                required 
                                placeholder="Votre numéro de téléphone" 
                                value="<?php echo isset($_GET['numtel']) ? htmlspecialchars($_GET['numtel']) : ''; ?>"
                                class="w-full pl-12 pr-4 py-3 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition"
                            >
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label for="adresse" class="block text-textColor">Adresse</label>
                        <div class="relative">
                            <i class='bx bx-home absolute left-4 top-1/2 transform -translate-y-1/2 text-accent text-xl'></i>
                            <input 
                                type="text" 
                                id="adresse" 
                                name="adresse" 
                                required 
                                placeholder="Votre adresse" 
                                value="<?php echo isset($_GET['adresse']) ? htmlspecialchars($_GET['adresse']) : ''; ?>"
                                class="w-full pl-12 pr-4 py-3 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition"
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
                                placeholder="Choisissez un mot de passe" 
                                class="w-full pl-12 pr-4 py-3 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition"
                            >
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label for="confirm-password" class="block text-textColor">Confirmer le mot de passe</label>
                        <div class="relative">
                            <i class='bx bx-lock-alt absolute left-4 top-1/2 transform -translate-y-1/2 text-accent text-xl'></i>
                            <input 
                                type="password" 
                                id="confirm-password" 
                                name="confirm-password" 
                                required 
                                placeholder="Confirmez votre mot de passe" 
                                class="w-full pl-12 pr-4 py-3 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition"
                            >
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-2">
                        <input 
                            type="checkbox" 
                            id="terms" 
                            name="terms" 
                            required 
                            class="mt-1 rounded text-accent focus:ring-accent"
                        >
                        <label for="terms" class="text-sm text-textColor">
                            J'accepte les <a href="#" class="text-accent hover:underline">termes et conditions</a>
                        </label>
                    </div>
                    
                    <button 
                        type="submit" 
                        class="w-full py-3 bg-accent text-white rounded-full hover:shadow-md hover:-translate-y-0.5 transition-all duration-300"
                    >
                        S'inscrire
                    </button>
                    
                    <div class="text-center text-sm text-textColor">
                        Déjà un compte ? <a href="connexion.php" class="text-accent font-semibold hover:underline">Se connecter</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <script src="js/script.js"></script>
</body>
</html>
