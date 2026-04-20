<?php $pageTitle = 'Dashboard Admin — Maison Design'; ?>

<div class="min-h-screen pt-8 pb-16 px-4 md:px-[10%] bg-background">
    <div class="max-w-[1200px] mx-auto">
        <h1 class="text-3xl font-bold text-textColor mb-8">Tableau de bord</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <a href="/admin/produits"
               class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-accent/10 rounded-full flex items-center justify-center">
                        <i class='bx bx-package text-2xl text-accent'></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Produits</p>
                        <p class="text-2xl font-bold text-textColor">Gérer</p>
                    </div>
                </div>
            </a>

            <a href="/admin/commandes"
               class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-accent/10 rounded-full flex items-center justify-center">
                        <i class='bx bx-cart text-2xl text-accent'></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Commandes</p>
                        <p class="text-2xl font-bold text-textColor">Gérer</p>
                    </div>
                </div>
            </a>

            <a href="/admin/clients"
               class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-accent/10 rounded-full flex items-center justify-center">
                        <i class='bx bx-user text-2xl text-accent'></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Clients</p>
                        <p class="text-2xl font-bold text-textColor">Gérer</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>