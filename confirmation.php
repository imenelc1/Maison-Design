<?php
// Commencer la session et vérifier si l'utilisateur est connecté
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
    header("Location: connexion.php");
    exit();
}

// Vérifier si l'ID de commande est présent
if (!isset($_GET['id'])) {
    header("Location: client.php");
    exit();
}

$commandeId = $_GET['id'];
$clientId = $_SESSION['user_id'];

// Récupérer les détails de la commande
require_once 'php/db.php';

// CORRIGÉ: Vérifier que la commande appartient bien à l'utilisateur connecté
try {
    // Essayer avec différents noms de colonnes
    $stmt = $pdo->prepare("SELECT * FROM commande WHERE IdCommande = ? AND IdClient = ?");
    $stmt->execute([$commandeId, $clientId]);
    $commande = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Si IdCommande n'existe pas, essayer avec Id
    try {
        $stmt = $pdo->prepare("SELECT * FROM commande WHERE Id = ? AND IdClient = ?");
        $stmt->execute([$commandeId, $clientId]);
        $commande = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e2) {
        error_log("Erreur récupération commande: " . $e2->getMessage());
        header("Location: client.php");
        exit();
    }
}

if (!$commande) {
    // CORRIGÉ: Ne pas rediriger, afficher un message d'erreur
    $error = "Commande non trouvée ou vous n'avez pas l'autorisation de la voir.";
} else {
    // Récupérer les produits de la commande
    try {
        // Essayer avec la table panier d'abord
        $stmtProduits = $pdo->prepare("
            SELECT p.IdProd, p.Qtt, pr.NomProduit, pr.Prix, i.URL as image
            FROM panier p
            JOIN produit pr ON p.IdProd = pr.IdProduit
            LEFT JOIN (
                SELECT IdProduit, MIN(URL) as URL
                FROM imageprod
                GROUP BY IdProduit
            ) i ON pr.IdProduit = i.IdProduit
            WHERE p.IdCom = ?
        ");
        $stmtProduits->execute([$commandeId]);
        $produits = $stmtProduits->fetchAll(PDO::FETCH_ASSOC);
        
        // Si pas de résultats avec panier, essayer avec detailcommande
        if (empty($produits)) {
            $stmtProduits = $pdo->prepare("
                SELECT dc.IdProduit as IdProd, dc.Quantite as Qtt, pr.NomProduit, dc.PrixUnitaire as Prix, i.URL as image
                FROM detailcommande dc
                JOIN produit pr ON dc.IdProduit = pr.IdProduit
                LEFT JOIN (
                    SELECT IdProduit, MIN(URL) as URL
                    FROM imageprod
                    GROUP BY IdProduit
                ) i ON pr.IdProduit = i.IdProduit
                WHERE dc.IdCommande = ?
            ");
            $stmtProduits->execute([$commandeId]);
            $produits = $stmtProduits->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        error_log("Erreur récupération produits: " . $e->getMessage());
        $produits = [];
    }

    // Récupérer les informations du client
    try {
        $stmtClient = $pdo->prepare("SELECT * FROM client WHERE IdClient = ?");
        $stmtClient->execute([$clientId]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);
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
        <div class="max-w-[800px] mx-auto">
            <?php if (isset($error)): ?>
            <!-- Message d'erreur -->
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
            <!-- Page de confirmation normale -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden p-8 text-center">
                <div class="mb-6">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
                        <i class='bx bx-check text-4xl text-green-600'></i>
                    </div>
                    <h1 class="text-3xl font-medium text-textColor mb-2">Commande confirmée !</h1>
                    <p class="text-gray-600">
                        <?php if ($client): ?>
                            Merci <?php echo htmlspecialchars($client['Username'] ?? $client['username'] ?? $client['Nom'] ?? 'cher client'); ?> pour votre commande.
                        <?php else: ?>
                            Merci cher client pour votre commande.
                        <?php endif; ?>
                        Votre commande a été enregistrée avec succès.
                    </p>
                </div>
                
                <div class="border-t border-b border-gray-200 py-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-left">
                        <div>
                            <!-- SUPPRIMÉ: Numéro de commande -->
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
                        </div>
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="font-medium">Statut:</span>
                                <?php 
                                // Déterminer le statut avec une approche plus directe
                                $status = '';
                                
                                // Vérifier toutes les variantes possibles de statut
                                if (isset($commande['Status']) && !empty($commande['Status'])) {
                                    $status = $commande['Status'];
                                } elseif (isset($commande['Statut']) && !empty($commande['Statut'])) {
                                    $status = $commande['Statut'];
                                } elseif (isset($commande['status']) && !empty($commande['status'])) {
                                    $status = $commande['status'];
                                } elseif (isset($commande['statut']) && !empty($commande['statut'])) {
                                    $status = $commande['statut'];
                                } else {
                                    // Si aucun statut n'est trouvé, vérifier si c'est une commande récente
                                    $status = 'en_attente'; // Valeur par défaut pour les nouvelles commandes
                                }
                                
                                // Afficher le statut formaté
                                switch(strtolower(trim($status))) {
                                    case 'en_attente':
                                    case 'en attente':
                                    case 'pending':
                                    case '':
                                        echo '<span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">En attente</span>';
                                        break;
                                    case 'confirme':
                                    case 'confirmé':
                                    case 'confirmed':
                                        echo '<span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">Confirmée</span>';
                                        break;
                                    case 'expedie':
                                    case 'expédié':
                                    case 'shipped':
                                        echo '<span class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs">Expédiée</span>';
                                        break;
                                    case 'livre':
                                    case 'livré':
                                    case 'delivered':
                                        echo '<span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">Livrée</span>';
                                        break;
                                    case 'annule':
                                    case 'annulé':
                                    case 'cancelled':
                                        echo '<span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs">Annulée</span>';
                                        break;
                                    default:
                                        echo '<span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">En attente</span>';
                                }
                                ?>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Total:</span>
                                <span class="text-accent font-bold">
                                    <?php 
                                    // CORRIGÉ: Gestion flexible du total
                                    $total = 0;
                                    if (isset($commande['TotalPrix'])) {
                                        $total = $commande['TotalPrix'];
                                    } elseif (isset($commande['MontantTotal'])) {
                                        $total = $commande['MontantTotal'];
                                    } elseif (isset($commande['Total'])) {
                                        $total = $commande['Total'];
                                    } elseif (isset($commande['total'])) {
                                        $total = $commande['total'];
                                    }
                                    
                                    echo number_format($total, 2, ',', ' '); 
                                    ?> DA
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($produits)): ?>
                <div class="mb-8">
                    <h2 class="text-xl font-medium text-textColor mb-4 text-left">Détails de la commande</h2>
                    
                    <div class="space-y-4">
                        <?php foreach ($produits as $produit): ?>
                            <div class="flex items-center gap-4 text-left border-b border-gray-100 pb-4">
                                <img src="<?php echo $produit['image'] ?? 'images/placeholder.jpeg'; ?>" 
                                     alt="<?php echo htmlspecialchars($produit['NomProduit']); ?>" 
                                     class="w-16 h-16 object-cover rounded-md"
                                     onerror="this.src='images/placeholder.jpeg'">
                                <div class="flex-1">
                                    <h3 class="font-medium"><?php echo htmlspecialchars($produit['NomProduit']); ?></h3>
                                    <div class="flex justify-between mt-1">
                                        <span class="text-sm text-gray-600">Quantité: <?php echo $produit['Qtt']; ?></span>
                                        <span class="font-medium"><?php echo number_format($produit['Prix'] * $produit['Qtt'], 2, ',', ' '); ?> DA</span>
                                    </div>
                                    <span class="text-xs text-gray-500"><?php echo number_format($produit['Prix'], 2, ',', ' '); ?> DA / unité</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
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

    <!-- Pied de page -->
    <?php include 'footer.php'; ?>

</body>
</html>
