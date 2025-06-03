// Version simplifiée des fonctions de panier
document.addEventListener("DOMContentLoaded", () => {
  // Initialiser le compteur au chargement
  updateCartCounter()

  // Ajouter des écouteurs pour les boutons d'ajout au panier sur toutes les pages
  document.querySelectorAll("[data-add-to-cart]").forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault()

      const productId = this.getAttribute("data-product-id")
      const quantity = Number.parseInt(this.getAttribute("data-quantity") || "1")

      addToCart(productId, quantity)
    })
  })
})

// Fonction simplifiée pour ajouter au panier
function addToCart(productId, quantity = 1) {
  return fetch("php/cart_actions.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
      "X-Requested-With": "XMLHttpRequest",
    },
    body: `action=ajouter&produitId=${productId}&quantite=${quantity}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Mettre à jour directement avec la valeur retournée
        updateCartCounterWithValue(data.cartCount)
        showNotification("Produit ajouté au panier !", "success")
      } else {
        showNotification(data.message || "Erreur lors de l'ajout au panier", "error")
      }
      return data
    })
    .catch((error) => {
      console.error("Erreur:", error)
      showNotification("Une erreur est survenue", "error")
      throw error
    })
}

// Fonction pour mettre à jour le compteur avec une valeur connue
function updateCartCounterWithValue(count) {
  const counters = [
    document.getElementById("cart-counter"),
    document.getElementById("cart-counter-mobile"),
    ...document.querySelectorAll(".cart-badge"),
  ].filter(Boolean)

  counters.forEach((counter) => {
    counter.textContent = count > 99 ? "99+" : count
    counter.style.display = count > 0 ? "flex" : "none"
  })

  console.log(`Compteur panier mis à jour: ${count}`)
}

// Fonction pour récupérer et mettre à jour le compteur
function updateCartCounter() {
  fetch("php/get_cart_count.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        updateCartCounterWithValue(data.count)
      }
    })
    .catch((error) => {
      console.error("Erreur lors de la mise à jour du compteur:", error)
    })
}

// Fonction simple pour les notifications
function showNotification(message, type) {
  const notification = document.createElement("div")
  notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg text-white shadow-lg ${
    type === "success" ? "bg-green-500" : "bg-red-500"
  }`
  notification.textContent = message

  document.body.appendChild(notification)

  setTimeout(() => {
    notification.remove()
  }, 3000)
}

// Exposer les fonctions globalement
window.addToCart = addToCart
window.updateCartCounter = updateCartCounter
window.updateCartCounterWithValue = updateCartCounterWithValue
window.showNotification = showNotification
