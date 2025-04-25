document.addEventListener("DOMContentLoaded", () => {
    // Initialiser les onglets
    initTabs();

    // Initialiser les accordéons pour les commandes
    initOrderAccordions();

    // Initialiser les formulaires
    initForms();

    // Initialiser le panier
    initCart();
});

/**
 * Initialise le système d'onglets
 */
function initTabs() {
    const tabButtons = document.querySelectorAll(".tab-button");
    const tabPanes = document.querySelectorAll(".tab-pane");

    tabButtons.forEach(button => {
        button.addEventListener("click", () => {
            // Récupérer l'ID de l'onglet à afficher
            const tabId = button.getAttribute("data-tab");
            
            // Désactiver tous les onglets
            tabButtons.forEach(btn => btn.classList.remove("bg-accent", "text-white"));
            tabPanes.forEach(pane => pane.classList.remove("active"));
            
            // Activer l'onglet sélectionné
            button.classList.add("bg-accent", "text-white");
            document.getElementById(`${tabId}-tab`).classList.add("active");
        });
    });
}

/**
 * Initialise les accordéons pour les commandes
 */
function initOrderAccordions() {
    const orderHeaders = document.querySelectorAll(".order-header");
    
    orderHeaders.forEach(header => {
        header.addEventListener("click", () => {
            const orderId = header.getAttribute("data-order");
            const orderDetails = document.getElementById(orderId);
            const chevron = header.querySelector(".bx-chevron-down");
            
            // Basculer l'affichage des détails
            if (orderDetails.classList.contains("hidden")) {
                orderDetails.classList.remove("hidden");
                chevron.style.transform = "rotate(180deg)";
            } else {
                orderDetails.classList.add("hidden");
                chevron.style.transform = "rotate(0deg)";
            }
        });
    });
}

/**
 * Initialise les formulaires et les boutons d'action
 */
function initForms() {
    // Gestion du profil
    const editProfileBtn = document.getElementById("edit-profile-btn");
    const cancelEditProfile = document.getElementById("cancel-edit-profile");
    const profileView = document.getElementById("profile-view");
    const profileEdit = document.getElementById("profile-edit");
    const profileForm = document.getElementById("profile-form");

    if (editProfileBtn && cancelEditProfile && profileView && profileEdit && profileForm) {
        // Afficher le formulaire d'édition
        editProfileBtn.addEventListener("click", () => {
            profileView.classList.add("hidden");
            profileEdit.classList.remove("hidden");
        });

        // Annuler l'édition
        cancelEditProfile.addEventListener("click", () => {
            profileView.classList.remove("hidden");
            profileEdit.classList.add("hidden");
        });

        // Soumettre le formulaire
        profileForm.addEventListener("submit", (e) => {
            e.preventDefault();
            
            // Simuler une mise à jour réussie
            alert("Profil mis à jour avec succès!");
            
            // Revenir à la vue normale
            profileView.classList.remove("hidden");
            profileEdit.classList.add("hidden");
        });
    }

    // Gestion du changement de mot de passe
    const changePasswordBtn = document.getElementById("change-password-btn");
    if (changePasswordBtn) {
        changePasswordBtn.addEventListener("click", () => {
            // Simuler une boîte de dialogue pour changer le mot de passe
            alert("Fonctionnalité de changement de mot de passe à implémenter.");
        });
    }

    // Gestion des adresses
    const addAddressBtn = document.getElementById("add-address-btn");
    const cancelAddAddress = document.getElementById("cancel-add-address");
    const addAddressForm = document.getElementById("add-address-form");

    if (addAddressBtn && cancelAddAddress && addAddressForm) {
        // Afficher le formulaire d'ajout d'adresse
        addAddressBtn.addEventListener("click", () => {
            addAddressForm.classList.remove("hidden");
            addAddressBtn.classList.add("hidden");
        });

        // Annuler l'ajout d'adresse
        cancelAddAddress.addEventListener("click", () => {
            addAddressForm.classList.add("hidden");
            addAddressBtn.classList.remove("hidden");
        });

        // Soumettre le formulaire d'adresse
        addAddressForm.querySelector("form").addEventListener("submit", (e) => {
            e.preventDefault();
            
            // Simuler un ajout réussi
            alert("Adresse ajoutée avec succès!");
            
            // Cacher le formulaire
            addAddressForm.classList.add("hidden");
            addAddressBtn.classList.remove("hidden");
        });
    }

    // Gestion des favoris
    const removeFromWishlistBtns = document.querySelectorAll(".tab-pane#wishlist-tab button[title='Retirer des favoris']");
    removeFromWishlistBtns.forEach(btn => {
        btn.addEventListener("click", (e) => {
            e.preventDefault();
            
            // Récupérer le produit parent
            const productCard = btn.closest(".bg-white.rounded-xl");
            
            // Simuler une animation de suppression
            productCard.style.opacity = "0.5";
            setTimeout(() => {
                productCard.remove();
            }, 500);
            
            // Afficher un message
            alert("Produit retiré des favoris!");
        });
    });
}

/**
 * Initialise le panier
 */
