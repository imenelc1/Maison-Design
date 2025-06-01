<footer class="bg-primary text-textColor">
    <div class="container mx-auto px-4 py-12 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
        <div>
            <h3 class="text-[1.3rem] md:text-[1.5rem] text-accent mb-4 font-frunchy">Maison Design</h3>
            <p>Découvrez des meubles élégants et raffinés pour votre intérieur.</p>
        </div>
        
        <div>
            <h3 class="text-[1.3rem] md:text-[1.5rem] text-accent mb-4 font-frunchy">Liens</h3>
            <ul class="space-y-2">
                <li><a href="index.php" class="hover:text-accent">Accueil</a></li>
                <li><a href="index.php#apropos" class="hover:text-accent">A propos</a></li>
                <li><a href="categories.php" class="hover:text-accent">Catégories</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="client.php" class="hover:text-accent">Mon compte</a></li>
                <li><a href="php/logout.php" class="hover:text-accent">Déconnexion</a></li>
                <?php else: ?>
                <li><a href="connexion.php" class="hover:text-accent">Connexion</a></li>
                <?php endif; ?>
            </ul>
        </div>
        
        <div id="contact">
            <h3 class="text-[1.3rem] md:text-[1.5rem] text-accent mb-4 font-frunchy">Contact</h3>
            <form action="php/send_contact.php" method="POST" class="space-y-3">
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
            
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <div class="mt-6 p-3 bg-accent/10 rounded-lg">
                <h4 class="font-medium text-accent mb-2">Administration</h4>
                <ul class="space-y-1">
                    <li><a href="admin/index.php" class="text-sm hover:text-accent flex items-center gap-1">
                        <i class='bx bx-dashboard'></i> Tableau de bord
                    </a></li>
                    <li><a href="admin/produits.php" class="text-sm hover:text-accent flex items-center gap-1">
                        <i class='bx bx-package'></i> Gestion des produits
                    </a></li>
                    <li><a href="admin/commandes.php" class="text-sm hover:text-accent flex items-center gap-1">
                        <i class='bx bx-cart'></i> Gestion des commandes
                    </a></li>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="py-4 text-center border-t border-secondary">
        <p>&copy; <?php echo date('Y'); ?> Maison Design - Tous droits réservés.</p>
    </div>
    
    <?php if (isset($_SESSION['flash_message'])): ?>
    <div id="flash-message" class="fixed bottom-4 right-4 bg-accent text-white px-4 py-2 rounded-lg shadow-lg z-50">
        <?php echo $_SESSION['flash_message']; ?>
        <?php unset($_SESSION['flash_message']); ?>
    </div>
    <script>
        // Masquer le message flash après 3 secondes
        setTimeout(() => {
            const flashMessage = document.getElementById('flash-message');
            if (flashMessage) {
                flashMessage.classList.add('opacity-0', 'translate-y-10');
                setTimeout(() => {
                    flashMessage.remove();
                }, 300);
            }
        }, 3000);
    </script>
    <?php endif; ?>
</footer>
