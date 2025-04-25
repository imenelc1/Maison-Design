/**
 * Maison Design - Script pour la page des catégories
 * Ce script gère le filtrage des produits par catégorie et le chargement depuis la BDD
 */

// Variable globale pour stocker tous les produits
let allProducts = []

document.addEventListener("DOMContentLoaded", () => {
  console.log("Page des catégories chargée")

  // Charger les produits depuis la base de données
  loadProducts()
})

/**
 * Charge les produits depuis la base de données
 */
function loadProducts() {
  const productsContainer = document.getElementById("products-container")
  const noProductsMessage = document.getElementById("no-products")

  if (!productsContainer || !noProductsMessage) {
    console.error("Éléments de produits non trouvés")
    return
  }

  // Afficher un indicateur de chargement
  productsContainer.innerHTML =
    '<div class="col-span-full text-center py-12"><div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-accent"></div><p class="mt-2 text-textColor">Chargement des produits...</p></div>'

  // Essayer plusieurs chemins possibles
  tryFetchFromMultiplePaths([
    "./php/get-products.php",
    "../php/get-products.php",
    "/php/get-products.php",
    "./products.json",
    "../products.json",
    "/products.json",
  ])

  function tryFetchFromMultiplePaths(paths, index = 0) {
    if (index >= paths.length) {
      // Tous les chemins ont échoué
      productsContainer.innerHTML = `
        <div class="col-span-full text-center py-12 text-red-500">
          <p>Une erreur est survenue lors du chargement des produits.</p>
          <p class="text-sm mt-2">Impossible d'accéder aux données depuis aucun chemin.</p>
          <p class="text-sm mt-2">Vérifiez la console pour plus de détails.</p>
        </div>
      `
      return
    }

    const path = paths[index]
    console.log(`Tentative de chargement depuis: ${path}`)

    fetch(path)
      .then((response) => {
        if (!response.ok) {
          throw new Error(`Erreur HTTP: ${response.status}`)
        }
        return response.json()
      })
      .then((data) => {
        if (data.success) {
          console.log(`Chargement réussi depuis: ${path}`)

          // Stocker tous les produits dans la variable globale
          allProducts = data.products
          console.log(`Nombre total de produits chargés: ${allProducts.length}`)

          // Initialiser les filtres et appliquer le filtre initial
          setupCategoryFilters()

          // Appliquer le filtre initial basé sur l'URL
          applyInitialFilter()
        } else {
          throw new Error(data.message || "Erreur lors du chargement des produits")
        }
      })
      .catch((error) => {
        console.error(`Échec du chargement depuis ${path}:`, error)
        // Essayer le chemin suivant
        tryFetchFromMultiplePaths(paths, index + 1)
      })
  }
}

/**
 * Configure les filtres de catégories
 */
function setupCategoryFilters() {
  const categoryFilters = document.querySelectorAll(".category-filter")

  if (!categoryFilters.length) {
    console.error("Filtres de catégories non trouvés")
    return
  }

  console.log(`Nombre de filtres de catégories trouvés: ${categoryFilters.length}`)

  // Ajouter les écouteurs d'événements aux filtres
  categoryFilters.forEach((filter) => {
    filter.addEventListener("click", () => {
      const category = filter.dataset.category
      console.log(`Filtre cliqué: ${category}`)

      // Mettre à jour l'état actif des filtres
      categoryFilters.forEach((f) => {
        f.classList.remove("active", "bg-accent", "text-white")
        f.classList.add("bg-primary", "text-textColor")
      })
      filter.classList.remove("bg-primary", "text-textColor")
      filter.classList.add("active", "bg-accent", "text-white")

      // Filtrer les produits
      filterProducts(category)

      // Mettre à jour l'URL sans recharger la page
      const url = new URL(window.location)
      url.searchParams.set("category", category)
      window.history.pushState({}, "", url)
    })
  })
}

/**
 * Applique le filtre initial basé sur l'URL
 */
