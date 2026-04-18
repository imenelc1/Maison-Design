// Gestionnaire de panier unifié - Version finale
class CartManager {
  constructor() {
    this.isUpdating = false
    this.init()
  }

  init() {
    console.log("CartManager initialisé")
    // Mettre à jour le compteur au chargement
    this.updateCartCounter()
  }

  // Méthode principale pour ajouter au panier - IDENTIQUE À PRODUCT.JS
  async addToCart(productId, quantity = 1, button = null) {
    if (this.isUpdating) {
      console.log("Ajout en cours, veuillez patienter...")
      return
    }

    if (!productId) {
      this.showNotification("Erreur: ID du produit non trouvé", "error")
      return
    }

    this.isUpdating = true
    console.log(`Ajout au panier - Produit ID: ${productId}, Quantité: ${quantity}`)

    // Désactiver le bouton pendant la requête
    if (button) {
      button.disabled = true
      const originalContent = button.innerHTML
      button.setAttribute("data-original-content", originalContent)
      button.innerHTML = '<i class="bx bx-loader-alt animate-spin"></i> Ajout...'
    }

    try {
      const response = await fetch("php/cart_actions.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: `action=ajouter&produitId=${productId}&quantite=${quantity}`,
      })

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const data = await response.json()
      console.log("Réponse du serveur:", data)

      if (data.success) {
        this.updateAllCartCounters(data.cartCount)
        this.showNotification(data.message || "Produit ajouté au panier !", "success")
      } else {
        this.showNotification(data.message || "Erreur lors de l'ajout au panier", "error")
      }
    } catch (error) {
      console.error("Erreur lors de l'ajout au panier:", error)
      this.showNotification("Une erreur est survenue", "error")
    } finally {
      this.isUpdating = false
      // Réactiver le bouton
      if (button) {
        button.disabled = false
        button.innerHTML = button.getAttribute("data-original-content") || '<i class="bx bx-cart-add"></i> Ajouter'
      }
    }
  }

  // Méthode pour mettre à jour le compteur panier
  async updateCartCounter() {
    try {
      const response = await fetch("php/get_cart_count.php")
      const data = await response.json()

      if (data.success) {
        this.updateAllCartCounters(data.count)
      }
    } catch (error) {
      console.error("Erreur lors de la mise à jour du compteur:", error)
    }
  }

  // Méthode pour mettre à jour tous les compteurs - CORRIGÉE
  updateAllCartCounters(count) {
    // Cibler EXACTEMENT les mêmes sélecteurs que votre header.php
    const selectors = [
      "#cart-counter", // Desktop
      "#cart-counter-mobile", // Mobile
      ".cart-counter",
      ".cart-count",
      ".cart-badge",
      "[data-cart-count]",
    ]

    let countersFound = 0

    selectors.forEach((selector) => {
      const elements = document.querySelectorAll(selector)
      elements.forEach((counter) => {
        if (counter) {
          counter.textContent = count > 99 ? "99+" : count
          // Utiliser la même logique que votre header.php
          counter.style.display = count > 0 ? "flex" : "none"
          countersFound++
        }
      })
    })

    console.log(`Compteur panier mis à jour: ${count} (${countersFound} compteurs trouvés)`)
  }

  // Notifications avec le style de votre site (bg-accent)
  showNotification(message, type = "success") {
    // Supprimer les notifications existantes
    const existingNotifications = document.querySelectorAll(".cart-notification")
    existingNotifications.forEach((notif) => notif.remove())

    // Créer la nouvelle notification
    const notification = document.createElement("div")
    notification.className =
      "cart-notification fixed bottom-4 right-4 px-4 py-2 rounded-lg shadow-lg transform translate-y-10 opacity-0 transition-all duration-300 z-50"

    // Appliquer le style selon le type - MÊME COULEUR QUE VOS PRODUITS
    switch (type) {
      case "success":
        notification.classList.add("bg-accent", "text-white") // Votre couleur verte
        break
      case "error":
        notification.classList.add("bg-red-500", "text-white")
        break
      default:
        notification.classList.add("bg-accent", "text-white")
    }

    notification.textContent = message
    document.body.appendChild(notification)

    // Animation d'entrée
    setTimeout(() => {
      notification.classList.remove("translate-y-10", "opacity-0")
    }, 100)

    // Animation de sortie après 3 secondes
    setTimeout(() => {
      notification.classList.add("translate-y-10", "opacity-0")
      setTimeout(() => {
        if (notification.parentNode) {
          notification.parentNode.removeChild(notification)
        }
      }, 300)
    }, 3000)
  }
}

// Instance globale
window.cartManager = new CartManager()

// Fonction globale pour compatibilité avec votre code existant
window.addToCart = (productId, quantity = 1) => {
  const button =
    document.querySelector(`[onclick*="addToCart(${productId})"]`) ||
    document.querySelector(`[data-product-id="${productId}"]`)
  window.cartManager.addToCart(productId, quantity, button)
}

// Fonction globale pour mettre à jour le compteur
window.updateCartCounter = () => {
  window.cartManager.updateCartCounter()
}

// Fonction globale pour les notifications
window.showNotification = (message, type = "success") => {
  window.cartManager.showNotification(message, type)
}
