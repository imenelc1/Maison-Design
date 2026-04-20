<?php
$nombreArticles = 0;
if (isset($_SESSION['panier'])) {
    foreach ($_SESSION['panier'] as $item) {
        $nombreArticles += $item['quantite'];
    }
}
$nomUtilisateur = $_SESSION['prenom'] ?? '';
$isLoggedIn     = isset($_SESSION['user_id']);
$isAdmin        = ($_SESSION['role'] ?? '') === 'admin';
$currentUri     = $_SERVER['REQUEST_URI'] ?? '/';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Maison Design'; ?></title>
    <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary:        '#EEE7DE',
                        secondary:      '#D6C9B6',
                        background:     '#F5F5F5',
                        textColor:      '#3D3D3D',
                        textColorLight: '#F5F5F5',
                        accent:         '#8E9675',
                    },
                    fontFamily: {
                        'cormorant': ['"Cormorant SC"', 'serif'],
                        'frunchy':   ['Frunchy', 'serif'],
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        @font-face {
            font-family: "Frunchy";
            src: url("/font/FrunchySage/Frunchy.woff2") format("woff2"),
                 url("/font/FrunchySage/Frunchy.woff")  format("woff"),
                 url("/font/FrunchySage/Frunchy.otf")   format("opentype");
        }
        .font-frunchy { font-family: "Frunchy", serif; }
    </style>
</head>
<body class="font-cormorant bg-background m-0 p-0">

<?php if (isset($flash) && $flash): ?>
<div id="flash-msg" class="fixed top-20 right-4 z-[100] px-4 py-3 rounded-lg shadow-lg text-white
    <?php echo $flash['type'] === 'success' ? 'bg-accent' : 'bg-red-500'; ?>">
    <?php echo htmlspecialchars($flash['message']); ?>
</div>
<script>setTimeout(() => { const f = document.getElementById('flash-msg'); if(f) f.remove(); }, 3000);</script>
<?php endif; ?>

<!-- HEADER -->
<header class="fixed top-0 left-0 w-full h-16 sm:h-20 bg-white/50 backdrop-blur-sm flex items-center justify-center z-50 shadow-md">
    <div class="w-[95%] sm:w-[90%] max-w-[1200px]">
        <nav class="flex items-center justify-between h-full">

            <!-- Logo -->
            <a href="/" class="flex items-center flex-shrink-0">
                <img src="/images/Logo3_1_1.png" alt="Maison Design"
                     class="w-[45px] sm:w-[60px] md:w-[70px] h-auto">
            </a>

            <!-- Menu desktop -->
            <div class="hidden lg:flex flex-1 justify-center">
                <ul class="flex gap-6 xl:gap-8">
                    <li>
                        <a href="/" class="text-textColor font-normal text-base xl:text-lg hover:text-accent transition-colors">
                            Accueil
                        </a>
                    </li>
                    <li>
                        <a href="/#apropos" class="text-textColor font-normal text-base xl:text-lg hover:text-accent transition-colors">
                            À propos
                        </a>
                    </li>
                    <li class="relative group">
                        <a href="/categories" class="text-textColor font-normal text-base xl:text-lg hover:text-accent transition-colors">
                            Catégories
                        </a>
                        <!-- Dropdown -->
                        <ul class="absolute top-full left-0 bg-white list-none py-2 min-w-[120px] rounded-lg shadow-md z-10 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300">
                            <li class="px-4 py-2 hover:bg-secondary"><a href="/categories?category=lits"     class="text-textColor text-sm block">Lits</a></li>
                            <li class="px-4 py-2 hover:bg-secondary"><a href="/categories?category=chaises"  class="text-textColor text-sm block">Chaises</a></li>
                            <li class="px-4 py-2 hover:bg-secondary"><a href="/categories?category=tables"   class="text-textColor text-sm block">Tables</a></li>
                            <li class="px-4 py-2 hover:bg-secondary"><a href="/categories?category=canapés"  class="text-textColor text-sm block">Canapés</a></li>
                            <li class="px-4 py-2 hover:bg-secondary"><a href="/categories?category=armoires" class="text-textColor text-sm block">Armoires</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="/#contact" class="text-textColor font-normal text-base xl:text-lg hover:text-accent transition-colors">
                            Contact
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2 sm:gap-3">

                <!-- Recherche -->
                <div class="relative">
                    <button id="search-toggle"
                            class="p-2 text-textColor hover:text-accent transition-colors rounded-full hover:bg-primary/30">
                        <i class='bx bx-search text-xl sm:text-2xl'></i>
                    </button>
                    <div id="search-dropdown"
                         class="absolute right-0 top-full mt-2 bg-white rounded-lg shadow-lg p-3 w-[280px] z-50 hidden">
                        <form action="/search" method="GET"
                              class="flex items-center bg-primary/20 rounded-full px-4 py-2 border border-accent/20">
                            <input type="text" name="q" placeholder="Rechercher un produit..."
                                   class="border-none outline-none bg-transparent text-sm w-full">
                            <button type="submit" class="text-accent">
                                <i class='bx bx-search'></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Panier -->
                <a href="/panier"
                   class="relative p-2 text-textColor hover:text-accent transition-colors rounded-full hover:bg-primary/30">
                    <i class='bx bx-cart text-xl sm:text-2xl'></i>
                    <span id="cart-counter"
                          class="absolute -top-1 -right-1 bg-accent text-white text-xs min-w-[18px] h-[18px] items-center justify-center rounded-full font-medium leading-none"
                          style="<?php echo $nombreArticles > 0 ? 'display:flex' : 'display:none'; ?>">
                        <?php echo $nombreArticles > 99 ? '99+' : $nombreArticles; ?>
                    </span>
                </a>

                <!-- User Desktop -->
                <div class="hidden lg:flex items-center">
                    <?php if ($isLoggedIn): ?>
                    <div class="relative user-menu">
                        <button id="user-menu-toggle"
                                class="flex items-center gap-1 px-3 py-1.5 bg-primary text-textColor rounded-full hover:bg-primary/80 text-sm cursor-pointer border-2 border-transparent hover:border-accent/20 transition-all">
                            <i class='bx bx-user text-lg'></i>
                            <span class="max-w-[80px] truncate"><?php echo htmlspecialchars($nomUtilisateur); ?></span>
                            <i class='bx bx-chevron-down text-xs' id="user-chevron"></i>
                        </button>

                        <div id="user-dropdown"
                             class="absolute right-0 top-full mt-2 bg-white rounded-lg shadow-lg border border-gray-200 min-w-[200px] z-50 opacity-0 invisible transition-all duration-300">
                            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50 rounded-t-lg">
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
                            </div>
                            <div class="py-2">
                                <a href="/compte" class="flex items-center px-4 py-2.5 text-sm text-textColor hover:bg-accent/10 hover:text-accent transition-colors">
                                    <i class='bx bx-user-circle text-lg mr-3'></i> Mon profil
                                </a>
                                <a href="/compte?tab=orders" class="flex items-center px-4 py-2.5 text-sm text-textColor hover:bg-accent/10 hover:text-accent transition-colors">
                                    <i class='bx bx-package text-lg mr-3'></i> Mes commandes
                                </a>
                                <?php if ($isAdmin): ?>
                                <div class="border-t border-gray-100 my-1"></div>
                                <a href="/admin" class="flex items-center px-4 py-2.5 text-sm text-textColor hover:bg-accent/10 hover:text-accent transition-colors">
                                    <i class='bx bx-cog text-lg mr-3'></i> Administration
                                </a>
                                <?php endif; ?>
                                <div class="border-t border-gray-100 my-1"></div>
                                <a href="/deconnexion" class="flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors rounded-b-lg">
                                    <i class='bx bx-log-out text-lg mr-3'></i> Déconnexion
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <a href="/connexion"
                       class="flex items-center gap-1 px-3 py-1.5 bg-accent text-white rounded-full hover:bg-accent/80 text-sm transition-colors">
                        <i class='bx bx-log-in text-lg'></i> Se connecter
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Burger mobile -->
                <button id="open-menu"
                        class="lg:hidden p-2 text-textColor hover:text-accent transition-colors rounded-full hover:bg-primary/30">
                    <i class='bx bx-menu text-xl sm:text-2xl'></i>
                </button>
            </div>
        </nav>
    </div>
</header>

<!-- Menu Mobile -->
<div id="mobile-menu"
     class="fixed top-0 right-0 lg:hidden bg-white z-[1000] h-screen w-[85%] max-w-[320px] shadow-xl overflow-y-auto"
     style="transform: translateX(100%); transition: transform 0.3s ease;">

    <div class="sticky top-0 bg-white border-b border-gray-200 p-4 flex items-center justify-between">
        <img src="/images/Logo3_1_1.png" alt="Logo" class="w-[50px] h-auto">
        <button id="close-menu" class="p-2 text-textColor hover:text-accent rounded-full hover:bg-gray-100">
            <i class='bx bx-x text-2xl'></i>
        </button>
    </div>

    <div class="p-4">
        <?php if ($isLoggedIn): ?>
        <div class="bg-gradient-to-r from-accent/10 to-primary/10 rounded-lg p-4 mb-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-accent rounded-full flex items-center justify-center">
                    <i class='bx bx-user text-white text-xl'></i>
                </div>
                <div>
                    <p class="font-medium text-textColor text-sm"><?php echo htmlspecialchars($nomUtilisateur); ?></p>
                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <nav class="mb-6">
            <ul class="space-y-1">
                <li><a href="/"           class="flex items-center w-full px-3 py-3 text-textColor hover:bg-accent/10 hover:text-accent rounded-lg"><i class='bx bx-home text-xl mr-3'></i> Accueil</a></li>
                <li><a href="/#apropos"   class="flex items-center w-full px-3 py-3 text-textColor hover:bg-accent/10 hover:text-accent rounded-lg"><i class='bx bx-info-circle text-xl mr-3'></i> À propos</a></li>
                <li><a href="/categories" class="flex items-center w-full px-3 py-3 text-textColor hover:bg-accent/10 hover:text-accent rounded-lg"><i class='bx bx-category text-xl mr-3'></i> Catégories</a></li>
                <li><a href="/#contact"  class="flex items-center w-full px-3 py-3 text-textColor hover:bg-accent/10 hover:text-accent rounded-lg"><i class='bx bx-envelope text-xl mr-3'></i> Contact</a></li>
                <li><a href="/panier"    class="flex items-center justify-between w-full px-3 py-3 text-textColor hover:bg-accent/10 hover:text-accent rounded-lg">
                    <span class="flex items-center"><i class='bx bx-cart text-xl mr-3'></i> Mon Panier</span>
                    <?php if ($nombreArticles > 0): ?>
                    <span class="bg-accent text-white text-xs px-2 py-0.5 rounded-full"><?php echo $nombreArticles; ?></span>
                    <?php endif; ?>
                </a></li>
            </ul>
        </nav>

        <?php if ($isLoggedIn): ?>
        <div class="border-t border-gray-200 pt-4 mb-4">
            <ul class="space-y-1">
                <li><a href="/compte"          class="flex items-center w-full px-3 py-2 text-textColor hover:bg-accent/10 hover:text-accent rounded-lg text-sm"><i class='bx bx-user-circle text-lg mr-3'></i> Mon profil</a></li>
                <li><a href="/compte?tab=orders" class="flex items-center w-full px-3 py-2 text-textColor hover:bg-accent/10 hover:text-accent rounded-lg text-sm"><i class='bx bx-package text-lg mr-3'></i> Mes commandes</a></li>
                <?php if ($isAdmin): ?>
                <li><a href="/admin" class="flex items-center w-full px-3 py-2 text-textColor hover:bg-accent/10 hover:text-accent rounded-lg text-sm"><i class='bx bx-cog text-lg mr-3'></i> Administration</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="border-t border-gray-200 pt-4">
            <?php if ($isLoggedIn): ?>
            <a href="/deconnexion"
               class="flex items-center justify-center w-full px-4 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                <i class='bx bx-log-out text-lg mr-2'></i> Déconnexion
            </a>
            <?php else: ?>
            <a href="/connexion"
               class="flex items-center justify-center w-full px-4 py-3 bg-accent text-white rounded-lg hover:bg-accent/80 transition-colors">
                <i class='bx bx-log-in text-lg mr-2'></i> Se connecter
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Overlay mobile -->
<div id="menu-overlay"
     class="fixed inset-0 bg-black/50 z-[999] lg:hidden"
     style="opacity:0; visibility:hidden; transition: all 0.3s;">
</div>

<!-- Modal recherche mobile -->
<div id="search-modal"
     class="fixed inset-0 bg-black/50 z-[1001] items-center justify-center p-4 sm:hidden"
     style="display:none;">
    <div class="bg-white rounded-xl w-full max-w-md p-5">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-textColor">Rechercher</h3>
            <button id="close-search-modal" class="text-2xl text-textColor hover:text-accent">
                <i class='bx bx-x'></i>
            </button>
        </div>
        <form action="/search" method="GET">
            <div class="flex items-center bg-primary/20 rounded-lg px-4 py-3 border border-accent/20">
                <input type="text" name="q" placeholder="Rechercher un produit..."
                       class="border-none outline-none bg-transparent text-base w-full">
                <button type="submit" class="bg-accent rounded-full w-8 h-8 text-white flex items-center justify-center ml-2">
                    <i class='bx bx-search'></i>
                </button>
            </div>
        </form>
    </div>
</div>

<main class="pt-16 sm:pt-20">