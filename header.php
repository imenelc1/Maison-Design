<?php
// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialiser le panier s'il n'existe pas
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Calculer le nombre d'articles dans le panier
$nombreArticles = 0;
foreach ($_SESSION['panier'] as $item) {
    $nombreArticles += $item['quantite'];
}

// Récupérer le nom de l'utilisateur connecté
$nomUtilisateur = '';
if (isset($_SESSION['user_id'])) {
    $nomUtilisateur = isset($_SESSION['prenom']) ? $_SESSION['prenom'] : 'Mon compte';
}
?>

<header class="fixed top-0 left-0 w-full h-16 sm:h-20 bg-white/50 backdrop-blur-sm flex items-center justify-center z-50 shadow-md">
    <div class="w-[95%] sm:w-[90%] max-w-[1200px]">
        <nav class="flex items-center justify-between h-full">
            <!-- Logo -->
            <div class="flex items-center flex-shrink-0">
                <a href="index.php" class="logo-content flex items-center">
                    <img src="images/Logo3_1_1.png" alt="Logo Maison Design" class="w-[45px] sm:w-[60px] md:w-[70px] h-auto">
                </a>
            </div>

            <!-- Menu desktop -->
            <div class="hidden lg:flex flex-1 justify-center">
                <ul class="flex flex-row gap-6 xl:gap-8">
                    <li class="relative">
                        <a href="index.php" class="text-textColor font-normal text-base xl:text-lg hover:text-accent transition-colors">Accueil</a>
                    </li>
                    <li class="relative">
                        <a href="index.php#apropos" class="text-textColor font-normal text-base xl:text-lg hover:text-accent transition-colors">À propos</a>
                    </li>
                    <li class="relative group">
                        <a href="index.php#categories" class="text-textColor font-normal text-base xl:text-lg hover:text-accent transition-colors">Catégories</a>
                        <ul class="absolute top-full left-0 bg-white list-none py-2.5 px-0 m-0 min-w-[120px] rounded-lg shadow-md z-10 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform translate-y-2 group-hover:translate-y-0">
                            <li class="px-4 py-2 hover:bg-secondary"><a href="categories.php?category=lits" class="no-underline text-textColor text-sm block">Lits</a></li>
                            <li class="px-4 py-2 hover:bg-secondary"><a href="categories.php?category=chaises" class="no-underline text-textColor text-sm block">Chaises</a></li>
                            <li class="px-4 py-2 hover:bg-secondary"><a href="categories.php?category=tables" class="no-underline text-textColor text-sm block">Tables</a></li>
                            <li class="px-4 py-2 hover:bg-secondary"><a href="categories.php?category=canapés" class="no-underline text-textColor text-sm block">Canapés</a></li>
                            <li class="px-4 py-2 hover:bg-secondary"><a href="categories.php?category=armoires" class="no-underline text-textColor text-sm block">Armoires</a></li>
                        </ul>
                    </li>
                    <li class="relative">
                        <a href="index.php#contact" class="text-textColor font-normal text-base xl:text-lg hover:text-accent transition-colors">Contact</a>
                    </li>
                </ul>
            </div>
            
            <!-- Actions desktop et mobile -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Recherche -->
                <div class="search-container relative">
                    <button id="search-toggle" class="search-toggle-btn bg-transparent border-none cursor-pointer text-textColor text-xl sm:text-2xl flex items-center justify-center hover:text-accent transition-colors p-1.5 sm:p-2 rounded-full hover:bg-primary/30" title="Rechercher">
                        <i class='bx bx-search'></i>
                    </button>
                    <!-- Dropdown recherche desktop -->
                    <div id="search-dropdown" class="absolute right-0 top-full mt-2 bg-white rounded-lg shadow-lg p-3 w-[280px] sm:w-[300px] z-50 opacity-0 invisible transform translate-y-2 transition-all duration-300 hidden sm:block">
                        <form action="search.php" method="GET" class="search-bar flex items-center bg-primary/20 rounded-full px-4 py-2 border border-accent/20 focus-within:border-accent transition-all duration-300">
                            <input type="text" name="q" placeholder="Rechercher un produit..." class="border-none outline-none bg-transparent text-sm sm:text-base w-full">
                            <button type="submit" class="search-button bg-transparent border-none cursor-pointer text-accent text-lg flex items-center justify-center hover:text-accent/70 transition-colors" title="Rechercher">
                                <i class='bx bx-search'></i>
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Panier avec notification  -->
                <div class="cart-container relative">
                    <a href="cart.php" class="cart-toggle-btn bg-transparent border-none cursor-pointer text-textColor text-xl sm:text-2xl flex items-center justify-center hover:text-accent transition-colors p-1.5 sm:p-2 rounded-full hover:bg-primary/30 relative" title="Panier">
                        <i class='bx bx-cart'></i>
                        <!-- SOLUTION TAILWIND: Badge avec positionnement fixe -->
                        <span id="cart-counter" class="absolute -top-1 -right-1 bg-accent text-white text-xs min-w-[18px] h-[18px] flex items-center justify-center rounded-full font-medium leading-none" style="<?php echo $nombreArticles > 0 ? '' : 'display: none;'; ?>">
                            <?php echo $nombreArticles > 99 ? '99+' : $nombreArticles; ?>
                        </span>
                    </a>
                </div>
                
                <!-- Menu utilisateur desktop SEULEMENT avec dropdown -->
                <div class="hidden lg:flex items-center">
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Menu déroulant utilisateur avec JavaScript -->
                    <div class="relative user-menu">
                        <button id="user-menu-toggle" class="flex items-center justify-center px-3 py-1.5 bg-primary text-textColor rounded-full hover:bg-primary/80 transition-all duration-300 text-sm font-medium cursor-pointer border-2 border-transparent hover:border-accent/20" title="Mon compte">
                            <i class='bx bx-user text-lg mr-1'></i>
                            <span class="max-w-[80px] truncate"><?php echo htmlspecialchars($nomUtilisateur); ?></span>
                            <i class='bx bx-chevron-down text-xs ml-1 transition-transform duration-300' id="user-chevron"></i>
                        </button>
                        
                        <!-- Menu déroulant -->
                        <div id="user-dropdown" class="absolute right-0 top-full mt-2 bg-white rounded-lg shadow-lg border border-gray-200 min-w-[220px] z-50 opacity-0 invisible transition-all duration-300 transform translate-y-2">
                            <!-- Flèche du menu -->
                            <div class="absolute -top-2 right-4 w-4 h-4 bg-white border-l border-t border-gray-200 transform rotate-45"></div>
                            
                            <!-- En-tête du menu -->
                            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50 rounded-t-lg">
                                <p class="text-sm font-medium text-textColor">Connecté en tant que</p>
                                <p class="text-xs text-gray-500 truncate"><?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'utilisateur@email.com'; ?></p>
                            </div>
                            
                            <!-- Options du menu -->
                            <div class="py-2">
                                <a href="client.php" class="flex items-center px-4 py-2.5 text-sm text-textColor hover:bg-accent/10 hover:text-accent transition-all duration-200">
                                    <i class='bx bx-user-circle text-lg mr-3 flex-shrink-0'></i>
                                    <span>Mon profil</span>
                                </a>
                                <a href="client.php?tab=orders" class="flex items-center px-4 py-2.5 text-sm text-textColor hover:bg-accent/10 hover:text-accent transition-all duration-200">
                                    <i class='bx bx-package text-lg mr-3 flex-shrink-0'></i>
                                    <span>Mes commandes</span>
                                </a>
                                <a href="client.php?tab=wishlist" class="flex items-center px-4 py-2.5 text-sm text-textColor hover:bg-accent/10 hover:text-accent transition-all duration-200">
                                    <i class='bx bx-heart text-lg mr-3 flex-shrink-0'></i>
                                    <span>Mes favoris</span>
                                </a>
                                
                                <!-- Séparateur -->
                                <div class="border-t border-gray-100 my-2"></div>
                                
                                <!-- Options admin si applicable -->
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <a href="admin.html" class="flex items-center px-4 py-2.5 text-sm text-textColor hover:bg-accent/10 hover:text-accent transition-all duration-200">
                                    <i class='bx bx-cog text-lg mr-3 flex-shrink-0'></i>
                                    <span>Administration</span>
                                </a>
                                <div class="border-t border-gray-100 my-2"></div>
                                <?php endif; ?>
                                
                                <!-- Déconnexion -->
                                <a href="php/logout.php" class="flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-all duration-200 rounded-b-lg">
                                    <i class='bx bx-log-out text-lg mr-3 flex-shrink-0'></i>
                                    <span>Déconnexion</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <a href="connexion.php" class="flex items-center justify-center px-3 py-1.5 bg-accent text-white rounded-full hover:bg-accent/80 transition-colors text-sm font-medium whitespace-nowrap" title="Connexion">
                        <i class='bx bx-log-in text-lg mr-1'></i>
                        <span>Se connecter</span>
                    </a>
                    <?php endif; ?>
                </div>
                
                <!-- Bouton menu mobile -->
                <button id="open-menu" class="lg:hidden bg-transparent border-none text-xl sm:text-2xl cursor-pointer text-textColor hover:text-accent transition-colors p-1.5 sm:p-2 rounded-full hover:bg-primary/30" title="Ouvrir menu">
                    <i class='bx bx-menu'></i>
                </button>
            </div>
        </nav>
    </div>
