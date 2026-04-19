<?php $pageTitle = 'Commandes Admin — Maison Design'; ?>

<div class="min-h-screen pt-8 pb-16 px-4 md:px-[10%] bg-background">
    <div class="max-w-[1200px] mx-auto">
        <h1 class="text-3xl font-bold text-textColor mb-8">Gestion des Commandes</h1>

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-secondary/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-medium text-textColor">ID</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-textColor">Client</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-textColor hidden md:table-cell">Date</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-textColor">Statut</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-textColor">Total</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-textColor">Actions</th>
                    </tr>
                </thead>
                <tbody id="orders-table-body">
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            Chargement...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const statusColors = {
    'en attente': 'bg-yellow-100 text-yellow-800',
    'expédié':    'bg-blue-100 text-blue-800',
    'livré':      'bg-green-100 text-green-800',
    'annulé':     'bg-red-100 text-red-800',
};

async function loadOrders() {
    const res  = await fetch('/api/admin/commandes', {method: 'POST'});

    if (!res.ok) return;
    const data = await res.json();
    if (!data.success) return;

    const tbody = document.getElementById('orders-table-body');
    tbody.innerHTML = data.data.map(o => `
        <tr class="border-t border-gray-100 hover:bg-gray-50">
            <td class="px-6 py-4">#${o.id}</td>
            <td class="px-6 py-4">${o.client}</td>
            <td class="px-6 py-4 hidden md:table-cell">${o.date}</td>
            <td class="px-6 py-4">
                <span class="px-2 py-1 rounded-full text-xs ${statusColors[o.statut] || 'bg-gray-100 text-gray-800'}">
                    ${o.statut}
                </span>
            </td>
            <td class="px-6 py-4">${o.total} DA</td>
            <td class="px-6 py-4">
                <select onchange="changeStatus(${o.id}, this.value)"
                        class="text-sm border rounded px-2 py-1">
                    <option value="en attente"  ${o.statut === 'en attente' ? 'selected' : ''}>En attente</option>
                    <option value="expédié"     ${o.statut === 'expédié'    ? 'selected' : ''}>Expédié</option>
                    <option value="livré"       ${o.statut === 'livré'      ? 'selected' : ''}>Livré</option>
                    <option value="annulé"      ${o.statut === 'annulé'     ? 'selected' : ''}>Annulé</option>
                </select>
            </td>
        </tr>
    `).join('');
}

async function changeStatus(id, statut) {
    await fetch('/api/admin/commandes/statut', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id=${id}&statut=${encodeURIComponent(statut)}`
    });
    loadOrders();
}

loadOrders();
</script>