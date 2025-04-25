/**
 * Maison Design - Script pour la page de détail du produit
 * Ce script gère l'affichage des détails d'un produit et des produits similaires
 */

document.addEventListener("DOMContentLoaded", () => {
  // Récupérer l'ID du produit depuis l'URL
  const urlParams = new URLSearchParams(window.location.search)
  const productId = urlParams.get("id")

  if (!productId) {
    showError("ID du produit non spécifié")
    return
  }

  // Charger les détails du produit
  loadProductDetails(productId)

  // Initialiser les gestionnaires d'événements
  initEventHandlers()
})

/**
 * Charge les détails du produit depuis l'API
 * @param {string|number} productId - ID du produit à charger
 */
function loadProductDetails(productId) {
  fetch(`php/get-product-details.php?id=${productId}`)
    .then((response) => {
      if (!response.ok) {
        throw new Error("Erreur réseau")
      }
      return response.json()
    })
    .then((data) => {
      if (data.success) {
        // Afficher les détails du produit
        displayProductDetails(data.product)
        // Afficher les produits similaires
        displaySimilarProducts(data.product.similarProducts)
      } else {
        throw new Error(data.message || "Erreur lors du chargement des détails du produit")
      }
    })
    .catch((error) => {
      console.error("Erreur:", error)
      showError(error.message)
    })
}
/**
 * Affiche les détails du produit dans la page
 * @param {Object} product - Données du produit
 */
function displayProductDetails(product) {
  // Mettre à jour le titre de la page
  document.title = `${product.name} - Maison Design`

  // Mettre à jour le fil d'Ariane
  document.getElementById("product-category").textContent = product.category
  document.getElementById("product-name-breadcrumb").textContent = product.name

  // Mettre à jour les informations du produit
  document.getElementById("product-name").textContent = product.name
  document.getElementById("product-category-badge").textContent = product.category

  // Formater le prix avec 2 décimales et espace comme séparateur de milliers
  const formattedPrice = Number.parseFloat(product.price).toLocaleString("fr-DZ", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
  document.getElementById("product-price").textContent = `${formattedPrice} DA`

  // Mettre à jour la description
  document.getElementById("product-description").textContent = product.description

  // Mettre à jour le badge de stock
  const stockBadge = document.getElementById("product-stock-badge")
  if (product.stock > 0) {
    stockBadge.textContent = `En stock (${product.stock})`
    stockBadge.className = "bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm"

    // Activer le bouton d'ajout au panier
    document.getElementById("add-to-cart").disabled = false
  } else {
    stockBadge.textContent = "Rupture de stock"
    stockBadge.className = "bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm"

    // Désactiver le bouton d'ajout au panier
    const addToCartButton = document.getElementById("add-to-cart")
    addToCartButton.disabled = true
    addToCartButton.classList.add("opacity-50", "cursor-not-allowed")
  }

  // Mettre à jour l'image principale
  const mainImage = document.getElementById("main-product-image")
  if (product.images && product.images.length > 0) {
    mainImage.src = product.images[0]
    mainImage.alt = product.name
  }

  // Mettre à jour les miniatures
  const thumbnailsContainer = document.getElementById("product-thumbnails")
  thumbnailsContainer.innerHTML = ""

  if (product.images && product.images.length > 0) {
    product.images.forEach((image, index) => {
      const thumbnailDiv = document.createElement("div")
      thumbnailDiv.className =
        "bg-white rounded-lg overflow-hidden shadow-sm cursor-pointer hover:shadow-md transition-shadow"

      const thumbnailImg = document.createElement("img")
      thumbnailImg.src = image
      thumbnailImg.alt = `${product.name} - Image ${index + 1}`
      thumbnailImg.className = "w-full h-20 object-cover"
      thumbnailImg.dataset.fullImage = image

      // Ajouter un écouteur d'événement pour changer l'image principale
      thumbnailImg.addEventListener("click", function () {
        mainImage.src = this.dataset.fullImage
      })

      thumbnailDiv.appendChild(thumbnailImg)
      thumbnailsContainer.appendChild(thumbnailDiv)
    })
  }

  // Limiter la quantité maximale au stock disponible
  const quantityInput = document.getElementById("quantity")
  quantityInput.max = product.stock
}

/**
 * Affiche les produits similaires
 * @param {Array} products - Liste des produits similaires
 */
function displaySimilarProducts(products) {
  const container = document.getElementById("similar-products-container")

  if (!container) {
    console.error("Conteneur de produits similaires non trouvé")
    return
  }

  // Vider le conteneur
  container.innerHTML = ""

  if (!products || products.length === 0) {
    container.innerHTML =
      '<div class="col-span-full text-center py-6"><p class="text-textColor/70">Aucun produit similaire disponible.</p></div>'
    return
  }

  // Ajouter chaque produit
  products.forEach((product) => {
    const productElement = document.createElement("div")
    productElement.className = "product-item bg-white rounded-xl overflow-hidden shadow-md"

    // Formater le prix avec 2 décimales et espace comme séparateur de milliers
    const formattedPrice = Number.parseFloat(product.price).toLocaleString("fr-DZ", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    })

    productElement.innerHTML = `
        <div class="relative h-[200px]">
          <img src="${product.image}" alt="${product.name}" class="w-full h-full object-cover">
          ${product.stock <= 0 ? '<div class="absolute top-2 right-2 bg-red-500 text-white px-3 py-1 rounded-full text-sm">Rupture</div>' : ""}
        </div>
        <div class="p-4">
          <h3 class="text-lg font-medium text-textColor">${product.name}</h3>
          <p class="text-accent font-bold mt-2">${formattedPrice} DA</p>
          <a href="product.html?id=${product.id}" class="mt-4 inline-block px-4 py-2 bg-accent text-white rounded-full hover:bg-accent/90 transition-colors">
            Voir détails
          </a>
        </div>
      `

    container.appendChild(productElement)
  })
}

