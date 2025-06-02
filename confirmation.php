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

// Vérifier que la commande appartient bien à l'utilisateur connecté
$stmt = $pdo->prepare("SELECT * FROM commande WHERE IdCommande = ? AND IdClient = ?");
$stmt->execute([$commandeId, $clientId]);
$commande = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$commande) {
    header("Location: client.php");
    exit();
}

// Récupérer les produits de la commande
$stmtProduits = $pdo->prepare("
    SELECT p.IdProd, p.Qtt, pr.NomProduit, pr.Prix, i.URL as image
    FROM panier p
    JOIN produit pr ON p.IdProd = pr.IdProduit
    LEFT JOIN imageprod i ON pr.IdProduit = i.IdProduit
    WHERE p.IdCom = ?
    GROUP BY p.IdProd
");
$stmtProduits->execute([$commandeId]);
$produits = $stmtProduits->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les informations de livraison
$stmtLivraison = $pdo->prepare("SELECT * FROM livraison WHERE IdComm = ?");
$stmtLivraison->execute([$commandeId]);
$livraison = $stmtLivraison->fetch(PDO::FETCH_ASSOC);
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
            <div class="bg-white rounded-xl shadow-md overflow-hidden p-8 text-center">
                <div class="mb-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                        <i class='bx bx-check text-3xl text-green-600'></i>
                    </div>
                    <h1 class="text-3xl font-medium text-textColor">Commande confirmée !</h1>
                    <p class="text-gray-600 mt-2">Merci pour votre commande. Votre commande a été enregistrée avec succès.</p>
                </div>
                
                <div class="border-t border-b border-gray-200 py-6 mb-6">
                    <div class="flex justify-between mb-2">
                        <span class="font-medium">Numéro de commande:</span>
                        <span>#<?php echo $commande['IdCommande']; ?></span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span class="font-medium">Date:</span>
                        <span><?php echo date('d/m/Y à H:i', strtotime($commande['DateCommande'])); ?></span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span class="font-medium">Statut:</span>
                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs"><?php echo $commande['Status']; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Total:</span>
                        <span class="text-accent font-bold"><?php echo number_format($commande['TotalPrix'], 2, ',', ' '); ?> DA</span>
                    </div>
                </div>
                
                <div class="mb-8">
                    <h2 class="text-xl font-medium text-textColor mb-4 text-left">Détails de la commande</h2>
                    
                    <div class="space-y-4">
                        <?php foreach ($produits as $produit): ?>
                            <div class="flex items-center gap-4 text-left">
                                <img src="<?php echo $produit['image'] ?? 'images/placeholder.jpeg'; ?>" alt="<?php echo htmlspecialchars($produit['NomProduit']); ?>" class="w-16 h-16 object-cover rounded-md">
                                <div class="flex-1">
                                    <h3 class="font-medium"><?php echo htmlspecialchars($produit['NomProduit']); ?></h3>
                                    <div class="flex justify-between mt-1">
                                        <span class="text-sm text-gray-600">Qté: <?php echo $produit['Qtt']; ?></span>
                                        <span class="font-medium"><?php echo number_format($produit['Prix'] * $produit['Qtt'], 2, ',', ' '); ?> DA</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
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
        </div>
    </main>

    <!-- Pied de page -->
    <?php include 'footer.php'; ?>

</body>
</html>
