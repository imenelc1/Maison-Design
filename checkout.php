<?php
// Commencer la session et vérifier si l'utilisateur est connecté
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
    header("Location: connexion.php?redirect=checkout.php");
    exit();
}

// Vérifier si le panier est vide
if (empty($_SESSION['panier'])) {
    header("Location: cart.php");
    exit();
}

// Récupérer les informations de l'utilisateur
require_once 'php/db.php';

$clientId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM client WHERE IdClient = ?");
$stmt->execute([$clientId]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

// Calculer les totaux
$sousTotal = 0;
foreach ($_SESSION['panier'] as $item) {
    $sousTotal += $item['prix'] * $item['quantite'];
}
$fraisLivraison = 1000; // Frais de livraison fixes
$total = $sousTotal + $fraisLivraison;

// Traiter la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adresseLivraison = isset($_POST['adresse_livraison']) ? trim($_POST['adresse_livraison']) : '';
    
    if (empty($adresseLivraison)) {
        $error = "Veuillez saisir une adresse de livraison.";
    } else {
        try {
            // Démarrer une transaction
            $pdo->beginTransaction();
            
            // 1. Insérer la commande principale
            $stmt = $pdo->prepare("INSERT INTO commande (IdClient, TotalPrix, Status) VALUES (?, ?, 'en attente')");
            $stmt->execute([$clientId, $total]);
            
            // Récupérer l'ID de la commande créée
            $commandeId = $pdo->lastInsertId();
            
            // 2. Insérer les produits dans le panier
            $stmtPanier = $pdo->prepare("INSERT INTO panier (IdProd, IdCom, Qtt) VALUES (?, ?, ?)");
            
            foreach ($_SESSION['panier'] as $produit) {
                $stmtPanier->execute([
                    $produit['id'],
                    $commandeId,
                    $produit['quantite']
                ]);
                
                // Mettre à jour le stock du produit
                $stmtStock = $pdo->prepare("UPDATE produit SET Stock = Stock - ? WHERE IdProduit = ?");
                $stmtStock->execute([$produit['quantite'], $produit['id']]);
            }
            
            // 3. Créer l'entrée de livraison
            $stmtLivraison = $pdo->prepare("INSERT INTO livraison (Adresse, StatutLivraison, Frais, IdComm) VALUES (?, 'En attente', ?, ?)");
            $stmtLivraison->execute([$adresseLivraison, $fraisLivraison, $commandeId]);
            
            // 4. Créer l'entrée de paiement
            $stmtPaiement = $pdo->prepare("INSERT INTO paiement (TotalPrixF, MethodePaiement, StatusP, Idclt, IdCom) VALUES (?, 'Cash', 'En attente', ?, ?)");
            $stmtPaiement->execute([$total, $clientId, $commandeId]);
            
            // Valider la transaction
            $pdo->commit();
            
            // Vider le panier
            $_SESSION['panier'] = [];
            
            // Rediriger vers une page de confirmation
            header("Location: confirmation.php?id=" . $commandeId);
            exit();
            
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $pdo->rollBack();
            $error = "Une erreur est survenue lors du traitement de votre commande: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finaliser votre commande - Maison Design</title>
    <!-- Inclure les styles et scripts nécessaires -->
    <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwind.config.js"></script>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="font-cormorant bg-background">
    <!-- En-tête -->
    <?php include 'header.php'; ?>

    <main class="min-h-screen pt-28 pb-16 px-4 md:px-[10%] bg-background">
        <div class="max-w-[1200px] mx-auto">
            <h1 class="text-3xl md:text-4xl font-frunchy text-textColor mb-8">Finaliser votre commande</h1>
            
            <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Formulaire de commande -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden p-6 mb-6">
                        <h2 class="text-2xl text-accent mb-4">Adresse de livraison</h2>
                        
                        <form id="checkout-form" action="checkout.php" method="POST">
                            <div class="mb-6">
                                <label for="adresse-livraison" class="block text-sm font-medium text-gray-700 mb-2">Adresse de livraison</label>
                                <textarea id="adresse-livraison" name="adresse_livraison" rows="3" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent" placeholder="Entrez votre adresse complète" required><?php echo htmlspecialchars($client['Adresse'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mb-6">
                                <h2 class="text-2xl text-accent mb-4">Mode de paiement</h2>
                                <div class="space-y-4">
                                    <div class="border rounded-lg p-4 cursor-pointer hover:border-accent transition-colors">
                                        <input type="radio" name="mode_paiement" value="Cash" id="paiement-cash" checked>
                                        <label for="paiement-cash" class="ml-2 cursor-pointer">Paiement à la livraison</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex justify-end">
                                <button type="submit" class="px-6 py-3 bg-accent text-white rounded-full hover:bg-accent/90 transition-colors flex items-center justify-center gap-2">
                                    Confirmer la commande
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Résumé de la commande -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden p-6 sticky top-24">
                        <h2 class="text-2xl text-accent mb-4">Résumé de la commande</h2>
                        
                        <div class="max-h-[300px] overflow-y-auto mb-4">
                            <?php foreach ($_SESSION['panier'] as $item): ?>
                            <div class="flex items-center gap-3 py-3 border-b border-gray-100">
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['nom']); ?>" class="w-12 h-12 object-cover rounded-md">
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium"><?php echo htmlspecialchars($item['nom']); ?></h4>
                                    <div class="flex items-center justify-between mt-1">
                                        <span class="text-xs">Qté: <?php echo $item['quantite']; ?></span>
                                        <span class="text-sm font-medium"><?php echo number_format($item['prix'] * $item['quantite'], 2, ',', ' '); ?> DA</span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-4 space-y-2">
                            <div class="flex justify-between">
                                <span>Sous-total:</span>
                                <span class="font-medium"><?php echo number_format($sousTotal, 2, ',', ' '); ?> DA</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Livraison:</span>
                                <span class="font-medium"><?php echo number_format($fraisLivraison, 2, ',', ' '); ?> DA</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-2 mt-2">
                                <span>Total:</span>
                                <span class="text-accent"><?php echo number_format($total, 2, ',', ' '); ?> DA</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Pied de page -->
    <?php include 'footer.php'; ?>

</body>
</html>