/**
 * Initialise les gestionnaires d'événements pour les boutons de quantité et d'ajout au panier
 */
function initEventHandlers() {
  const quantityInput = document.getElementById("quantity")
  const decreaseBtn = document.getElementById("decrease-quantity")
  const increaseBtn = document.getElementById("increase-quantity")
  const addToCartBtn = document.getElementById("add-to-cart")
  const addToWishlistBtn = document.getElementById("add-to-wishlist")

  // Gestionnaire pour diminuer la quantité
  decreaseBtn.addEventListener("click", () => {
    const currentValue = Number.parseInt(quantityInput.value)
    if (currentValue > 1) {
      quantityInput.value = currentValue - 1
    }
  })

  // Gestionnaire pour augmenter la quantité
  increaseBtn.addEventListener("click", () => {
    const currentValue = Number.parseInt(quantityInput.value)
    let maxValue = Number.parseInt(quantityInput.max)

    if (!maxValue || isNaN(maxValue)) {
      maxValue = 99 // Valeur par défaut si max n'est pas défini
    }

    if (currentValue < maxValue) {
      quantityInput.value = currentValue + 1
    }
  })

  // Gestionnaire pour ajouter au panier
  addToCartBtn.addEventListener("click", () => {
    const urlParams = new URLSearchParams(window.location.search)
    const productId = urlParams.get("id")
    const quantity = Number.parseInt(quantityInput.value)

    // Ici, vous pouvez implémenter la logique d'ajout au panier
    // Par exemple, envoyer une requête AJAX à un script PHP

    alert(`Produit ${productId} ajouté au panier (${quantity} unité(s))`)
  })

  // Gestionnaire pour ajouter aux favoris
  addToWishlistBtn.addEventListener("click", () => {
    const urlParams = new URLSearchParams(window.location.search)
    const productId = urlParams.get("id")

    // Ici, vous pouvez implémenter la logique d'ajout aux favoris

    alert(`Produit ${productId} ajouté aux favoris`)
  })
}

/**
 * Affiche un message d'erreur
 * @param {string} message - Message d'erreur à afficher
 */
function showError(message) {
  const mainContent = document.querySelector("main > div")

  if (mainContent) {
    mainContent.innerHTML = `
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-6 my-8 mx-auto max-w-2xl">
          <h2 class="text-xl font-medium mb-4">Une erreur est survenue</h2>
          <p>${message}</p>
          <a href="categories.html" class="mt-6 inline-block px-4 py-2 bg-accent text-white rounded-full hover:bg-accent/90 transition-colors">
            Retour aux catégories
          </a>
        </div>
      `
  }
}
