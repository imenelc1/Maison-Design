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

// Traitement de la commande - VERSION CORRIGÉE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Vérifier que les conditions sont acceptées
        if (!isset($_POST['terms']) || $_POST['terms'] !== 'on') {
            throw new Exception("Vous devez accepter les conditions générales de vente.");
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
        
        // Insérer la commande avec le statut - VERSION CORRIGÉE
        try {
            // D'abord, essayer de déterminer la structure de la table
            $stmt = $pdo->query("DESCRIBE commande");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "<!-- DEBUG: Colonnes table commande: " . implode(', ', $columns) . " -->";
            
            // Construire la requête selon les colonnes disponibles
            if (in_array('Status', $columns) && in_array('TotalPrix', $columns)) {
                // Structure avec Status et TotalPrix
                $stmt = $pdo->prepare("
                    INSERT INTO commande (IdClient, DateCommande, TotalPrix, Status) 
                    VALUES (?, NOW(), ?, 'en_attente')
                ");
                $result = $stmt->execute([$_SESSION['user_id'], $total]);
            } elseif (in_array('Statut', $columns) && in_array('MontantTotal', $columns)) {
                // Structure avec Statut et MontantTotal
                $stmt = $pdo->prepare("
                    INSERT INTO commande (IdClient, DateCommande, MontantTotal, Statut) 
                    VALUES (?, NOW(), ?, 'en_attente')
                ");
                $result = $stmt->execute([$_SESSION['user_id'], $total]);
            } elseif (in_array('Status', $columns)) {
                // Seulement Status disponible
                $stmt = $pdo->prepare("
                    INSERT INTO commande (IdClient, DateCommande, Status) 
                    VALUES (?, NOW(), 'en_attente')
                ");
                $result = $stmt->execute([$_SESSION['user_id']]);
                
                // Mettre à jour le total séparément si possible
                if (in_array('TotalPrix', $columns)) {
                    $commandeId = $pdo->lastInsertId();
                    $stmt = $pdo->prepare("UPDATE commande SET TotalPrix = ? WHERE IdCommande = ?");
                    $stmt->execute([$total, $commandeId]);
                }
            } elseif (in_array('Statut', $columns)) {
                // Seulement Statut disponible
                $stmt = $pdo->prepare("
                    INSERT INTO commande (IdClient, DateCommande, Statut) 
                    VALUES (?, NOW(), 'en_attente')
                ");
                $result = $stmt->execute([$_SESSION['user_id']]);
                
                // Mettre à jour le total séparément si possible
                if (in_array('MontantTotal', $columns)) {
                    $commandeId = $pdo->lastInsertId();
                    $stmt = $pdo->prepare("UPDATE commande SET MontantTotal = ? WHERE IdCommande = ?");
                    $stmt->execute([$total, $commandeId]);
                }
            } else {
                // Aucune colonne de statut trouvée, insertion basique
                $stmt = $pdo->prepare("
                    INSERT INTO commande (IdClient, DateCommande) 
                    VALUES (?, NOW())
                ");
                $result = $stmt->execute([$_SESSION['user_id']]);
                
                echo "<!-- ATTENTION: Aucune colonne de statut trouvée dans la table commande -->";
            }
            
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la création de la commande: " . $e->getMessage());
        }
        
        if (!$result) {
            throw new Exception("Erreur lors de la création de la commande dans la base de données.");
        }
        
        $commandeId = $pdo->lastInsertId();
        
        if (!$commandeId) {
            throw new Exception("Impossible de récupérer l'ID de la commande créée.");
        }
        
        // Nettoyer d'abord les anciens enregistrements pour cette commande (au cas où)
        try {
            $stmt = $pdo->prepare("DELETE FROM panier WHERE IdCom = ?");
            $stmt->execute([$commandeId]);
        } catch (PDOException $e) {
            // Ignorer si la table n'existe pas ou autre erreur
        }
        
        // Insérer les détails de la commande et mettre à jour le stock
        foreach ($_SESSION['panier'] as $item) {
            // Utiliser REPLACE INTO pour éviter les doublons
            try {
                $stmt = $pdo->prepare("
                    REPLACE INTO panier (IdCom, IdProd, Qtt) 
                    VALUES (?, ?, ?)
                ");
                $result = $stmt->execute([$commandeId, $item['id'], $item['quantite']]);
            } catch (PDOException $e) {
                try {
                    // Fallback vers detailcommande
                    $stmt = $pdo->prepare("
                        INSERT INTO detailcommande (IdCommande, IdProduit, Quantite, PrixUnitaire) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $result = $stmt->execute([$commandeId, $item['id'], $item['quantite'], $item['prix']]);
                } catch (PDOException $e2) {
                    throw new Exception("Impossible d'ajouter le produit '{$item['nom']}' à la commande. Erreur: " . $e2->getMessage());
                }
            }
            
            if (!$result) {
                throw new Exception("Erreur lors de l'ajout du produit '{$item['nom']}' à la commande.");
            }
            
            // Mettre à jour le stock avec vérification
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
        
        // CORRIGÉ: Redirection avec gestion d'erreurs
        echo "<script>console.log('Commande créée avec succès, ID: " . $commandeId . "');</script>";
        
        // Nettoyer la sortie avant la redirection
        ob_clean();
        
        // Rediriger vers votre fichier confirmation.php existant
        header('Location: confirmation.php?id=' . $commandeId);
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
        // Log l'erreur pour le debug
        error_log("Erreur checkout: " . $e->getMessage() . " - User ID: " . ($_SESSION['user_id'] ?? 'non connecté') . " - " . date('Y-m-d H:i:s'));
        
        // Debug: Afficher l'erreur pour voir ce qui se passe
        echo "<script>console.log('Erreur checkout: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// Récupérer les informations du client avec gestion des erreurs
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
                    <br><small>Consultez la console du navigateur pour plus de détails (F12)</small>
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
                        <h3 class="font-medium text-gray-800 mb-2">Informations de livraison</h3>
                        <div class="text-sm text-gray-700">
                            <?php
                            // Construire le nom complet
                            $nomComplet = '';
                            if (isset($client['Nom']) && isset($client['Prenom'])) {
                                $nomComplet = $client['Nom'] . ' ' . $client['Prenom'];
                            } elseif (isset($client['nom']) && isset($client['prenom'])) {
                                $nomComplet = $client['nom'] . ' ' . $client['prenom'];
                            } elseif (isset($client['username'])) {
                                $nomComplet = $client['username'];
                            } elseif (isset($client['Username'])) {
                                $nomComplet = $client['Username'];
                            }
                            
                            if ($nomComplet): ?>
                                <p class="font-medium"><?php echo htmlspecialchars($nomComplet); ?></p>
                            <?php endif; ?>
                            
                            <?php if (isset($client['Email']) && !empty($client['Email'])): ?>
                                <p>Email: <?php echo htmlspecialchars($client['Email']); ?></p>
                            <?php elseif (isset($client['email']) && !empty($client['email'])): ?>
                                <p>Email: <?php echo htmlspecialchars($client['email']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="space-y-6" id="checkout-form">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h3 class="font-medium text-blue-800 mb-2">Informations importantes</h3>
                            <ul class="text-sm text-blue-700 space-y-1">
                                <li>• Votre commande sera traitée dans les 24h</li>
                                <li>• Vous recevrez un email de confirmation</li>
                                <li>• La livraison se fait sous 3-5 jours ouvrables</li>
                                <li>• Paiement à la livraison</li>
                            </ul>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="terms" name="terms" required class="rounded">
                            <label for="terms" class="text-sm text-gray-700">
                                J'accepte les <a href="#" class="text-accent hover:underline">conditions générales de vente</a>
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

    <!-- Script amélioré pour éviter les doubles soumissions -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('checkout-form');
            const submitBtn = document.getElementById('submit-btn');
            const submitText = document.getElementById('submit-text');
            
            if (form && submitBtn) {
                form.addEventListener('submit', function(e) {
                    console.log('Formulaire soumis...');
                    
                    // Vérifier que les conditions sont acceptées
                    const termsCheckbox = document.getElementById('terms');
                    if (!termsCheckbox.checked) {
                        e.preventDefault();
                        alert('Vous devez accepter les conditions générales de vente.');
                        return false;
                    }
                    
                    // Désactiver le bouton pour éviter les doubles soumissions
                    submitBtn.disabled = true;
                    submitText.textContent = 'Traitement en cours...';
                    submitBtn.classList.add('opacity-50');
                    
                    // Ajouter un spinner
                    const icon = submitBtn.querySelector('i');
                    icon.className = 'bx bx-loader-alt animate-spin text-xl';
                    
                    console.log('Bouton désactivé, envoi en cours...');
                    
                    // Réactiver après 15 secondes en cas d'erreur de réseau
                    setTimeout(() => {
                        if (submitBtn.disabled) {
                            submitBtn.disabled = false;
                            submitText.textContent = 'Confirmer la commande';
                            submitBtn.classList.remove('opacity-50');
                            icon.className = 'bx bx-check-circle text-xl';
                            console.log('Timeout atteint, bouton réactivé');
                        }
                    }, 15000);
                });
            }
        });
    </script>
</body>
</html>
