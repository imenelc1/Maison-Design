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

<header class="fixed top-0 left-0 w-full h-20 bg-white/50 backdrop-blur-sm flex items-center justify-center z-50 shadow-md">
    <div class="w-[90%] max-w-[1200px]">
        <nav class="flex items-center justify-between">
            <div class="w-[20%] flex items-center">
                <a href="index.php" class="logo-content flex items-center">
                    <img src="images/Logo3_1_1.png" alt="Logo Maison Design" class="w-[60px] md:w-[70px] h-auto">
                </a>
            </div>

            <div class="w-[60%] flex justify-center">
                <!-- Menu desktop -->
                <ul class="hidden md:flex flex-row gap-8">
                    <li class="relative"><a href="index.php" class="text-textColor font-normal text-lg hover:text-accent transition-colors">Accueil</a></li>
                    <li class="relative"><a href="index.php#apropos" class="text-textColor font-normal text-lg hover:text-accent transition-colors">A propos</a></li>
                    <li class="relative group">
                        <a href="categories.php" class="text-textColor font-normal text-lg hover:text-accent transition-colors">Catégories</a>
                        <ul class="absolute top-full left-0 bg-white list-none py-2.5 px-0 m-0 min-w-[100px] rounded-lg shadow-md z-10 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform translate-y-2 group-hover:translate-y-0">
                            <li class="px-5 py-2.5 hover:bg-secondary"><a href="categories.php?category=lit" class="no-underline text-textColor text-base block">Lits</a></li>
                            <li class="px-5 py-2.5 hover:bg-secondary"><a href="categories.php?category=chaise" class="no-underline text-textColor text-base block">Chaises</a></li>
                            <li class="px-5 py-2.5 hover:bg-secondary"><a href="categories.php?category=table" class="no-underline text-textColor text-base block">Tables</a></li>
                            <li class="px-5 py-2.5 hover:bg-secondary"><a href="categories.php?category=canapé" class="no-underline text-textColor text-base block">Canapés</a></li>
                            <li class="px-5 py-2.5 hover:bg-secondary"><a href="categories.php?category=armoire" class="no-underline text-textColor text-base block">Armoires</a></li>
                        </ul>
                    </li>
                    <li class="relative"><a href="index.php#contact" class="text-textColor font-normal text-lg hover:text-accent transition-colors">Contact</a></li>
                </ul>
            </div>
            
            <div class="w-[20%] flex items-center justify-end gap-4">
                <!--  barre de recherche  -->
                <div class="search-container relative">
                    <button id="search-toggle" class="search-toggle-btn bg-transparent border-none cursor-pointer text-textColor text-2xl flex items-center justify-center hover:text-accent transition-colors p-2 rounded-full hover:bg-primary/30" title="Rechercher">
                        <i class='bx bx-search'></i>
                    </button>
                    <div id="search-dropdown" class="absolute right-0 top-full mt-2 bg-white rounded-lg shadow-lg p-3 w-[300px] z-50 opacity-0 invisible transform translate-y-2 transition-all duration-300">
                        <form action="search.php" method="GET" class="search-bar flex items-center bg-primary/20 rounded-full px-4 py-2 border border-accent/20 focus-within:border-accent transition-all duration-300">
                            <input type="text" name="q" placeholder="Rechercher un produit..." class="border-none outline-none bg-transparent text-base w-full">
                            <button type="submit" class="search-button bg-transparent border-none cursor-pointer text-accent text-lg flex items-center justify-center hover:text-accent/70 transition-colors" title="Rechercher">
                                <i class='bx bx-search'></i>
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Panier -->
                <div class="cart-container relative">
                    <a href="cart.php" class="cart-toggle-btn bg-transparent border-none cursor-pointer text-textColor text-2xl flex items-center justify-center hover:text-accent transition-colors p-2 rounded-full hover:bg-primary/30 relative" title="Panier">
                        <i class='bx bx-cart'></i>
                        <span class="absolute -top-1 -right-1 bg-accent text-white text-xs w-5 h-5 flex items-center justify-center rounded-full"><?php echo $nombreArticles; ?></span>
                    </a>
                </div>
                
                <!-- Menu utilisateur amélioré -->
                <div class="hidden md:flex cote-droit items-center gap-4 text-lg text-textColor">
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Menu déroulant utilisateur -->
                    <div class="relative group user-menu">
                        <button class="flex items-center justify-center px-4 py-1.5 bg-primary text-textColor rounded-full hover:bg-primary/80 transition-all duration-300 text-sm font-medium cursor-pointer border-2 border-transparent hover:border-accent/20" title="Mon compte">
                            <i class='bx bx-user text-lg mr-2'></i>
                            <span><?php echo htmlspecialchars($nomUtilisateur); ?></span>
                            <i class='bx bx-chevron-down text-sm ml-2 transition-transform duration-300 group-hover:rotate-180'></i>
                        </button>
                        
                        <!-- Menu déroulant -->
                        <div class="absolute right-0 top-full mt-2 bg-white rounded-lg shadow-lg border border-gray-200 min-w-[200px] z-50 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform translate-y-2 group-hover:translate-y-0">
                            <!-- Flèche du menu -->
                            <div class="absolute -top-2 right-4 w-4 h-4 bg-white border-l border-t border-gray-200 transform rotate-45"></div>
                            
                            <!-- En-tête du menu -->
                            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50 rounded-t-lg">
                                <p class="text-sm font-medium text-textColor">Connecté en tant que</p>
                                <p class="text-xs text-gray-500"><?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'utilisateur@email.com'; ?></p>
                            </div>
                            
                            <!-- Options du menu -->
                            <div class="py-2">
                                <a href="client.php" class="flex items-center px-4 py-2 text-sm text-textColor hover:bg-accent/10 hover:text-accent transition-all duration-200 hover:translate-x-1">
                                    <i class='bx bx-user-circle text-lg mr-3'></i>
                                    Mon profil
                                </a>
                                <a href="client.php?tab=orders" class="flex items-center px-4 py-2 text-sm text-textColor hover:bg-accent/10 hover:text-accent transition-all duration-200 hover:translate-x-1">
                                    <i class='bx bx-package text-lg mr-3'></i>
                                    Mes commandes
                                </a>
                                <a href="client.php?tab=addresses" class="flex items-center px-4 py-2 text-sm text-textColor hover:bg-accent/10 hover:text-accent transition-all duration-200 hover:translate-x-1">
                                    <i class='bx bx-map text-lg mr-3'></i>
                                    Mes adresses
                                </a>
                                <a href="client.php?tab=wishlist" class="flex items-center px-4 py-2 text-sm text-textColor hover:bg-accent/10 hover:text-accent transition-all duration-200 hover:translate-x-1">
                                    <i class='bx bx-heart text-lg mr-3'></i>
                                    Mes favoris
                                </a>
                                
                                <!-- Séparateur -->
                                <div class="border-t border-gray-100 my-2"></div>
                                
                                <!-- Options admin si applicable -->
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <a href="admin.html" class="flex items-center px-4 py-2 text-sm text-textColor hover:bg-accent/10 hover:text-accent transition-all duration-200 hover:translate-x-1">
                                    <i class='bx bx-cog text-lg mr-3'></i>
                                    Administration
                                </a>
                                <div class="border-t border-gray-100 my-2"></div>
                                <?php endif; ?>
                                
                                <!-- Déconnexion -->
                                <a href="php/logout.php" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-all duration-200 hover:translate-x-1 rounded-b-lg">
                                    <i class='bx bx-log-out text-lg mr-3'></i>
                                    Déconnexion
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <a href="connexion.php" class="flex items-center justify-center px-4 py-1.5 bg-accent text-white rounded-full hover:bg-accent/80 transition-colors text-sm font-medium" title="Connexion">
                        <i class='bx bx-log-in text-lg mr-2'></i>
                        Se connecter
                    </a>
                    <?php endif; ?>
                </div>
                
                <!-- Icône pour ouvrir menu uniquement sur mobile -->
                <button id="open-menu" class="md:hidden bg-transparent border-none text-2xl cursor-pointer text-textColor hover:text-accent transition-colors ml-2" title="ouvrir menu">
                    <i class='bx bx-menu'></i>
                </button>
            </div>

            <!-- Menu mobile amélioré -->
            <div id="mobile-menu" class="fixed top-0 right-[-280px] md:hidden bg-white p-6 z-[1000] h-screen w-[280px] transition-all duration-300 shadow-lg">           
                <button id="close-menu" class="absolute top-4 left-4 bg-transparent border-none text-2xl cursor-pointer text-textColor hover:text-accent transition-colors" title="fermer menu">
                    <i class='bx bx-x'></i>
                </button>

                <!-- Logo dans le menu mobile -->
                <div class="flex justify-center mt-4 mb-8">
                    <img src="images/Logo3_1_1.png" alt="Logo Maison Design" class="w-[60px] h-auto">
                </div>

                <!-- Informations utilisateur en mobile -->
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="bg-accent/10 rounded-lg p-4 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-accent rounded-full flex items-center justify-center">
                            <i class='bx bx-user text-white text-lg'></i>
                        </div>
                        <div>
                            <p class="font-medium text-textColor text-sm"><?php echo htmlspecialchars($nomUtilisateur); ?></p>
                            <p class="text-xs text-gray-500"><?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'utilisateur@email.com'; ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <ul class="menu-list flex flex-col items-start gap-6 w-full mb-8">
                    <li class="relative w-full"><a href="index.php" class="text-textColor font-normal text-lg hover:text-accent transition-colors">Accueil</a></li>
                    <li class="relative w-full"><a href="index.php#apropos" class="text-textColor font-normal text-lg hover:text-accent transition-colors">A propos</a></li>
                    <li class="relative w-full mobile-dropdown">
                        <button class="text-textColor font-normal text-lg hover:text-accent transition-colors flex justify-between items-center w-full">
                            Catégories
                            <i class='bx bx-chevron-down text-lg transition-transform duration-300' id="chevron-mobile"></i>
                        </button>
                        <ul class="bg-white/10 list-none py-0 px-0 m-0 rounded-lg mt-2 overflow-hidden max-h-0 transition-all duration-300" id="mobile-submenu">
                            <li class="px-5 py-2 hover:bg-white/20"><a href="categories.php?category=lit" class="no-underline text-textColor text-base block">Lits</a></li>
                            <li class="px-5 py-2 hover:bg-white/20"><a href="categories.php?category=chaise" class="no-underline text-textColor text-base block">Chaises</a></li>
                            <li class="px-5 py-2 hover:bg-white/20"><a href="categories.php?category=table" class="no-underline text-textColor text-base block">Tables</a></li>
                            <li class="px-5 py-2 hover:bg-white/20"><a href="categories.php?category=canapé" class="no-underline text-textColor text-base block">Canapés</a></li>
                            <li class="px-5 py-2 hover:bg-white/20"><a href="categories.php?category=armoire" class="no-underline text-textColor text-base block">Armoires</a></li>
                        </ul>
                    </li>
                    <li class="relative w-full"><a href="index.php#contact" class="text-textColor font-normal text-lg hover:text-accent transition-colors">Contact</a></li>
                    <li class="relative w-full">
                        <a href="cart.php" class="text-textColor font-normal text-lg hover:text-accent transition-colors flex items-center gap-2">
                            <i class='bx bx-cart'></i> Mon Panier
                            <span class="bg-accent text-white text-xs w-5 h-5 flex items-center justify-center rounded-full"><?php echo $nombreArticles; ?></span>
                        </a>
                    </li>
                </ul>

                <!-- Menu utilisateur mobile -->
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="border-t border-gray-200 pt-4 mb-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-3 uppercase tracking-wide">Mon compte</h3>
                    <div class="space-y-2">
                        <a href="client.php" class="flex items-center w-full px-3 py-2 text-textColor hover:bg-accent/10 hover:text-accent transition-all duration-200 hover:translate-x-1 rounded-lg text-sm">
                            <i class='bx bx-user-circle text-lg mr-3'></i>
                            Mon profil
                        </a>
                        <a href="client.php?tab=orders" class="flex items-center w-full px-3 py-2 text-textColor hover:bg-accent/10 hover:text-accent transition-all duration-200 hover:translate-x-1 rounded-lg text-sm">
                            <i class='bx bx-package text-lg mr-3'></i>
                            Mes commandes
                        </a>
                        <a href="client.php?tab=addresses" class="flex items-center w-full px-3 py-2 text-textColor hover:bg-accent/10 hover:text-accent transition-all duration-200 hover:translate-x-1 rounded-lg text-sm">
                            <i class='bx bx-map text-lg mr-3'></i>
                            Mes adresses
                        </a>
                        <a href="client.php?tab=wishlist" class="flex items-center w-full px-3 py-2 text-textColor hover:bg-accent/10 hover:text-accent transition-all duration-200 hover:translate-x-1 rounded-lg text-sm">
                            <i class='bx bx-heart text-lg mr-3'></i>
                            Mes favoris
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Boutons de connexion dans le menu mobile -->
                <div class="mt-auto">
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
        </nav>
    </div>
