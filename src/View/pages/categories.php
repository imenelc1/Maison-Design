<?php $pageTitle = 'Catégories — Maison Design'; ?>

<div class="min-h-screen pt-8 pb-16 px-4 md:px-[10%] bg-background">
    <div class="max-w-[1400px] mx-auto">
        <h1 class="text-3xl md:text-4xl text-center mb-8 text-textColor" 
            style="font-family: Frunchy, serif">
            Nos Catégories
        </h1>

        <!-- Filtres -->
        <div class="flex flex-wrap justify-center gap-3 mb-10" id="category-filters">
            <button class="category-filter px-4 py-2 rounded-full transition-all bg-accent text-white"
                    data-category="all">
                Tous
            </button>
            <?php foreach (['Lits', 'Armoires', 'Canapés', 'Chaises', 'Tables'] as $cat): ?>
            <button class="category-filter px-4 py-2 rounded-full transition-all bg-primary text-textColor hover:bg-accent hover:text-white"
                    data-category="<?php echo strtolower($cat); ?>">
                <?php echo $cat; ?>
            </button>
            <?php endforeach; ?>
        </div>

        <!-- Titre section -->
        <h2 class="text-2xl font-medium text-textColor mb-6" id="products-title">
            <?php
            if ($selectedCategory === 'all') {
                echo 'Tous nos produits (' . count($produits) . ')';
            } else {
                echo htmlspecialchars($selectedCategory) . ' (' . count($produits) . ')';
            }
            ?>
        </h2>

        <!-- Loading -->
        <div id="loading" class="text-center py-12 hidden">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-accent"></div>
            <p class="mt-2 text-textColor">Chargement...</p>
        </div>

        <!-- Grille produits -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"
             id="products-container">

            <?php if (empty($produits)): ?>
            <div class="col-span-full text-center py-12">
                <p class="text-xl text-textColor/70">Aucun produit trouvé.</p>
            </div>
            <?php else: ?>
                <?php foreach ($produits as $produit): ?>
                <div class="bg-white rounded-xl overflow-hidden shadow-md hover:-translate-y-1 transition-transform"
                     data-product-id="<?php echo $produit->getId(); ?>">

                    <a href="/produit/<?php echo $produit->getId(); ?>" class="block">
                        <div class="h-48 overflow-hidden">
                            <img src="/<?php echo str_replace('%2F', '/', rawurlencode($produit->getImage())); ?>"
                                 alt="<?php echo htmlspecialchars($produit->getNom()); ?>"
                                 class="w-full h-full object-cover hover:scale-105 transition-transform"
                                 onerror="this.src='/images/placeholder.jpeg'">
                        </div>
                    </a>

                    <div class="p-4">
                        <h3 class="text-lg font-medium text-textColor mb-1">
                            <?php echo htmlspecialchars($produit->getNom()); ?>
                        </h3>
                        <p class="text-accent font-bold text-xl">
                            <?php echo $produit->getPrixFormate(); ?>
                        </p>

                        <?php if ($produit->isDisponible()): ?>
                        <div class="flex items-center gap-1 mt-2 text-green-600">
                            <i class='bx bx-check'></i>
                            <span class="text-sm">Disponible (<?php echo $produit->getStock(); ?>)</span>
                        </div>
                        <?php else: ?>
                        <div class="flex items-center gap-1 mt-2 text-red-600">
                            <i class='bx bx-x'></i>
                            <span class="text-sm">Indisponible</span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="p-4 pt-0 flex flex-col gap-2">
                        <a href="/produit/<?php echo $produit->getId(); ?>"
                           class="w-full px-3 py-2 bg-primary text-textColor rounded-full hover:bg-accent hover:text-white transition-colors text-sm text-center">
                            Voir détails
                        </a>

                        <?php if ($produit->isDisponible()): ?>
                        <button onclick="addToCart(<?php echo $produit->getId(); ?>)"
                                class="w-full px-3 py-2 bg-accent text-white rounded-full hover:bg-accent/80 transition-colors text-sm flex items-center justify-center gap-1">
                            <i class='bx bx-cart-add'></i> Ajouter au panier
                        </button>
                        <?php else: ?>
                        <button disabled
                                class="w-full px-3 py-2 bg-gray-300 text-gray-500 rounded-full cursor-not-allowed text-sm">
                            Indisponible
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
    </div>
