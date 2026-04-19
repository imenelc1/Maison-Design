</main>

<footer class="bg-primary text-textColor mt-16">
    <div class="container mx-auto px-4 py-12 grid grid-cols-1 md:grid-cols-3 gap-8">
        <div>
            <h3 class="text-accent mb-4 font-frunchy text-xl">Maison Design</h3>
            <p>Meubles élégants pour votre intérieur.</p>
        </div>
        <div>
            <h3 class="text-accent mb-4 font-frunchy text-xl">Liens</h3>
            <ul class="space-y-2">
                <li><a href="/" class="hover:text-accent">Accueil</a></li>
                <li><a href="/categories" class="hover:text-accent">Catégories</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="/compte" class="hover:text-accent">Mon compte</a></li>
                <li><a href="/deconnexion" class="hover:text-accent">Déconnexion</a></li>
                <?php else: ?>
                <li><a href="/connexion" class="hover:text-accent">Connexion</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <div>
            <h3 class="text-accent mb-4 font-frunchy text-xl">Contact</h3>
            <p>Email: contact@maisondesign.com</p>
            <p>Téléphone: +213 555 123 456</p>
        </div>
    </div>
    <div class="py-4 text-center border-t border-secondary">
        <p>&copy; <?php echo date('Y'); ?> Maison Design</p>
    </div>
</footer>

<script src="/js/shared-cart-functions.js"></script>
<script src="/js/header-menu.js"></script>
</body>
</html>