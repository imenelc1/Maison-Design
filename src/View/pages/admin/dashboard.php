<?php $pageTitle = 'Dashboard Admin — Maison Design'; ?>

<div class="min-h-screen pt-8 pb-16 px-4 md:px-[10%] bg-background">
    <div class="max-w-[1200px] mx-auto">
        <h1 class="text-3xl font-bold text-textColor mb-8">Tableau de bord</h1>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8" id="stats-grid">
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-accent/10 rounded-full flex items-center justify-center">
                        <i class='bx bx-package text-2xl text-accent'></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Produits</p>
                        <p class="text-3xl font-bold text-textColor" id="stat-produits">—</p>
                    </div>
                </div>
                <a href="/admin/produits" class="block mt-4 text-sm text-accent hover:underline">
                    Gérer les produits →
                </a>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-accent/10 rounded-full flex items-center justify-center">
                        <i class='bx bx-cart text-2xl text-accent'></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Commandes</p>
                        <p class="text-3xl font-bold text-textColor" id="stat-commandes">—</p>
                    </div>
                </div>
                <a href="/admin/commandes" class="block mt-4 text-sm text-accent hover:underline">
                    Gérer les commandes →
                </a>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-accent/10 rounded-full flex items-center justify-center">
                        <i class='bx bx-user text-2xl text-accent'></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Clients</p>
                        <p class="text-3xl font-bold text-textColor" id="stat-clients">—</p>
                    </div>
                </div>
                <a href="/admin/clients" class="block mt-4 text-sm text-accent hover:underline">
                    Gérer les clients →
                </a>
            </div>
        </div>

        <!-- Commandes récentes -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold text-textColor mb-4">Dernières commandes</h2>
            <div id="recent-orders">
                <div class="text-center py-4 text-gray-400">Chargement...</div>
            </div>
        </div>
    </div>
</div>

<script>
async function loadStats() {
    const [prodRes, cmdRes, cliRes] = await Promise.all([
        fetch('/api/admin/produits', {method: 'POST'}),
        fetch('/api/admin/commandes', {method: 'POST'}),
        fetch('/api/admin/clients',   {method: 'POST'}),
    ]);

    const [prodData, cmdData, cliData] = await Promise.all([
        prodRes.json(), cmdRes.json(), cliRes.json()
    ]);

    if (prodData.success) document.getElementById('stat-produits').textContent  = prodData.data.length;
    if (cmdData.success)  document.getElementById('stat-commandes').textContent = cmdData.data.length;
    if (cliData.success)  document.getElementById('stat-clients').textContent   = cliData.data.length;

    // Dernières commandes (5 max)
    if (cmdData.success) {
        const recent = cmdData.data.slice(0, 5);
        const statusColors = {
            'en attente': 'bg-yellow-100 text-yellow-800',
            'expédié':    'bg-blue-100 text-blue-800',
            'livré':      'bg-green-100 text-green-800',
            'annulé':     'bg-red-100 text-red-800',
        };

        document.getElementById('recent-orders').innerHTML = recent.length === 0
            ? '<p class="text-gray-400 text-center py-4">Aucune commande</p>'
            : `<table class="min-w-full">
                <thead>
                    <tr class="text-left text-sm text-gray-500 border-b">
                        <th class="pb-3">ID</th>
                        <th class="pb-3">Client</th>
                        <th class="pb-3 hidden md:table-cell">Date</th>
                        <th class="pb-3">Statut</th>
                        <th class="pb-3">Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${recent.map(o => `
                        <tr class="border-b border-gray-50 hover:bg-gray-50">
                            <td class="py-3 text-sm">#${o.id}</td>
                            <td class="py-3 text-sm">${o.client}</td>
                            <td class="py-3 text-sm hidden md:table-cell">${o.date}</td>
                            <td class="py-3">
                                <span class="px-2 py-1 rounded-full text-xs ${statusColors[o.statut] || 'bg-gray-100 text-gray-800'}">
                                    ${o.statut}
                                </span>
                            </td>
                            <td class="py-3 text-sm font-medium">${o.total} DA</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>`;
    }
}

loadStats();
</script>