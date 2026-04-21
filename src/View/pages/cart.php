<?php $pageTitle = 'Mon Panier — Maison Design'; ?>

<div class="min-h-screen pt-8 pb-16 px-4 md:px-[10%] bg-background">
    <div class="max-w-[1200px] mx-auto">
        <h1 class="text-3xl md:text-4xl text-textColor mb-8"
            style="font-family: Frunchy, serif">
            Mon Panier
        </h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Articles -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-md p-6">

                    <?php if (empty($items)): ?>
                    <div class="text-center py-8">
                        <i class='bx bx-cart text-6xl text-gray-300'></i>
                        <p class="text-gray-500 mt-4 mb-6">Votre panier est vide</p>
                        <a href="/categories"
                           class="px-6 py-3 bg-accent text-white rounded-full hover:bg-accent/90">
                            Découvrir nos produits
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($items as $item): ?>
                        <div class="flex items-center gap-4 py-4 border-b border-gray-100">
                            <img src="/<?php echo htmlspecialchars($item['image']); ?>"
                                 alt="<?php echo htmlspecialchars($item['nom']); ?>"
                                 class="w-20 h-20 object-cover rounded-md"
                                 onerror="this.src='/images/placeholder.jpeg'">
                            <div class="flex-1">
                                <h3 class="font-medium"><?php echo htmlspecialchars($item['nom']); ?></h3>
                                <div class="flex items-center justify-between mt-2">
                                    <!-- Quantité -->
                                    <div class="flex items-center gap-2">
                                        <form method="POST" action="/panier/modifier">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="produitId" value="<?php echo $item['id']; ?>">
                                            <input type="hidden" name="delta" value="-1">
                                            <button class="w-6 h-6 bg-gray-100 rounded flex items-center justify-center hover:bg-accent hover:text-white">
                                                <i class='bx bx-minus'></i>
                                            </button>
                                        </form>
                                        <span class="w-8 text-center"><?php echo $item['quantite']; ?></span>
                                        <form method="POST" action="/panier/modifier">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="produitId" value="<?php echo $item['id']; ?>">
                                            <input type="hidden" name="delta" value="1">
                                            <button class="w-6 h-6 bg-gray-100 rounded flex items-center justify-center hover:bg-accent hover:text-white">
                                                <i class='bx bx-plus'></i>
                                            </button>
                                        </form>
                                    </div>
                                    <span class="font-medium">
                                        <?php echo number_format($item['prix'] * $item['quantite'], 2, ',', ' '); ?> DA
                                    </span>
                                </div>
                            </div>
                            <form method="POST" action="/panier/supprimer">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="produitId" value="<?php echo $item['id']; ?>">
                                <button class="text-gray-400 hover:text-red-500 p-2">
                                    <i class='bx bx-trash text-xl'></i>
                                </button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="flex justify-between mt-6">
                        <a href="/categories" class="text-accent hover:underline flex items-center gap-1">
                            <i class='bx bx-arrow-back'></i> Continuer mes achats
                        </a>
                        <a href="/panier/vider" class="text-gray-500 hover:text-red-500 flex items-center gap-1">
                            <i class='bx bx-trash'></i> Vider le panier
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Résumé -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-md p-6 sticky top-24">
                    <h2 class="text-2xl text-accent mb-4">Résumé</h2>

                    <div class="space-y-2 mb-6">
                        <div class="flex justify-between">
                            <span>Sous-total :</span>
                            <span class="font-medium">
                                <?php echo number_format($sousTotal, 2, ',', ' '); ?> DA
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span>Livraison :</span>
                            <span class="font-medium">
                                <?php echo number_format($livraison, 2, ',', ' '); ?> DA
                            </span>
                        </div>
                        <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-2">
                            <span>Total :</span>
                            <span class="text-accent">
                                <?php echo number_format($total, 2, ',', ' '); ?> DA
                            </span>
                        </div>
                    </div>

                    <?php if (!empty($items)): ?>
                    <a href="/checkout"
                       class="w-full px-6 py-3 bg-accent text-white rounded-full hover:bg-accent/90 transition-colors flex items-center justify-center gap-2">
                        <i class='bx bx-check-circle text-xl'></i>
                        Passer la commande
                    </a>
                    <?php else: ?>
                    <button disabled
                            class="w-full px-6 py-3 bg-gray-400 text-white rounded-full cursor-not-allowed">
                        Passer la commande
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>