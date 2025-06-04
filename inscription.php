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

    <!-- Conteneur pour les notifications -->
    <div id="notification-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <main class="min-h-screen py-24 px-4 bg-background flex items-center justify-center">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-3xl shadow-lg p-8">
                <h2 class="text-3xl font-frunchy text-textColor mb-8 text-center">Inscription</h2>
                
                <form class="space-y-6" action="php/inscription.php" method="POST" id="inscription-form">
                    <div class="space-y-2">
                        <label for="nom" class="block text-textColor">Nom *</label>
                        <div class="relative">
                            <i class='bx bx-user absolute left-4 top-1/2 transform -translate-y-1/2 text-accent text-xl'></i>
                            <input 
                                type="text" 
                                id="nom" 
                                name="nom" 
                                required 
                                maxlength="50"
                                pattern="[A-Za-zÀ-ÿ\s\-']+"
                                title="Seules les lettres, espaces, tirets et apostrophes sont autorisés"
                                placeholder="Votre nom" 
                                value="<?php echo isset($_GET['nom']) ? htmlspecialchars(urldecode($_GET['nom'])) : ''; ?>"
                                class="w-full pl-12 pr-4 py-3 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition"
                            >
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label for="prenom" class="block text-textColor">Prénom *</label>
                        <div class="relative">
                            <i class='bx bx-user absolute left-4 top-1/2 transform -translate-y-1/2 text-accent text-xl'></i>
                            <input 
                                type="text" 
                                id="prenom" 
                                name="prenom" 
                                required 
                                maxlength="50"
                                pattern="[A-Za-zÀ-ÿ\s\-']+"
                                title="Seules les lettres, espaces, tirets et apostrophes sont autorisés"
                                placeholder="Votre prénom" 
                                value="<?php echo isset($_GET['prenom']) ? htmlspecialchars(urldecode($_GET['prenom'])) : ''; ?>"
                                class="w-full pl-12 pr-4 py-3 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition"
                            >
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label for="email" class="block text-textColor">Email *</label>
                        <div class="relative">
                            <i class='bx bx-envelope absolute left-4 top-1/2 transform -translate-y-1/2 text-accent text-xl'></i>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                required 
                                maxlength="100"
                                placeholder="votre.email@exemple.com" 
                                value="<?php echo isset($_GET['email']) ? htmlspecialchars(urldecode($_GET['email'])) : ''; ?>"
                                class="w-full pl-12 pr-4 py-3 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition"
                            >
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label for="numtel" class="block text-textColor">Numéro de téléphone *</label>
                        <div class="relative">
                            <i class='bx bx-phone absolute left-4 top-1/2 transform -translate-y-1/2 text-accent text-xl'></i>
                            <input 
                                type="tel" 
                                id="numtel" 
                                name="numtel" 
                                required 
                                pattern="^(0[5-7][0-9]{8}|(\+213|0213)[5-7][0-9]{8})$"
                                title="Format: 05XXXXXXXX, 06XXXXXXXX ou 07XXXXXXXX"
                                placeholder="05XXXXXXXX" 
                                value="<?php echo isset($_GET['numtel']) ? htmlspecialchars(urldecode($_GET['numtel'])) : ''; ?>"
                                class="w-full pl-12 pr-4 py-3 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition"
                            >
                        </div>
                        <p class="text-xs text-gray-500">Format accepté: 05XXXXXXXX, 06XXXXXXXX ou 07XXXXXXXX</p>
                    </div>
                    
                    <div class="space-y-2">
                        <label for="adresse" class="block text-textColor">Adresse *</label>
                        <div class="relative">
                            <i class='bx bx-home absolute left-4 top-1/2 transform -translate-y-1/2 text-accent text-xl'></i>
                            <input 
                                type="text" 
                                id="adresse" 
                                name="adresse" 
                                required 
                                maxlength="200"
                                placeholder="Votre adresse complète" 
                                value="<?php echo isset($_GET['adresse']) ? htmlspecialchars(urldecode($_GET['adresse'])) : ''; ?>"
                                class="w-full pl-12 pr-4 py-3 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition"
                            >
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label for="password" class="block text-textColor">Mot de passe *</label>
                        <div class="relative">
                            <i class='bx bx-lock-alt absolute left-4 top-1/2 transform -translate-y-1/2 text-accent text-xl'></i>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required 
                                minlength="6"
                                placeholder="Minimum 8 caractères" 
                                class="w-full pl-12 pr-4 py-3 rounded-full border border-primary focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition"
                            >
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label for="confirm-password" class="block text-textColor">Confirmer le mot de passe *</label>
                        <div class="relative">
                            <i class='bx bx-lock-alt absolute left-4 top-1/2 transform -translate-y-1/2 text-accent text-xl'></i>
                            <input 
                                type="password" 
                                id="confirm-password" 
                                name="confirm-password" 
                                required 
                                minlength="6"
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
                            J'accepte les <a href="#" class="text-accent hover:underline">termes et conditions</a> *
                        </label>
                    </div>
                    
                    <button 
                        type="submit" 
                        id="submit-btn"
                        class="w-full py-3 bg-accent text-white rounded-full hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span id="submit-text">S'inscrire</span>
                        <i id="submit-icon" class="bx bx-user-plus ml-2"></i>
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

    <script>
        // Système de notifications
        class NotificationManager {
            constructor() {
                this.container = document.getElementById('notification-container');
            }

            show(message, type = 'info', duration = 5000) {
                const notification = this.createNotification(message, type);
                this.container.appendChild(notification);

                // Animation d'entrée
                setTimeout(() => {
                    notification.classList.remove('translate-x-full', 'opacity-0');
                }, 100);

                // Auto-suppression
                setTimeout(() => {
                    this.remove(notification);
                }, duration);

                return notification;
            }

            createNotification(message, type) {
                const notification = document.createElement('div');
                
                // Classes de base
                notification.className = `
                    transform translate-x-full opacity-0 transition-all duration-300 ease-in-out
                    max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto flex ring-1 ring-black ring-opacity-5
                `;

                // Couleurs selon le type
                let iconClass, bgClass, textClass, borderClass;
                
                switch(type) {
                    case 'success':
                        iconClass = 'bx-check-circle text-green-500';
                        bgClass = 'bg-green-50';
                        textClass = 'text-green-800';
                        borderClass = 'border-l-4 border-green-500';
                        break;
                    case 'error':
                        iconClass = 'bx-error-circle text-red-500';
                        bgClass = 'bg-red-50';
                        textClass = 'text-red-800';
                        borderClass = 'border-l-4 border-red-500';
                        break;
                    case 'warning':
                        iconClass = 'bx-error text-yellow-500';
                        bgClass = 'bg-yellow-50';
                        textClass = 'text-yellow-800';
                        borderClass = 'border-l-4 border-yellow-500';
                        break;
                    default:
                        iconClass = 'bx-info-circle text-blue-500';
                        bgClass = 'bg-blue-50';
                        textClass = 'text-blue-800';
                        borderClass = 'border-l-4 border-blue-500';
                }

                notification.innerHTML = `
                    <div class="flex-1 w-0 p-4 ${bgClass} ${borderClass}">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="bx ${iconClass} text-xl"></i>
                            </div>
                            <div class="ml-3 w-0 flex-1 pt-0.5">
                                <p class="text-sm font-medium ${textClass}">${message}</p>
                            </div>
                            <div class="ml-4 flex-shrink-0 flex">
                                <button class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" onclick="notificationManager.remove(this.closest('.transform'))">
                                    <span class="sr-only">Fermer</span>
                                    <i class="bx bx-x text-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                return notification;
            }

            remove(notification) {
                notification.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }

            showMultiple(errors) {
                errors.forEach((error, index) => {
                    setTimeout(() => {
                        this.show(this.getErrorMessage(error), 'error');
                    }, index * 200);
                });
            }

            getErrorMessage(errorCode) {
                const messages = {
                    'password_mismatch': 'Les mots de passe ne correspondent pas',
                    'email_exists': 'Cette adresse email est déjà utilisée',
                    'empty': 'Veuillez remplir tous les champs obligatoires',
                    'invalid_email': 'L\'adresse email n\'est pas valide',
                    'invalid_phone': 'Le numéro de téléphone n\'est pas valide (format: 05XXXXXXXX)',
                    'constraint_violation': 'Erreur de validation des données',
                    'database_error': 'Erreur de base de données. Veuillez réessayer',
                    'insert_failed': 'Erreur lors de l\'inscription. Veuillez réessayer'
                };
                
                return messages[errorCode] || 'Une erreur est survenue';
            }
        }

        // Initialiser le gestionnaire de notifications
        const notificationManager = new NotificationManager();

        // Vérifier les erreurs dans l'URL au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const errors = urlParams.get('error');
            const success = urlParams.get('success');

            if (errors) {
                const errorList = errors.split(',');
                notificationManager.showMultiple(errorList);
                
                // Nettoyer l'URL
                const newUrl = window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);
            }

            if (success === 'registered') {
                notificationManager.show('Inscription réussie ! Vous pouvez maintenant vous connecter.', 'success');
                
                // Nettoyer l'URL
                const newUrl = window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);
            }
        });

        // Validation du formulaire
        document.getElementById('inscription-form').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submit-btn');
            const submitText = document.getElementById('submit-text');
            const submitIcon = document.getElementById('submit-icon');
            
            // Validation des mots de passe
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                notificationManager.show('Les mots de passe ne correspondent pas', 'error');
                return false;
            }
            
            // Validation du téléphone
            const phone = document.getElementById('numtel').value;
            const phonePattern = /^(0[5-7][0-9]{8}|(\+213|0213)[5-7][0-9]{8})$/;
            
            if (!phonePattern.test(phone)) {
                e.preventDefault();
                notificationManager.show('Le numéro de téléphone doit être au format: 05XXXXXXXX, 06XXXXXXXX ou 07XXXXXXXX', 'error');
                return false;
            }

            // Validation de l'email
            const email = document.getElementById('email').value;
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!emailPattern.test(email)) {
                e.preventDefault();
                notificationManager.show('Veuillez entrer une adresse email valide', 'error');
                return false;
            }

            // Validation des champs obligatoires
            const requiredFields = ['nom', 'prenom', 'email', 'numtel', 'adresse', 'password'];
            let hasEmptyFields = false;
            
            requiredFields.forEach(fieldName => {
                const field = document.getElementById(fieldName);
                if (!field.value.trim()) {
                    hasEmptyFields = true;
                    field.classList.add('border-red-500');
                } else {
                    field.classList.remove('border-red-500');
                }
            });
            
            if (hasEmptyFields) {
                e.preventDefault();
                notificationManager.show('Veuillez remplir tous les champs obligatoires', 'error');
                return false;
            }

            // Vérifier les termes et conditions
            const terms = document.getElementById('terms');
            if (!terms.checked) {
                e.preventDefault();
                notificationManager.show('Vous devez accepter les termes et conditions', 'error');
                return false;
            }
            
            // Désactiver le bouton et changer le texte
            submitBtn.disabled = true;
            submitText.textContent = 'Inscription en cours...';
            submitIcon.className = 'bx bx-loader-alt animate-spin ml-2';
            
            notificationManager.show('Inscription en cours...', 'info');
            
            // Réactiver le bouton après 10 secondes en cas de problème
            setTimeout(() => {
                if (submitBtn.disabled) {
                    submitBtn.disabled = false;
                    submitText.textContent = 'S\'inscrire';
                    submitIcon.className = 'bx bx-user-plus ml-2';
                }
            }, 10000);
        });

        // Validation en temps réel
        document.getElementById('confirm-password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.classList.add('border-red-500');
            } else {
                this.classList.remove('border-red-500');
            }
        });

        document.getElementById('numtel').addEventListener('input', function() {
            const phone = this.value;
            const phonePattern = /^(0[5-7][0-9]{8}|(\+213|0213)[5-7][0-9]{8})$/;
            
            if (phone && !phonePattern.test(phone)) {
                this.classList.add('border-red-500');
            } else {
                this.classList.remove('border-red-500');
            }
        });

        document.getElementById('email').addEventListener('input', function() {
            const email = this.value;
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailPattern.test(email)) {
                this.classList.add('border-red-500');
            } else {
                this.classList.remove('border-red-500');
            }
        });
    </script>
</body>
</html>
