<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

// Vérifier si le panier n'est pas vide
if (!isset($_SESSION['panier']) || empty($_SESSION['panier'])) {
    header('Location: cart.php');
    exit();
}

require_once 'php/db.php';

// Calculer les totaux
$sousTotal = 0;
foreach ($_SESSION['panier'] as $item) {
    $sousTotal += $item['prix'] * $item['quantite'];
}
$fraisLivraison = 1000;
$total = $sousTotal + $fraisLivraison;

// Variable pour indiquer le succès de la commande
$commandeReussie = false;
$commandeId = null;

// Traitement de la commande
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Vérifier que les conditions sont acceptées
        if (!isset($_POST['terms']) || $_POST['terms'] !== 'on') {
            throw new Exception("Vous devez accepter les conditions générales de vente.");
        }

        // Vérifier que l'adresse de livraison est fournie
        if (empty($_POST['adresse_livraison'])) {
            throw new Exception("L'adresse de livraison est obligatoire.");
        }

        $pdo->beginTransaction();
        
        // Vérifier le stock AVANT de créer la commande
        foreach ($_SESSION['panier'] as $item) {
            $stmt = $pdo->prepare("SELECT Stock, NomProduit FROM produit WHERE IdProduit = ?");
            $stmt->execute([$item['id']]);
            $produit = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$produit) {
                throw new Exception("Le produit '{$item['nom']}' n'existe plus dans notre catalogue.");
            }
            
            if ($produit['Stock'] < $item['quantite']) {
                throw new Exception("Stock insuffisant pour '{$produit['NomProduit']}'. Stock disponible: {$produit['Stock']}, demandé: {$item['quantite']}");
            }
        }
        
        // Créer la commande
        $stmt = $pdo->prepare("
            INSERT INTO commande (IdClient, DateCommande, TotalPrix, Status) 
            VALUES (?, NOW(), ?, 'en attente')
        ");
        $result = $stmt->execute([$_SESSION['user_id'], $total]);
        
        if (!$result) {
            throw new Exception("Erreur lors de la création de la commande dans la base de données.");
        }
        
        $commandeId = $pdo->lastInsertId();
        
        if (!$commandeId) {
            throw new Exception("Impossible de récupérer l'ID de la commande créée.");
        }
        
        // Créer l'entrée de livraison
        $stmt = $pdo->prepare("
            INSERT INTO livraison (Adresse, DateLivraison, StatutLivraison, Frais, IdComm) 
            VALUES (?, NOW(), 'En attente', ?, ?)
        ");
        $result = $stmt->execute([$_POST['adresse_livraison'], $fraisLivraison, $commandeId]);
        
        if (!$result) {
            throw new Exception("Erreur lors de la création de la livraison.");
        }
        
        // SOLUTION SIMPLE: Consolider les produits identiques avant insertion
        $produitsConsolides = [];
        
        // Regrouper les produits identiques et additionner leurs quantités
        foreach ($_SESSION['panier'] as $item) {
            $produitId = $item['id'];
            
            if (isset($produitsConsolides[$produitId])) {
                // Si le produit existe déjà, additionner la quantité
                $produitsConsolides[$produitId]['quantite'] += $item['quantite'];
            } else {
                // Sinon, ajouter le produit
                $produitsConsolides[$produitId] = $item;
            }
        }
        
        // Insérer les produits consolidés
        foreach ($produitsConsolides as $item) {
            $stmt = $pdo->prepare("
                INSERT INTO panier (IdCom, IdProd, Qtt, DatePanier) 
                VALUES (?, ?, ?, NOW())
            ");
            $result = $stmt->execute([$commandeId, $item['id'], $item['quantite']]);
            
            if (!$result) {
                throw new Exception("Erreur lors de l'ajout du produit '{$item['nom']}' à la commande.");
            }
            
            // Mettre à jour le stock
            $stmt = $pdo->prepare("
                UPDATE produit 
                SET Stock = Stock - ? 
                WHERE IdProduit = ? AND Stock >= ?
            ");
            $result = $stmt->execute([$item['quantite'], $item['id'], $item['quantite']]);
            
            if ($stmt->rowCount() === 0) {
                throw new Exception("Impossible de mettre à jour le stock pour '{$item['nom']}'. Le stock a peut-être changé.");
            }
        }
        
        $pdo->commit();
        
        // Vider le panier
        $_SESSION['panier'] = [];
        
        // Marquer la commande comme réussie
        $commandeReussie = true;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
        error_log("Erreur checkout: " . $e->getMessage() . " - User ID: " . ($_SESSION['user_id'] ?? 'non connecté') . " - " . date('Y-m-d H:i:s'));
    }
}

// Récupérer les informations complètes du client
$client = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM client WHERE IdClient = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Erreur récupération client: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finaliser la commande - Maison Design</title>
    <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwind.config.js"></script>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="font-cormorant bg-background">
    <!-- HEADER -->
    <?php include 'header.php'; ?>

    <main class="min-h-screen pt-28 pb-16 px-4 md:px-[10%] bg-background">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl md:text-4xl font-frunchy text-textColor mb-8">Finaliser la commande</h1>
            
            <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 flex items-center gap-2">
                <i class='bx bx-error-circle text-xl'></i>
                <div>
                    <strong>Erreur:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Résumé de la commande -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-2xl text-accent mb-4">Résumé de votre commande</h2>
                    
                    <div class="space-y-4 mb-6">
                        <?php foreach ($_SESSION['panier'] as $item): ?>
                        <div class="flex items-center gap-4 py-2 border-b border-gray-100">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['nom']); ?>" 
                                 class="w-16 h-16 object-cover rounded-md"
                                 onerror="this.src='images/placeholder.jpeg'">
                            <div class="flex-1">
                                <h3 class="font-medium"><?php echo htmlspecialchars($item['nom']); ?></h3>
                                <p class="text-gray-600">Quantité: <?php echo $item['quantite']; ?></p>
                                <p class="text-sm text-gray-500"><?php echo number_format($item['prix'], 2, ',', ' '); ?> DA / unité</p>
                            </div>
                            <span class="font-medium"><?php echo number_format($item['prix'] * $item['quantite'], 2, ',', ' '); ?> DA</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="space-y-2 border-t border-gray-200 pt-4">
                        <div class="flex justify-between">
                            <span>Sous-total:</span>
                            <span><?php echo number_format($sousTotal, 2, ',', ' '); ?> DA</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Livraison:</span>
                            <span><?php echo number_format($fraisLivraison, 2, ',', ' '); ?> DA</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold">
                            <span>Total:</span>
                            <span class="text-accent"><?php echo number_format($total, 2, ',', ' '); ?> DA</span>
                        </div>
                    </div>
                </div>
                
                <!-- Formulaire de confirmation -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-2xl text-accent mb-4">Confirmer la commande</h2>
                    
                    <!-- Afficher les infos client -->
                    <?php if ($client): ?>
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h3 class="font-medium text-gray-800 mb-3 flex items-center gap-2">
                            <i class='bx bx-user text-accent'></i>
                            Informations client
                        </h3>
                        <div class="text-sm text-gray-700 space-y-2">
                            <p class="flex items-center gap-2">
                                <i class='bx bx-user-circle text-gray-500'></i>
                                <span class="font-medium"><?php echo htmlspecialchars($client['NomClient'] . ' ' . $client['PrenomClient']); ?></span>
                            </p>
                            
                            <?php if (!empty($client['Email'])): ?>
                            <p class="flex items-center gap-2">
                                <i class='bx bx-envelope text-gray-500'></i>
                                <span><?php echo htmlspecialchars($client['Email']); ?></span>
                            </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($client['NumTel'])): ?>
                            <p class="flex items-center gap-2">
                                <i class='bx bx-phone text-gray-500'></i>
                                <span><?php echo htmlspecialchars($client['NumTel']); ?></span>
                            </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($client['Adresse'])): ?>
                            <p class="flex items-start gap-2">
                                <i class='bx bx-map text-gray-500 mt-0.5'></i>
                                <span><?php echo htmlspecialchars($client['Adresse']); ?></span>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="space-y-6" id="checkout-form">
                        <div>
                            <label for="adresse_livraison" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class='bx bx-map-pin text-accent'></i>
                                Adresse de livraison *
                            </label>
                            <textarea 
                                id="adresse_livraison" 
                                name="adresse_livraison" 
                                required 
                                rows="3"
                                placeholder="Entrez votre adresse complète de livraison..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent"
                            ><?php echo isset($client['Adresse']) ? htmlspecialchars($client['Adresse']) : ''; ?></textarea>
                            <p class="text-xs text-gray-500 mt-1">
                                Cette adresse sera utilisée pour la livraison de votre commande
                            </p>
                        </div>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h3 class="font-medium text-blue-800 mb-2 flex items-center gap-2">
                                <i class='bx bx-info-circle'></i>
                                Informations importantes
                            </h3>
                            <ul class="text-sm text-blue-700 space-y-1">
                                <li>• Votre commande sera traitée dans les 24h</li>
                                <li>• Vous recevrez un email de confirmation</li>
                                <li>• La livraison se fait sous 3-5 jours ouvrables</li>
                                <li>• Paiement à la livraison (espèces uniquement)</li>
                                <li>• Frais de livraison: <?php echo number_format($fraisLivraison, 2, ',', ' '); ?> DA</li>
                            </ul>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="terms" name="terms" required class="rounded">
                            <label for="terms" class="text-sm text-gray-700">
                                J'accepte les <a href="#" class="text-accent hover:underline">conditions générales de vente</a>
                                et je confirme que les informations fournies sont exactes
                            </label>
                        </div>
                        
                        <button type="submit" id="submit-btn"
                                class="w-full px-6 py-3 bg-accent text-white rounded-full hover:bg-accent/90 transition-colors flex items-center justify-center gap-2 disabled:bg-gray-400 disabled:cursor-not-allowed">
                            <i class='bx bx-check-circle text-xl'></i>
                            <span id="submit-text">Confirmer la commande</span>
                        </button>
                    </form>
                    
                    <div class="mt-4 text-center">
                        <a href="cart.php" class="text-gray-600 hover:text-accent flex items-center justify-center gap-1">
                            <i class='bx bx-arrow-back'></i>
                            Retour au panier
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <?php include 'footer.php'; ?>

    <script src="js/shared-cart-functions.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('checkout-form');
            const submitBtn = document.getElementById('submit-btn');
            const submitText = document.getElementById('submit-text');
            
            function waitForCartManager(callback) {
                if (window.cartManager && window.cartManager.showNotification) {
                    callback();
                } else {
                    setTimeout(() => waitForCartManager(callback), 100);
                }
            }
            
            <?php if ($commandeReussie && $commandeId): ?>
                waitForCartManager(() => {
                    window.cartManager.showNotification("Commande confirmée avec succès !", "success");
                    
                    setTimeout(() => {
                        window.location.href = 'confirmation.php?id=<?php echo $commandeId; ?>';
                    }, 2000);
                });
            <?php endif; ?>
            
            if (form && submitBtn) {
                form.addEventListener('submit', function(e) {
                    const termsCheckbox = document.getElementById('terms');
                    if (!termsCheckbox.checked) {
                        e.preventDefault();
                        
                        waitForCartManager(() => {
                            window.cartManager.showNotification('Vous devez accepter les conditions générales de vente.', 'error');
                        });
                        return false;
                    }
                    
                    const adresseField = document.getElementById('adresse_livraison');
                    if (!adresseField.value.trim()) {
                        e.preventDefault();
                        
                        waitForCartManager(() => {
                            window.cartManager.showNotification('L\'adresse de livraison est obligatoire.', 'error');
                        });
                        adresseField.focus();
                        return false;
                    }
                    
                    submitBtn.disabled = true;
                    submitText.textContent = 'Traitement en cours...';
                    submitBtn.classList.add('opacity-50');
                    
                    const icon = submitBtn.querySelector('i');
                    icon.className = 'bx bx-loader-alt animate-spin text-xl';
                    
                    waitForCartManager(() => {
                        window.cartManager.showNotification('Traitement de votre commande en cours...', 'info');
                    });
                    
                    setTimeout(() => {
                        if (submitBtn.disabled) {
                            submitBtn.disabled = false;
                            submitText.textContent = 'Confirmer la commande';
                            submitBtn.classList.remove('opacity-50');
                            icon.className = 'bx bx-check-circle text-xl';
                        }
                    }, 15000);
                });
            }
        });
    </script>
</body>
</html>
