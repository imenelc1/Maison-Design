<?php
// Test simple pour identifier le problème exact
session_start();

// Initialiser le panier s'il n'existe pas
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Calculer le nombre d'articles
$nombreArticles = 0;
foreach ($_SESSION['panier'] as $item) {
    $nombreArticles += $item['quantite'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Panier Simple</title>
    <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'accent': '#8E9675'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Test Panier - Diagnostic</h1>
        
        <!-- Compteur panier -->
        <div class="bg-white p-6 rounded-lg shadow mb-6">
            <h2 class="text-xl font-semibold mb-4">Compteur Panier</h2>
            <div class="flex items-center gap-4 mb-4">
                <span>Articles dans le panier: </span>
                <span id="cart-counter" class="bg-accent text-white px-3 py-1 rounded-full font-bold">
                    <?php echo $nombreArticles; ?>
                </span>
            </div>
            
            <!-- Boutons de test -->
            <div class="space-y-2">
                <button onclick="testAddProduct(36)" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    🛒 Ajouter Produit ID 36 (Table Prestige)
                </button>
                <button onclick="testAddProduct(40)" class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    🛒 Ajouter Produit ID 40 (Canapé Élégance)
                </button>
                <button onclick="updateCounter()" class="w-full bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                    🔄 Actualiser Compteur Manuellement
                </button>
                <button onclick="clearCart()" class="w-full bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                    🗑️ Vider le Panier
                </button>
            </div>
        </div>

        <!-- Console de debug -->
        <div class="bg-gray-900 text-green-400 p-4 rounded-lg">
            <h3 class="text-lg font-semibold mb-2">Console de Debug</h3>
            <div id="debug-log" class="font-mono text-sm h-64 overflow-y-auto bg-black p-2 rounded">
                <div class="text-yellow-400">[INIT] Page chargée - Articles actuels: <?php echo $nombreArticles; ?></div>
            </div>
        </div>
    </div>

    <script>
        // Fonction de log
        function log(message, type = 'info') {
            const debugLog = document.getElementById('debug-log');
            const timestamp = new Date().toLocaleTimeString();
            const colors = {
                'info': 'text-green-400',
                'success': 'text-blue-400', 
                'error': 'text-red-400',
                'warning': 'text-yellow-400'
            };
            
            const logEntry = document.createElement('div');
            logEntry.className = colors[type] || 'text-green-400';
            logEntry.textContent = `[${timestamp}] ${message}`;
            
            debugLog.appendChild(logEntry);
            debugLog.scrollTop = debugLog.scrollHeight;
            
            console.log(`[CART_DEBUG] ${message}`);
        }

        // Test d'ajout de produit
        async function testAddProduct(productId) {
            log(`🚀 Début ajout produit ${productId}`, 'info');
            
            try {
                // Étape 1: Préparer la requête
                log(`📤 Envoi requête vers cart_actions.php`, 'info');
                
                const response = await fetch('php/cart_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `action=ajouter&produitId=${productId}&quantite=1`
                });

                log(`📥 Réponse reçue - Status: ${response.status}`, response.ok ? 'success' : 'error');

                // Étape 2: Parser la réponse
                const responseText = await response.text();
                log(`📄 Réponse brute: ${responseText.substring(0, 200)}...`, 'info');

                let data;
                try {
                    data = JSON.parse(responseText);
                    log(`✅ JSON parsé avec succès`, 'success');
                } catch (parseError) {
                    log(`❌ Erreur parsing JSON: ${parseError.message}`, 'error');
                    log(`📄 Réponse complète: ${responseText}`, 'error');
                    return;
                }

                // Étape 3: Traiter la réponse
                if (data.success) {
                    log(`✅ Succès: ${data.message}`, 'success');
                    log(`🔢 Nouveau compteur: ${data.cartCount}`, 'success');
                    
                    // ÉTAPE CRITIQUE: Mettre à jour le compteur
                    updateCounterDisplay(data.cartCount);
                    
                } else {
                    log(`❌ Échec: ${data.message}`, 'error');
                }

            } catch (error) {
                log(`💥 Exception: ${error.message}`, 'error');
            }
        }

        // Fonction pour mettre à jour l'affichage du compteur
        function updateCounterDisplay(count) {
            log(`🔄 Mise à jour affichage compteur: ${count}`, 'info');
            
            const counter = document.getElementById('cart-counter');
            if (counter) {
                const oldValue = counter.textContent;
                counter.textContent = count;
                log(`📊 Compteur mis à jour: ${oldValue} → ${count}`, 'success');
            } else {
                log(`❌ Élément cart-counter non trouvé!`, 'error');
            }
        }

        // Fonction pour actualiser le compteur
        async function updateCounter() {
            log(`🔄 Actualisation manuelle du compteur`, 'info');
            
            try {
                const response = await fetch('php/get_cart_count.php');
                const data = await response.json();
                
                if (data.success) {
                    log(`✅ Compteur récupéré: ${data.count}`, 'success');
                    updateCounterDisplay(data.count);
                } else {
                    log(`❌ Erreur récupération compteur`, 'error');
                }
            } catch (error) {
                log(`💥 Erreur actualisation: ${error.message}`, 'error');
            }
        }

        // Fonction pour vider le panier
        async function clearCart() {
            log(`🗑️ Vidage du panier`, 'warning');
            
            try {
                const response = await fetch('php/cart_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'action=vider'
                });

                const data = await response.json();
                
                if (data.success) {
                    log(`✅ Panier vidé avec succès`, 'success');
                    updateCounterDisplay(0);
                } else {
                    log(`❌ Erreur vidage panier`, 'error');
                }
            } catch (error) {
                log(`💥 Erreur vidage: ${error.message}`, 'error');
            }
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', () => {
            log(`🎯 DOM chargé - Initialisation terminée`, 'success');
        });
    </script>
</body>
</html>