function initCart() {
    const cartToggle = document.getElementById("cart-toggle");
    const cartDropdown = document.getElementById("cart-dropdown");
    
    if (cartToggle && cartDropdown) {
        cartToggle.addEventListener("click", (e) => {
            e.stopPropagation();
            cartDropdown.classList.toggle("active");
        });
        
        // Fermer le panier quand on clique ailleurs
        document.addEventListener("click", (e) => {
            if (!cartToggle.contains(e.target) && !cartDropdown.contains(e.target)) {
                cartDropdown.classList.remove("active");
            }
        });
        
        // Gestion des quantités dans le panier
        const quantityBtns = cartDropdown.querySelectorAll(".flex-1 .flex button");
        quantityBtns.forEach(btn => {
            btn.addEventListener("click", (e) => {
                const isPlus = btn.textContent === "+";
                const quantitySpan = btn.parentElement.querySelector("span");
                let quantity = parseInt(quantitySpan.textContent);
                
                if (isPlus) {
                    quantity++;
                } else if (quantity > 1) {
                    quantity--;
                }
                
                quantitySpan.textContent = quantity;
            });
        });
        
        // Gestion de la suppression d'articles
        const removeItemBtns = cartDropdown.querySelectorAll(".bx-trash");
        removeItemBtns.forEach(btn => {
            btn.addEventListener("click", () => {
                const cartItem = btn.closest(".flex.items-center");
                cartItem.style.opacity = "0";
                setTimeout(() => {
                    cartItem.remove();
                    updateCartCount();
                    updateCartTotal();
                }, 300);
            });
        });
    }
    
    // Gestion du menu mobile
    const openMenuBtn = document.getElementById("open-menu");
    const closeMenuBtn = document.getElementById("close-menu");
    const mobileMenu = document.getElementById("mobile-menu");
    
    if (openMenuBtn && closeMenuBtn && mobileMenu) {
        openMenuBtn.addEventListener("click", () => {
            mobileMenu.classList.add("active");
        });
        
        closeMenuBtn.addEventListener("click", () => {
            mobileMenu.classList.remove("active");
        });
    }
    
    // Gestion de la recherche
    const searchToggle = document.getElementById("search-toggle");
    const searchDropdown = document.getElementById("search-dropdown");
    
    if (searchToggle && searchDropdown) {
        searchToggle.addEventListener("click", (e) => {
            e.stopPropagation();
            searchDropdown.classList.toggle("hidden");
            setTimeout(() => {
                searchDropdown.classList.toggle("opacity-0");
                searchDropdown.classList.toggle("-translate-y-2.5");
            }, 10);
        });
        
        document.addEventListener("click", (e) => {
            if (!searchToggle.contains(e.target) && !searchDropdown.contains(e.target)) {
                searchDropdown.classList.add("opacity-0", "-translate-y-2.5");
                setTimeout(() => {
                    searchDropdown.classList.add("hidden");
                }, 300);
            }
        });
    }
}

/**
 * Met à jour le compteur d'articles dans le panier
 */
function updateCartCount() {
    const cartItems = document.querySelectorAll("#cart-dropdown .cart-items > div").length;
    const cartCountBadge = document.querySelector("#cart-toggle span");
    
    if (cartCountBadge) {
        cartCountBadge.textContent = cartItems;
        
        // Si le panier est vide, afficher un message
        const cartItemsContainer = document.querySelector("#cart-dropdown .cart-items");
        const cartSummary = document.querySelector("#cart-dropdown .cart-summary");
        const cartActions = document.querySelector("#cart-dropdown .cart-actions");
        
        if (cartItems === 0) {
            cartItemsContainer.innerHTML = '<div class="py-8 text-center"><p class="text-gray-500">Votre panier est vide</p><a href="categories.html" class="inline-block mt-4 px-4 py-2 bg-accent text-white rounded-full hover:bg-accent/80 transition-colors">Découvrir nos produits</a></div>';
            cartSummary.classList.add("hidden");
            cartActions.classList.add("hidden");
        }
    }
}

/**
 * Met à jour le total du panier
 */
function updateCartTotal() {
    const cartItems = document.querySelectorAll("#cart-dropdown .cart-items > div");
    let total = 0;
    
    cartItems.forEach(item => {
        const priceText = item.querySelector(".font-medium.text-accent").textContent;
        const price = parseFloat(priceText.replace(/[^0-9,]/g, '').replace(',', '.'));
        const quantity = parseInt(item.querySelector(".flex-1 .flex span").textContent);
        total += price * quantity;
    });
    
    const subtotalElement = document.querySelector("#cart-dropdown .cart-summary div:nth-child(1) span:nth-child(2)");
    const totalElement = document.querySelector("#cart-dropdown .cart-summary div:nth-child(3) span:nth-child(2)");
    
    if (subtotalElement && totalElement) {
        subtotalElement.textContent = total.toLocaleString("fr-DZ", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }) + " DA";
        
        // Ajouter les frais de livraison (1000 DA)
        totalElement.textContent = (total + 1000).toLocaleString("fr-DZ", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }) + " DA";
    }
}