<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: connexion.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: client.php");
    exit();
}

$commandeId = $_GET['id'];
$clientId = $_SESSION['user_id'];

require_once 'php/db.php';

try {
    // Récupérer la commande avec vérification de propriété
    $stmt = $pdo->prepare("SELECT * FROM commande WHERE IdCommande = ? AND IdClient = ?");
    $stmt->execute([$commandeId, $clientId]);
    $commande = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur récupération commande: " . $e->getMessage());
    header("Location: client.php");
    exit();
}

if (!$commande) {
    $error = "Commande non trouvée ou vous n'avez pas l'autorisation de la voir.";
} else {
    // CORRECTION: Récupérer UNIQUEMENT les produits de cette commande avec GROUP BY pour éviter les doublons
    try {
        $stmtProduits = $pdo->prepare("
            SELECT 
                p.IdProd, 
                p.Qtt, 
                pr.NomProduit, 
                pr.Prix, 
                i.URL as image
            FROM panier p
            INNER JOIN produit pr ON p.IdProd = pr.IdProduit
            LEFT JOIN (
                SELECT IdProduit, MIN(URL) as URL
                FROM imageprod
                GROUP BY IdProduit
            ) i ON pr.IdProduit = i.IdProduit
            WHERE p.IdCom = ?
            GROUP BY p.IdProd, pr.NomProduit, pr.Prix, p.Qtt, i.URL
            ORDER BY pr.NomProduit
        ");
        $stmtProduits->execute([$commandeId]);
        $produitsRaw = $stmtProduits->fetchAll(PDO::FETCH_ASSOC);
        
        // CORRECTION: Éliminer les doublons côté PHP aussi
        $produits = [];
        $produitsVus = [];
        
        foreach ($produitsRaw as $produit) {
            $cle = $produit['IdProd'] . '_' . $produit['Qtt'] . '_' . $produit['Prix'];
            
            if (!in_array($cle, $produitsVus)) {
                $produitsVus[] = $cle;
                $produits[] = $produit;
            }
        }
        
    } catch (PDOException $e) {
        error_log("Erreur récupération produits: " . $e->getMessage());
        $produits = [];
    }

    // Récupérer l'adresse de livraison depuis la table livraison
    try {
        $stmtLivraison = $pdo->prepare("SELECT * FROM livraison WHERE IdComm = ?");
        $stmtLivraison->execute([$commandeId]);
        $livraison = $stmtLivraison->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur récupération livraison: " . $e->getMessage());
        $livraison = null;
    }

    // Récupérer les informations du client
    try {
        $stmtClient = $pdo->prepare("SELECT * FROM client WHERE IdClient = ?");
        $stmtClient->execute([$clientId]);
        $client = $stmtClient->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur récupération client: " . $e->getMessage());
        $client = null;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de commande - Maison Design</title>
    <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwind.config.js"></script>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="font-cormorant bg-background">
    <?php include 'header.php'; ?>

    <main class="min-h-screen pt-28 pb-16 px-4 md:px-[10%] bg-background">
        <div class="max-w-[800px] mx-auto">
            <?php if (isset($error)): ?>
            <div class="bg-white rounded-xl shadow-md overflow-hidden p-8 text-center">
                <div class="mb-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-4">
                        <i class='bx bx-error text-3xl text-red-600'></i>
                    </div>
                    <h1 class="text-3xl font-medium text-textColor">Erreur</h1>
                    <p class="text-gray-600 mt-2"><?php echo htmlspecialchars($error); ?></p>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="client.php?tab=orders" class="px-6 py-3 bg-primary text-textColor rounded-full hover:bg-primary/80 transition-colors">
                        Voir mes commandes
                    </a>
                    <a href="categories.php" class="px-6 py-3 bg-accent text-white rounded-full hover:bg-accent/90 transition-colors">
                        Continuer mes achats
                    </a>
                </div>
            </div>
            <?php else: ?>
            <div class="bg-white rounded-xl shadow-md overflow-hidden p-8 text-center">
                <div class="mb-6">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
                        <i class='bx bx-check text-4xl text-green-600'></i>
                    </div>
                    <h1 class="text-3xl font-medium text-textColor mb-2">Commande confirmée !</h1>
                    <p class="text-gray-600">
                        <?php if ($client): ?>
                            Merci <?php echo htmlspecialchars($client['NomClient'] . ' ' . $client['PrenomClient']); ?> pour votre commande.
                        <?php else: ?>
                            Merci cher client pour votre commande.
                        <?php endif; ?>
                        Votre commande a été enregistrée avec succès.
                    </p>
                </div>
                
                <div class="border-t border-b border-gray-200 py-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-left">
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="font-medium">Numéro de commande:</span>
                                <span>#<?php echo str_pad($commandeId, 6, '0', STR_PAD_LEFT); ?></span>
                            </div>
                            <div class="flex justify-between mb-2">
                                <span class="font-medium">Date:</span>
                                <span>
                                    <?php 
                                    if (isset($commande['DateCommande'])) {
                                        echo date('d/m/Y à H:i', strtotime($commande['DateCommande']));
                                    } else {
                                        echo date('d/m/Y à H:i');
                                    }
                                    ?>
                                </span>
                            </div>
                            
                            <?php if ($livraison && !empty($livraison['Adresse'])): ?>
                            <div class="mt-4">
                                <span class="font-medium block mb-1">Adresse de livraison:</span>
                                <div class="bg-gray-50 p-2 rounded text-gray-700 text-sm">
                                    <?php echo nl2br(htmlspecialchars($livraison['Adresse'])); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="font-medium">Statut commande:</span>
                                <?php 
                                $status = $commande['Status'] ?? 'en attente';
                                switch(strtolower(trim($status))) {
                                    case 'en_attente':
                                    case 'en attente':
                                    case '':
                                        echo '<span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">En attente</span>';
                                        break;
                                    case 'confirme':
                                    case 'confirmé':
                                        echo '<span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">Confirmée</span>';
                                        break;
                                    case 'expedie':
                                    case 'expédié':
                                    case 'expe?die?':
                                        echo '<span class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs">Expédiée</span>';
                                        break;
                                    case 'livre':
                                    case 'livré':
                                    case 'livre?':
                                        echo '<span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">Livrée</span>';
                                        break;
                                    case 'annule':
                                    case 'annulé':
                                    case 'annule?':
                                        echo '<span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs">Annulée</span>';
                                        break;
                                    default:
                                        echo '<span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">En attente</span>';
                                }
                                ?>
                            </div>
                            
                            <?php if ($livraison): ?>
                            <div class="flex justify-between mb-2">
                                <span class="font-medium">Statut livraison:</span>
                                <?php 
                                $statutLivraison = $livraison['StatutLivraison'] ?? 'En attente';
                                switch(strtolower(trim($statutLivraison))) {
                                    case 'en attente':
                                        echo '<span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">En attente</span>';
                                        break;
                                    case 'en route':
                                        echo '<span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">En route</span>';
                                        break;
                                    case 'livre':
                                    case 'livré':
                                        echo '<span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">Livrée</span>';
                                        break;
                                    default:
                                        echo '<span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">En attente</span>';
                                }
                                ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="flex justify-between">
                                <span class="font-medium">Total:</span>
                                <span class="text-accent font-bold">
                                    <?php echo number_format($commande['TotalPrix'], 2, ',', ' '); ?> DA
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($produits)): ?>
                <div class="mb-8">
                    <h2 class="text-xl font-medium text-textColor mb-4 text-left">Détails de la commande</h2>
                    
                    <!-- CORRECTION: Affichage sécurisé des produits sans doublons -->
                    <div class="space-y-4">
                        <?php 
                        // Debug: afficher le nombre de produits
                        // echo "<!-- Debug: " . count($produits) . " produits trouvés -->";
                        
                        foreach ($produits as $index => $produit): 
                        ?>
                            <div class="flex items-center gap-4 text-left border-b border-gray-100 pb-4" data-product-index="<?php echo $index; ?>">
                                <img src="<?php echo !empty($produit['image']) ? htmlspecialchars($produit['image']) : 'images/placeholder.jpeg'; ?>" 
                                     alt="<?php echo htmlspecialchars($produit['NomProduit']); ?>" 
                                     class="w-16 h-16 object-cover rounded-md"
                                     onerror="this.src='images/placeholder.jpeg'">
                                <div class="flex-1">
                                    <h3 class="font-medium"><?php echo htmlspecialchars($produit['NomProduit']); ?></h3>
                                    <div class="flex justify-between mt-1">
                                        <span class="text-sm text-gray-600">Quantité: <?php echo (int)$produit['Qtt']; ?></span>
                                        <span class="font-medium"><?php echo number_format((float)$produit['Prix'] * (int)$produit['Qtt'], 2, ',', ' '); ?> DA</span>
                                    </div>
                                    <span class="text-xs text-gray-500"><?php echo number_format((float)$produit['Prix'], 2, ',', ' '); ?> DA / unité</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Affichage du sous-total calculé -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <?php
                        $sousTotal = 0;
                        foreach ($produits as $produit) {
                            $sousTotal += (float)$produit['Prix'] * (int)$produit['Qtt'];
                        }
                        $fraisLivraison = $livraison ? (float)$livraison['Frais'] : 1000;
                        $totalCalcule = $sousTotal + $fraisLivraison;
                        ?>
                        <div class="flex justify-between text-sm text-gray-600 mb-1">
                            <span>Sous-total produits:</span>
                            <span><?php echo number_format($sousTotal, 2, ',', ' '); ?> DA</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600 mb-1">
                            <span>Frais de livraison:</span>
                            <span><?php echo number_format($fraisLivraison, 2, ',', ' '); ?> DA</span>
                        </div>
                        <div class="flex justify-between font-medium text-lg">
                            <span>Total calculé:</span>
                            <span class="text-accent"><?php echo number_format($totalCalcule, 2, ',', ' '); ?> DA</span>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="mb-8">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                        <i class='bx bx-info-circle text-yellow-600 text-2xl mb-2'></i>
                        <p class="text-yellow-800">Aucun produit trouvé pour cette commande.</p>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-left">
                    <h3 class="font-medium text-blue-800 mb-2">Prochaines étapes</h3>
                    <ul class="text-sm text-blue-700 space-y-1">
                        <li>• Votre commande sera préparée dans les 24h</li>
                        <li>• Vous serez contacté pour organiser la livraison</li>
                        <li>• Paiement à effectuer à la livraison</li>
                        <?php if ($client && !empty($client['Email'])): ?>
                            <li>• Un email de confirmation sera envoyé à <?php echo htmlspecialchars($client['Email']); ?></li>
                        <?php endif; ?>
                        <?php if ($livraison): ?>
                            <li>• Frais de livraison: <?php echo number_format($livraison['Frais'], 2, ',', ' '); ?> DA</li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="client.php?tab=orders" class="px-6 py-3 bg-primary text-textColor rounded-full hover:bg-primary/80 transition-colors flex items-center justify-center gap-2">
                        <i class='bx bx-list-ul'></i>
                        Voir mes commandes
                    </a>
                    <a href="categories.php" class="px-6 py-3 bg-accent text-white rounded-full hover:bg-accent/90 transition-colors flex items-center justify-center gap-2">
                        <i class='bx bx-shopping-bag'></i>
                        Continuer mes achats
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'footer.php'; ?>

</body>
</html>
