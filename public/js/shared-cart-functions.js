class CartManager {
    constructor() {
        this.isUpdating = false;
        this.updateCartCounter();
    }

    async addToCart(productId, quantity = 1, button = null) {
        if (this.isUpdating) return;
        if (!productId) { this.showNotification('Erreur: produit introuvable', 'error'); return; }

        this.isUpdating = true;

        if (button) {
            button.disabled = true;
            button.setAttribute('data-original', button.innerHTML);
            button.innerHTML = '<i class="bx bx-loader-alt animate-spin"></i> Ajout...';
        }

        try {
            const res  = await fetch('/api/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `produitId=${productId}&quantite=${quantity}`
            });

            const data = await res.json();

            if (data.success) {
                this.updateAllCartCounters(data.cartCount);
                this.showNotification(data.message || 'Produit ajouté !', 'success');
            } else {
                this.showNotification(data.message || 'Erreur', 'error');
            }
        } catch (err) {
            this.showNotification('Une erreur est survenue', 'error');
        } finally {
            this.isUpdating = false;
            if (button) {
                button.disabled  = false;
                button.innerHTML = button.getAttribute('data-original') || '<i class="bx bx-cart-add"></i> Ajouter';
            }
        }
    }

    async updateCartCounter() {
        try {
            const res  = await fetch('/api/cart/count', {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            });
            const data = await res.json();
            if (data.success) this.updateAllCartCounters(data.count);
        } catch (err) {}
    }

    updateAllCartCounters(count) {
        ['#cart-counter', '#cart-counter-mobile', '.cart-counter'].forEach(sel => {
            document.querySelectorAll(sel).forEach(el => {
                el.textContent    = count > 99 ? '99+' : count;
                el.style.display  = count > 0 ? 'flex' : 'none';
            });
        });
    }

    showNotification(message, type = 'success') {
        document.querySelectorAll('.cart-notification').forEach(n => n.remove());

        const notif = document.createElement('div');
        notif.className = `cart-notification fixed bottom-4 right-4 px-4 py-2 rounded-lg shadow-lg text-white z-50 transition-all duration-300`;
        notif.style.backgroundColor = type === 'success' ? '#8E9675' : '#ef4444';
        notif.textContent = message;
        document.body.appendChild(notif);

        setTimeout(() => notif.remove(), 3000);
    }
}

window.cartManager = new CartManager();

window.addToCart = (productId, quantity = 1) => {
    const button = document.querySelector(`[data-product-id="${productId}"]`);
    window.cartManager.addToCart(productId, quantity, button);
};

window.updateCartCounter = () => window.cartManager.updateCartCounter();
window.showNotification  = (msg, type) => window.cartManager.showNotification(msg, type);