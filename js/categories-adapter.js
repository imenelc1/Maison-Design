class CategoryManager {
  constructor() {
    this.currentCategory = window.initialData.selectedCategory || "all";
    this.categories = window.initialData.categories || [];
    this.isLoggedIn = window.sessionData ? window.sessionData.isLoggedIn : false;
    this.clientId = window.sessionData ? window.sessionData.clientId : null;
    this.productsCache = new Map(); // Cache pour stocker les produits par catégorie
    this.init();
  }

  init() {
    console.log("Initialisation du gestionnaire de catégories...");
    console.log("Utilisateur connecté:", this.isLoggedIn);
    console.log("ID client:", this.clientId);
    this.setupEventListeners();
    this.updateActiveFilter(this.currentCategory);
  }

  setupEventListeners() {
    const categoryFilters = document.getElementById("category-filters");
    if (!categoryFilters) {
      console.error("Container des filtres non trouvé");
      return;
    }

    categoryFilters.addEventListener("click", (e) => {
      if (e.target.classList.contains("category-filter")) {
        e.preventDefault();
        const category = e.target.getAttribute("data-category");
        console.log("Filtre cliqué:", category);
        this.filterByCategory(category);
      }
    });

    window.addEventListener("popstate", (e) => {
      const urlParams = new URLSearchParams(window.location.search);
      const categoryParam = urlParams.get("category") || "all";
      this.filterByCategory(categoryParam, false);
    });
  }

  async filterByCategory(category, updateURL = true) {
    if (category === this.currentCategory && this.productsCache.has(category)) {
      console.log("Utilisation du cache pour la catégorie:", category);
      this.renderProducts(this.productsCache.get(category));
      return;
    }

    try {
      this.showLoading(true);
      this.hideError();
      this.currentCategory = category;
      this.updateActiveFilter(category);

      const url = category === "all" 
        ? "categories.php?ajax=1" 
        : `categories.php?category=${encodeURIComponent(category)}&ajax=1`;

      const response = await fetch(url, {
        headers: {
          'Cache-Control': 'no-cache',
          'Pragma': 'no-cache'
        }
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      console.log("Données reçues pour", category, ":", data);

      if (data.success) {
        const uniqueProducts = this.removeDuplicates(data.products);
        this.productsCache.set(category, uniqueProducts);
        this.renderProducts(uniqueProducts);
        this.updateTitle(category, uniqueProducts.length);

        if (updateURL) {
          this.updateURL(category);
        }
      } else {
        throw new Error(data.message || "Erreur inconnue");
      }
    } catch (error) {
      console.error("Erreur lors du filtrage:", error);
      this.showError("Erreur lors du chargement des produits: " + error.message);
    } finally {
      this.showLoading(false);
    }
  }

  removeDuplicates(products) {
    const uniqueProducts = [];
    const seenIds = new Set();
    
    for (const product of products) {
      if (!seenIds.has(product.IdProduit)) {
        seenIds.add(product.IdProduit);
        uniqueProducts.push(product);
      }
    }
    
    return uniqueProducts;
  }

renderProducts(products) {
    let productHTML = ""
    if (products && products.length > 0) {
      products.forEach((product) => {
        productHTML += `
          <div class="w-full sm:w-1/2 md:w-1/3 lg:w-1/4 xl:w-1/4 p-2">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
              <img src="${product.Image}" alt="${product.Nom}" class="w-full h-48 object-cover">
              <div class="p-4">
                <h3 class="text-lg font-semibold mb-2">${product.Nom}</h3>
                <p class="text-gray-600 text-sm">${product.DescriptionCourte}</p>
                <div class="mt-3 flex items-center justify-between">
                  <span class="text-primary font-bold">${product.Prix} €</span>
                  <a href="produit.php?id=${product.IdProduit}" 
                     class="w-full px-3 py-2 bg-primary text-textColor rounded-full hover:bg-accent hover:text-white transition-colors text-sm flex items-center justify-center gap-2 font-medium">
                      Voir détails
                  </a>
                </div>
              </div>
            </div>
          </div>
        `
      })
    } else {
      productHTML = "<p>No products found in this category.</p>"
    }
    return productHTML
  }

  updateTitle(category, productCount) {
    const title = document.getElementById("products-title");
    if (!title) return;

    if (category === "all") {
      title.textContent = `Tous nos produits (${productCount})`;
    } else {
      const categoryObj = this.categories.find(
        (cat) => cat.NomCategorie.toLowerCase() === category.toLowerCase()
      );
      title.textContent = `Nos ${categoryObj ? categoryObj.NomCategorie : category} (${productCount})`;
    }
  }

  updateURL(category) {
    const url = new URL(window.location);
    if (category === "all") {
      url.searchParams.delete("category");
    } else {
      url.searchParams.set("category", category);
    }
    window.history.pushState({}, "", url);
  }

  showLoading(show) {
    const loading = document.getElementById("loading");
    if (loading) {
      loading.classList.toggle("hidden", !show);
    }
  }

  showError(message) {
    const errorDiv = document.getElementById("error-message");
    const errorText = document.getElementById("error-text");

    if (errorDiv && errorText) {
      errorText.textContent = message;
      errorDiv.classList.remove("hidden");
    }
    console.error("Erreur affichée:", message);
  }

  hideError() {
    const errorDiv = document.getElementById("error-message");
    if (errorDiv) {
      errorDiv.classList.add("hidden");
    }
  }

  escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }

  formatPrice(price) {
    return new Intl.NumberFormat("fr-FR", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(price);
  }

  attachFavoriteEvents() {
    const favoriteButtons = document.querySelectorAll(".favorite-btn");
    favoriteButtons.forEach((button) => {
      button.addEventListener("click", (e) => {
        e.preventDefault();
        const productId = parseInt(button.getAttribute("data-product-id"), 10);
        this.toggleFavorite(productId, button);
      });
    });
  }

  toggleFavorite(productId, button) {
    if (!this.isLoggedIn) {
      showNotification('Veuillez vous connecter pour ajouter des produits aux favoris', 'error');
      setTimeout(() => {
        window.location.href = 'connexion.php';
      }, 1500);
      return;
    }

    const icon = button.querySelector('i');
    button.disabled = true;

    fetch('php/favorites_actions.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: `action=toggle&produitId=${productId}`
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        if (data.action === 'added') {
          icon.classList.remove('far', 'fa-heart');
          icon.classList.add('fas', 'fa-heart', 'text-red-600');
          button.classList.remove('bg-gray-100', 'text-gray-600');
          button.classList.add('bg-red-100', 'text-red-600');
          button.title = 'Retirer des favoris';
          showNotification('Produit ajouté aux favoris !', 'success');
        } else {
          icon.classList.remove('fas', 'fa-heart', 'text-red-600');
          icon.classList.add('far', 'fa-heart');
          button.classList.remove('bg-red-100', 'text-red-600');
          button.classList.add('bg-gray-100', 'text-gray-600');
          button.title = 'Ajouter aux favoris';
          showNotification('Produit retiré des favoris', 'info');
        }
        
        // Invalider le cache après modification des favoris
        this.productsCache.clear();
        this.filterByCategory(this.currentCategory, false);
      } else {
        showNotification('Erreur: ' + data.message, 'error');
      }
    })
    .catch(error => {
      console.error('Erreur:', error);
      showNotification('Une erreur est survenue', 'error');
    })
    .finally(() => {
      button.disabled = false;
    });
  }
}

document.addEventListener("DOMContentLoaded", () => {
  console.log("DOM chargé, initialisation du CategoryManager...");
  new CategoryManager();
});