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
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            Chargement...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
async function loadClients() {
    const res  = await fetch('/api/admin/clients', { method: 'POST' });
    const data = await res.json();

    if (!data.success) return;

    const tbody = document.getElementById('clients-table-body');
    tbody.innerHTML = data.data.map(c => `
        <tr class="border-t border-gray-100 hover:bg-gray-50">
            <td class="px-6 py-4">${c.id}</td>
            <td class="px-6 py-4">${c.prenom} ${c.nom}</td>
            <td class="px-6 py-4 hidden md:table-cell">${c.email}</td>
            <td class="px-6 py-4 hidden md:table-cell">${c.telephone || 'N/A'}</td>
            <td class="px-6 py-4">
                <button onclick="deleteClient(${c.id})"
                        class="px-3 py-1 bg-red-100 text-red-600 rounded-full text-sm hover:bg-red-200">
                    Supprimer
                </button>
            </td>
        </tr>
    `).join('');
}

async function deleteClient(id) {
    if (!confirm('Supprimer ce client ?')) return;
    await fetch('/api/admin/clients/supprimer', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id=${id}`
    });
    loadClients();
}

loadClients();
</script>