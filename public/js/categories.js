// Script pour la page catégories - Version corrigée
document.addEventListener("DOMContentLoaded", () => {
  console.log("Page catégories chargée")

  const categoryFilters = document.querySelectorAll(".category-filter")
  const productsContainer = document.getElementById("products-container")
  const productsTitle = document.getElementById("products-title")
  const loading = document.getElementById("loading")
  const errorMessage = document.getElementById("error-message")

  let currentCategory = window.initialData?.selectedCategory || "all"

  // Attacher les événements aux boutons favoris existants
  attachFavoriteEvents()

  // Attacher les événements aux boutons d'ajout au panier existants
  attachCartEvents()

  // Gestion des filtres de catégories
  categoryFilters.forEach((filter) => {
    filter.addEventListener("click", function (e) {
      e.preventDefault()
      const category = this.getAttribute("data-category")

      if (category === currentCategory) {
        console.log("Catégorie déjà sélectionnée:", category)
        return
      }

      // Mettre à jour l'apparence des filtres
      categoryFilters.forEach((f) => {
        f.classList.remove("bg-accent", "text-white")
        f.classList.add("bg-primary", "text-textColor")
      })
      this.classList.remove("bg-primary", "text-textColor")
      this.classList.add("bg-accent", "text-white")

      // Charger les produits
      loadProducts(category)
    })
  })

  async function loadProducts(category) {
    currentCategory = category

    try {
      loading.classList.remove("hidden")
      errorMessage.classList.add("hidden")

      const url =
        category === "all" ? "categories.php?ajax=1" : `categories.php?category=${encodeURIComponent(category)}&ajax=1`

      const response = await fetch(url)
      const data = await response.json()

      loading.classList.add("hidden")

      if (data.success) {
        // Mettre à jour les favoris globaux
        window.userFavorites = data.userFavorites || []

        // Mettre à jour le titre
        productsTitle.textContent =
          category === "all"
            ? `Tous nos produits (${data.products.length})`
            : `Nos ${category} (${data.products.length})`

        // Mettre à jour l'URL
        const newUrl = category === "all" ? "categories.php" : `categories.php?category=${encodeURIComponent(category)}`
        window.history.pushState({}, "", newUrl)

        // Afficher les produits
        updateProductsDisplay(data.products)
      } else {
        showError(data.message || "Erreur lors du chargement")
      }
    } catch (error) {
      loading.classList.add("hidden")
      showError("Une erreur est survenue lors du chargement")
      console.error("Erreur:", error)
    }
  }

  function updateProductsDisplay(products) {
    if (!products || products.length === 0) {
      productsContainer.innerHTML = `
                <div class="col-span-full text-center py-12">
                    <p class="text-xl text-textColor/70">Aucun produit trouvé dans cette catégorie.</p>
                </div>
            `
      return
    }

    // Éliminer les doublons côté client
    const uniqueProducts = []
    const seenIds = new Set()

    products.forEach((product) => {
      if (!seenIds.has(product.IdProduit)) {
        seenIds.add(product.IdProduit)
        uniqueProducts.push(product)
      }
    })

    let html = ""
    uniqueProducts.forEach((product) => {
      const isFavorite = product.isFavorite || false
      const isInStock = product.Stock > 0

      html += `
                <div class="product-item bg-white rounded-xl overflow-hidden shadow-md transition-transform hover:-translate-y-1" 
                     data-product-id="${product.IdProduit}">
                    
                    <a href="produit.php?id=${product.IdProduit}" class="block">
                        <div class="product-image h-48 overflow-hidden relative group">
                            <img src="${product.image}" 
                                 alt="${product.NomProduit}" 
                                 class="w-full h-full object-cover transition-transform group-hover:scale-105"
                                 onerror="this.src='images/placeholder.jpeg'">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
                                <span class="text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300 font-medium">
                                    Voir détails
                                </span>
                            </div>
                        </div>
                    </a>
                    
                    <div class="product-info p-4">
                        <h3 class="text-lg font-medium text-textColor mb-1">${product.NomProduit}</h3>
                        <p class="text-accent font-bold text-xl">${Number.parseFloat(product.Prix).toLocaleString("fr-FR", { minimumFractionDigits: 2 })} DA</p>
                        
                        ${
                          isInStock
                            ? `<div class="flex items-center gap-1 mt-2 text-green-600">
                                <i class='bx bx-check'></i>
                                <span class="text-sm">Disponible (${product.Stock})</span>
                            </div>`
                            : `<div class="flex items-center gap-1 mt-2 text-red-600">
                                <i class='bx bx-x'></i>
                                <span class="text-sm">Indisponible</span>
                            </div>`
                        }
                    </div>
                    
                    <div class="p-4 pt-0 flex flex-col gap-2">
                        <a href="produit.php?id=${product.IdProduit}" 
                           class="flex-1 bg-primary text-textColor py-2 px-3 rounded-full hover:bg-accent hover:text-white transition-colors flex items-center justify-center text-sm">
                            Voir détails
                        </a>
                        
                        <div class="flex gap-2">
                            ${
                              isInStock
                                ? `
    <button class="add-to-cart-btn flex-1 bg-accent text-white py-2 px-3 rounded-full hover:bg-accent/90 transition-colors flex items-center justify-center text-sm"
            data-product-id="${product.IdProduit}"
            data-original-content="<i class='bx bx-cart mr-1'></i> Ajouter">
        <i class='bx bx-cart mr-1'></i> Ajouter
    </button>
`
                                : `
    <button disabled 
            class="flex-1 bg-gray-300 text-gray-500 py-2 px-3 rounded-full cursor-not-allowed flex items-center justify-center text-sm">
        <i class='bx bx-cart mr-1'></i> Indisponible
    </button>
`
                            }
                            
                            <button class="favorite-btn px-3 py-2 ${isFavorite ? "bg-red-100 text-red-600" : "bg-gray-100 text-gray-600"} rounded-full hover:bg-red-100 hover:text-red-600 transition-colors flex items-center justify-center"
                                    title="${isFavorite ? "Retirer des favoris" : "Ajouter aux favoris"}"
                                    data-product-id="${product.IdProduit}">
                                <i class='${isFavorite ? "bx bxs-heart text-red-600" : "bx bx-heart"}'></i>
                            </button>
                        </div>
                    </div>
                </div>
            `
    })

    productsContainer.innerHTML = html

    // Réattacher les événements
    attachFavoriteEvents()
    attachCartEvents()

    console.log(`Produits affichés: ${uniqueProducts.length}`)
  }

  function attachCartEvents() {
    const cartButtons = document.querySelectorAll(".add-to-cart-btn")
    cartButtons.forEach((button) => {
      // Supprimer les anciens événements
      const newButton = button.cloneNode(true)
      button.parentNode.replaceChild(newButton, button)

      newButton.addEventListener("click", function (e) {
        e.preventDefault()
        const productId = Number.parseInt(this.getAttribute("data-product-id"))

        if (!productId) {
          window.cartManager.showNotification("Erreur: ID du produit non trouvé", "error")
          return
        }

        // Utiliser le CartManager global
        window.cartManager.addToCart(productId, 1, this)
      })
    })
  }

  function attachFavoriteEvents() {
    const favoriteButtons = document.querySelectorAll(".favorite-btn")
    favoriteButtons.forEach((button) => {
      // Supprimer les anciens événements
      const newButton = button.cloneNode(true)
      button.parentNode.replaceChild(newButton, button)

      newButton.addEventListener("click", function (e) {
        e.preventDefault()
        const productId = Number.parseInt(this.getAttribute("data-product-id"))

        if (!window.sessionData?.isLoggedIn) {
          window.cartManager.showNotification("Veuillez vous connecter pour ajouter des produits aux favoris", "error")
          setTimeout(() => {
            window.location.href = "connexion.php"
          }, 1500)
          return
        }

        toggleFavorite(productId, this)
      })
    })
  }

  async function toggleFavorite(productId, button) {
    const icon = button.querySelector("i")
    const originalContent = button.innerHTML
    button.disabled = true
    button.innerHTML = '<i class="bx bx-loader-alt animate-spin"></i>'

    try {
      const response = await fetch("php/favorites_actions.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `action=toggle&produitId=${productId}`,
      })

      const data = await response.json()

      if (data.success) {
        if (data.action === "added") {
          icon.classList.remove("far", "fa-heart")
          icon.classList.add("fas", "fa-heart", "text-red-600")
          button.classList.remove("bg-gray-100", "text-gray-600")
          button.classList.add("bg-red-100", "text-red-600")
          button.title = "Retirer des favoris"
          window.cartManager.showNotification("Produit ajouté aux favoris !", "success")

          if (!window.userFavorites.includes(productId)) {
            window.userFavorites.push(productId)
          }
        } else {
          icon.classList.remove("fas", "fa-heart", "text-red-600")
          icon.classList.add("far", "fa-heart")
          button.classList.remove("bg-red-100", "text-red-600")
          button.classList.add("bg-gray-100", "text-gray-600")
          button.title = "Ajouter aux favoris"
          window.cartManager.showNotification("Produit retiré des favoris", "success")

          window.userFavorites = window.userFavorites.filter((id) => id !== productId)
        }
      } else {
        window.cartManager.showNotification("Erreur: " + data.message, "error")
      }
    } catch (error) {
      console.error("Erreur:", error)
      window.cartManager.showNotification("Une erreur est survenue", "error")
    } finally {
      button.disabled = false
      button.innerHTML = originalContent
    }
  }

  function showError(message) {
    if (errorMessage) {
      const errorText = document.getElementById("error-text")
      if (errorText) {
        errorText.textContent = message
      }
      errorMessage.classList.remove("hidden")
    }
  }
})

// Fonction globale pour compatibilité
window.addToCart = (productId, quantity = 1) => {
  const button = document.querySelector(`[data-product-id="${productId}"].add-to-cart-btn`)
  window.cartManager.addToCart(productId, quantity, button)
}
