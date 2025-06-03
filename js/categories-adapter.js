class CategoryManager {
  constructor() {
    this.currentCategory = window.initialData.selectedCategory || "all"
    this.categories = window.initialData.categories || []
    this.isLoggedIn = window.sessionData ? window.sessionData.isLoggedIn : false
    this.clientId = window.sessionData ? window.sessionData.clientId : null
    this.productsCache = new Map()
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

    categoryFilters.addEventListener("click", (e) => {
      if (e.target.classList.contains("category-filter")) {
        e.preventDefault()
        const category = e.target.getAttribute("data-category")
        console.log("Filtre cliqué:", category)
        this.filterByCategory(category)
      }
    })

    // Gestionnaire pour les boutons d'ajout au panier
    document.addEventListener("click", (e) => {
      if (e.target.closest(".add-to-cart-btn")) {
        e.preventDefault()
        const button = e.target.closest(".add-to-cart-btn")
        const productId = button.getAttribute("data-product-id")
        this.addToCart(productId, button)
      }
    })

    window.addEventListener("popstate", (e) => {
      const urlParams = new URLSearchParams(window.location.search)
      const categoryParam = urlParams.get("category") || "all"
      this.filterByCategory(categoryParam, false)
    })
  }

  async filterByCategory(category, updateURL = true) {
    if (category === this.currentCategory && this.productsCache.has(category)) {
      console.log("Utilisation du cache pour la catégorie:", category)
      this.renderProducts(this.productsCache.get(category))
      return
    }

    try {
      this.showLoading(true)
      this.hideError()
      this.currentCategory = category
      this.updateActiveFilter(category)

      const url =
        category === "all" ? "categories.php?ajax=1" : `categories.php?category=${encodeURIComponent(category)}&ajax=1`

      const response = await fetch(url, {
        headers: {
          "Cache-Control": "no-cache",
          Pragma: "no-cache",
        },
      })

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const data = await response.json()
      console.log("Données reçues pour", category, ":", data)

      if (data.success) {
        const uniqueProducts = this.removeDuplicates(data.products)
        this.productsCache.set(category, uniqueProducts)
        this.renderProducts(uniqueProducts)
        this.updateTitle(category, uniqueProducts.length)

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

  removeDuplicates(products) {
    const uniqueProducts = []
    const seenIds = new Set()

    for (const product of products) {
      if (!seenIds.has(product.IdProduit)) {
        seenIds.add(product.IdProduit)
        uniqueProducts.push(product)
      }
    }

    return uniqueProducts
  }

  renderProducts(products) {
    const productsContainer = document.getElementById("products-container")
    if (!productsContainer) {
      console.error("Container des produits non trouvé")
      return
    }

    let productHTML = ""
    if (products && products.length > 0) {
      products.forEach((product) => {
        const isInStock = product.Stock > 0
        const stockClass = isInStock ? "text-green-600" : "text-red-600"
        const stockText = isInStock ? `En stock (${product.Stock})` : "Rupture de stock"

        productHTML += `
          <div class="w-full sm:w-1/2 md:w-1/3 lg:w-1/4 xl:w-1/4 p-2">
            <div class="bg-white rounded-lg shadow-md overflow-hidden transition-transform hover:-translate-y-1">
              <div class="relative">
                <img src="${this.escapeHtml(product.Image || product.image)}" 
                     alt="${this.escapeHtml(product.Nom || product.NomProduit)}" 
                     class="w-full h-48 object-cover"
                     onerror="this.src='images/placeholder.jpeg'">
                <span class="absolute top-2 right-2 ${stockClass} bg-white px-2 py-1 rounded-full text-xs font-medium">
                  ${stockText}
                </span>
              </div>
              <div class="p-4">
                <h3 class="text-lg font-semibold mb-2 line-clamp-2">${this.escapeHtml(product.Nom || product.NomProduit)}</h3>
                <p class="text-gray-600 text-sm mb-3 line-clamp-2">${this.escapeHtml(product.DescriptionCourte || product.Description || "")}</p>
                <div class="flex items-center justify-between mb-3">
                  <span class="text-accent font-bold text-lg">${this.formatPrice(product.Prix)} DA</span>
                </div>
                <div class="flex gap-2">
                  <a href="produit.php?id=${product.IdProduit}" 
                     class="flex-1 px-3 py-2 bg-gray-100 text-textColor rounded-full hover:bg-gray-200 transition-colors text-sm flex items-center justify-center gap-2 font-medium">
                    <i class='bx bx-show'></i> Détails
                  </a>
                  ${
                    isInStock
                      ? `
                    <button class="add-to-cart-btn flex-1 px-3 py-2 bg-accent text-white rounded-full hover:bg-accent/80 transition-colors text-sm flex items-center justify-center gap-2 font-medium"
                            data-product-id="${product.IdProduit}">
                      <i class='bx bx-cart-add'></i> Ajouter
                    </button>
                  `
                      : `
                    <button disabled class="flex-1 px-3 py-2 bg-gray-300 text-gray-500 rounded-full cursor-not-allowed text-sm flex items-center justify-center gap-2 font-medium">
                      <i class='bx bx-cart-add'></i> Indisponible
                    </button>
                  `
                  }
                </div>
              </div>
            </div>
          </div>
        `
      })
    } else {
      productHTML = `
        <div class="col-span-full text-center py-12">
          <i class='bx bx-package text-6xl text-gray-300 mb-4'></i>
          <p class="text-gray-500 text-lg">Aucun produit trouvé dans cette catégorie.</p>
        </div>
      `
    }

    productsContainer.innerHTML = productHTML
    this.attachFavoriteEvents()
  }

  // MÉTHODE CORRIGÉE: Ajouter au panier - IDENTIQUE À product.js
  async addToCart(productId, button) {
    if (!productId) {
      this.afficherNotification("Erreur: ID du produit non trouvé")
      return
    }

    console.log("Ajout au panier - Produit ID:", productId)

    // Désactiver le bouton pendant la requête
    button.disabled = true
    const originalContent = button.innerHTML
    button.innerHTML = '<i class="bx bx-loader-alt animate-spin"></i> Ajout en cours...'

    try {
      const response = await fetch("php/cart_actions.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: `action=ajouter&produitId=${productId}&quantite=1`,
      })

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const data = await response.json()
      console.log("Réponse du serveur:", data)

      if (data.success) {
        // CORRECTION PRINCIPALE: Utiliser la même méthode que dans product.js
        const cartCount = data.cartCount
        this.updateAllCartCounters(cartCount)
        this.afficherNotification(data.message || "Produit ajouté au panier !")
      } else {
        this.afficherNotification(data.message || "Erreur lors de l'ajout au panier")
      }
    } catch (error) {
      console.error("Erreur lors de l'ajout au panier:", error)
      this.afficherNotification("Une erreur est survenue")
    } finally {
      // Réactiver le bouton
      button.disabled = false
      button.innerHTML = originalContent
    }
  }

  // NOUVELLE MÉTHODE: Identique à celle de product.js
  updateAllCartCounters(count) {
    // Cibler tous les compteurs possibles
    const counters = [
      document.getElementById("cart-counter"),
      document.getElementById("cart-counter-mobile"),
      ...document.querySelectorAll(".cart-badge"),
    ].filter(Boolean)

    // Mettre à jour chaque compteur
    counters.forEach((counter) => {
      counter.textContent = count > 99 ? "99+" : count
      counter.style.display = count > 0 ? "flex" : "none"
    })

    console.log(`Compteur panier mis à jour: ${count} (${counters.length} compteurs trouvés)`)
  }

  // NOUVELLE MÉTHODE: Identique à celle de product.js
  afficherNotification(message) {
    // Créer l'élément de notification s'il n'existe pas
    let notification = document.getElementById("notification")
    if (!notification) {
      notification = document.createElement("div")
      notification.id = "notification"
      notification.className =
        "fixed bottom-4 right-4 bg-accent text-white px-4 py-2 rounded-lg shadow-lg transform translate-y-10 opacity-0 transition-all duration-300 z-50"
      document.body.appendChild(notification)
    }

    // Afficher le message
    notification.textContent = message
    notification.classList.remove("translate-y-10", "opacity-0")

    // Masquer après 3 secondes
    setTimeout(() => {
      notification.classList.add("translate-y-10", "opacity-0")
    }, 3000)
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

  updateTitle(category, productCount) {
    const title = document.getElementById("products-title")
    if (!title) return

    if (category === "all") {
      title.textContent = `Tous nos produits (${productCount})`
    } else {
      const categoryObj = this.categories.find((cat) => cat.NomCategorie.toLowerCase() === category.toLowerCase())
      title.textContent = `Nos ${categoryObj ? categoryObj.NomCategorie : category} (${productCount})`
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
    if (!text) return ""
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

  attachFavoriteEvents() {
    const favoriteButtons = document.querySelectorAll(".favorite-btn")
    favoriteButtons.forEach((button) => {
      button.addEventListener("click", (e) => {
        e.preventDefault()
        const productId = Number.parseInt(button.getAttribute("data-product-id"), 10)
        this.toggleFavorite(productId, button)
      })
    })
  }

  toggleFavorite(productId, button) {
    if (!this.isLoggedIn) {
      this.afficherNotification("Veuillez vous connecter pour ajouter des produits aux favoris")
      setTimeout(() => {
        window.location.href = "connexion.php"
      }, 1500)
      return
    }

    const icon = button.querySelector("i")
    button.disabled = true

    fetch("php/favorites_actions.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `action=toggle&produitId=${productId}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          if (data.action === "added") {
            icon.classList.remove("far", "fa-heart")
            icon.classList.add("fas", "fa-heart", "text-red-600")
            button.classList.remove("bg-gray-100", "text-gray-600")
            button.classList.add("bg-red-100", "text-red-600")
            button.title = "Retirer des favoris"
            this.afficherNotification("Produit ajouté aux favoris !")
          } else {
            icon.classList.remove("fas", "fa-heart", "text-red-600")
            icon.classList.add("far", "fa-heart")
            button.classList.remove("bg-red-100", "text-red-600")
            button.classList.add("bg-gray-100", "text-gray-600")
            button.title = "Ajouter aux favoris"
            this.afficherNotification("Produit retiré des favoris")
          }

          this.productsCache.clear()
          this.filterByCategory(this.currentCategory, false)
        } else {
          this.afficherNotification("Erreur: " + data.message)
        }
      })
      .catch((error) => {
        console.error("Erreur:", error)
        this.afficherNotification("Une erreur est survenue")
      })
      .finally(() => {
        button.disabled = false
      })
  }
}

// Initialisation
document.addEventListener("DOMContentLoaded", () => {
  console.log("DOM chargé, initialisation du CategoryManager...")
  new CategoryManager()
})

// Fonction globale pour compatibilité avec le code existant
window.addToCart = (productId) => {
  const button = document.querySelector(`.add-to-cart-btn[data-product-id="${productId}"]`)
  if (window.categoryManager) {
    window.categoryManager.addToCart(productId, button || null)
  } else {
    // Fallback si le gestionnaire n'est pas disponible
    fetch("php/cart_actions.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: `action=ajouter&produitId=${productId}&quantite=1`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Mettre à jour tous les compteurs possibles
          const counters = [
            document.getElementById("cart-counter"),
            document.getElementById("cart-counter-mobile"),
            ...document.querySelectorAll(".cart-badge"),
          ].filter(Boolean)

          counters.forEach((counter) => {
            counter.textContent = data.cartCount > 99 ? "99+" : data.cartCount
            counter.style.display = data.cartCount > 0 ? "flex" : "none"
          })

          // Notification
          let notification = document.getElementById("notification")
          if (!notification) {
            notification = document.createElement("div")
            notification.id = "notification"
            notification.className =
              "fixed bottom-4 right-4 bg-accent text-white px-4 py-2 rounded-lg shadow-lg transform translate-y-10 opacity-0 transition-all duration-300 z-50"
            document.body.appendChild(notification)
          }

          notification.textContent = "Produit ajouté au panier !"
          notification.classList.remove("translate-y-10", "opacity-0")

          setTimeout(() => {
            notification.classList.add("translate-y-10", "opacity-0")
          }, 3000)
        }
      })
      .catch((error) => {
        console.error("Erreur:", error)
      })
  }
}