</div>

<script>
// Filtres catégories
document.querySelectorAll('.category-filter').forEach(btn => {
    btn.addEventListener('click', async function() {
        const category = this.dataset.category;

        // UI
        document.querySelectorAll('.category-filter').forEach(b => {
            b.classList.remove('bg-accent', 'text-white');
            b.classList.add('bg-primary', 'text-textColor');
        });
        this.classList.add('bg-accent', 'text-white');
        this.classList.remove('bg-primary', 'text-textColor');

        // Charger les produits
        document.getElementById('loading').classList.remove('hidden');
        document.getElementById('products-container').innerHTML = '';

        const url = category === 'all'
            ? '/categories?ajax=1'
            : `/categories?category=${category}&ajax=1`;

        const res  = await fetch(url);
        const data = await res.json();

        document.getElementById('loading').classList.add('hidden');

        if (data.success) {
            document.getElementById('products-title').textContent =
                category === 'all'
                    ? `Tous nos produits (${data.products.length})`
                    : `${category} (${data.products.length})`;

            renderProducts(data.products);
        }
    });
});

function renderProducts(products) {
    const container = document.getElementById('products-container');

    if (!products.length) {
        container.innerHTML = '<div class="col-span-full text-center py-12"><p class="text-xl text-textColor/70">Aucun produit trouvé.</p></div>';
        return;
    }

    container.innerHTML = products.map(p => `
        <div class="bg-white rounded-xl overflow-hidden shadow-md hover:-translate-y-1 transition-transform">
            <a href="/produit/${p.IdProduit}" class="block">
                <div class="h-48 overflow-hidden">
                    <img src="/${encodeURIComponent(p.image).replace(/%2F/g, '/')}" alt="${p.NomProduit}"
                         class="w-full h-full object-cover hover:scale-105 transition-transform"
                         onerror="this.src='/images/placeholder.jpeg'">
                </div>
            </a>
            <div class="p-4">
                <h3 class="text-lg font-medium text-textColor mb-1">${p.NomProduit}</h3>
                <p class="text-accent font-bold text-xl">
                    ${parseFloat(p.Prix).toLocaleString('fr-FR', {minimumFractionDigits: 2})} DA
                </p>
                ${p.Stock > 0
                    ? '<div class="flex items-center gap-1 mt-2 text-green-600"><i class="bx bx-check"></i><span class="text-sm">Disponible</span></div>'
                    : '<div class="flex items-center gap-1 mt-2 text-red-600"><i class="bx bx-x"></i><span class="text-sm">Indisponible</span></div>'
                }
            </div>
            <div class="p-4 pt-0 flex flex-col gap-2">
                <a href="/produit/${p.IdProduit}"
                   class="w-full px-3 py-2 bg-primary text-textColor rounded-full hover:bg-accent hover:text-white transition-colors text-sm text-center">
                    Voir détails
                </a>
                ${p.Stock > 0
                    ? `<button onclick="addToCart(${p.IdProduit})"
                              class="w-full px-3 py-2 bg-accent text-white rounded-full hover:bg-accent/80 text-sm flex items-center justify-center gap-1">
                           <i class='bx bx-cart-add'></i> Ajouter au panier
                       </button>`
                    : `<button disabled class="w-full px-3 py-2 bg-gray-300 text-gray-500 rounded-full cursor-not-allowed text-sm">
                           Indisponible
                       </button>`
                }
            </div>
        </div>
    `).join('');
}

// Ajouter au panier via AJAX
async function addToCart(productId) {
    const res  = await fetch('/api/cart/add', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `produitId=${productId}&quantite=1`
    });
    const data = await res.json();

    showNotification(data.message, data.success ? 'success' : 'error');

    if (data.success) {
        const counter = document.querySelector('#cart-counter');
        if (counter) {
            counter.textContent = data.cartCount;
            counter.style.display = 'flex';
        }
    }
}

function showNotification(message, type = 'success') {
    const notif = document.createElement('div');
    notif.className = `fixed bottom-4 right-4 px-4 py-2 rounded-lg shadow-lg text-white z-50 transition-all
        ${type === 'success' ? 'bg-accent' : 'bg-red-500'}`;
    notif.textContent = message;
    document.body.appendChild(notif);
    setTimeout(() => notif.remove(), 3000);
}
</script>