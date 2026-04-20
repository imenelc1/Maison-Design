<?php $pageTitle = 'Commande confirmée — Maison Design'; ?>

<div class="min-h-screen pt-8 pb-16 px-4 md:px-[10%] bg-background">
    <div class="max-w-[800px] mx-auto">
        <div class="bg-white rounded-xl shadow-md p-8 text-center">

            <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-6">
                <i class='bx bx-check text-4xl text-green-600'></i>
            </div>

            <h1 class="text-3xl font-medium text-textColor mb-2">Commande confirmée !</h1>
            <p class="text-gray-600 mb-8">
                Merci pour votre commande. Elle a été enregistrée avec succès.
            </p>

            <div class="border-t border-b border-gray-200 py-6 mb-8 text-left">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="font-medium">Numéro de commande :</p>
                        <p class="text-accent">#<?php echo str_pad($commande->getId(), 6, '0', STR_PAD_LEFT); ?></p>
                    </div>
                    <div>
                        <p class="font-medium">Total :</p>
                        <p class="text-accent font-bold"><?php echo $commande->getTotalFormate(); ?></p>
                    </div>
                    <div>
                        <p class="font-medium">Statut :</p>
                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">
                            En attente
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8 text-left">
                <h3 class="font-medium text-blue-800 mb-2">Prochaines étapes</h3>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• Votre commande sera préparée dans les 24h</li>
                    <li>• Vous serez contacté pour organiser la livraison</li>
                    <li>• Paiement à effectuer à la livraison</li>
                </ul>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/compte"
                   class="px-6 py-3 bg-primary text-textColor rounded-full hover:bg-primary/80 transition-colors">
                    Voir mes commandes
                </a>
                <a href="/categories"
                   class="px-6 py-3 bg-accent text-white rounded-full hover:bg-accent/90 transition-colors">
                    Continuer mes achats
                </a>
            </div>
        </div>
    </div>
</div>