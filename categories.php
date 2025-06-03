<?php
// Version corrigée pour résoudre le problème d'affichage des doublons
session_start();

// Connexion à la base de données
require_once 'php/db.php';

// Récupérer la catégorie depuis l'URL
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : 'all';

try {
    // 1. Récupérer toutes les catégories
    $stmtCategories = $pdo->query("SELECT * FROM categorie ORDER BY NomCategorie");
    $categories = $stmtCategories->fetchAll(PDO::FETCH_ASSOC);
    
    // 2. Récupérer les favoris de l'utilisateur connecté
    $userFavorites = [];
    if (isset($_SESSION['user_id'])) {
        try {
            $stmtFavorites = $pdo->prepare("SELECT IdProduit FROM favoris WHERE IdClient = ?");
            $stmtFavorites->execute([$_SESSION['user_id']]);
            $userFavorites = $stmtFavorites->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            $userFavorites = [];
        }
    }
    
    // 3. Si c'est une requête AJAX, traiter et retourner JSON
    if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
        header('Content-Type: application/json');
        
        if ($selectedCategory === 'all') {
            $stmtProduits = $pdo->query("
                SELECT p.IdProduit, p.NomProduit, p.Prix, p.Stock, p.Description, c.NomCategorie as categorie
                FROM produit p
                LEFT JOIN categorie c ON p.IdCat = c.IdCategorie
                ORDER BY p.IdProduit DESC
            ");
        } else {
            $stmtProduits = $pdo->prepare("
                SELECT p.IdProduit, p.NomProduit, p.Prix, p.Stock, p.Description, c.NomCategorie as categorie
                FROM produit p
                LEFT JOIN categorie c ON p.IdCat = c.IdCategorie
                WHERE LOWER(c.NomCategorie) = LOWER(?)
                ORDER BY p.IdProduit DESC
            ");
            $stmtProduits->execute([$selectedCategory]);
        }
        
        $produits = $stmtProduits->fetchAll(PDO::FETCH_ASSOC);
        
        // CORRECTION: Vérifier l'unicité des produits
        $uniqueProduits = [];
        $seenIds = [];
        
        foreach ($produits as $produit) {
            if (!in_array($produit['IdProduit'], $seenIds)) {
                $seenIds[] = $produit['IdProduit'];
                
                // Récupérer l'image du produit
                $stmtImage = $pdo->prepare("SELECT URL FROM imageprod WHERE IdProduit = ? LIMIT 1");
                $stmtImage->execute([$produit['IdProduit']]);
                $image = $stmtImage->fetchColumn();
                
                if ($image) {
                    $produit['image'] = strpos($image, 'images/') === 0 ? $image : 'images/' . basename($image);
                } else {
                    $produit['image'] = 'images/placeholder.jpeg';
                }
                
                // Marquer si c'est un favori
                $produit['isFavorite'] = in_array($produit['IdProduit'], $userFavorites);
                
                $uniqueProduits[] = $produit;
            }
        }
        
        echo json_encode([
            'success' => true, 
            'products' => $uniqueProduits,
            'categories' => $categories,
            'userFavorites' => $userFavorites
        ]);
        exit;
    }
    
    // 4. Pour le chargement initial de la page
    if ($selectedCategory === 'all') {
        $stmtProduits = $pdo->query("
            SELECT p.IdProduit, p.NomProduit, p.Prix, p.Stock, p.Description, c.NomCategorie as categorie
            FROM produit p
            LEFT JOIN categorie c ON p.IdCat = c.IdCategorie
            ORDER BY p.IdProduit DESC
        ");
    } else {
        $stmtProduits = $pdo->prepare("
            SELECT p.IdProduit, p.NomProduit, p.Prix, p.Stock, p.Description, c.NomCategorie as categorie
            FROM produit p
            LEFT JOIN categorie c ON p.IdCat = c.IdCategorie
            WHERE LOWER(c.NomCategorie) = LOWER(?)
            ORDER BY p.IdProduit DESC
        ");
        $stmtProduits->execute([$selectedCategory]);
    }
    
    $produitsRaw = $stmtProduits->fetchAll(PDO::FETCH_ASSOC);
    
    // CORRECTION: Assurer l'unicité des produits pour l'affichage initial
    $produits = [];
    $seenIds = [];
    
    foreach ($produitsRaw as $produit) {
        if (!in_array($produit['IdProduit'], $seenIds)) {
            $seenIds[] = $produit['IdProduit'];
            
            // Récupérer l'image du produit
            $stmtImage = $pdo->prepare("SELECT URL FROM imageprod WHERE IdProduit = ? LIMIT 1");
            $stmtImage->execute([$produit['IdProduit']]);
            $image = $stmtImage->fetchColumn();
            
            if ($image) {
                $produit['image'] = strpos($image, 'images/') === 0 ? $image : 'images/' . basename($image);
            } else {
                $produit['image'] = 'images/placeholder.jpeg';
            }
            
            $produits[] = $produit;
        }
    }
    
    // Debug
    error_log("Produits bruts récupérés: " . count($produitsRaw));
    error_log("Produits uniques après filtrage: " . count($produits));
    error_log("IDs des produits uniques: " . implode(', ', array_column($produits, 'IdProduit')));
    
} catch (PDOException $e) {
    $error = "Une erreur est survenue lors du chargement des produits: " . $e->getMessage();
    error_log("Erreur SQL: " . $e->getMessage());
    
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
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#EEE7DE',
                        'accent': '#8E9675',
                        'background': '#F5F5F5',
                        'textColor': '#3D3D3D'
                    },
                    fontFamily: {
                        'cormorant': ['Cormorant Garamond', 'serif'],
                        'frunchy': ['Frunchy', 'serif']
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="css/style.css">
    <script>
        // Variables globales pour JavaScript
        window.sessionData = {
            isLoggedIn: <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>,
            clientId: <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>
        };
        window.userFavorites = <?php echo json_encode($userFavorites); ?>;
        window.initialData = {
            selectedCategory: <?php echo json_encode($selectedCategory); ?>,
            categories: <?php echo json_encode($categories); ?>
        };
        
        console.log('DEBUG - Session:', window.sessionData);
        console.log('DEBUG - Favoris:', window.userFavorites);
        console.log('DEBUG - Nombre de produits PHP:', <?php echo count($produits); ?>);
        console.log('DEBUG - IDs des produits:', <?php echo json_encode(array_column($produits, 'IdProduit')); ?>);
        console.log('DEBUG - Noms des produits:', <?php echo json_encode(array_column($produits, 'NomProduit')); ?>);
    </script>
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
                    <button type="button"
                            class="category-filter <?php echo $selectedCategory === 'all' ? 'bg-accent text-white' : 'bg-primary text-textColor hover:bg-accent hover:text-white'; ?> px-4 py-2 rounded-full transition-all duration-300" 
                            data-category="all">
                        Tous
                    </button>
                    <?php foreach ($categories as $category): ?>
                    <button type="button"
                            class="category-filter <?php echo strtolower($selectedCategory) === strtolower($category['NomCategorie']) ? 'bg-accent text-white' : 'bg-primary text-textColor hover:bg-accent hover:text-white'; ?> px-4 py-2 rounded-full transition-all duration-300" 
                            data-category="<?php echo strtolower($category['NomCategorie']); ?>">
                        <?php echo htmlspecialchars($category['NomCategorie']); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Titre de la section des produits -->
            <h2 class="text-2xl md:text-3xl font-medium text-textColor mb-6" id="products-title">
                <?php 
                if ($selectedCategory === 'all') {
                    echo 'Tous nos produits (' . count($produits) . ')';
                } else {
                    echo 'Nos ' . htmlspecialchars($selectedCategory) . ' (' . count($produits) . ')';
                }
                ?>
            </h2>

            <!-- Message de chargement -->
            <div id="loading" class="text-center py-12 hidden">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-accent"></div>
                <p class="mt-2 text-textColor">Chargement des produits...</p>
            </div>

            <?php if (isset($error)): ?>
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
                    <?php 
                    // CORRECTION: S'assurer qu'on itère sur les produits uniques
                    foreach ($produits as $index => $produit): 
                    ?>
                    <div class="product-item bg-white rounded-xl overflow-hidden shadow-md transition-transform hover:-translate-y-1" 
                         data-product-id="<?php echo $produit['IdProduit']; ?>" 
                         data-index="<?php echo $index; ?>">
                        
                        <!-- Image du produit -->
                        <a href="produit.php?id=<?php echo $produit['IdProduit']; ?>" class="block">
                            <div class="product-image h-48 overflow-hidden relative group">
                                <img src="<?php echo htmlspecialchars($produit['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($produit['NomProduit']); ?>" 
                                     class="w-full h-full object-cover transition-transform group-hover:scale-105"
                                     onerror="this.src='images/placeholder.jpeg'">
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
                                    <span class="text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300 font-medium">
                                        Voir détails
                                    </span>
                                </div>
                            </div>
                        </a>
                        
                        <!-- Informations du produit -->
                        <div class="product-info p-4">
                            <h3 class="text-lg font-medium text-textColor mb-1"><?php echo htmlspecialchars($produit['NomProduit']); ?></h3>
                            <p class="text-accent font-bold text-xl"><?php echo number_format($produit['Prix'], 2, ',', ' '); ?> DA</p>
                            
                            <!-- Disponibilité -->
                            <?php if ($produit['Stock'] > 0): ?>
                            <div class="flex items-center gap-1 mt-2 text-green-600">
                                <i class='bx bx-check'></i>
                                <span class="text-sm">Disponible (<?php echo $produit['Stock']; ?>)</span>
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
                            <a href="produit.php?id=<?php echo $produit['IdProduit']; ?>" 
                               class="w-full px-3 py-2 bg-primary text-textColor rounded-full hover:bg-accent hover:text-white transition-colors text-sm flex items-center justify-center gap-2 font-medium">
                                Voir détails
                            </a>
                            
                            <!-- Boutons Ajouter au panier et Favoris -->
                            <div class="flex gap-2">
                                <?php if ($produit['Stock'] > 0): ?>
                                <button onclick="addToCart(<?php echo $produit['IdProduit']; ?>)"
                                        class="flex-1 px-3 py-2 bg-accent text-white rounded-full hover:bg-accent/80 transition-colors text-sm flex items-center justify-center gap-1">
                                    <i class='bx bx-cart-add'></i> Ajouter
                                </button>
                                <?php else: ?>
                                <button disabled 
                                        class="flex-1 px-3 py-2 bg-gray-300 text-gray-500 rounded-full cursor-not-allowed text-sm flex items-center justify-center gap-1">
                                    <i class='bx bx-cart-add'></i> Ajouter
                                </button>
                                <?php endif; ?>
                                
                                <!-- Bouton Favoris -->
                                <?php $isFavorite = in_array($produit['IdProduit'], $userFavorites); ?>
                                <button class="favorite-btn px-3 py-2 <?php echo $isFavorite ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600'; ?> rounded-full hover:bg-red-100 hover:text-red-600 transition-colors text-sm flex items-center justify-center"
                                        title="<?php echo $isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris'; ?>"
                                        data-product-id="<?php echo $produit['IdProduit']; ?>">
                                    <i class='<?php echo $isFavorite ? 'fas fa-heart text-red-600' : 'far fa-heart'; ?>'></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <?php include 'footer.php'; ?>

    <!-- Scripts -->
    <script src="js/script.js"></script>
    <script src="js/categories.js"></script>
    <script src="js/categories-adapter.js"></script>
   
</body>
</html>
