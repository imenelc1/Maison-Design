class CategoryManager {
  constructor() {
    this.currentCategory = window.initialData.selectedCategory || "all"
    this.categories = window.initialData.categories || []
    this.isLoggedIn = window.sessionData ? window.sessionData.isLoggedIn : false
    this.clientId = window.sessionData ? window.sessionData.clientId : null
    this.init()
  }

  init() {
    console.log("Initialisation du gestionnaire de catégories...")
    console.log("Utilisateur connecté:", this.isLoggedIn)
    console.log("ID client:", this.clientId)
    this.setupEventListeners()
    this.updateActiveFilter(this.currentCategory)
  }

  setupEventListeners() {
    const categoryFilters = document.getElementById("category-filters")
    if (!categoryFilters) {
      console.error("Container des filtres non trouvé")
      return
    }

    // Intercepter les clics sur les liens de catégorie
    categoryFilters.addEventListener("click", (e) => {
      if (e.target.classList.contains("category-filter")) {
        e.preventDefault() // Empêcher le rechargement de page
        const category = e.target.getAttribute("data-category")
        console.log("Filtre cliqué:", category)
        this.filterByCategory(category)
      }
    })

    // Écouter les changements d'URL (bouton retour du navigateur)
    window.addEventListener("popstate", (e) => {
      const urlParams = new URLSearchParams(window.location.search)
      const categoryParam = urlParams.get("category") || "all"
      this.filterByCategory(categoryParam, false) // false = ne pas mettre à jour l'URL
    })
  }

  async filterByCategory(category, updateURL = true) {
    if (category === this.currentCategory) return

    console.log("Filtrage par catégorie:", category)

    try {
      this.showLoading(true)
      this.hideError()
      this.currentCategory = category
      this.updateActiveFilter(category)

      // Faire l'appel AJAX (CORRECTION: enlever les ../)
      const url =
        category === "all" ? "categories.php?ajax=1" : `categories.php?category=${encodeURIComponent(category)}&ajax=1`

      const response = await fetch(url)

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const data = await response.json()
      console.log("Données reçues:", data)

      if (data.success) {
        this.renderProducts(data.products)
        this.updateTitle(category)

        // Mettre à jour l'URL sans recharger la page
        if (updateURL) {
          this.updateURL(category)
        }
      } else {
        throw new Error(data.message || "Erreur inconnue")
      }
    } catch (error) {
      console.error("Erreur lors du filtrage:", error)
      this.showError("Erreur lors du chargement des produits: " + error.message)
    } finally {
      this.showLoading(false)
    }
  }

  renderProducts(products) {
    const container = document.getElementById("products-container")
    const noProductsMessage = document.getElementById("no-products")

    if (!container) {
      console.error("Container des produits non trouvé")
      return
    }

    if (products.length === 0) {
      container.innerHTML = ""
      if (noProductsMessage) {
        noProductsMessage.classList.remove("hidden")
      }
      return
    }

    if (noProductsMessage) {
      noProductsMessage.classList.add("hidden")
    }

    container.innerHTML = products
      .map(
        (product) => `
        <div class="product-item bg-white rounded-xl overflow-hidden shadow-md transition-transform hover:-translate-y-1" data-category="${(product.categorie || "").toLowerCase()}">
          <!-- Image du produit (cliquable) -->
          <a href="product.php?id=${product.IdProduit}" class="block">
            <div class="product-image h-48 overflow-hidden relative group">
              <img src="${product.image || "images/placeholder.jpeg"}" 
                   alt="${this.escapeHtml(product.NomProduit)}" 
                   class="w-full h-full object-cover transition-transform group-hover:scale-105"
                   onerror="this.src='images/placeholder.jpeg'">
              <!-- Overlay au survol -->
              <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
                <span class="text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300 font-medium">
                  Voir détails
                </span>
              </div>
            </div>
          </a>
          
          <!-- Informations du produit (simplifiées) -->
          <div class="product-info p-4">
            <h3 class="text-lg font-medium text-textColor mb-1">${this.escapeHtml(product.NomProduit)}</h3>
            <p class="text-accent font-bold text-xl">${this.formatPrice(product.Prix)} DA</p>
            
            <!-- Disponibilité (icône seulement) -->
            ${
              product.Stock > 0
                ? '<div class="flex items-center gap-1 mt-2 text-green-600"><i class="bx bx-check"></i><span class="text-sm">Disponible</span></div>'
                : '<div class="flex items-center gap-1 mt-2 text-red-600"><i class="bx bx-x"></i><span class="text-sm">Indisponible</span></div>'
            }
          </div>
          
          <!-- Boutons d'action -->
          <div class="p-4 pt-0 flex flex-col gap-2">
            <!-- Bouton Voir détails -->
            <a href="product.php?id=${product.IdProduit}" 
               class="w-full px-3 py-2 bg-primary text-textColor rounded-full hover:bg-accent hover:text-white transition-colors text-sm flex items-center justify-center gap-2 font-medium">
              <i class='bx bx-show'></i> Voir détails
            </a>
            
            <!-- Boutons Ajouter au panier et Favoris -->
            <div class="flex gap-2">
              ${
                product.Stock > 0
                  ? `<a href="php/cart_actions.php?action=ajouter&produitId=${product.IdProduit}&quantite=1" 
                       class="flex-1 px-3 py-2 bg-accent text-white rounded-full hover:bg-accent/80 transition-colors text-sm flex items-center justify-center gap-1">
                      <i class='bx bx-cart-add'></i> Ajouter
                    </a>`
                  : `<button disabled 
                            class="flex-1 px-3 py-2 bg-gray-300 text-gray-500 rounded-full cursor-not-allowed text-sm flex items-center justify-center gap-1">
                      <i class='bx bx-cart-add'></i> Ajouter
                    </button>`
              }
              
              <!-- Bouton Favoris -->
              <button class="favorite-btn px-3 py-2 bg-gray-100 text-gray-600 rounded-full hover:bg-red-100 hover:text-red-600 transition-colors text-sm flex items-center justify-center"
                      title="Ajouter aux favoris"
                      data-product-id="${product.IdProduit}">
                <i class='bx bx-heart'></i>
              </button>
            </div>
          </div>
        </div>
      `,
      )
      .join("")

    // Ajouter les événements aux boutons favoris après le rendu
    this.attachFavoriteEvents()

    console.log("Produits rendus:", products.length)
  }

  // Nouvelle méthode pour attacher les événements aux boutons favoris
  attachFavoriteEvents() {
    const favoriteButtons = document.querySelectorAll(".favorite-btn")
    favoriteButtons.forEach((button) => {
      button.addEventListener("click", (e) => {
        e.preventDefault()
        const productId = Number.parseInt(button.getAttribute("data-product-id"))
        this.toggleFavorite(productId, button)
      })
    })
  }

  // Méthode pour gérer les favoris
  toggleFavorite(productId, button) {
    console.log("toggleFavorite appelé pour produit:", productId)
    console.log("Utilisateur connecté:", this.isLoggedIn)

    if (!this.isLoggedIn) {
      alert("Veuillez vous connecter pour ajouter des produits aux favoris")
      window.location.href = "connexion.html"
      return
    }

    const icon = button.querySelector("i")

    // Désactiver le bouton temporairement
    button.disabled = true

    // CORRECTION: Utiliser le bon nom de fichier et le bon chemin
    fetch("php/favorites_actions.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `action=toggle&produitId=${productId}`,
    })
      .then((response) => {
        console.log("Réponse reçue:", response)
        return response.json()
      })
      .then((data) => {
        console.log("Données reçues:", data)
        if (data.success) {
          if (data.action === "added") {
            icon.classList.remove("bx-heart")
            icon.classList.add("bxs-heart")
            button.classList.remove("bg-gray-100", "text-gray-600")
            button.classList.add("bg-red-100", "text-red-600")
            button.title = "Retirer des favoris"
          } else {
            icon.classList.remove("bxs-heart")
            icon.classList.add("bx-heart")
            button.classList.remove("bg-red-100", "text-red-600")
            button.classList.add("bg-gray-100", "text-gray-600")
            button.title = "Ajouter aux favoris"
          }
        } else {
          alert("Erreur: " + data.message)
        }
      })
      .catch((error) => {
        console.error("Erreur:", error)
        alert("Une erreur est survenue")
      })
      .finally(() => {
        // Réactiver le bouton
        button.disabled = false
      })
  }

  updateActiveFilter(category) {
    const filters = document.querySelectorAll(".category-filter")
    filters.forEach((filter) => {
      const filterCategory = filter.getAttribute("data-category")
      if (filterCategory === category) {
        filter.classList.remove("bg-primary", "text-textColor")
        filter.classList.add("bg-accent", "text-white")
      } else {
        filter.classList.remove("bg-accent", "text-white")
        filter.classList.add("bg-primary", "text-textColor")
      }
    })
  }

  updateTitle(category) {
    const title = document.getElementById("products-title")
    if (!title) return

    if (category === "all") {
      title.textContent = "Tous nos produits"
    } else {
      const categoryObj = this.categories.find((cat) => cat.NomCategorie.toLowerCase() === category.toLowerCase())
      title.textContent = `Nos ${categoryObj ? categoryObj.NomCategorie : category}`
    }
  }

  updateURL(category) {
    const url = new URL(window.location)
    if (category === "all") {
      url.searchParams.delete("category")
    } else {
      url.searchParams.set("category", category)
    }
    window.history.pushState({}, "", url)
  }

  showLoading(show) {
    const loading = document.getElementById("loading")
    if (loading) {
      loading.classList.toggle("hidden", !show)
    }
  }

  showError(message) {
    const errorDiv = document.getElementById("error-message")
    const errorText = document.getElementById("error-text")

    if (errorDiv && errorText) {
      errorText.textContent = message
      errorDiv.classList.remove("hidden")
    }
    console.error("Erreur affichée:", message)
  }

  hideError() {
    const errorDiv = document.getElementById("error-message")
    if (errorDiv) {
      errorDiv.classList.add("hidden")
    }
  }

  escapeHtml(text) {
    const div = document.createElement("div")
    div.textContent = text
    return div.innerHTML
  }

  formatPrice(price) {
    return new Intl.NumberFormat("fr-FR", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(price)
  }
}

// Initialiser le gestionnaire de catégories quand le DOM est chargé
document.addEventListener("DOMContentLoaded", () => {
  console.log("DOM chargé, initialisation du CategoryManager...")
  new CategoryManager()
})
