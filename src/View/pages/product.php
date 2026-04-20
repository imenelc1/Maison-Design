<?php $pageTitle = htmlspecialchars($product->getNom()) . ' — Maison Design'; ?>

<div class="min-h-screen pt-8 pb-16 px-4 md:px-[10%] bg-background">
    <div class="max-w-[1400px] mx-auto">

        <!-- Breadcrumb -->
        <nav class="mb-8 text-sm text-gray-600">
            <a href="/" class="hover:text-accent">Accueil</a>
            <span class="mx-2">/</span>
            <a href="/categories" class="hover:text-accent">Catégories</a>
            <span class="mx-2">/</span>
            <a href="/categories?category=<?php echo strtolower($product->getCategorie()); ?>"
               class="hover:text-accent">
                <?php echo htmlspecialchars($product->getCategorie()); ?>
            </a>
            <span class="mx-2">/</span>
            <span class="text-textColor"><?php echo htmlspecialchars($product->getNom()); ?></span>
        </nav>

        <!-- Produit -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-16">

            <!-- Image -->
            <div>
                <div class="aspect-square overflow-hidden rounded-xl bg-white shadow-lg">
                    <img src="/<?php echo htmlspecialchars($product->getImage()); ?>"
                         alt="<?php echo htmlspecialchars($product->getNom()); ?>"
                         class="w-full h-full object-cover"
                         onerror="this.src='/images/placeholder.jpeg'">
                </div>
            </div>

            <!-- Infos -->
            <div class="space-y-6">
                <div>
                    <p class="text-accent text-sm font-medium mb-2">
                        <?php echo htmlspecialchars($product->getCategorie()); ?>
                    </p>
                    <h1 class="text-3xl md:text-4xl font-bold text-textColor">
                        <?php echo htmlspecialchars($product->getNom()); ?>
                    </h1>
                </div>

                <!-- Prix -->
                <div class="bg-primary/20 rounded-xl p-6">
                    <p class="text-3xl font-bold text-accent">
                        <?php echo $product->getPrixFormate(); ?>
                    </p>
                </div>

                <!-- Stock -->
                <?php if ($product->isDisponible()): ?>
                <span class="inline-flex items-center gap-2 bg-green-100 text-green-800 px-4 py-2 rounded-full">
                    <i class='bx bx-check-circle'></i>
                    En stock (<?php echo $product->getStock(); ?> disponible<?php echo $product->getStock() > 1 ? 's' : ''; ?>)
                </span>
                <?php else: ?>
                <span class="inline-flex items-center gap-2 bg-red-100 text-red-800 px-4 py-2 rounded-full">
                    <i class='bx bx-x-circle'></i>
                    Rupture de stock
                </span>
                <?php endif; ?>

                <!-- Description -->
                <div>
                    <h3 class="text-xl font-semibold text-textColor mb-3">Description</h3>
                    <p class="text-gray-700 leading-relaxed">
                        <?php echo nl2br(htmlspecialchars($product->getDescription())); ?>
                    </p>
                </div>

                <!-- Quantité + Bouton -->
                <?php if ($product->isDisponible()): ?>
                <div class="flex items-center gap-4">
                    <label class="text-lg font-medium">Quantité :</label>
                    <div class="flex items-center border border-gray-300 rounded-lg">
                        <button type="button" id="decrease"
                                class="px-3 py-2 hover:bg-gray-100 transition-colors">
                            <i class='bx bx-minus'></i>
                        </button>
                        <input type="number" id="quantity" value="1" min="1"
                               max="<?php echo $product->getStock(); ?>"
                               class="w-16 text-center border-0 focus:outline-none">
                        <button type="button" id="increase"
                                class="px-3 py-2 hover:bg-gray-100 transition-colors">
                            <i class='bx bx-plus'></i>
                        </button>
                    </div>
                </div>

                <button id="add-to-cart-btn"
                        data-product-id="<?php echo $product->getId(); ?>"
                        class="w-full px-6 py-3 bg-accent text-white rounded-full hover:bg-accent/80 transition-colors text-lg font-medium flex items-center justify-center gap-2">
                    <i class='bx bx-cart-add'></i> Ajouter au panier
                </button>
                <?php else: ?>
                <button disabled
                        class="w-full px-6 py-3 bg-gray-300 text-gray-500 rounded-full cursor-not-allowed text-lg">
                    Produit indisponible
                </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Produits similaires -->
        <?php if (!empty($similaires)): ?>
        <section>
            <h2 class="text-2xl font-bold text-textColor mb-8 text-center">Produits similaires</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($similaires as $sim): ?>
                <div class="bg-white rounded-xl overflow-hidden shadow-md hover:-translate-y-1 transition-transform">
                    <a href="/produit/<?php echo $sim->getId(); ?>" class="block">
                        <div class="h-48 overflow-hidden">
                            <img src="/<?php echo htmlspecialchars($sim->getImage()); ?>"
                                 alt="<?php echo htmlspecialchars($sim->getNom()); ?>"
                                 class="w-full h-full object-cover hover:scale-105 transition-transform"
                                 onerror="this.src='/images/placeholder.jpeg'">
                        </div>
                        <div class="p-4">
                            <h3 class="font-medium text-textColor mb-1">
                                <?php echo htmlspecialchars($sim->getNom()); ?>
                            </h3>
                            <p class="text-accent font-bold"><?php echo $sim->getPrixFormate(); ?></p>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>
</div>

<script>
// Quantité
const qty      = document.getElementById('quantity');
const maxStock = <?php echo $product->getStock(); ?>;

document.getElementById('decrease')?.addEventListener('click', () => {
    if (parseInt(qty.value) > 1) qty.value--;
});

document.getElementById('increase')?.addEventListener('click', () => {
    if (parseInt(qty.value) < maxStock) qty.value++;
});

// Ajouter au panier
document.getElementById('add-to-cart-btn')?.addEventListener('click', async function() {
    const productId = this.dataset.productId;
    const quantite  = parseInt(qty.value);

    this.disabled  = true;
    this.innerHTML = '<i class="bx bx-loader-alt animate-spin"></i> Ajout...';

    const res  = await fetch('/api/cart/add', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `produitId=${productId}&quantite=${quantite}`
    });
    const data = await res.json();

    this.disabled  = false;
    this.innerHTML = '<i class="bx bx-cart-add"></i> Ajouter au panier';

    // Notification
    const notif = document.createElement('div');
    notif.className = `fixed bottom-4 right-4 px-4 py-2 rounded-lg shadow-lg text-white z-50
        ${data.success ? 'bg-accent' : 'bg-red-500'}`;
    notif.textContent = data.message;
    document.body.appendChild(notif);
    setTimeout(() => notif.remove(), 3000);

    if (data.success) {
        const counter = document.querySelector('#cart-counter');
        if (counter) {
            counter.textContent = data.cartCount;
            counter.style.display = 'flex';
        }
    }
});
</script>