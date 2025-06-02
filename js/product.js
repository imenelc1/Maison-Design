// Fichier JavaScript pour la page produit
document.addEventListener("DOMContentLoaded", () => {
  // Initialiser les interactions UI
  initImageGallery()
  initQuantityButtons()
  initProductActions()
})

// Fonction pour initialiser la galerie d'images
function initImageGallery() {
  const thumbnails = document.querySelectorAll(".thumbnail-item img")
  const mainImage = document.getElementById("main-product-image")

  if (!thumbnails.length || !mainImage) return

  thumbnails.forEach((thumbnail, index) => {
    thumbnail.addEventListener("click", function () {
      // Changer l'image principale
      mainImage.src = this.dataset.fullImage || this.src

      // Mettre à jour les bordures des miniatures
      document.querySelectorAll(".thumbnail-item").forEach((item) => {
        item.classList.remove("border-accent")
        item.classList.add("border-transparent")
      })

      // Ajouter la bordure à la miniature sélectionnée
      this.closest(".thumbnail-item").classList.remove("border-transparent")
      this.closest(".thumbnail-item").classList.add("border-accent")
    })
  })
}

// Fonction pour initialiser les boutons de quantité
function initQuantityButtons() {
  const decreaseBtn = document.getElementById("decrease-quantity")
  const increaseBtn = document.getElementById("increase-quantity")
  const quantityInput = document.getElementById("quantity")

  if (!decreaseBtn || !increaseBtn || !quantityInput) return

  decreaseBtn.addEventListener("click", () => {
    const currentValue = Number.parseInt(quantityInput.value)
    if (currentValue > 1) {
      quantityInput.value = currentValue - 1
    }
  })

  increaseBtn.addEventListener("click", () => {
    const currentValue = Number.parseInt(quantityInput.value)
    const maxStock = Number.parseInt(quantityInput.max)
    if (currentValue < maxStock) {
      quantityInput.value = currentValue + 1
    }
  })

  // Validation de la quantité lors de la saisie manuelle
  quantityInput.addEventListener("input", function () {
    const max = Number.parseInt(this.getAttribute("max"))
    const min = Number.parseInt(this.getAttribute("min"))
    const value = Number.parseInt(this.value)

    if (value > max) this.value = max
    if (value < min) this.value = min
  })
}

// Fonction pour initialiser les actions du produit (panier, favoris)
function initProductActions() {
  const addToCartBtn = document.getElementById("add-to-cart-btn")
  const favoriteBtn = document.getElementById("favorite-btn")

  // Ajouter au panier
  if (addToCartBtn) {
    addToCartBtn.addEventListener("click", addToCart)
  }

  // Toggle favoris
  if (favoriteBtn) {
    favoriteBtn.addEventListener("click", () => {
      const productId = Number.parseInt(favoriteBtn.getAttribute("data-product-id"))
      toggleFavorite(productId)
    })
  }
}

// Fonction pour ajouter au panier
function addToCart() {
  const quantityInput = document.getElementById("quantity")
  const quantity = quantityInput ? quantityInput.value : 1
  const productId = window.productData.id

  if (!productId) {
    showNotification("Erreur: ID du produit non trouvé", "error")
    return
  }

  fetch("php/cart_actions.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `action=ajouter&produitId=${productId}&quantite=${quantity}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification("Produit ajouté au panier !", "success")
      } else {
        showNotification("Erreur: " + data.message, "error")
      }
    })
    .catch((error) => {
      console.error("Erreur:", error)
      showNotification("Une erreur est survenue", "error")
    })
}

// Fonction pour toggle favoris
function toggleFavorite(productId) {
  console.log("toggleFavorite appelé pour produit:", productId)

  const button = document.getElementById("favorite-btn")
  const icon = button.querySelector("i")

  if (!window.sessionData.isLoggedIn) {
    showNotification("Veuillez vous connecter pour ajouter des produits aux favoris", "error")
    setTimeout(() => {
      window.location.href = "connexion.php"
    }, 1500)
    return
  }

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
          showNotification("Produit ajouté aux favoris !", "success")
          window.productData.isFavorite = true
        } else {
          icon.classList.remove("fas", "fa-heart", "text-red-600")
          icon.classList.add("far", "fa-heart")
          button.classList.remove("bg-red-100", "text-red-600")
          button.classList.add("bg-gray-100", "text-gray-600")
          button.title = "Ajouter aux favoris"
          showNotification("Produit retiré des favoris", "info")
          window.productData.isFavorite = false
        }
      } else {
        showNotification("Erreur: " + data.message, "error")
      }
    })
    .catch((error) => {
      console.error("Erreur:", error)
      showNotification("Une erreur est survenue", "error")
    })
    .finally(() => {
      button.disabled = false
    })
}

// Fonction pour afficher les notifications
function showNotification(message, type = "info") {
  // Supprimer les notifications existantes
  const existingNotifications = document.querySelectorAll(".notification")
  existingNotifications.forEach((notif) => notif.remove())

  const notification = document.createElement("div")
  notification.className = `notification fixed top-4 right-4 z-50 px-4 py-2 rounded-lg text-white ${
    type === "success" ? "bg-green-500" : type === "error" ? "bg-red-500" : "bg-blue-500"
  } transition-all duration-300 transform translate-x-full`
  notification.textContent = message

  document.body.appendChild(notification)

  // Animation d'entrée
  setTimeout(() => {
    notification.classList.remove("translate-x-full")
  }, 100)

  // Animation de sortie
  setTimeout(() => {
    notification.classList.add("translate-x-full")
    setTimeout(() => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification)
      }
    }, 300)
  }, 3000)
}
