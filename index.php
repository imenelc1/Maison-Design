<?php
// Démarrer la session au début du fichier
session_start();

// Initialiser le panier s'il n'existe pas
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maison Design</title>
    <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwind.config.js"></script>
   
    <link rel="stylesheet" href="css/style.css">
    <!-- Inclure les styles du header -->
    <link rel="stylesheet" href="css/header-styles.css">
</head>
<body class="font-cormorant bg-background m-0 p-0 box-border">
    <!-- HEADER -->
    <?php include 'header.php'; ?>

    <main>
        <!-- SECTION D'ACCUEIL -->
        <section class="home h-screen w-full relative" id="home">
            <div class="home-img relative h-screen bg-[url('images/acceuil.jpg')] bg-cover bg-center bg-no-repeat before:content-[''] before:absolute before:top-0 before:left-0 before:w-full before:h-full before:bg-black/40">
                <div class="home-details absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-center text-white justify-items-center w-full px-4">
                    <div class="logo"><img src="images/mdblanc.png" alt="Logo" class="max-w-[100px] md:max-w-[150px] h-auto mx-auto"></div>
                    <div class="titre font-frunchy text-[40px] md:text-[60px] leading-none">M A I S O N . D E S I G N</div>
                    <div class="home-text">
                        <h2 class="homeTitle font-cormorant text-xl md:text-2xl font-semibold uppercase text-textColorLight mt-4">Transformez votre intérieur avec élégance</h2>
                    </div>
                    <a href="categories.html" class="button inline-block px-[20px] md:px-[30px] py-2 md:py-3 text-base md:text-lg font-semibold text-textColorLight bg-accent border-none rounded-full cursor-pointer no-underline transition-all duration-300 ease-in-out shadow-md hover:translate-y-[-3px] mt-6 md:mt-4">Découvrir</a>
                </div>
            </div>
        </section>
        <!-- SECTION A PROPOS -->
        <section id="apropos" class="about-us py-12 md:py-16 px-4 md:px-[10%] bg-background flex justify-center items-center">
            <div class="about-container flex items-center justify-between gap-8 md:gap-12 max-w-[1200px] w-full md:flex-row flex-col">
                <div class="about-image md:order-1 order-2 w-full md:w-auto">
                    <img src="images/about-us.jpg" alt="deco" class="h-auto md:h-[30rem] w-full max-w-[500px] rounded-[15px] shadow-md mx-auto">
                </div>
                <div class="about-text flex-1 text-left md:order-2 order-1">
                    <h2 class="text-[2rem] md:text-[2.5rem] text-textColor mb-4 font-frunchy text-center md:text-left">À propos de <span class="text-accent">Maison Design</span></h2>
                    <p class="text-[1.1rem] md:text-[1.4rem] text-textColor leading-relaxed mb-6 md:mb-8">
                        Chez <strong>Maison Design</strong>, nous croyons que chaque intérieur mérite une touche d'élégance et d'harmonie.  
                        Notre mission est de vous offrir des meubles et des décorations uniques, alliant style moderne et qualité artisanale.
                    </p>
                    <div class="about-values flex gap-4 md:gap-8 mb-6 md:mb-8 flex-col sm:flex-row">
                        <div class="value-box text-center flex-1 p-3 md:p-4 bg-primary rounded-[15px] shadow-md">
                            <i class='bx bxs-truck text-xl md:text-2xl text-accent mb-1 md:mb-2'></i>
                            <h3 class="text-[1.1rem] md:text-[1.3rem] text-textColor">Livraison Rapide</h3>
                            <p class="text-[0.9rem] md:text-[1.1rem] text-textColor">Des meubles livrés chez vous, en toute sécurité.</p>
                        </div>
                        <div class="value-box text-center flex-1 p-3 md:p-4 bg-primary rounded-[15px] shadow-md">
                            <i class='bx bxs-phone text-xl md:text-2xl text-accent mb-1 md:mb-2'></i>
                            <h3 class="text-[1.1rem] md:text-[1.3rem] text-textColor">Service Client</h3>
                            <p class="text-[0.9rem] md:text-[1.1rem] text-textColor">Disponible pour répondre à toutes vos questions.</p>
                        </div>
                        <div class="value-box text-center flex-1 p-3 md:p-4 bg-primary rounded-[15px] shadow-md">
                            <i class='bx bxs-wallet text-xl md:text-2xl text-accent mb-1 md:mb-2'></i>
                            <h3 class="text-[1.1rem] md:text-[1.3rem] text-textColor">Paiement Sécurisé </h3>
                            <p class="text-[0.9rem] md:text-[1.1rem] text-textColor">Par carte ou en espèces à la livraison.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- SECTION CATEGORIES -->
        <section id="categories" class="categories-section py-12 md:py-16 bg-background min-h-screen flex items-center">
            <div class="categories-container w-[95%] max-w-[1400px] mx-auto flex flex-col md:flex-row gap-8 items-center">
                <!-- Partie texte à gauche -->
                <div class="categories-text w-full md:w-[30%] min-w-[250px] order-2 md:order-1 relative">
                    <!-- Descriptions de catégories -->
                    <div class="category-description active" data-category="chaise">
                        <h3 class="text-xl sm:text-2xl mb-4 text-accent text-center md:text-left">Chaises</h3>
                        <p class="text-base sm:text-lg leading-relaxed mb-6 md:mb-8 text-textColor text-center md:text-left">Découvrez notre collection exclusive de chaises. Un mélange parfait de confort et d'élégance pour votre intérieur.</p>
                        <div class="text-center md:text-left">
                            <a href="categories.php?category=chaise" class="discover-btn inline-block px-[20px] sm:px-[30px] py-2 sm:py-3 bg-accent text-white no-underline rounded-full transition-transform duration-300 hover:translate-y-[-3px] text-sm sm:text-base">Découvrir</a>
                        </div>
                    </div>
                    <div class="category-description" data-category="lit">
                        <h3 class="text-xl sm:text-2xl mb-4 text-accent text-center md:text-left">Lits</h3>
                        <p class="text-base sm:text-lg leading-relaxed mb-6 md:mb-8 text-textColor text-center md:text-left">Une collection de lits élégants pour transformer votre chambre en un havre de paix luxueux.</p>
                        <div class="text-center md:text-left">
                            <a href="categories.php?category=lit" class="discover-btn inline-block px-[20px] sm:px-[30px] py-2 sm:py-3 bg-accent text-white no-underline rounded-full transition-transform duration-300 hover:translate-y-[-3px] text-sm sm:text-base">Découvrir</a>
                        </div>
                    </div>
                    <div class="category-description" data-category="table">
                        <h3 class="text-xl sm:text-2xl mb-4 text-accent text-center md:text-left">Tables</h3>
                        <p class="text-base sm:text-lg leading-relaxed mb-6 md:mb-8 text-textColor text-center md:text-left">Des tables au design raffiné qui sublimeront vos espaces de vie et de réception.</p>
                        <div class="text-center md:text-left">
                            <a href="categories.php?category=table" class="discover-btn inline-block px-[20px] sm:px-[30px] py-2 sm:py-3 bg-accent text-white no-underline rounded-full transition-transform duration-300 hover:translate-y-[-3px] text-sm sm:text-base">Découvrir</a>
                        </div>
                    </div>
                    <div class="category-description" data-category="canapé">
                        <h3 class="text-xl sm:text-2xl mb-4 text-accent text-center md:text-left">Canapés</h3>
                        <p class="text-base sm:text-lg leading-relaxed mb-6 md:mb-8 text-textColor text-center md:text-left">Des canapés modernes et confortables qui allient style et confort pour votre salon.</p>
                        <div class="text-center md:text-left">
                            <a href="categories.php?category=canapé" class="discover-btn inline-block px-[20px] sm:px-[30px] py-2 sm:py-3 bg-accent text-white no-underline rounded-full transition-transform duration-300 hover:translate-y-[-3px] text-sm sm:text-base">Découvrir</a>
                        </div>
                    </div>
                    <div class="category-description" data-category="armoire">
                        <h3 class="text-xl sm:text-2xl mb-4 text-accent text-center md:text-left">Armoires</h3>
                        <p class="text-base sm:text-lg leading-relaxed mb-6 md:mb-8 text-textColor text-center md:text-left">Des armoires élégantes offrant des solutions de rangement pratiques et esthétiques.</p>
                        <div class="text-center md:text-left">
                            <a href="categories.php?category=armoire" class="discover-btn inline-block px-[20px] sm:px-[30px] py-2 sm:py-3 bg-accent text-white no-underline rounded-full transition-transform duration-300 hover:translate-y-[-3px] text-sm sm:text-base">Découvrir</a>
                        </div>
                    </div>
                </div>
                
                <!-- Partie slider à droite -->
                <div class="categories-slider relative w-full md:w-[70%] overflow-hidden order-1 md:order-2">
                    <button class="slider-arrow prev-arrow absolute top-1/2 -translate-y-1/2 w-8 h-8 md:w-10 md:h-10 rounded-full bg-accent/70 border-none text-white cursor-pointer flex items-center justify-center z-10 transition-all duration-300 hover:bg-accent left-2" aria-label="Précédent"> 
                        <i class='bx bx-chevron-left text-xl'></i>
                    </button>
                    <button class="slider-arrow next-arrow absolute top-1/2 -translate-y-1/2 w-8 h-8 md:w-10 md:h-10 rounded-full bg-accent/70 border-none text-white cursor-pointer flex items-center justify-center z-10 transition-all duration-300 hover:bg-accent right-2" aria-label="Suivant">
                        <i class='bx bx-chevron-right text-xl'></i>
                    </button>
                    
                    <!-- Conteneur du slider avec plusieurs slides visibles -->
                    <div class="slider-container overflow-visible relative w-full px-4">
                        <div class="slides-wrapper flex gap-4">
                            <div class="slide flex-shrink-0 w-full sm:w-[calc(50%-8px)] md:w-[calc(33.333%-16px)] min-w-[200px]" data-category="chaise">
                                <div class="relative h-full rounded-xl overflow-hidden">
                                    <img src="images/chaise.jpeg" alt="Chaise design" class="w-full h-[300px] md:h-[400px] object-cover">
                                    <div class="absolute bottom-0 left-0 w-full p-4 bg-gradient-to-t from-black/70 to-transparent">
                                        <span class="text-white text-xl font-medium">Chaise</span>
                                    </div>
                                </div>
                            </div>
                            <div class="slide flex-shrink-0 w-full sm:w-[calc(50%-8px)] md:w-[calc(33.333%-16px)] min-w-[200px]" data-category="lit">
                                <div class="relative h-full rounded-xl overflow-hidden">
                                    <img src="images/lit.jpeg" alt="Lit design" class="w-full h-[300px] md:h-[400px] object-cover">
                                    <div class="absolute bottom-0 left-0 w-full p-4 bg-gradient-to-t from-black/70 to-transparent">
                                        <span class="text-white text-xl font-medium">Lit</span>
                                    </div>
                                </div>
                            </div>
                            <div class="slide flex-shrink-0 w-full sm:w-[calc(50%-8px)] md:w-[calc(33.333%-16px)] min-w-[200px]" data-category="table">
                                <div class="relative h-full rounded-xl overflow-hidden">
                                    <img src="images/table.jpeg" alt="Table design" class="w-full h-[300px] md:h-[400px] object-cover">
                                    <div class="absolute bottom-0 left-0 w-full p-4 bg-gradient-to-t from-black/70 to-transparent">
                                        <span class="text-white text-xl font-medium">Table</span>
                                    </div>
                                </div>
                            </div>
                            <div class="slide flex-shrink-0 w-full sm:w-[calc(50%-8px)] md:w-[calc(33.333%-16px)] min-w-[200px]" data-category="canapé">
                                <div class="relative h-full rounded-xl overflow-hidden">
                                    <img src="images/canape.jpeg" alt="Canapé design" class="w-full h-[300px] md:h-[400px] object-cover">
                                    <div class="absolute bottom-0 left-0 w-full p-4 bg-gradient-to-t from-black/70 to-transparent">
                                        <span class="text-white text-xl font-medium">Canapé</span>
                                    </div>
                                </div>
                            </div>
                            <div class="slide flex-shrink-0 w-full sm:w-[calc(50%-8px)] md:w-[calc(33.333%-16px)] min-w-[200px]" data-category="armoire">
                                <div class="relative h-full rounded-xl overflow-hidden">
                                    <img src="images/armoire.jpeg" alt="Armoire design" class="w-full h-[300px] md:h-[400px] object-cover">
                                    <div class="absolute bottom-0 left-0 w-full p-4 bg-gradient-to-t from-black/70 to-transparent">
                                        <span class="text-white text-xl font-medium">Armoire</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <!--FOOTER-->
    <?php include 'footer.php'; ?>

    <script src="js/script.js"></script>
</body>
</html>
