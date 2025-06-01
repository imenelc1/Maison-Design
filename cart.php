<?php
// Démarrer la session
session_start();

// Connexion à la base de données
require_once 'php/db.php';

// Initialiser le panier s'il n'existe pas
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Calculer les totaux
$sousTotal = 0;
foreach ($_SESSION['panier'] as $item) {
    $sousTotal += $item['prix'] * $item['quantite'];
}
$fraisLivraison = 1000; // Frais de livraison fixes
$total = $sousTotal + $fraisLivraison;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier - Maison Design</title>
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
        <div class="max-w-[1200px] mx-auto">
            <h1 class="text-3xl md:text-4xl font-frunchy text-textColor mb-8">Mon Panier</h1>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Liste des produits du panier -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden p-6 mb-6">
                        <h2 class="text-2xl text-accent mb-4">Articles dans votre panier</h2>
                        
                        <?php if (empty($_SESSION['panier'])): ?>
                        <div id="empty-cart-message" class="text-center py-8">
                            <p class="text-gray-500 mb-4">Votre panier est vide</p>
                            <a href="categories.php" class="px-6 py-3 bg-accent text-white rounded-full hover:bg-accent/90 transition-colors inline-block">
                                Découvrir nos produits
                            </a>
                        </div>
                        <?php else: ?>
                        <div id="cart-items" class="space-y-4">
                            <?php foreach ($_SESSION['panier'] as $index => $item): ?>
                            <div class="flex items-center gap-4 py-4 border-b border-gray-100">
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['nom']); ?>" class="w-20 h-20 object-cover rounded-md">
                                <div class="flex-1">
                                    <h3 class="font-medium"><?php echo htmlspecialchars($item['nom']); ?></h3>
                                    <div class="flex items-center justify-between mt-2">
                                        <div class="flex items-center gap-2">
                                            <a href="php/cart_actions.php?action=modifier&produitId=<?php echo $item['id']; ?>&delta=-1" class="w-6 h-6 bg-gray-100 rounded flex items-center justify-center hover:bg-accent hover:text-white transition-colors">
                                                <i class='bx bx-minus'></i>
                                            </a>
                                            <span class="w-8 text-center"><?php echo $item['quantite']; ?></span>
                                            <a href="php/cart_actions.php?action=modifier&produitId=<?php echo $item['id']; ?>&delta=1" class="w-6 h-6 bg-gray-100 rounded flex items-center justify-center hover:bg-accent hover:text-white transition-colors">
                                                <i class='bx bx-plus'></i>
                                            </a>
                                        </div>
                                        <span class="font-medium"><?php echo number_format($item['prix'] * $item['quantite'], 2, ',', ' '); ?> DA</span>
                                    </div>
                                </div>
                                <a href="php/cart_actions.php?action=supprimer&produitId=<?php echo $item['id']; ?>" class="text-gray-400 hover:text-red-500 p-2">
                                    <i class='bx bx-trash text-xl'></i>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="flex justify-between mt-6">
                            <a href="categories.php" class="text-accent hover:underline flex items-center gap-1">
                                <i class='bx bx-arrow-back'></i> Continuer mes achats
                            </a>
                            <a href="php/cart_actions.php?action=vider" class="text-gray-500 hover:text-red-500 flex items-center gap-1">
                                <i class='bx bx-trash'></i> Vider le panier
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Résumé de la commande -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden p-6 sticky top-24">
                        <h2 class="text-2xl text-accent mb-4">Résumé</h2>
                        
                        <div class="space-y-2 mb-6">
                            <div class="flex justify-between">
                                <span>Sous-total:</span>
                                <span id="cart-subtotal" class="font-medium"><?php echo number_format($sousTotal, 2, ',', ' '); ?> DA</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Livraison:</span>
                                <span class="font-medium"><?php echo number_format($fraisLivraison, 2, ',', ' '); ?> DA</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-2 mt-2">
                                <span>Total:</span>
                                <span id="cart-total" class="text-accent"><?php echo number_format($total, 2, ',', ' '); ?> DA</span>
                            </div>
                        </div>
                        
                        <?php if (!empty($_SESSION['panier'])): ?>
                        <a href="checkout.php" id="checkout-button" class="w-full px-6 py-3 bg-accent text-white rounded-full hover:bg-accent/90 transition-colors flex items-center justify-center gap-2">
                            <i class='bx bx-check-circle text-xl'></i>
                            Passer la commande
                        </a>
                        <?php else: ?>
                        <button disabled class="w-full px-6 py-3 bg-gray-400 text-white rounded-full cursor-not-allowed flex items-center justify-center gap-2">
                            <i class='bx bx-check-circle text-xl'></i>
                            Passer la commande
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <?php include 'footer.php'; ?>

</body>
</html>
