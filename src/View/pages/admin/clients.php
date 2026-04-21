<?php $pageTitle = 'Clients Admin — Maison Design'; ?>

<div class="min-h-screen pt-8 pb-16 px-4 md:px-[10%] bg-background">
    <div class="max-w-[1200px] mx-auto">
        <h1 class="text-3xl font-bold text-textColor mb-8">Gestion des Clients</h1>

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-secondary/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-medium text-textColor">ID</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-textColor">Nom</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-textColor hidden md:table-cell">Email</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-textColor hidden md:table-cell">Téléphone</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-textColor">Actions</th>
                    </tr>
                </thead>
                <tbody id="clients-table-body">
                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">Chargement...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal détails client -->
<div id="client-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-textColor">Détails du client</h2>
            <button onclick="closeClientModal()" class="text-2xl text-gray-400 hover:text-gray-600">&times;</button>
        </div>

        <div class="flex items-center gap-4 mb-6 p-4 bg-primary/20 rounded-xl">
            <div class="w-14 h-14 bg-accent rounded-full flex items-center justify-center flex-shrink-0">
                <i class='bx bx-user text-white text-2xl'></i>
            </div>
            <div>
                <p class="font-bold text-textColor text-lg" id="modal-client-nom"></p>
                <p class="text-gray-500 text-sm" id="modal-client-email"></p>
            </div>
        </div>

        <div class="space-y-3 mb-6">
            <div class="flex items-center gap-3">
                <i class='bx bx-phone text-accent text-lg'></i>
                <div>
                    <p class="text-xs text-gray-400">Téléphone</p>
                    <p class="text-sm font-medium" id="modal-client-tel"></p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <i class='bx bx-map text-accent text-lg'></i>
                <div>
                    <p class="text-xs text-gray-400">Adresse</p>
                    <p class="text-sm font-medium" id="modal-client-adresse"></p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <i class='bx bx-calendar text-accent text-lg'></i>
                <div>
                    <p class="text-xs text-gray-400">Inscrit le</p>
                    <p class="text-sm font-medium" id="modal-client-date"></p>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button onclick="closeClientModal()"
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-full hover:bg-gray-50 text-sm">
                Fermer
            </button>
            <button id="modal-delete-btn"
                    class="flex-1 px-4 py-2 bg-red-500 text-white rounded-full hover:bg-red-600 text-sm">
                Supprimer
            </button>
        </div>
    </div>
</div>

<script>
let currentClientId = null;

async function loadClients() {
    const res  = await fetch('/api/admin/clients', {method: 'POST'});
    const data = await res.json();
    if (!data.success) return;

    const tbody = document.getElementById('clients-table-body');
    tbody.innerHTML = data.data.map(c => `
        <tr class="border-t border-gray-100 hover:bg-gray-50 cursor-pointer" onclick="openClientModal(${JSON.stringify(c).replace(/"/g, '&quot;')})">
            <td class="px-6 py-4 text-sm">${c.id}</td>
            <td class="px-6 py-4 text-sm font-medium">${c.prenom} ${c.nom}</td>
            <td class="px-6 py-4 text-sm hidden md:table-cell">${c.email}</td>
            <td class="px-6 py-4 text-sm hidden md:table-cell">${c.telephone || 'N/A'}</td>
            <td class="px-6 py-4">
                <button onclick="event.stopPropagation(); confirmDelete(${c.id})"
                        class="px-3 py-1 bg-red-100 text-red-600 rounded-full text-sm hover:bg-red-200">
                    Supprimer
                </button>
            </td>
        </tr>
    `).join('');
}

function openClientModal(c) {
    currentClientId = c.id;
    document.getElementById('modal-client-nom').textContent     = `${c.prenom} ${c.nom}`;
    document.getElementById('modal-client-email').textContent   = c.email;
    document.getElementById('modal-client-tel').textContent     = c.telephone || 'Non renseigné';
    document.getElementById('modal-client-adresse').textContent = c.adresse   || 'Non renseignée';
    document.getElementById('modal-client-date').textContent    = c.dateInscription
        ? new Date(c.dateInscription).toLocaleDateString('fr-FR') : 'N/A';

    document.getElementById('modal-delete-btn').onclick = () => confirmDelete(c.id);

    const modal = document.getElementById('client-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeClientModal() {
    const modal = document.getElementById('client-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

async function confirmDelete(id) {
    if (!confirm('Supprimer ce client ?')) return;
    const res  = await fetch('/api/admin/clients/supprimer', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id=${id}`
    });
    const data = await res.json();
    if (data.success) { closeClientModal(); loadClients(); }
    else alert(data.message || 'Suppression impossible');
}

document.getElementById('client-modal').addEventListener('click', function(e) {
    if (e.target === this) closeClientModal();
});

loadClients();
</script>