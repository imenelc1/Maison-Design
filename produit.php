<?php
// Fichier produit.php corrigé - Version sans JavaScript inline
session_start();

// Connexion à la base de données
require_once 'php/db.php';

// Récupérer l'ID du produit depuis l'URL
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($productId <= 0) {
    header('Location: categories.php');
    exit;
}

try {
    // Récupérer les détails du produit
    $stmtProduct = $pdo->prepare("
        SELECT p.*, c.NomCategorie as categorie
        FROM produit p
        LEFT JOIN categorie c ON p.IdCat = c.IdCategorie
        WHERE p.IdProduit = ?
    ");
    $stmtProduct->execute([$productId]);
    $product = $stmtProduct->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        header('Location: categories.php');
        exit;
    }
    
    // Récupérer toutes les images du produit
    $stmtImages = $pdo->prepare("
        SELECT URL 
        FROM imageprod 
        WHERE IdProduit = ?
        ORDER BY IdImage
    ");
    $stmtImages->execute([$productId]);
    $images = $stmtImages->fetchAll(PDO::FETCH_COLUMN);
    
    // Traiter les images
    $processedImages = [];
    foreach ($images as $image) {
        if (!empty($image)) {
            if (strpos($image, 'images/') !== 0) {
                $processedImages[] = 'images/' . basename($image);
            } else {
                $processedImages[] = $image;
            }
        }
    }
    
    // Si aucune image, utiliser placeholder
    if (empty($processedImages)) {
        $processedImages[] = 'images/placeholder.jpeg';
    }
    
    // Récupérer des produits similaires (même catégorie)
    $stmtSimilar = $pdo->prepare("
        SELECT p.*, c.NomCategorie as categorie, i.URL as image
        FROM produit p
        LEFT JOIN categorie c ON p.IdCat = c.IdCategorie
        LEFT JOIN (
            SELECT IdProduit, MIN(URL) as URL
            FROM imageprod
            GROUP BY IdProduit
        ) i ON p.IdProduit = i.IdProduit
        WHERE p.IdCat = ? AND p.IdProduit != ?
        ORDER BY RAND()
        LIMIT 4
    ");
    $stmtSimilar->execute([$product['IdCat'], $productId]);
    $similarProducts = $stmtSimilar->fetchAll(PDO::FETCH_ASSOC);
    
    // Traiter les images des produits similaires
    foreach ($similarProducts as &$similarProduct) {
        if (!empty($similarProduct['image'])) {
            if (strpos($similarProduct['image'], 'images/') !== 0) {
                $similarProduct['image'] = 'images/' . basename($similarProduct['image']);
            }
        } else {
            $similarProduct['image'] = 'images/placeholder.jpeg';
        }
    }
    
    // Vérifier si le produit est dans les favoris de l'utilisateur connecté
    $isFavorite = false;
    if (isset($_SESSION['user_id'])) {
        $stmtFavorite = $pdo->prepare("SELECT COUNT(*) FROM favoris WHERE IdClient = ? AND IdProduit = ?");
        $stmtFavorite->execute([$_SESSION['user_id'], $productId]);
        $isFavorite = $stmtFavorite->fetchColumn() > 0;
    }
    
} catch (PDOException $e) {
    $error = "Une erreur est survenue lors du chargement du produit: " . $e->getMessage();
    error_log("Erreur produit.php: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['NomProduit'] ?? 'Produit'); ?> - Maison Design</title>
    <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
        <script src="tailwind.config.js"></script>

    <link rel="stylesheet" href="css/style.css">
    <script>
        // Variables globales pour JavaScript
        window.productData = {
            id: <?php echo $product['IdProduit']; ?>,
            stock: <?php echo $product['Stock']; ?>,
            isFavorite: <?php echo $isFavorite ? 'true' : 'false'; ?>
        };
        
        window.sessionData = {
            isLoggedIn: <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>,
            clientId: <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>
        };
    </script>
</head>
<body class="font-cormorant bg-background m-0 p-0 box-border">
    <!-- HEADER -->
    <?php include 'header.php'; ?>

    <main class="min-h-screen pt-28 pb-16 px-4 md:px-[10%] bg-background">
        <div class="max-w-[1400px] mx-auto">
            
            <?php if (isset($error)): ?>
            <!-- Message d'erreur -->
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php else: ?>
            
            <!-- Breadcrumb -->
            <nav class="mb-8">
                <ol class="flex items-center space-x-2 text-sm text-gray-600">
                    <li><a href="index.php" class="hover:text-accent">Accueil</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a href="categories.php" class="hover:text-accent">Catégories</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a href="categories.php?category=<?php echo urlencode(strtolower($product['categorie'])); ?>" class="hover:text-accent"><?php echo htmlspecialchars($product['categorie']); ?></a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li class="text-textColor font-medium"><?php echo htmlspecialchars($product['NomProduit']); ?></li>
                </ol>
            </nav>

            <!-- Détails du produit -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-16">
                
                <!-- Galerie d'images -->
                <div class="space-y-4">
                    <!-- Image principale -->
                    <div class="aspect-square overflow-hidden rounded-xl bg-white shadow-lg">
                        <img id="main-product-image" 
                             src="<?php echo htmlspecialchars($processedImages[0]); ?>" 
                             alt="<?php echo htmlspecialchars($product['NomProduit']); ?>" 
                             class="w-full h-full object-cover"
                             onerror="this.src='images/placeholder.jpeg'">
                    </div>
                    
                    <!-- Miniatures -->
                    <?php if (count($processedImages) > 1): ?>
                    <div class="flex gap-2 overflow-x-auto">
                        <?php foreach ($processedImages as $index => $image): ?>
                        <div class="thumbnail-item flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden border-2 border-transparent hover:border-accent transition-colors <?php echo $index === 0 ? 'border-accent' : ''; ?> cursor-pointer">
                            <img src="<?php echo htmlspecialchars($image); ?>" 
                                 alt="<?php echo htmlspecialchars($product['NomProduit']); ?>" 
                                 class="w-full h-full object-cover"
                                 data-full-image="<?php echo htmlspecialchars($image); ?>"
                                 onerror="this.src='images/placeholder.jpeg'">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Informations du produit -->
                <div class="space-y-6">
                    <div>
                        <h1 class="text-3xl md:text-4xl font-bold text-textColor mb-2"><?php echo htmlspecialchars($product['NomProduit']); ?></h1>
                        <p class="text-gray-600 text-lg">Catégorie: <span class="text-accent font-medium"><?php echo htmlspecialchars($product['categorie']); ?></span></p>
                    </div>

                    <!-- Prix -->
                    <div class="bg-primary/20 rounded-xl p-6">
                        <p class="text-3xl md:text-4xl font-bold text-accent"><?php echo number_format($product['Prix'], 2, ',', ' '); ?> DA</p>
                    </div>

                    <!-- Stock -->
                    <div class="flex items-center gap-4">
                        <?php if ($product['Stock'] > 0): ?>
                        <span class="inline-flex items-center gap-2 bg-green-100 text-green-800 px-4 py-2 rounded-full">
                            <i class='bx bx-check-circle'></i>
                            En stock (<?php echo $product['Stock']; ?> disponible<?php echo $product['Stock'] > 1 ? 's' : ''; ?>)
                        </span>
                        <?php else: ?>
                        <span class="inline-flex items-center gap-2 bg-red-100 text-red-800 px-4 py-2 rounded-full">
                            <i class='bx bx-x-circle'></i>
                            Rupture de stock
                        </span>
                        <?php endif; ?>
                    </div>

                    <!-- Description -->
                    <div>
                        <h3 class="text-xl font-semibold text-textColor mb-3">Description</h3>
                        <p class="text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($product['Description'])); ?></p>
                    </div>

                    <!-- Quantité et actions -->
                    <div class="space-y-4">
                        <?php if ($product['Stock'] > 0): ?>
                        <div class="flex items-center gap-4">
                            <label for="quantity" class="text-lg font-medium text-textColor">Quantité:</label>
                            <div class="flex items-center border border-gray-300 rounded-lg">
                                <button type="button" id="decrease-quantity" class="px-3 py-2 hover:bg-gray-100 transition-colors">
                                    <i class='bx bx-minus'></i>
                                </button>
                                <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['Stock']; ?>" 
                                       class="w-16 text-center border-0 focus:outline-none">
                                <button type="button" id="increase-quantity" class="px-3 py-2 hover:bg-gray-100 transition-colors">
                                    <i class='bx bx-plus'></i>
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Boutons d'action -->
                        <div class="flex flex-col sm:flex-row gap-4">
                            <?php if ($product['Stock'] > 0): ?>
                            <button id="add-to-cart-btn" 
                                    class="flex-1 px-6 py-3 bg-accent text-white rounded-full hover:bg-accent/80 transition-colors text-lg font-medium flex items-center justify-center gap-2">
                                <i class='bx bx-cart-add'></i> Ajouter au panier
                            </button>
                            <?php else: ?>
                            <button disabled 
                                    class="flex-1 px-6 py-3 bg-gray-300 text-gray-500 rounded-full cursor-not-allowed text-lg font-medium flex items-center justify-center gap-2">
                                <i class='bx bx-cart-add'></i> Produit indisponible
                            </button>
                            <?php endif; ?>
                            
                            <button id="favorite-btn" 
                                    class="px-6 py-3 <?php echo $isFavorite ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600'; ?> rounded-full hover:bg-red-100 hover:text-red-600 transition-colors text-lg font-medium flex items-center justify-center gap-2"
                                    title="<?php echo $isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris'; ?>"
                                    data-product-id="<?php echo $product['IdProduit']; ?>">
                                <i class="<?php echo $isFavorite ? 'fas fa-heart text-red-600' : 'far fa-heart'; ?>"></i> Favoris
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Produits similaires -->
            <?php if (!empty($similarProducts)): ?>
            <section class="mb-16">
                <h2 class="text-2xl md:text-3xl font-bold text-textColor mb-8 text-center">Produits similaires</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <?php foreach ($similarProducts as $similarProduct): ?>
                    <div class="bg-white rounded-xl overflow-hidden shadow-md transition-transform hover:-translate-y-1">
                        <a href="produit.php?id=<?php echo $similarProduct['IdProduit']; ?>" class="block">
                            <div class="h-48 overflow-hidden">
                                <img src="<?php echo htmlspecialchars($similarProduct['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($similarProduct['NomProduit']); ?>" 
                                     class="w-full h-full object-cover transition-transform hover:scale-105"
                                     onerror="this.src='images/placeholder.jpeg'">
                            </div>
                            <div class="p-4">
                                <h3 class="text-lg font-medium text-textColor mb-1"><?php echo htmlspecialchars($similarProduct['NomProduit']); ?></h3>
                                <p class="text-accent font-bold"><?php echo number_format($similarProduct['Prix'], 2, ',', ' '); ?> DA</p>
                                <?php if ($similarProduct['Stock'] > 0): ?>
                                <span class="inline-block mt-2 bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">En stock</span>
                                <?php else: ?>
                                <span class="inline-block mt-2 bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">Rupture de stock</span>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <?php endif; ?>
        </div>
    </main>

    <!-- FOOTER -->
    <?php include 'footer.php'; ?>

    <!-- Scripts -->
    <script src="js/script.js"></script>
    <script src="js/product.js"></script>
</body>
</html>
