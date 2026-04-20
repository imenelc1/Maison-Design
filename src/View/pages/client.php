<?php $pageTitle = 'Mon Compte — Maison Design'; ?>

<div class="min-h-screen pt-8 pb-16 px-4 md:px-[10%] bg-background">
    <div class="max-w-[1200px] mx-auto">

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-textColor mb-2">Mon Compte</h1>
            <p class="text-textColor/70">
                Bienvenue, <?php echo htmlspecialchars($_SESSION['prenom'] ?? ''); ?>
                <?php echo htmlspecialchars($_SESSION['nom'] ?? ''); ?>
            </p>
        </div>

        <!-- Onglets -->
        <div class="mb-8 flex flex-wrap gap-2">
            <button class="tab-btn px-4 py-2 rounded-full transition-colors
                <?php echo $activeTab === 'profile' ? 'bg-accent text-white' : 'bg-primary text-textColor hover:bg-primary/50'; ?>"
                    data-tab="profile">
                <i class='bx bx-user-circle mr-1'></i> Profil
            </button>
            <button class="tab-btn px-4 py-2 rounded-full transition-colors
                <?php echo $activeTab === 'orders' ? 'bg-accent text-white' : 'bg-primary text-textColor hover:bg-primary/50'; ?>"
                    data-tab="orders">
                <i class='bx bx-package mr-1'></i> Mes Commandes
            </button>
        </div>

        <!-- Contenu onglets -->
        <!-- Profil -->
        <div id="profile-tab"
             class="<?php echo $activeTab === 'profile' ? 'block' : 'hidden'; ?>">
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-2xl text-accent mb-6">Informations personnelles</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Prénom</p>
                        <p class="font-medium"><?php echo htmlspecialchars($_SESSION['prenom'] ?? ''); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Nom</p>
                        <p class="font-medium"><?php echo htmlspecialchars($_SESSION['nom'] ?? ''); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Email</p>
                        <p class="font-medium"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Téléphone</p>
                        <p class="font-medium"><?php echo htmlspecialchars($_SESSION['telephone'] ?? 'Non renseigné'); ?></p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-500 mb-1">Adresse</p>
                        <p class="font-medium"><?php echo htmlspecialchars($_SESSION['adresse'] ?? 'Non renseignée'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Commandes -->
        <div id="orders-tab"
             class="<?php echo $activeTab === 'orders' ? 'block' : 'hidden'; ?>">
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-2xl text-accent mb-6">
                    Mes commandes (<?php echo count($orders ?? []); ?>)
                </h2>

                <?php if (empty($orders)): ?>
                    <div class="text-center py-12">
                        <i class='bx bx-package text-6xl text-gray-300'></i>
                        <p class="text-gray-500 mt-4 mb-4">Vous n'avez pas encore passe de commande.</p>
                        <a href="/categories"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-accent text-white rounded-full hover:bg-accent/80">
                            <i class='bx bx-shopping-bag'></i> Voir les produits
                        </a>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($orders as $order): ?>
                            <?php
                            $status = strtolower($order->getStatus());
                            $statusClasses = match ($status) {
                                'livre', 'livré' => 'bg-green-100 text-green-700',
                                'expedie', 'expediee', 'expédié', 'expédiée' => 'bg-blue-100 text-blue-700',
                                'annule', 'annulee', 'annulé', 'annulée' => 'bg-red-100 text-red-700',
                                default => 'bg-yellow-100 text-yellow-700',
                            };
                            ?>
                            <div class="border border-gray-200 rounded-xl p-4">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-textColor">
                                            Commande #<?php echo str_pad((string)$order->getId(), 6, '0', STR_PAD_LEFT); ?>
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            Passee le <?php echo htmlspecialchars($order->getDateCommande()); ?>
                                        </p>
                                    </div>

                                    <div class="flex flex-col md:items-end gap-2">
                                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium <?php echo $statusClasses; ?>">
                                            <?php echo htmlspecialchars($order->getStatus()); ?>
                                        </span>
                                        <span class="font-bold text-accent">
                                            <?php echo htmlspecialchars($order->getTotalFormate()); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const tab = this.dataset.tab;

        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('bg-accent', 'text-white');
            b.classList.add('bg-primary', 'text-textColor');
        });
        this.classList.add('bg-accent', 'text-white');
        this.classList.remove('bg-primary', 'text-textColor');

        document.getElementById('profile-tab').classList.add('hidden');
        document.getElementById('orders-tab').classList.add('hidden');
        document.getElementById(tab + '-tab').classList.remove('hidden');
    });
});
</script>
