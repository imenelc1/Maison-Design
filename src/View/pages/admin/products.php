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

<div id="product-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-2xl bg-white rounded-2xl shadow-xl p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-textColor">Ajouter un produit</h2>
            <button id="close-product-modal" class="text-2xl text-gray-400 hover:text-gray-600">&times;</button>
        </div>

        <form id="product-form" class="space-y-4" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="nom" class="block text-sm font-medium text-textColor mb-2">Nom</label>
                    <input id="nom" name="nom" type="text" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                </div>
                <div>
                    <label for="categorie" class="block text-sm font-medium text-textColor mb-2">Categorie</label>
                    <input id="categorie" name="categorie" type="text" list="categories-list" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                    <datalist id="categories-list"></datalist>
                </div>
                <div>
                    <label for="prix" class="block text-sm font-medium text-textColor mb-2">Prix</label>
                    <input id="prix" name="prix" type="number" min="0" step="0.01" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                </div>
                <div>
                    <label for="stock" class="block text-sm font-medium text-textColor mb-2">Stock</label>
                    <input id="stock" name="stock" type="number" min="0" step="1" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                </div>
            </div>

            <div>
                <label for="image" class="block text-sm font-medium text-textColor mb-2">Photo du produit</label>
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp,image/gif"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                <p class="text-sm text-gray-500 mt-2">Formats acceptes: JPG, PNG, WEBP, GIF.</p>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-textColor mb-2">Description</label>
                <textarea id="description" name="description" rows="4"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"></textarea>
            </div>

            <p id="product-form-message" class="hidden text-sm"></p>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" id="cancel-product-modal"
                        class="px-4 py-2 border border-gray-300 rounded-full hover:bg-gray-50">
                    Annuler
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-accent text-white rounded-full hover:bg-accent/80">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const modal = document.getElementById('product-modal');
const form = document.getElementById('product-form');
const formMessage = document.getElementById('product-form-message');
const categoriesList = document.getElementById('categories-list');
let cachedProducts = [];

function openProductModal() {
    form.reset();
    formMessage.className = 'hidden text-sm';
    formMessage.textContent = '';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeProductModal() {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function renderCategorySuggestions(products) {
    const categories = [...new Set(products.map(p => (p.categorie || '').trim()).filter(Boolean))];
    categoriesList.innerHTML = categories.map(category => `<option value="${category}"></option>`).join('');
}

// Charger les produits via API
async function loadProducts() {
    const res  = await fetch('/api/admin/produits', {method: 'POST'});
    const data = await res.json();

    if (!data.success) return;

    cachedProducts = data.data;
    renderCategorySuggestions(cachedProducts);

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

form.addEventListener('submit', async function (event) {
    event.preventDefault();

    const body = new FormData(form);
    const res = await fetch('/api/admin/produits/ajouter', {
        method: 'POST',
        body
    });

    const data = await res.json();

    formMessage.classList.remove('hidden', 'text-green-600', 'text-red-600');
    formMessage.classList.add(data.success ? 'text-green-600' : 'text-red-600');
    formMessage.textContent = data.message;

    if (data.success) {
        await loadProducts();
        setTimeout(closeProductModal, 500);
    }
});

document.getElementById('add-product-btn').addEventListener('click', openProductModal);
document.getElementById('close-product-modal').addEventListener('click', closeProductModal);
document.getElementById('cancel-product-modal').addEventListener('click', closeProductModal);
modal.addEventListener('click', function (event) {
    if (event.target === modal) {
        closeProductModal();
    }
});

loadProducts();
</script>
