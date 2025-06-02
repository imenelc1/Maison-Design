<?php
// Démarrer la session et vérifier si l'utilisateur est connecté
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

// Déterminer l'onglet actif
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'profile';

// Récupérer les favoris de l'utilisateur avec correction du stock
require_once 'php/db.php';

$favoris = [];
if (isset($_SESSION['user_id'])) {
    try {
        // Requête SQL corrigée pour récupérer les favoris avec le stock
        $stmt = $pdo->prepare("
            SELECT 
                p.IdProduit,
                p.NomProduit,
                p.Description,
                p.Prix,
                p.Stock,
                p.DateAjout,
                c.IdCategorie,
                c.NomCategorie,
                i.URL as ImageProduit,
                f.DateAjout as DateAjoutFavori
            FROM favoris f 
            JOIN produit p ON f.IdProduit = p.IdProduit 
            JOIN categorie c ON p.IdCat = c.IdCategorie
            LEFT JOIN imageprod i ON p.IdProduit = i.IdProduit AND i.IdImage = (
                SELECT MIN(IdImage) FROM imageprod WHERE IdProduit = p.IdProduit
            )
            WHERE f.IdClient = ? 
            ORDER BY f.DateAjout DESC
        ");
        
        $stmt->execute([$_SESSION['user_id']]);
        $favoris = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug: Log des résultats pour vérification
        error_log("Favoris récupérés: " . count($favoris) . " produits");
        
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des favoris: " . $e->getMessage());
        $favoris = [];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte - Maison Design</title>
    <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwind.config.js"></script>
    <link rel="stylesheet" href="css/style.css">
   
</head>
<body class="bg-background">
    <!-- Header -->
    <?php include 'header.php'; ?>

    <!-- Notification Container -->
    <div id="notification-container"></div>

    <main class="min-h-screen pt-28 pb-16 px-4 md:px-[10%] bg-background">
        <div class="max-w-[1200px] mx-auto">
            <!-- En-tête de la page -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-textColor mb-2">Mon Compte</h1>
                <p class="text-textColor/70">Bienvenue, <?php echo htmlspecialchars($_SESSION['prenom'] ?? ''); ?> <?php echo htmlspecialchars($_SESSION['nom'] ?? ''); ?></p>
            </div>

            <!-- Onglets -->
            <div class="mb-8 flex flex-wrap gap-2">
                <button class="tab-button <?php echo $activeTab === 'profile' ? 'bg-accent text-white' : 'bg-primary text-textColor hover:bg-primary/50'; ?> px-4 py-2 rounded-full transition-colors" data-tab="profile">
                    <i class='bx bx-user-circle mr-1'></i> Profil
                </button>
                <button class="tab-button <?php echo $activeTab === 'orders' ? 'bg-accent text-white' : 'bg-primary text-textColor hover:bg-primary/50'; ?> px-4 py-2 rounded-full transition-colors" data-tab="orders">
                    <i class='bx bx-package mr-1'></i> Mes Commandes
                </button>
                <button class="tab-button <?php echo $activeTab === 'wishlist' ? 'bg-accent text-white' : 'bg-primary text-textColor hover:bg-primary/50'; ?> px-4 py-2 rounded-full transition-colors" data-tab="wishlist">
                    <i class='bx bx-heart mr-1'></i> Mes Favoris 
                    <?php if (count($favoris) > 0): ?>
                        <span class="ml-1 bg-white text-accent px-2 py-0.5 rounded-full text-xs font-medium"><?php echo count($favoris); ?></span>
                    <?php endif; ?>
                </button>
            </div>
            
            <!-- Contenu des onglets -->
            <div class="tab-content">
                <!-- Onglet Profil -->
                <div id="profile-tab" class="tab-pane <?php echo $activeTab === 'profile' ? 'block' : 'hidden'; ?>">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden p-6">
                        <h2 class="text-2xl text-accent mb-6">Informations personnelles</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Prénom</p>
                                <p class="font-medium"><?php echo htmlspecialchars($_SESSION['prenom'] ?? ''); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Nom</p>
                                <p class="font-medium"><?php echo htmlspecialchars($_SESSION['nom'] ?? ''); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Email</p>
                                <p class="font-medium"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Téléphone</p>
                                <p class="font-medium"><?php echo htmlspecialchars($_SESSION['telephone'] ?? ''); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Date d'inscription</p>
                                <p class="font-medium"><?php echo isset($_SESSION['date_inscription']) ? date('d/m/Y', strtotime($_SESSION['date_inscription'])) : ''; ?></p>
                            </div>
                        </div>
                        
                        <button class="px-4 py-2 bg-accent text-white rounded-full hover:bg-accent/90 transition-colors">
                            <i class='bx bx-edit mr-1'></i> Modifier mon profil
                        </button>
                    </div>
                </div>
                
                <!-- Onglet Commandes -->
                <div id="orders-tab" class="tab-pane <?php echo $activeTab === 'orders' ? 'block' : 'hidden'; ?>">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden p-6">
                        <h2 class="text-2xl text-accent mb-6">Mes commandes</h2>
                        <div class="text-center py-12">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                                <i class='bx bx-package text-3xl text-gray-400'></i>
                            </div>
                            <p class="text-gray-500 mb-4">Vous n'avez pas encore passé de commande.</p>
                            <a href="categories.php" class="inline-block px-4 py-2 bg-accent text-white rounded-full hover:bg-accent/80 transition-colors">
                                <i class='bx bx-shopping-bag mr-1'></i> Découvrir nos produits
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Onglet Favoris - VERSION CORRIGÉE -->
                <div id="wishlist-tab" class="tab-pane <?php echo $activeTab === 'wishlist' ? 'block' : 'hidden'; ?>">
                    <div class="space-y-6">
                        <!-- En-tête des favoris -->
                        <div class="bg-white rounded-xl shadow-md overflow-hidden p-6">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h2 class="text-2xl text-accent mb-2">Mes Favoris</h2>
                                    <p class="text-textColor/70">
                                        <?php echo count($favoris); ?> produit<?php echo count($favoris) > 1 ? 's' : ''; ?> dans vos favoris
                                    </p>
                                </div>
                                <?php if (count($favoris) > 0): ?>
                                    <button id="clear-all-favorites" class="px-4 py-2 bg-red-100 text-red-600 rounded-full hover:bg-red-200 transition-colors flex items-center">
                                        <i class='bx bx-trash mr-1'></i> Vider les favoris
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Liste des favoris -->
                        <div id="favorites-container">
                            <?php if (count($favoris) === 0): ?>
                                <div class="bg-white rounded-xl shadow-md overflow-hidden p-6">
                                    <div class="text-center py-12">
                                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                                            <i class='bx bx-heart text-3xl text-gray-400'></i>
                                        </div>
                                        <h3 class="text-xl font-semibold text-textColor mb-2">Aucun favori pour le moment</h3>
                                        <p class="text-textColor/70 mb-6">Découvrez nos produits et ajoutez vos coups de cœur à vos favoris</p>
                                        <a href="categories.php" class="inline-block px-4 py-2 bg-accent text-white rounded-full hover:bg-accent/80 transition-colors">
                                            <i class='bx bx-shopping-bag mr-1'></i> Découvrir nos produits
                                        </a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <?php foreach ($favoris as $produit): ?>
                                        <?php 
                                        // CORRECTION: Vérification correcte du stock
                                        $stock = (int)$produit['Stock']; // Conversion explicite en entier
                                        $isAvailable = $stock > 0;
                                        ?>
                                        
                                        <div class="product-card bg-white rounded-xl overflow-hidden shadow-md" data-product-id="<?php echo $produit['IdProduit']; ?>">
                                            <div class="relative">
                                                <a href="produit.php?id=<?php echo $produit['IdProduit']; ?>" class="block">
                                                    <div class="product-image h-48 overflow-hidden relative group">
                                                        <img src="<?php echo htmlspecialchars($produit['ImageProduit'] ?? 'images/placeholder.jpeg'); ?>" 
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
                                                
                                                <!-- Badge de catégorie -->
                                                <div class="absolute top-2 left-2">
                                                    <span class="bg-primary text-textColor px-2 py-1 rounded-full text-xs font-medium">
                                                        <?php echo htmlspecialchars($produit['NomCategorie']); ?>
                                                    </span>
                                                </div>
                                                
                                                <!-- Bouton favori -->
                                                <button class="favorite-btn absolute top-2 right-2 w-8 h-8 bg-red-100 text-red-600 rounded-full hover:bg-red-200 transition-colors flex items-center justify-center"
                                                        title="Retirer des favoris"
                                                        data-product-id="<?php echo $produit['IdProduit']; ?>">
                                                    <i class='fas fa-heart text-sm'></i>
                                                </button>
                                                
                                                <!-- Overlay indisponible - CORRECTION -->
                                                <?php if (!$isAvailable): ?>
                                                    <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                                                        <span class="bg-red-600 text-white px-3 py-1 rounded-full text-sm font-medium">
                                                            Indisponible
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="product-info p-4">
                                                <h3 class="text-lg font-medium text-textColor mb-2 line-clamp-2"><?php echo htmlspecialchars($produit['NomProduit']); ?></h3>
                                                <p class="text-accent font-bold text-xl mb-2"><?php echo number_format($produit['Prix'], 2, ',', ' '); ?> DA</p>
                                                
                                                <!-- Statut de disponibilité - CORRECTION -->
                                                <?php if ($isAvailable): ?>
                                                    <div class="flex items-center gap-1 mb-2 text-green-600">
                                                        <i class='bx bx-check'></i>
                                                        <span class="text-sm">Disponible (<?php echo $stock; ?>)</span>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="flex items-center gap-1 mb-2 text-red-600">
                                                        <i class='bx bx-x'></i>
                                                        <span class="text-sm">Indisponible</span>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <p class="text-sm text-textColor/60">
                                                    Ajouté le <?php echo date('d/m/Y', strtotime($produit['DateAjoutFavori'])); ?>
                                                </p>
                                            </div>
                                            
                                            <div class="p-4 pt-0 flex flex-col gap-2">
                                                <a href="produit.php?id=<?php echo $produit['IdProduit']; ?>" 
                                                   class="w-full px-3 py-2 bg-primary text-textColor rounded-full hover:bg-accent hover:text-white transition-colors text-sm flex items-center justify-center gap-2 font-medium">
                                                    Voir détails
                                                </a>
                                                
                                                <div class="flex gap-2">
                                                    <!-- Bouton ajouter au panier - CORRECTION -->
                                                    <?php if ($isAvailable): ?>
                                                        <button onclick="addToCart(<?php echo $produit['IdProduit']; ?>)"
                                                                class="flex-1 px-3 py-2 bg-accent text-white rounded-full hover:bg-accent/80 transition-colors text-sm flex items-center justify-center gap-1">
                                                            <i class='bx bx-cart-add'></i> Ajouter au panier
                                                        </button>
                                                    <?php else: ?>
                                                        <button disabled 
                                                                class="flex-1 px-3 py-2 bg-gray-300 text-gray-500 rounded-full cursor-not-allowed text-sm flex items-center justify-center gap-1">
                                                            <i class='bx bx-cart-add'></i> Indisponible
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <button class="remove-favorite-btn px-3 py-2 bg-red-100 text-red-600 rounded-full hover:bg-red-200 transition-colors text-sm flex items-center justify-center"
                                                            title="Retirer des favoris"
                                                            data-product-id="<?php echo $produit['IdProduit']; ?>">
                                                        <i class='bx bx-trash'></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <script src="js/client.js"></script>
</body>
</html>
