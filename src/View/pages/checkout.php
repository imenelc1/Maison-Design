<?php $pageTitle = 'Finaliser la commande — Maison Design'; ?>

<div class="min-h-screen pt-8 pb-16 px-4 md:px-[10%] bg-background">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl md:text-4xl text-textColor mb-8"
            style="font-family: Frunchy, serif">
            Finaliser la commande
        </h1>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            <!-- Résumé commande -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-2xl text-accent mb-4">Résumé</h2>

                <div class="space-y-4 mb-6">
                    <?php foreach ($items as $item): ?>
                    <div class="flex items-center gap-4 py-2 border-b border-gray-100">
                        <img src="/<?php echo htmlspecialchars($item['image']); ?>"
                             alt="<?php echo htmlspecialchars($item['nom']); ?>"
                             class="w-16 h-16 object-cover rounded-md"
                             onerror="this.src='/images/placeholder.jpeg'">
                        <div class="flex-1">
                            <h3 class="font-medium"><?php echo htmlspecialchars($item['nom']); ?></h3>
                            <p class="text-gray-600 text-sm">Qté : <?php echo $item['quantite']; ?></p>
                        </div>
                        <span class="font-medium">
                            <?php echo number_format($item['prix'] * $item['quantite'], 2, ',', ' '); ?> DA
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="space-y-2 border-t border-gray-200 pt-4">
                    <div class="flex justify-between">
                        <span>Sous-total :</span>
                        <span><?php echo number_format($sousTotal, 2, ',', ' '); ?> DA</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Livraison :</span>
                        <span><?php echo number_format($livraison, 2, ',', ' '); ?> DA</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold">
                        <span>Total :</span>
                        <span class="text-accent"><?php echo number_format($total, 2, ',', ' '); ?> DA</span>
                    </div>
                </div>
            </div>

            <!-- Formulaire confirmation -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-2xl text-accent mb-4">Confirmer</h2>

                <form method="POST" action="/checkout" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Adresse de livraison *
                        </label>
                        <textarea name="adresse_livraison" required rows="3"
                                  placeholder="Entrez votre adresse complète..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                        ><?php echo htmlspecialchars($_SESSION['adresse'] ?? ''); ?></textarea>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="font-medium text-blue-800 mb-2">Informations</h3>
                        <ul class="text-sm text-blue-700 space-y-1">
                            <li>• Commande traitée dans les 24h</li>
                            <li>• Livraison sous 3-5 jours ouvrables</li>
                            <li>• Paiement à la livraison (espèces)</li>
                        </ul>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="terms" name="terms" required class="rounded">
                        <label for="terms" class="text-sm text-gray-700">
                            J'accepte les conditions générales de vente
                        </label>
                    </div>

                    <button type="submit"
                            class="w-full px-6 py-3 bg-accent text-white rounded-full hover:bg-accent/90 transition-colors flex items-center justify-center gap-2">
                        <i class='bx bx-check-circle text-xl'></i>
                        Confirmer la commande
                    </button>
                </form>

                <div class="mt-4 text-center">
                    <a href="/panier" class="text-gray-600 hover:text-accent flex items-center justify-center gap-1">
                        <i class='bx bx-arrow-back'></i> Retour au panier
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>