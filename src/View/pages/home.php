<?php $pageTitle = 'Maison Design — Accueil'; ?>

<!-- HERO -->
<section class="h-screen relative bg-[url('/images/acceuil.jpg')] bg-cover bg-center">
    <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
        <div class="text-center text-white px-4">
            <img src="/images/mdblanc.png" alt="Logo" class="w-24 mx-auto mb-4">
            <h1 class="font-frunchy text-5xl md:text-7xl mb-4 tracking-widest">
                M A I S O N . D E S I G N
            </h1>
            <h2 class="text-xl md:text-2xl font-light uppercase tracking-wide mb-8">
                Transformez votre intérieur avec élégance
            </h2>
            <a href="/categories"
               class="inline-block px-8 py-3 bg-accent text-white rounded-full hover:bg-accent/80 hover:-translate-y-1 transition-all duration-300 text-lg font-medium shadow-md">
                Découvrir
            </a>
        </div>
    </div>
</section>

<!-- A PROPOS -->
<section id="apropos" class="py-16 px-4 md:px-[10%] bg-background">
    <div class="max-w-[1200px] mx-auto flex flex-col md:flex-row items-center gap-12">

        <div class="w-full md:w-1/2">
            <img src="/images/about-us.jpg" alt="À propos"
                 class="h-auto md:h-[30rem] w-full rounded-[15px] shadow-md object-cover"
                 onerror="this.style.display='none'">
        </div>

        <div class="flex-1">
            <h2 class="text-3xl md:text-4xl text-textColor mb-4 text-center md:text-left"
                style="font-family: Frunchy, serif">
                À propos de <span class="text-accent">Maison Design</span>
            </h2>
            <p class="text-lg md:text-xl text-textColor leading-relaxed mb-8">
                Chez <strong>Maison Design</strong>, nous croyons que chaque intérieur mérite une touche
                d'élégance et d'harmonie. Notre mission est de vous offrir des meubles et des décorations
                uniques, alliant style moderne et qualité artisanale.
            </p>

            <div class="flex flex-col sm:flex-row gap-4">
                <div class="text-center flex-1 p-4 bg-primary rounded-[15px] shadow-md">
                    <i class='bx bxs-truck text-2xl text-accent mb-2'></i>
                    <h3 class="text-lg text-textColor font-medium">Livraison Rapide</h3>
                    <p class="text-sm text-textColor mt-1">Livrés chez vous en toute sécurité.</p>
                </div>
                <div class="text-center flex-1 p-4 bg-primary rounded-[15px] shadow-md">
                    <i class='bx bxs-phone text-2xl text-accent mb-2'></i>
                    <h3 class="text-lg text-textColor font-medium">Service Client</h3>
                    <p class="text-sm text-textColor mt-1">Disponible pour toutes vos questions.</p>
                </div>
                <div class="text-center flex-1 p-4 bg-primary rounded-[15px] shadow-md">
                    <i class='bx bxs-wallet text-2xl text-accent mb-2'></i>
                    <h3 class="text-lg text-textColor font-medium">Paiement Sécurisé</h3>
                    <p class="text-sm text-textColor mt-1">Paiement à la réception.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CATEGORIES -->
<section id="categories" class="py-16 bg-background">
    <div class="max-w-[1400px] mx-auto px-4 md:px-[10%]">
        <h2 class="text-3xl md:text-4xl text-center text-textColor mb-12"
            style="font-family: Frunchy, serif">
            Nos Catégories
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            $categories = [
                ['nom' => 'Lits',     'image' => '/images/lit.jpeg',     'slug' => 'lits'],
                ['nom' => 'Canapés',  'image' => '/images/canape.jpeg',  'slug' => 'canapés'],
                ['nom' => 'Chaises',  'image' => '/images/chaise.jpeg',  'slug' => 'chaises'],
                ['nom' => 'Tables',   'image' => '/images/table.jpeg',   'slug' => 'tables'],
                ['nom' => 'Armoires', 'image' => '/images/armoire.jpeg', 'slug' => 'armoires'],
            ];
            foreach ($categories as $cat):
            ?>
            <a href="/categories?category=<?php echo $cat['slug']; ?>"
               class="group relative h-64 rounded-xl overflow-hidden shadow-md hover:-translate-y-1 transition-transform duration-300">
                <img src="<?php echo $cat['image']; ?>"
                     alt="<?php echo $cat['nom']; ?>"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                     onerror="this.src='/images/placeholder.jpeg'">
                <div class="absolute inset-0 bg-black/30 group-hover:bg-black/50 transition-colors duration-300 flex items-end">
                    <div class="p-6 w-full">
                        <h3 class="text-white text-2xl font-medium"><?php echo $cat['nom']; ?></h3>
                        <p class="text-white/80 text-sm mt-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            Découvrir la collection →
                        </p>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CONTACT -->
<section id="contact" class="py-16 px-4 md:px-[10%] bg-primary/30">
    <div class="max-w-[600px] mx-auto text-center">
        <h2 class="text-3xl md:text-4xl text-textColor mb-4"
            style="font-family: Frunchy, serif">
            Contactez-nous
        </h2>
        <p class="text-textColor/70 mb-8">
            Une question ? N'hésitez pas à nous contacter.
        </p>

        <form action="#" method="POST" class="space-y-4 text-left">
            <input type="text" name="nom" placeholder="Votre Nom" required
                   class="w-full px-4 py-3 rounded-lg border border-secondary focus:outline-none focus:ring-2 focus:ring-accent bg-white">
            <input type="email" name="email" placeholder="Votre Email" required
                   class="w-full px-4 py-3 rounded-lg border border-secondary focus:outline-none focus:ring-2 focus:ring-accent bg-white">
            <textarea name="message" placeholder="Votre Message" required rows="4"
                      class="w-full px-4 py-3 rounded-lg border border-secondary focus:outline-none focus:ring-2 focus:ring-accent bg-white"></textarea>
            <button type="submit"
                    class="w-full px-6 py-3 bg-accent text-white rounded-full hover:bg-accent/80 transition-colors">
                Envoyer
            </button>
        </form>

        <div class="mt-8 flex justify-center gap-4">
            <a href="https://www.facebook.com" target="_blank" class="text-accent hover:text-textColor text-2xl">
                <i class="bx bxl-facebook"></i>
            </a>
            <a href="https://www.instagram.com" target="_blank" class="text-accent hover:text-textColor text-2xl">
                <i class="bx bxl-instagram"></i>
            </a>
        </div>
    </div>
</section>