</header>

<!-- Menu mobile amélioré - CORRECTION DU POSITIONNEMENT -->
<div id="mobile-menu" class="fixed top-0 right-0 lg:hidden bg-white z-[1000] h-screen w-[85%] max-w-[320px] transition-all duration-300 shadow-xl overflow-y-auto transform translate-x-full">           
    <!-- En-tête du menu mobile -->
    <div class="sticky top-0 bg-white border-b border-gray-200 p-4 flex items-center justify-between">
        <img src="images/Logo3_1_1.png" alt="Logo Maison Design" class="w-[50px] h-auto">
        <button id="close-menu" class="bg-transparent border-none text-2xl cursor-pointer text-textColor hover:text-accent transition-colors p-2 rounded-full hover:bg-gray-100" title="Fermer menu">
            <i class='bx bx-x'></i>
        </button>
    </div>

    <div class="p-4">
        <!-- Informations utilisateur en mobile -->
        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="bg-gradient-to-r from-accent/10 to-primary/10 rounded-lg p-4 mb-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-accent rounded-full flex items-center justify-center flex-shrink-0">
                    <i class='bx bx-user text-white text-xl'></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="font-medium text-textColor text-sm truncate"><?php echo htmlspecialchars($nomUtilisateur); ?></p>
                    <p class="text-xs text-gray-500 truncate"><?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'utilisateur@email.com'; ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Navigation principale -->
        <nav class="mb-6">
            <ul class="space-y-2">
                <li>
                    <a href="index.php" class="flex items-center w-full px-3 py-3 text-textColor hover:bg-accent/10 hover:text-accent transition-all duration-200 rounded-lg text-base font-medium">
                        <i class='bx bx-home text-xl mr-3'></i>
                        Accueil
                    </a>
                </li>
                <li>
                    <a href="index.php#apropos" class="flex items-center w-full px-3 py-3 text-textColor hover:bg-accent/10 hover:text-accent transition-all duration-200 rounded-lg text-base font-medium">
                        <i class='bx bx-info-circle text-xl mr-3'></i>
                        À propos
                    </a>
                </li>
                <li class="mobile-dropdown">
                    <button class="flex items-center justify-between w-full px-3 py-3 text-textColor hover:bg-accent/10 hover:text-accent transition-all duration-200 rounded-lg text-base font-medium">
                        <div class="flex items-center">
                            <i class='bx bx-category text-xl mr-3'></i>
                            Catégories
                        </div>
                        <i class='bx bx-chevron-down text-lg transition-transform duration-300' id="chevron-mobile"></i>
                    </button>
                    <ul class="bg-gray-50 rounded-lg mt-2 overflow-hidden max-h-0 transition-all duration-300" id="mobile-submenu">
                        <li><a href="categories.php?category=lits" class="block px-6 py-2 text-textColor hover:bg-accent/10 hover:text-accent transition-colors text-sm">Lits</a></li>
                        <li><a href="categories.php?category=chaises" class="block px-6 py-2 text-textColor hover:bg-accent/10 hover:text-accent transition-colors text-sm">Chaises</a></li>
                        <li><a href="categories.php?category=tables" class="block px-6 py-2 text-textColor hover:bg-accent/10 hover:text-accent transition-colors text-sm">Tables</a></li>
                        <li><a href="categories.php?category=canapés" class="block px-6 py-2 text-textColor hover:bg-accent/10 hover:text-accent transition-colors text-sm">Canapés</a></li>
                        <li><a href="categories.php?category=armoires" class="block px-6 py-2 text-textColor hover:bg-accent/10 hover:text-accent transition-colors text-sm">Armoires</a></li>
                    </ul>
                </li>
                <li>
                    <a href="index.php#contact" class="flex items-center w-full px-3 py-3 text-textColor hover:bg-accent/10 hover:text-accent transition-all duration-200 rounded-lg text-base font-medium">
                        <i class='bx bx-envelope text-xl mr-3'></i>
                        Contact
                    </a>
                </li>
                <li>
                    <a href="cart.php" class="flex items-center justify-between w-full px-3 py-3 text-textColor hover:bg-accent/10 hover:text-accent transition-all duration-200 rounded-lg text-base font-medium">
                        <div class="flex items-center">
                            <i class='bx bx-cart text-xl mr-3'></i>
                            Mon Panier
                        </div>
                        <!-- SOLUTION TAILWIND: Badge mobile -->
                        <span id="cart-counter-mobile" class="absolute -top-1 -right-1 bg-accent text-white text-xs min-w-[18px] h-[18px] flex items-center justify-center rounded-full font-medium leading-none" style="<?php echo $nombreArticles > 0 ? '' : 'display: none;'; ?>">
                            <?php echo $nombreArticles > 99 ? '99+' : $nombreArticles; ?>
                        </span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Menu utilisateur mobile -->
        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="border-t border-gray-200 pt-4 mb-6">
            <h3 class="text-sm font-medium text-gray-500 mb-3 uppercase tracking-wide px-3">Mon compte</h3>
            <div class="space-y-1">
                <a href="client.php" class="flex items-center w-full px-3 py-2 text-textColor hover:bg-accent/10 hover:text-accent transition-all duration-200 rounded-lg text-sm">
                    <i class='bx bx-user-circle text-lg mr-3'></i>
                    Mon profil
                </a>
                <a href="client.php?tab=orders" class="flex items-center w-full px-3 py-2 text-textColor hover:bg-accent/10 hover:text-accent transition-all duration-200 rounded-lg text-sm">
                    <i class='bx bx-package text-lg mr-3'></i>
                    Mes commandes
                </a>
                <a href="client.php?tab=addresses" class="flex items-center w-full px-3 py-2 text-textColor hover:bg-accent/10 hover:text-accent transition-all duration-200 rounded-lg text-sm">
                    <i class='bx bx-map text-lg mr-3'></i>
                    Mes adresses
                </a>
                <a href="client.php?tab=wishlist" class="flex items-center w-full px-3 py-2 text-textColor hover:bg-accent/10 hover:text-accent transition-all duration-200 rounded-lg text-sm">
                    <i class='bx bx-heart text-lg mr-3'></i>
                    Mes favoris
                </a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="admin.html" class="flex items-center w-full px-3 py-2 text-textColor hover:bg-accent/10 hover:text-accent transition-all duration-200 rounded-lg text-sm">
                    <i class='bx bx-cog text-lg mr-3'></i>
                    Administration
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Boutons de connexion dans le menu mobile -->
        <div class="border-t border-gray-200 pt-4">
            <?php if (isset($_SESSION['user_id'])): ?>
            <a href="php/logout.php" class="flex items-center justify-center w-full px-4 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors text-base font-medium">
                <i class='bx bx-log-out text-lg mr-2'></i>
                Déconnexion
            </a>
            <?php else: ?>
            <a href="connexion.php" class="flex items-center justify-center w-full px-4 py-3 bg-accent text-white rounded-lg hover:bg-accent/80 transition-colors text-base font-medium">
                <i class='bx bx-log-in text-lg mr-2'></i>
                Se connecter
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Overlay pour le menu mobile -->
<div id="menu-overlay" class="fixed inset-0 bg-black/50 z-[999] lg:hidden opacity-0 invisible transition-all duration-300"></div>

