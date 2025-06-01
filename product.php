<?php
// Démarrer la session
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
    if (isset($_SESSION['client_id'])) {
        $stmtFavorite = $pdo->prepare("SELECT COUNT(*) FROM favoris WHERE IdClient = ? AND IdProduit = ?");
        $stmtFavorite->execute([$_SESSION['client_id'], $productId]);
        $isFavorite = $stmtFavorite->fetchColumn() > 0;
    }
    
} catch (PDOException $e) {
    $error = "Une erreur est survenue lors du chargement du produit: " . $e->getMessage();
}

// Debug : afficher les informations de session (à supprimer en production)
// echo "<!-- Debug Session: " . print_r($_SESSION, true) . " -->";
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
                    <li><a href="index.html" class="hover:text-accent">Accueil</a></li>
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
                        <img id="main-image" 
                             src="<?php echo htmlspecialchars($processedImages[0]); ?>" 
                             alt="<?php echo htmlspecialchars($product['NomProduit']); ?>" 
                             class="w-full h-full object-cover"
                             onerror="this.src='images/placeholder.jpeg'">
                    </div>
                    
                    <!-- Miniatures -->
                    <?php if (count($processedImages) > 1): ?>
                    <div class="flex gap-2 overflow-x-auto">
                        <?php foreach ($processedImages as $index => $image): ?>
                        <button onclick="changeMainImage('<?php echo htmlspecialchars($image); ?>')" 
                                class="thumbnail flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden border-2 border-transparent hover:border-accent transition-colors <?php echo $index === 0 ? 'border-accent' : ''; ?>">
                            <img src="<?php echo htmlspecialchars($image); ?>" 
                                 alt="<?php echo htmlspecialchars($product['NomProduit']); ?>" 
                                 class="w-full h-full object-cover"
                                 onerror="this.src='images/placeholder.jpeg'">
                        </button>
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
                                <button type="button" onclick="decreaseQuantity()" class="px-3 py-2 hover:bg-gray-100 transition-colors">
                                    <i class='bx bx-minus'></i>
                                </button>
                                <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['Stock']; ?>" 
                                       class="w-16 text-center border-0 focus:outline-none">
                                <button type="button" onclick="increaseQuantity()" class="px-3 py-2 hover:bg-gray-100 transition-colors">
                                    <i class='bx bx-plus'></i>
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Boutons d'action -->
                        <div class="flex flex-col sm:flex-row gap-4">
                            <?php if ($product['Stock'] > 0): ?>
                            <button onclick="addToCart()" 
                                    class="flex-1 px-6 py-3 bg-accent text-white rounded-full hover:bg-accent/80 transition-colors text-lg font-medium flex items-center justify-center gap-2">
                                <i class='bx bx-cart-add'></i> Ajouter au panier
                            </button>
                            <?php else: ?>
                            <button disabled 
                                    class="flex-1 px-6 py-3 bg-gray-300 text-gray-500 rounded-full cursor-not-allowed text-lg font-medium flex items-center justify-center gap-2">
                                <i class='bx bx-cart-add'></i> Produit indisponible
                            </button>
                            <?php endif; ?>
                            
                            <button onclick="toggleFavorite(<?php echo $product['IdProduit']; ?>)" 
                                    class="px-6 py-3 <?php echo $isFavorite ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600'; ?> rounded-full hover:bg-red-100 hover:text-red-600 transition-colors text-lg font-medium flex items-center justify-center gap-2"
                                    title="<?php echo $isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris'; ?>"
                                    data-product-id="<?php echo $product['IdProduit']; ?>">
                                <i class='<?php echo $isFavorite ? 'bxs-heart' : 'bx-heart'; ?>'></i> Favoris
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
                        <a href="product.php?id=<?php echo $similarProduct['IdProduit']; ?>" class="block">
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

    <script src="js/script.js"></script>
    <script>
        // Variables globales pour le debug
        const isLoggedIn = <?php echo isset($_SESSION['client_id']) ? 'true' : 'false'; ?>;
        const clientId = <?php echo isset($_SESSION['client_id']) ? $_SESSION['client_id'] : 'null'; ?>;
        
        // Ajouter après les variables existantes isLoggedIn et clientId
        // Données de session pour JavaScript (comme dans categories.php)
        window.sessionData = {
            isLoggedIn: <?php echo isset($_SESSION['client_id']) ? 'true' : 'false'; ?>,
            clientId: <?php echo isset($_SESSION['client_id']) ? $_SESSION['client_id'] : 'null'; ?>
        };

        console.log('Données de session:', window.sessionData);
        
        console.log('État de connexion:', isLoggedIn);
        console.log('ID client:', clientId);

        // Changer l'image principale
        function changeMainImage(imageSrc) {
            document.getElementById('main-image').src = imageSrc;
            
            // Mettre à jour les bordures des miniatures
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('border-accent');
                thumb.classList.add('border-transparent');
            });
            
            // Ajouter la bordure à la miniature sélectionnée
            event.target.closest('.thumbnail').classList.remove('border-transparent');
            event.target.closest('.thumbnail').classList.add('border-accent');
        }

        // Gestion de la quantité
        function increaseQuantity() {
            const input = document.getElementById('quantity');
            const max = parseInt(input.getAttribute('max'));
            const current = parseInt(input.value);
            if (current < max) {
                input.value = current + 1;
            }
        }

        function decreaseQuantity() {
            const input = document.getElementById('quantity');
            const current = parseInt(input.value);
            if (current > 1) {
                input.value = current - 1;
            }
        }

        // Ajouter au panier
        function addToCart() {
            const quantity = document.getElementById('quantity').value;
            const productId = <?php echo $product['IdProduit']; ?>;
            
            window.location.href = `php/cart_actions.php?action=ajouter&produitId=${productId}&quantite=${quantity}`;
        }

        // Remplacer la fonction toggleFavorite existante par :
        function toggleFavorite(productId) {
            console.log('toggleFavorite appelé pour produit:', productId);
            console.log('Utilisateur connecté:', window.sessionData.isLoggedIn);
            
            const button = document.querySelector(`[data-product-id="${productId}"]`);
            const icon = button.querySelector('i');
            
            if (!window.sessionData.isLoggedIn) {
                alert('Veuillez vous connecter pour ajouter des produits aux favoris');
                window.location.href = 'connexion.html';
                return;
            }
            
            // Désactiver le bouton temporairement
            button.disabled = true;
            
            fetch('php/favorites_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=toggle&produitId=${productId}`
            })
            .then(response => {
                console.log('Réponse reçue:', response);
                return response.json();
            })
            .then(data => {
                console.log('Données reçues:', data);
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
                // Réactiver le bouton
                button.disabled = false;
            });
        }

        // Validation de la quantité
        document.getElementById('quantity').addEventListener('input', function() {
            const max = parseInt(this.getAttribute('max'));
            const min = parseInt(this.getAttribute('min'));
            let value = parseInt(this.value);
            
            if (value > max) this.value = max;
            if (value < min) this.value = min;
        });
    </script>
</body>
</html>
