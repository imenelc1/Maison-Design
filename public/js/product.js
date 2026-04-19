// Script pour la page produit - Version corrigée
document.addEventListener("DOMContentLoaded", () => {
  console.log("Page produit chargée")

  // Gestion de la galerie d'images
  initImageGallery()

  // Gestion des boutons de quantité
  initQuantityControls()

  // Gestion du bouton d'ajout au panier
  initAddToCartButton()

  // Gestion du bouton favoris
  initFavoriteButton()
})

function initImageGallery() {
  const mainImage = document.getElementById("main-product-image")
  const thumbnails = document.querySelectorAll(".thumbnail-item img")

  thumbnails.forEach((thumbnail, index) => {
    thumbnail.addEventListener("click", () => {
      if (mainImage) {
        mainImage.src = thumbnail.getAttribute("data-full-image") || thumbnail.src
      }

      // Mettre à jour les bordures des miniatures
      document.querySelectorAll(".thumbnail-item").forEach((item) => {
        item.classList.remove("border-accent")
        item.classList.add("border-transparent")
      })
      thumbnail.parentElement.classList.remove("border-transparent")
      thumbnail.parentElement.classList.add("border-accent")
    })
  })
}

function initQuantityControls() {
  const quantityInput = document.getElementById("quantity")
  const decreaseBtn = document.getElementById("decrease-quantity")
  const increaseBtn = document.getElementById("increase-quantity")

  if (!quantityInput) return

  const maxStock = window.productData?.stock || 1

  if (decreaseBtn) {
    decreaseBtn.addEventListener("click", () => {
      const currentValue = Number.parseInt(quantityInput.value) || 1
      if (currentValue > 1) {
        quantityInput.value = currentValue - 1
      }
    })
  }

  if (increaseBtn) {
    increaseBtn.addEventListener("click", () => {
      const currentValue = Number.parseInt(quantityInput.value) || 1
      if (currentValue < maxStock) {
        quantityInput.value = currentValue + 1
      }
    })
  }

  // Validation de la saisie
  quantityInput.addEventListener("input", () => {
    let value = Number.parseInt(quantityInput.value) || 1
    if (value < 1) value = 1
    if (value > maxStock) value = maxStock
    quantityInput.value = value
  })
}

function initAddToCartButton() {
  const addToCartBtn = document.getElementById("add-to-cart-btn")
  if (!addToCartBtn) return

  // Stocker le contenu original
  addToCartBtn.setAttribute("data-original-content", addToCartBtn.innerHTML)

  addToCartBtn.addEventListener("click", function (e) {
    e.preventDefault()

    const productId = window.productData?.id
    const quantityInput = document.getElementById("quantity")
    const quantity = quantityInput ? Number.parseInt(quantityInput.value) || 1 : 1

    if (!productId) {
      window.cartManager.showNotification("Erreur: ID du produit non trouvé", "error")
      return
    }

    // Utiliser le CartManager global
    window.cartManager.addToCart(productId, quantity, this)
  })
}

function initFavoriteButton() {
  const favoriteBtn = document.getElementById("favorite-btn")
  if (!favoriteBtn) return

  favoriteBtn.addEventListener("click", function (e) {
    e.preventDefault()

    const productId = window.productData?.id

    if (!window.sessionData?.isLoggedIn) {
      window.cartManager.showNotification("Veuillez vous connecter pour ajouter des produits aux favoris", "error")
      setTimeout(() => {
        window.location.href = "connexion.php"
      }, 1500)
      return
    }

    if (!productId) {
      window.cartManager.showNotification("Erreur: ID du produit non trouvé", "error")
      return
    }

    const icon = this.querySelector("i")
    const originalContent = this.innerHTML
    this.disabled = true
    this.innerHTML = '<i class="bx bx-loader-alt animate-spin"></i> Traitement...'

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
            this.classList.remove("bg-gray-100", "text-gray-600")
            this.classList.add("bg-red-100", "text-red-600")
            this.title = "Retirer des favoris"
            window.cartManager.showNotification("Produit ajouté aux favoris !", "success")
          } else {
            icon.classList.remove("fas", "fa-heart", "text-red-600")
            icon.classList.add("far", "fa-heart")
            this.classList.remove("bg-red-100", "text-red-600")
            this.classList.add("bg-gray-100", "text-gray-600")
            this.title = "Ajouter aux favoris"
            window.cartManager.showNotification("Produit retiré des favoris", "success")
          }
        } else {
          window.cartManager.showNotification("Erreur: " + data.message, "error")
        }
      })
      .catch((error) => {
        console.error("Erreur:", error)
        window.cartManager.showNotification("Une erreur est survenue", "error")
      })
      .finally(() => {
        this.disabled = false
        this.innerHTML = originalContent
      })
  })
}