<!-- Modal de recherche pour mobile -->
<div id="search-modal" class="search-modal fixed inset-0 bg-black/50 z-[1001] flex items-center justify-center p-4 opacity-0 invisible transition-all duration-300 sm:hidden">
    <div class="search-modal-content bg-white rounded-xl w-full max-w-md p-5 transform -translate-y-5 transition-transform duration-300">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-textColor">Rechercher</h3>
            <button id="close-search-modal" class="text-2xl text-textColor hover:text-accent p-1 rounded-full hover:bg-gray-100" title="Fermer">
                <i class='bx bx-x'></i>
            </button>
        </div>
        <form action="search.php" method="GET">
            <div class="search-bar flex items-center bg-primary/20 rounded-lg px-4 py-3 border border-accent/20 focus-within:border-accent transition-all duration-300 mb-4">
                <input type="text" name="q" placeholder="Rechercher un produit..." class="border-none outline-none bg-transparent text-base w-full">
                <button type="submit" class="search-button bg-accent rounded-full w-8 h-8 border-none cursor-pointer text-white text-lg flex items-center justify-center hover:bg-accent/80 transition-colors ml-2" title="Rechercher">
                    <i class='bx bx-search'></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Fonctions partagées pour la gestion du panier -->
<script src="js/shared-cart-functions.js"></script>
<script src="js/header-menu.js"></script>