</header>

<!-- Overlay pour le menu mobile -->
<div id="menu-overlay" class="fixed inset-0 bg-black/50 z-[999] md:hidden opacity-0 invisible transition-all duration-300"></div>

<!-- Modal de recherche pour mobile -->
<div id="search-modal" class="search-modal fixed inset-0 bg-black/50 z-[1001] flex items-center justify-center p-4 opacity-0 invisible transition-all duration-300">
    <div class="search-modal-content bg-white rounded-xl w-full max-w-md p-5 transform -translate-y-5 transition-transform duration-300">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-textColor">Rechercher</h3>
            <button id="close-search-modal" class="text-2xl text-textColor hover:text-accent" title="close">
                <i class='bx bx-x'></i>
            </button>
        </div>
        <form action="search.php" method="GET">
            <div class="search-bar flex items-center bg-primary/20 rounded-lg px-4 py-3 border border-accent/20 focus-within:border-accent transition-all duration-300 mb-4">
                <input type="text" name="q" placeholder="Rechercher un produit..." class="border-none outline-none bg-transparent text-base w-full">
                <button type="submit" class="search-button bg-accent rounded-full w-8 h-8 border-none cursor-pointer text-white text-lg flex items-center justify-center hover:bg-accent/80 transition-colors" title="Rechercher">
                    <i class='bx bx-search'></i>
                </button>
            </div>
        </form>
    </div>
</div>
 <script src="js/header-menu.js"></script>
