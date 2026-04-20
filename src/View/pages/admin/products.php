<?php $pageTitle = 'Produits Admin — Maison Design'; ?>

<div class="min-h-screen pt-8 pb-16 px-4 md:px-[10%] bg-background">
    <div class="max-w-[1200px] mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-textColor">Gestion des Produits</h1>
            <button id="add-product-btn"
                    class="px-4 py-2 bg-accent text-white rounded-full hover:bg-accent/80 flex items-center gap-2">
                <i class='bx bx-plus'></i> Ajouter
            </button>
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-secondary/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-medium text-textColor">ID</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-textColor">Nom</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-textColor hidden md:table-cell">Catégorie</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-textColor">Prix</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-textColor hidden md:table-cell">Stock</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-textColor">Actions</th>
                    </tr>
                </thead>
                <tbody id="products-table-body">
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <div class="inline-block animate-spin rounded-full h-6 w-6 border-t-2 border-accent"></div>
                            <p class="mt-2">Chargement...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Charger les produits via API
async function loadProducts() {
    const res  = await fetch('/api/admin/produits', {method: 'POST'});
    const data = await res.json();

    if (!data.success) return;

    const tbody = document.getElementById('products-table-body');
    tbody.innerHTML = data.data.map(p => `
        <tr class="border-t border-gray-100 hover:bg-gray-50">
            <td class="px-6 py-4">${p.id}</td>
            <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                    <img src="/${p.image}" class="w-10 h-10 object-cover rounded-md"
                         onerror="this.style.display='none'">
                    <span>${p.nom}</span>
                </div>
            </td>
            <td class="px-6 py-4 hidden md:table-cell">${p.categorie}</td>
            <td class="px-6 py-4">${p.prix} DA</td>
            <td class="px-6 py-4 hidden md:table-cell">
                <span class="${p.stock > 0 ? 'text-green-600' : 'text-red-600'}">${p.stock}</span>
            </td>
            <td class="px-6 py-4">
                <button onclick="deleteProduct(${p.id})"
                        class="px-3 py-1 bg-red-100 text-red-600 rounded-full text-sm hover:bg-red-200">
                    Supprimer
                </button>
            </td>
        </tr>
    `).join('');
}

async function deleteProduct(id) {
    if (!confirm('Supprimer ce produit ?')) return;
    const res  = await fetch('/api/admin/produits/supprimer', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id=${id}`
    });
    const data = await res.json();
    if (data.success) loadProducts();
}

loadProducts();
</script>