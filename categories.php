<?php
// Démarrer la session
session_start();

// Connexion à la base de données
require_once 'php/db.php';

// Récupérer la catégorie depuis l'URL
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : 'all';

try {
    // Requête pour récupérer toutes les catégories
    $stmtCategories = $pdo->query("SELECT * FROM categorie ORDER BY NomCategorie");
    $categories = $stmtCategories->fetchAll(PDO::FETCH_ASSOC);
    
    // Si c'est une requête AJAX, retourner les données JSON
    if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
        header('Content-Type: application/json');
        
        if ($selectedCategory === 'all') {
            $stmtProduits = $pdo->query("
                SELECT p.IdProduit, p.NomProduit, p.Prix, p.Stock, c.NomCategorie as categorie, i.URL as image
                FROM produit p
                LEFT JOIN categorie c ON p.IdCat = c.IdCategorie
                LEFT JOIN (
                    SELECT IdProduit, MIN(URL) as URL
                    FROM imageprod
                    GROUP BY IdProduit
                ) i ON p.IdProduit = i.IdProduit
                ORDER BY p.IdProduit DESC
            ");
        } else {
            $stmtProduits = $pdo->prepare("
                SELECT p.IdProduit, p.NomProduit, p.Prix, p.Stock, c.NomCategorie as categorie, i.URL as image
                FROM produit p
                LEFT JOIN categorie c ON p.IdCat = c.IdCategorie
                LEFT JOIN (
                    SELECT IdProduit, MIN(URL) as URL
                    FROM imageprod
                    GROUP BY IdProduit
                ) i ON p.IdProduit = i.IdProduit
                WHERE LOWER(c.NomCategorie) = LOWER(?)
                ORDER BY p.IdProduit DESC
            ");
            $stmtProduits->execute([$selectedCategory]);
        }
        
        $produits = $stmtProduits->fetchAll(PDO::FETCH_ASSOC);
        
        // Traiter les images
        foreach ($produits as &$produit) {
            if (!empty($produit['image'])) {
                if (strpos($produit['image'], 'images/') !== 0) {
                    $produit['image'] = 'images/' . basename($produit['image']);
                }
            } else {
                $produit['image'] = 'images/placeholder.jpeg';
            }
        }
        
        echo json_encode([
            'success' => true, 
            'products' => $produits,
            'categories' => $categories
        ]);
        exit;
    }
    
    // Pour le chargement initial de la page
    if ($selectedCategory === 'all') {
        $stmtProduits = $pdo->query("
            SELECT p.IdProduit, p.NomProduit, p.Prix, p.Stock, c.NomCategorie as categorie, i.URL as image
            FROM produit p
            LEFT JOIN categorie c ON p.IdCat = c.IdCategorie
            LEFT JOIN (
                SELECT IdProduit, MIN(URL) as URL
                FROM imageprod
                GROUP BY IdProduit
            ) i ON p.IdProduit = i.IdProduit
            ORDER BY p.IdProduit DESC
        ");
    } else {
        $stmtProduits = $pdo->prepare("
            SELECT p.IdProduit, p.NomProduit, p.Prix, p.Stock, c.NomCategorie as categorie, i.URL as image
            FROM produit p
            LEFT JOIN categorie c ON p.IdCat = c.IdCategorie
            LEFT JOIN (
                SELECT IdProduit, MIN(URL) as URL
                FROM imageprod
                GROUP BY IdProduit
            ) i ON p.IdProduit = i.IdProduit
            WHERE LOWER(c.NomCategorie) = LOWER(?)
            ORDER BY p.IdProduit DESC
        ");
        $stmtProduits->execute([$selectedCategory]);
    }
    
    $produits = $stmtProduits->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Gérer l'erreur
    $error = "Une erreur est survenue lors du chargement des produits: " . $e->getMessage();
    
    // Si c'est une requête AJAX, retourner l'erreur en JSON
    if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $error]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catégories - Maison Design</title>
    <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwind.config.js"></script>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="font-cormorant bg-background m-0 p-0 box-border">
    <!-- HEADER -->
    <?php include 'header.php'; ?>

    <main class="min-h-screen pt-28 pb-16 px-4 md:px-[10%] bg-background">
        <div class="max-w-[1400px] mx-auto">
            <h1 class="text-3xl md:text-4xl font-frunchy text-center mb-8 text-textColor">Nos Catégories</h1>

            <!-- Filtres de catégories -->
            <div class="mb-10">
                <div class="flex flex-wrap justify-center gap-3 md:gap-4" id="category-filters">
                    <a href="categories.php" 
                       class="category-filter <?php echo $selectedCategory === 'all' ? 'bg-accent text-white' : 'bg-primary text-textColor hover:bg-accent hover:text-white'; ?> px-4 py-2 rounded-full transition-all duration-300" 
                       data-category="all">
                        Tous
                    </a>
                    <?php foreach ($categories as $category): ?>
                    <a href="categories.php?category=<?php echo urlencode(strtolower($category['NomCategorie'])); ?>" 
                       class="category-filter <?php echo strtolower($selectedCategory) === strtolower($category['NomCategorie']) ? 'bg-accent text-white' : 'bg-primary text-textColor hover:bg-accent hover:text-white'; ?> px-4 py-2 rounded-full transition-all duration-300" 
                       data-category="<?php echo strtolower($category['NomCategorie']); ?>">
                        <?php echo htmlspecialchars($category['NomCategorie']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Titre de la section des produits -->
            <h2 class="text-2xl md:text-3xl font-medium text-textColor mb-6" id="products-title">
                <?php 
                if ($selectedCategory === 'all') {
                    echo 'Tous nos produits';
                } else {
                    echo 'Nos ' . htmlspecialchars($selectedCategory);
                }
                ?>
            </h2>

            <!-- Message de chargement -->
            <div id="loading" class="text-center py-12 hidden">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-accent"></div>
                <p class="mt-2 text-textColor">Chargement des produits...</p>
            </div>

            <?php if (isset($error)): ?>
            <!-- Message d'erreur -->
            <div id="error-message" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <span id="error-text"><?php echo htmlspecialchars($error); ?></span>
            </div>
            <?php else: ?>
            <div id="error-message" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 hidden">
                <span id="error-text"></span>
            </div>
            <?php endif; ?>

            <!-- Grille de produits -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6" id="products-container">
                <?php if (empty($produits)): ?>
                <div class="col-span-full text-center py-12" id="no-products">
                    <p class="text-xl text-textColor/70">Aucun produit trouvé dans cette catégorie.</p>
                </div>
                <?php else: ?>
                    <?php foreach ($produits as $produit): ?>
                    <div class="product-item bg-white rounded-xl overflow-hidden shadow-md transition-transform hover:-translate-y-1" data-category="<?php echo strtolower($produit['categorie'] ?? ''); ?>">
                        <!-- Image du produit (cliquable) -->
                        <a href="product.php?id=<?php echo $produit['IdProduit']; ?>" class="block">
                            <div class="product-image h-48 overflow-hidden relative group">
                                <img src="<?php echo htmlspecialchars($produit['image'] ?? 'images/placeholder.jpeg'); ?>" 
                                     alt="<?php echo htmlspecialchars($produit['NomProduit']); ?>" 
                                     class="w-full h-full object-cover transition-transform group-hover:scale-105"
                                     onerror="this.src='images/placeholder.jpeg'">
                                <!-- Overlay au survol -->
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
                                    <span class="text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300 font-medium">
                                        Voir détails
                                    </span>
                                </div>
                            </div>
                        </a>
                        
                        <!-- Informations du produit (simplifiées) -->
                        <div class="product-info p-4">
                            <h3 class="text-lg font-medium text-textColor mb-1"><?php echo htmlspecialchars($produit['NomProduit']); ?></h3>
                            <p class="text-accent font-bold text-xl"><?php echo number_format($produit['Prix'], 2, ',', ' '); ?> DA</p>
                            
                            <!-- Disponibilité (icône seulement) -->
                            <?php if ($produit['Stock'] > 0): ?>
                            <div class="flex items-center gap-1 mt-2 text-green-600">
                                <i class='bx bx-check'></i>
                                <span class="text-sm">Disponible</span>
                            </div>
                            <?php else: ?>
                            <div class="flex items-center gap-1 mt-2 text-red-600">
                                <i class='bx bx-x'></i>
                                <span class="text-sm">Indisponible</span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Boutons d'action -->
                        <div class="p-4 pt-0 flex flex-col gap-2">
                            <!-- Bouton Voir détails -->
                            <a href="product.php?id=<?php echo $produit['IdProduit']; ?>" 
                               class="w-full px-3 py-2 bg-primary text-textColor rounded-full hover:bg-accent hover:text-white transition-colors text-sm flex items-center justify-center gap-2 font-medium">
                                <i class='bx bx-show'></i> Voir détails
                            </a>
                            
                            <!-- Boutons Ajouter au panier et Favoris -->
                            <div class="flex gap-2">
                                <?php if ($produit['Stock'] > 0): ?>
                                <a href="php/cart_actions.php?action=ajouter&produitId=<?php echo $produit['IdProduit']; ?>&quantite=1" 
                                   class="flex-1 px-3 py-2 bg-accent text-white rounded-full hover:bg-accent/80 transition-colors text-sm flex items-center justify-center gap-1">
                                    <i class='bx bx-cart-add'></i> Ajouter
                                </a>
                                <?php else: ?>
                                <button disabled 
                                        class="flex-1 px-3 py-2 bg-gray-300 text-gray-500 rounded-full cursor-not-allowed text-sm flex items-center justify-center gap-1">
                                    <i class='bx bx-cart-add'></i> Ajouter
                                </button>
                                <?php endif; ?>
                                
                                <!-- Bouton Favoris -->
                                <button class="favorite-btn px-3 py-2 bg-gray-100 text-gray-600 rounded-full hover:bg-red-100 hover:text-red-600 transition-colors text-sm flex items-center justify-center"
                                        title="Ajouter aux favoris"
                                        data-product-id="<?php echo $produit['IdProduit']; ?>">
                                    <i class='bx bx-heart'></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <!-- Message "Aucun produit" caché par défaut -->
                    <div id="no-products" class="col-span-full text-center py-12 hidden">
                        <p class="text-xl text-textColor/70">Aucun produit trouvé dans cette catégorie.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <?php include 'footer.php'; ?>

    <!-- Script pour le menu mobile et la recherche -->
    <script src="js/script.js"></script>
    <script>
        // Données initiales passées du PHP au JavaScript
        window.initialData = {
            selectedCategory: <?php echo json_encode($selectedCategory); ?>,
            categories: <?php echo json_encode($categories); ?>
        };
        
        // Données de session pour JavaScript
        window.sessionData = {
            isLoggedIn: <?php echo isset($_SESSION['client_id']) ? 'true' : 'false'; ?>,
            clientId: <?php echo isset($_SESSION['client_id']) ? $_SESSION['client_id'] : 'null'; ?>
        };
        
        console.log('Données de session:', window.sessionData);

        // Attacher les événements favoris aux produits initiaux
        document.addEventListener('DOMContentLoaded', function() {
            const initialFavoriteButtons = document.querySelectorAll('.favorite-btn');
            initialFavoriteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const productId = parseInt(button.getAttribute('data-product-id'));
                    
                    if (!window.sessionData.isLoggedIn) {
                        alert('Veuillez vous connecter pour ajouter des produits aux favoris');
                        window.location.href = 'connexion.html';
                        return;
                    }
                    
                    const icon = button.querySelector('i');
                    button.disabled = true;
                    
                    fetch('php/favorites_actions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=toggle&produitId=${productId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (data.action === 'added') {
                                icon.classList.remove('bx-heart');
                                icon.classList.add('bxs-heart');
                                button.classList.remove('bg-gray-100', 'text-gray-600');
                                button.classList.add('bg-red-100', 'text-red-600');
                                button.title = 'Retirer des favoris';
                            } else {
                                icon.classList.remove('bxs-heart');
                                icon.classList.add('bx-heart');
                                button.classList.remove('bg-red-100', 'text-red-600');
                                button.classList.add('bg-gray-100', 'text-gray-600');
                                button.title = 'Ajouter aux favoris';
                            }
                        } else {
                            alert('Erreur: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Une erreur est survenue');
                    })
                    .finally(() => {
                        button.disabled = false;
                    });
                });
            });
        });
    </script>
    <script src="js/categories-adapted.js"></script>
</body>
</html>
