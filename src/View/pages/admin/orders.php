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
                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">Chargement...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal détails commande -->
<div id="order-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-lg bg-white rounded-2xl shadow-xl p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-textColor">Détails commande <span id="modal-order-id" class="text-accent"></span></h2>
            <button onclick="closeOrderModal()" class="text-2xl text-gray-400 hover:text-gray-600">&times;</button>
        </div>

        <!-- Infos générales -->
        <div class="grid grid-cols-2 gap-4 mb-6 p-4 bg-primary/20 rounded-xl text-sm">
            <div>
                <p class="text-gray-400 text-xs">Client</p>
                <p class="font-medium" id="modal-order-client"></p>
            </div>
            <div>
                <p class="text-gray-400 text-xs">Date</p>
                <p class="font-medium" id="modal-order-date"></p>
            </div>
            <div>
                <p class="text-gray-400 text-xs">Statut</p>
                <span id="modal-order-statut" class="px-2 py-1 rounded-full text-xs"></span>
            </div>
            <div>
                <p class="text-gray-400 text-xs">Total</p>
                <p class="font-bold text-accent" id="modal-order-total"></p>
            </div>
        </div>

        <!-- Changer statut -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-textColor mb-2">Changer le statut</label>
            <div class="flex gap-2">
                <select id="modal-statut-select" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                    <option value="en attente">En attente</option>
                    <option value="expédié">Expédié</option>
                    <option value="livré">Livré</option>
                    <option value="annulé">Annulé</option>
                </select>
                <button onclick="updateStatus()" class="px-4 py-2 bg-accent text-white rounded-lg text-sm hover:bg-accent/80">
                    Mettre à jour
                </button>
            </div>
        </div>

        <!-- Articles -->
        <div>
            <h3 class="font-medium text-textColor mb-3">Articles commandés</h3>
            <div id="modal-order-items" class="space-y-2"></div>
        </div>

        <button onclick="closeOrderModal()"
                class="w-full mt-6 px-4 py-2 border border-gray-300 rounded-full hover:bg-gray-50 text-sm">
            Fermer
        </button>
    </div>
</div>

<script>
const statusColors = {
    'en attente': 'bg-yellow-100 text-yellow-800',
    'expédié':    'bg-blue-100 text-blue-800',
    'livré':      'bg-green-100 text-green-800',
    'annulé':     'bg-red-100 text-red-800',
};

let currentOrderId = null;
let allOrders      = [];

async function loadOrders() {
    const res  = await fetch('/api/admin/commandes', {method: 'POST'});
    const data = await res.json();
    if (!data.success) return;

    allOrders = data.data;

    const tbody = document.getElementById('orders-table-body');
    tbody.innerHTML = data.data.map(o => `
        <tr class="border-t border-gray-100 hover:bg-gray-50 cursor-pointer" onclick="openOrderModal(${o.id})">
            <td class="px-6 py-4 text-sm">#${o.id}</td>
            <td class="px-6 py-4 text-sm">${o.client}</td>
            <td class="px-6 py-4 text-sm hidden md:table-cell">${o.date}</td>
            <td class="px-6 py-4">
                <span class="px-2 py-1 rounded-full text-xs ${statusColors[o.statut] || 'bg-gray-100 text-gray-800'}">
                    ${o.statut}
                </span>
            </td>
            <td class="px-6 py-4 text-sm font-medium">${o.total} DA</td>
            <td class="px-6 py-4">
                <button onclick="event.stopPropagation(); openOrderModal(${o.id})"
                        class="px-3 py-1 bg-accent/10 text-accent rounded-full text-sm hover:bg-accent/20">
                    <i class='bx bx-detail'></i> Détails
                </button>
            </td>
        </tr>
    `).join('');
}

async function openOrderModal(id) {
    currentOrderId = id;
    const order = allOrders.find(o => o.id == id);
    if (!order) return;

    document.getElementById('modal-order-id').textContent     = `#${order.id}`;
    document.getElementById('modal-order-client').textContent = order.client;
    document.getElementById('modal-order-date').textContent   = order.date;
    document.getElementById('modal-order-total').textContent  = `${order.total} DA`;

    const statutEl = document.getElementById('modal-order-statut');
    statutEl.textContent  = order.statut;
    statutEl.className    = `px-2 py-1 rounded-full text-xs ${statusColors[order.statut] || 'bg-gray-100 text-gray-800'}`;

    document.getElementById('modal-statut-select').value = order.statut;

    // Charger les articles via l'API panier
    document.getElementById('modal-order-items').innerHTML = `
        <div class="text-center py-4 text-gray-400 text-sm">
            <i class='bx bx-loader-alt animate-spin'></i> Chargement des articles...
        </div>`;

    try {
        const res  = await fetch(`/api/admin/commande-items?id=${id}`, {method: 'POST'});
        if (res.ok) {
            const data = await res.json();
            if (data.success && data.items.length > 0) {
                document.getElementById('modal-order-items').innerHTML = data.items.map(item => `
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <p class="text-sm font-medium">${item.nom}</p>
                            <p class="text-xs text-gray-500">Qté : ${item.quantite} × ${item.prix} DA</p>
                        </div>
                        <p class="text-sm font-bold text-accent">${item.total} DA</p>
                    </div>
                `).join('');
            } else {
                document.getElementById('modal-order-items').innerHTML =
                    '<p class="text-sm text-gray-400 text-center py-2">Aucun article trouvé</p>';
            }
        }
    } catch(e) {
        document.getElementById('modal-order-items').innerHTML =
            '<p class="text-sm text-gray-400 text-center py-2">Articles non disponibles</p>';
    }

    const modal = document.getElementById('order-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeOrderModal() {
    document.getElementById('order-modal').classList.add('hidden');
    document.getElementById('order-modal').classList.remove('flex');
}

async function updateStatus() {
    const statut = document.getElementById('modal-statut-select').value;
    await fetch('/api/admin/commandes/statut', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id=${currentOrderId}&statut=${encodeURIComponent(statut)}`
    });
    closeOrderModal();
    loadOrders();
}

document.getElementById('order-modal').addEventListener('click', function(e) {
    if (e.target === this) closeOrderModal();
});

loadOrders();
</script>