function applyInitialFilter() {
  // Vérifier s'il y a un paramètre de catégorie dans l'URL
  const urlParams = new URLSearchParams(window.location.search)
  const categoryParam = urlParams.get("category")

  console.log(`Paramètre de catégorie dans l'URL: ${categoryParam}`)

  if (categoryParam) {
    // Trouver le filtre correspondant
    const categoryFilter = document.querySelector(`.category-filter[data-category="${categoryParam}"]`)

    if (categoryFilter) {
      console.log(`Filtre trouvé pour la catégorie: ${categoryParam}`)

      // Simuler un clic sur le filtre
      categoryFilter.click()
    } else {
      console.warn(`Aucun filtre trouvé pour la catégorie: ${categoryParam}`)
      // Afficher tous les produits par défaut
      filterProducts("all")

      // Activer le filtre "Tous"
      const allFilter = document.querySelector('.category-filter[data-category="all"]')
      if (allFilter) {
        allFilter.classList.add("active", "bg-accent", "text-white")
        allFilter.classList.remove("bg-primary", "text-textColor")
      }
    }
  } else {
    console.log("Aucun paramètre de catégorie dans l'URL, affichage de tous les produits")
    // Afficher tous les produits par défaut
    filterProducts("all")

    // Activer le filtre "Tous"
    const allFilter = document.querySelector('.category-filter[data-category="all"]')
    if (allFilter) {
      allFilter.classList.add("active", "bg-accent", "text-white")
      allFilter.classList.remove("bg-primary", "text-textColor")
    }
  }
}

/**
 * Filtre les produits par catégorie
 * @param {string} category - La catégorie à filtrer
 */
function filterProducts(category) {
  const productsContainer = document.getElementById("products-container")
  const productsTitle = document.getElementById("products-title")
  const noProductsMessage = document.getElementById("no-products")

  if (!productsContainer || !productsTitle || !noProductsMessage) {
    console.error("Éléments nécessaires non trouvés")
    return
  }

  console.log(`Filtrage des produits par catégorie: ${category}`)

  // Mettre à jour le titre
  if (category === "all") {
    productsTitle.textContent = "Tous nos produits"
  } else {
    // Trouver le nom de la catégorie à partir du bouton actif
    const activeFilter = document.querySelector(`.category-filter[data-category="${category}"]`)
    if (activeFilter) {
      productsTitle.textContent = `Nos ${activeFilter.textContent.trim()}`
    }
  }

  // Filtrer les produits
  let filteredProducts = []
  if (category === "all") {
    filteredProducts = allProducts
  } else {
    filteredProducts = allProducts.filter((product) => {
      // Vérifier si la catégorie du produit correspond à la catégorie sélectionnée
      // Convertir en minuscules pour une comparaison insensible à la casse
      const productCategory = String(product.categoryId).toLowerCase()
      const selectedCategory = category.toLowerCase()

      console.log(`Produit: ${product.name}, Catégorie: ${productCategory}, Sélectionnée: ${selectedCategory}`)

      return (
        productCategory === selectedCategory ||
        productCategory.includes(selectedCategory) ||
        selectedCategory.includes(productCategory)
      )
    })
  }

  console.log(`Nombre de produits filtrés: ${filteredProducts.length}`)

  // Afficher les produits filtrés
  renderFilteredProducts(filteredProducts)

  // Afficher ou masquer le message "Aucun produit"
  if (filteredProducts.length === 0) {
    noProductsMessage.classList.remove("hidden")
  } else {
    noProductsMessage.classList.add("hidden")
  }
}

/**
 * Affiche les produits filtrés dans le conteneur
 * @param {Array} products - Liste des produits à afficher
 */
function renderFilteredProducts(products) {
  const productsContainer = document.getElementById("products-container")

  if (!productsContainer) {
    console.error("Conteneur de produits non trouvé")
    return
  }

  // Vider le conteneur
  productsContainer.innerHTML = ""

  if (products.length === 0) {
    productsContainer.innerHTML =
      '<div class="col-span-full text-center py-12"><p class="text-textColor/70">Aucun produit disponible pour cette catégorie.</p></div>'
    return
  }

  // Ajouter chaque produit
  products.forEach((product) => {
    const productElement = document.createElement("div")
    productElement.className = "product-item bg-white rounded-xl overflow-hidden shadow-md"
    productElement.dataset.category = product.categoryId

    // Formater le prix avec 2 décimales et espace comme séparateur de milliers
    const formattedPrice = Number.parseFloat(product.price).toLocaleString("fr-DZ", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    })

    productElement.innerHTML = `
      <div class="relative h-[250px]">
        <img src="${product.image}" alt="${product.name}" class="w-full h-full object-cover">
        ${product.stock <= 0 ? '<div class="absolute top-2 right-2 bg-red-500 text-white px-3 py-1 rounded-full text-sm">Rupture</div>' : ""}
      </div>
      <div class="p-4">
        <h3 class="text-lg font-medium text-textColor">${product.name}</h3>
        <p class="text-sm text-gray-600 mt-1 line-clamp-2">${product.description}</p>
        <p class="text-accent font-bold mt-2">${formattedPrice} DA</p>
        <a href="product.html?id=${product.id}" class="mt-4 inline-block px-4 py-2 bg-accent text-white rounded-full hover:bg-accent/90 transition-colors">
          Voir détails
        </a>
      </div>
    `

    productsContainer.appendChild(productElement)
  })
}
