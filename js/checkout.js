document.addEventListener("DOMContentLoaded", () => {
  // Gestion du formulaire de checkout
  const checkoutForm = document.getElementById("checkout-form")
  const submitBtn = document.getElementById("submit-btn")
  const submitText = document.getElementById("submit-text")

  if (checkoutForm && submitBtn) {
    checkoutForm.addEventListener("submit", (e) => {
      // Vérifier que les conditions sont acceptées
      const termsCheckbox = document.getElementById("terms")
      if (!termsCheckbox.checked) {
        e.preventDefault()
        showNotification("Vous devez accepter les conditions générales de vente.", "error")
        return
      }

      // Désactiver le bouton pour éviter les doubles soumissions
      submitBtn.disabled = true
      submitText.textContent = "Traitement en cours..."
      submitBtn.classList.add("opacity-50")

      // Ajouter un spinner
      const spinner = document.createElement("i")
      spinner.className = "bx bx-loader-alt animate-spin text-xl"
      submitBtn.querySelector("i").replaceWith(spinner)

      // Réactiver après 10 secondes en cas d'erreur de réseau
      setTimeout(() => {
        if (submitBtn.disabled) {
          submitBtn.disabled = false
          submitText.textContent = "Confirmer la commande"
          submitBtn.classList.remove("opacity-50")
          spinner.className = "bx bx-check-circle text-xl"
          showNotification("Délai d'attente dépassé. Veuillez réessayer.", "error")
        }
      }, 10000)
    })
  }

  // Validation en temps réel
  const termsCheckbox = document.getElementById("terms")
  if (termsCheckbox && submitBtn) {
    termsCheckbox.addEventListener("change", function () {
      submitBtn.disabled = !this.checked
      submitBtn.classList.toggle("opacity-50", !this.checked)
    })

    // État initial
    submitBtn.disabled = !termsCheckbox.checked
    submitBtn.classList.toggle("opacity-50", !termsCheckbox.checked)
  }
})

// Fonction pour afficher les notifications
function showNotification(message, type = "info") {
  // Supprimer les notifications existantes
  const existingNotifications = document.querySelectorAll(".notification-toast")
  existingNotifications.forEach((notif) => notif.remove())

  const notification = document.createElement("div")
  notification.className = `notification-toast fixed top-4 right-4 z-50 px-6 py-3 rounded-lg text-white shadow-lg transition-all duration-300 transform translate-x-full ${
    type === "success"
      ? "bg-green-500"
      : type === "error"
        ? "bg-red-500"
        : type === "warning"
          ? "bg-yellow-500"
          : "bg-blue-500"
  }`

  notification.innerHTML = `
        <div class="flex items-center gap-2">
            <i class='bx ${
              type === "success"
                ? "bx-check-circle"
                : type === "error"
                  ? "bx-error-circle"
                  : type === "warning"
                    ? "bx-error"
                    : "bx-info-circle"
            }'></i>
            <span>${message}</span>
        </div>
    `

  document.body.appendChild(notification)

  // Animation d'entrée
  setTimeout(() => {
    notification.classList.remove("translate-x-full")
  }, 100)

  // Suppression automatique
  setTimeout(() => {
    notification.classList.add("translate-x-full")
    setTimeout(() => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification)
      }
    }, 300)
  }, 5000)
}
