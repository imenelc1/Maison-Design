// Script pour la page produit - Solution directe
document.addEventListener("DOMContentLoaded", () => {
  // Bouton d'ajout au panier
  const addToCartBtn = document.getElementById("add-to-cart-btn")
  if (addToCartBtn) {
    addToCartBtn.addEventListener("click", function () {
      // Récupérer les données du produit
      const productId = window.productData?.id
      const quantityInput = document.getElementById("quantity")
      const quantity = quantityInput ? Number.parseInt(quantityInput.value) || 1 : 1

      if (!productId) {
        alert("Erreur: ID du produit non trouvé")
        return
      }

      // Désactiver le bouton pendant la requête
      this.disabled = true
      const originalText = this.innerHTML
      this.innerHTML = '<i class="bx bx-loader-alt animate-spin"></i> Ajout en cours...'

      // Requête AJAX directe vers votre fichier PHP existant
      fetch("php/cart_actions.php", {
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
            // SOLUTION DIRECTE: Utiliser cartCount de la réponse
            const cartCount = data.cartCount

            // Mettre à jour tous les compteurs possibles
            updateAllCartCounters(cartCount)

            // Notification
            afficherNotification(data.message || "Produit ajouté au panier !")
          } else {
            afficherNotification(data.message || "Erreur lors de l'ajout au panier")
          }
        })
        .catch((error) => {
          console.error("Erreur:", error)
          afficherNotification("Une erreur est survenue")
        })
        .finally(() => {
          // Réactiver le bouton
          this.disabled = false
          this.innerHTML = originalText
        })
    })
  }

  // Fonction pour mettre à jour tous les compteurs de panier
  function updateAllCartCounters(count) {
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

  // Utiliser votre fonction existante pour les notifications
  function afficherNotification(message) {
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
})
