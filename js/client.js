/**
 * Script pour la page client
 * Gère les onglets et les fonctionnalités du tableau de bord avec récupération des données BDD
 */

document.addEventListener("DOMContentLoaded", () => {
  console.log("Initialisation de la page client...")

  // Initialiser les onglets
  initTabs()

  // Charger les données client au démarrage
  loadClientData()
})

// Variable globale pour stocker les données client
let clientData = null

/**
 * Charge toutes les données du client depuis la base de données
 */
async function loadClientData() {
  try {
    showLoading()

    const response = await fetch("php/get_client_data.php", {
      method: "GET",
      credentials: "include",
      headers: {
        "Content-Type": "application/json",
      },
    })

    if (!response.ok) {
      throw new Error(`Erreur HTTP: ${response.status}`)
    }

    const data = await response.json()

    if (data.error) {
      if (data.error === "not_authenticated") {
        window.location.href = "connexion.php"
        return
      }
      throw new Error(data.error)
    }

    clientData = data
    console.log("Données client chargées:", clientData)

    // Mettre à jour l'affichage selon l'onglet actif
    updateActiveTabContent()
  } catch (error) {
    console.error("Erreur lors du chargement des données:", error)
    showError("Erreur lors du chargement de vos données: " + error.message)
  } finally {
    hideLoading()
  }
}

/**
 * Affiche un indicateur de chargement
 */
function showLoading() {
  const loadingHTML = `
    <div class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-accent"></div>
      <span class="ml-3 text-gray-600">Chargement...</span>
    </div>
  `

  // Afficher le loading dans tous les onglets
  document.querySelectorAll(".tab-pane").forEach((pane) => {
    pane.innerHTML = loadingHTML
  })
}

/**
 * Masque l'indicateur de chargement
 */
function hideLoading() {
  // Le contenu sera remplacé par les données réelles
}

/**
 * Affiche un message d'erreur
 */
function showError(message) {
  const errorHTML = `
    <div class="bg-white rounded-xl shadow-md overflow-hidden p-6">
      <div class="text-center py-12">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 mb-4">
          <i class='bx bx-error text-3xl text-red-500'></i>
        </div>
        <p class="text-red-600 mb-4">${message}</p>
        <button onclick="loadClientData()" class="px-4 py-2 bg-accent text-white rounded-full hover:bg-accent/90 transition-colors">
          Réessayer
        </button>
      </div>
    </div>
  `

  document.querySelectorAll(".tab-pane").forEach((pane) => {
    pane.innerHTML = errorHTML
  })
}

/**
 * Met à jour le contenu de l'onglet actif
 */
function updateActiveTabContent() {
  const activeTab = document.querySelector(".tab-pane:not(.hidden)")
  if (!activeTab || !clientData) return

  const tabId = activeTab.id.replace("-tab", "")

  switch (tabId) {
    case "profile":
      updateProfileTab()
      break
    case "orders":
      updateOrdersTab()
      break
    case "addresses":
      updateAddressesTab()
      break
    case "wishlist":
      updateWishlistTab()
      break
  }
}

/**
 * Met à jour l'onglet profil
 */
function updateProfileTab() {
  const client = clientData.client
  const profileHTML = `
    <div class="bg-white rounded-xl shadow-md overflow-hidden p-6">
      <h2 class="text-2xl text-accent mb-6">Informations personnelles</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
          <p class="text-sm text-gray-500 mb-1">Prénom</p>
          <p class="font-medium">${client.prenom || "Non renseigné"}</p>
        </div>
        <div>
          <p class="text-sm text-gray-500 mb-1">Nom</p>
          <p class="font-medium">${client.nom || "Non renseigné"}</p>
        </div>
        <div>
          <p class="text-sm text-gray-500 mb-1">Email</p>
          <p class="font-medium">${client.email}</p>
        </div>
        <div>
          <p class="text-sm text-gray-500 mb-1">Téléphone</p>
          <p class="font-medium">${client.telephone || "Non renseigné"}</p>
        </div>
        <div>
          <p class="text-sm text-gray-500 mb-1">Date d'inscription</p>
          <p class="font-medium">${formatDate(client.dateInscription)}</p>
        </div>
      </div>
      
      <button onclick="editProfile()" class="px-4 py-2 bg-accent text-white rounded-full hover:bg-accent/90 transition-colors">
        <i class='bx bx-edit mr-1'></i> Modifier mon profil
      </button>
    </div>
  `

  document.getElementById("profile-tab").innerHTML = profileHTML
}

/**
 * Active le mode édition du profil
 */
function editProfile() {
  const client = clientData.client
  const profileHTML = `
    <div class="bg-white rounded-xl shadow-md overflow-hidden p-6">
      <h2 class="text-2xl text-accent mb-6">Modifier mon profil</h2>
      
      <form id="profile-form" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="prenom" class="block text-sm font-medium text-gray-700 mb-2">Prénom</label>
            <input 
              type="text" 
              id="prenom" 
              name="prenom" 
              value="${client.prenom || ""}"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent"
              required
            >
          </div>
          <div>
            <label for="nom" class="block text-sm font-medium text-gray-700 mb-2">Nom</label>
            <input 
              type="text" 
              id="nom" 
              name="nom" 
              value="${client.nom || ""}"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent"
              required
            >
          </div>
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
            <input 
              type="email" 
              id="email" 
              name="email" 
              value="${client.email || ""}"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent"
              required
            >
          </div>
          <div>
            <label for="telephone" class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
            <input 
              type="tel" 
              id="telephone" 
              name="telephone" 
              value="${client.telephone || ""}"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent"
            >
          </div>
        </div>
        
        <div class="flex justify-end gap-2 pt-4">
          <button 
            type="button" 
            onclick="cancelEditProfile()"
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
          >
            Annuler
          </button>
          <button 
            type="submit"
            class="px-4 py-2 bg-accent text-white rounded-lg hover:bg-accent/90 transition-colors"
          >
            <i class='bx bx-save mr-1'></i> Enregistrer
          </button>
        </div>
      </form>
    </div>
  `

  document.getElementById("profile-tab").innerHTML = profileHTML

  // Ajouter l'écouteur d'événement pour le formulaire
  const form = document.getElementById("profile-form")
  if (form) {
    form.addEventListener("submit", handleProfileSubmit)
  }
}

/**
 * Annule l'édition du profil
 */
function cancelEditProfile() {
  updateProfileTab()
}

/**
 * Gère la soumission du formulaire de profil
 */
async function handleProfileSubmit(e) {
  e.preventDefault()

  const formData = new FormData(e.target)
  const profileData = {
    prenom: formData.get("prenom"),
    nom: formData.get("nom"),
    email: formData.get("email"),
    telephone: formData.get("telephone"),
  }

  // Validation simple
  if (!profileData.prenom.trim() || !profileData.nom.trim() || !profileData.email.trim()) {
    showNotification("Veuillez remplir tous les champs obligatoires", "error")
    return
  }

  // Validation email
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(profileData.email)) {
    showNotification("Veuillez entrer une adresse email valide", "error")
    return
  }

  try {
    // Afficher un indicateur de chargement
    const submitBtn = e.target.querySelector('button[type="submit"]')
    const originalText = submitBtn.innerHTML
    submitBtn.innerHTML = '<i class="bx bx-loader-alt animate-spin mr-1"></i> Enregistrement...'
    submitBtn.disabled = true

    const response = await fetch("php/update_profile.php", {
      method: "POST",
      credentials: "include",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(profileData),
    })

    if (!response.ok) {
      throw new Error(`Erreur HTTP: ${response.status}`)
    }

    const result = await response.json()

    if (result.error) {
      throw new Error(result.error)
    }

    // Mettre à jour les données locales
    clientData.client = { ...clientData.client, ...profileData }

    // Retourner à la vue normale
    updateProfileTab()

    showNotification("Profil mis à jour avec succès!", "success")
  } catch (error) {
    console.error("Erreur lors de la mise à jour:", error)
    showNotification("Erreur lors de la mise à jour: " + error.message, "error")

    // Restaurer le bouton
    const submitBtn = e.target.querySelector('button[type="submit"]')
    submitBtn.innerHTML = originalText
    submitBtn.disabled = false
  }
}

/**
 * Met à jour l'onglet commandes
 */
function updateOrdersTab() {
  const commandes = clientData.commandes

  let ordersHTML = `
    <div class="bg-white rounded-xl shadow-md overflow-hidden p-6">
      <h2 class="text-2xl text-accent mb-6">Mes commandes (${commandes.length})</h2>
  `

  if (commandes.length === 0) {
    ordersHTML += `
      <div class="text-center py-12">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
          <i class='bx bx-package text-3xl text-gray-400'></i>
        </div>
        <p class="text-gray-500 mb-4">Vous n'avez pas encore passé de commande.</p>
        <a href="categories.php" class="inline-block px-4 py-2 bg-accent text-white rounded-full hover:bg-accent/80 transition-colors">
          <i class='bx bx-shopping-bag mr-1'></i> Découvrir nos produits
        </a>
      </div>
    `
  } else {
    ordersHTML += '<div class="space-y-4">'

    commandes.forEach((commande) => {
      const statusClass = getStatusClass(commande.statut)

      ordersHTML += `
        <div class="bg-gray-50 rounded-lg p-4 border">
          <div class="flex justify-between items-center mb-3">
            <div>
              <span class="text-sm text-gray-500">Commande #${commande.id}</span>
              <p class="font-medium">${commande.date}</p>
              ${commande.adresse_livraison ? `<p class="text-sm text-gray-600">Livraison: ${commande.adresse_livraison}</p>` : ""}
            </div>
            <div class="flex items-center gap-2">
              <span class="px-2 py-1 rounded-full text-xs ${statusClass}">
                ${commande.statut}
              </span>
              <span class="font-bold text-accent">${commande.montant_total} DA</span>
            </div>
          </div>
          <div class="space-y-1">
            ${commande.produits
              .map(
                (produit) => `
              <div class="flex items-center gap-2">
                <img src="${produit.image}" alt="${produit.nom}" class="w-10 h-10 object-cover rounded" onerror="this.src='images/placeholder.jpeg'">
                <div class="flex-1">
                  <p class="text-sm">${produit.nom}</p>
                  <p class="text-xs text-gray-500">Qté: ${produit.quantite} × ${produit.prix_unitaire} DA</p>
                </div>
              </div>
            `,
              )
              .join("")}
          </div>
        </div>
      `
    })

    ordersHTML += "</div>"
  }

  ordersHTML += "</div>"
  document.getElementById("orders-tab").innerHTML = ordersHTML
}

/**
 * Met à jour l'onglet adresses
 */
function updateAddressesTab() {
  const adresses = clientData.adresses

  let addressesHTML = `
    <div class="bg-white rounded-xl shadow-md overflow-hidden p-6">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl text-accent">Mes adresses (${adresses.length})</h2>
        <button class="px-4 py-2 bg-accent text-white rounded-full hover:bg-accent/90 transition-colors flex items-center">
          <i class='bx bx-plus mr-1'></i> Ajouter une adresse
        </button>
      </div>
  `

  if (adresses.length === 0) {
    addressesHTML += `
      <div class="text-center py-12">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
          <i class='bx bx-map text-3xl text-gray-400'></i>
        </div>
        <p class="text-gray-500 mb-4">Vous n'avez pas encore ajouté d'adresse.</p>
        <button class="inline-block px-4 py-2 bg-accent text-white rounded-full hover:bg-accent/80 transition-colors">
          <i class='bx bx-plus mr-1'></i> Ajouter une adresse
        </button>
      </div>
    `
  } else {
    addressesHTML += '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">'

    adresses.forEach((adresse) => {
      addressesHTML += `
        <div class="bg-white rounded-lg border p-4 relative ${adresse.isPrimary ? "border-accent" : "border-gray-200"}">
          ${
            adresse.isPrimary
              ? `
            <span class="absolute top-2 right-2 px-2 py-0.5 bg-amber-100 text-amber-800 text-xs rounded-full flex items-center gap-1">
              <i class='bx bx-star text-xs'></i>
              Principale
            </span>
          `
              : ""
          }
          <h3 class="font-medium mb-1">${adresse.titre}</h3>
          <p class="text-gray-600 mb-4">${adresse.adresse}</p>
          <div class="flex justify-end gap-2">
            <button class="px-3 py-1 text-sm border border-gray-300 rounded-full hover:bg-gray-50 transition-colors">
              <i class='bx bx-edit text-xs mr-1'></i> Modifier
            </button>
            ${
              !adresse.isPrimary
                ? `
              <button class="px-3 py-1 text-sm border border-red-300 text-red-600 rounded-full hover:bg-red-50 transition-colors">
                <i class='bx bx-trash text-xs mr-1'></i> Supprimer
              </button>
            `
                : ""
            }
          </div>
        </div>
      `
    })

    addressesHTML += "</div>"
  }

  addressesHTML += "</div>"
  document.getElementById("addresses-tab").innerHTML = addressesHTML
}

/**
 * Met à jour l'onglet favoris
 */
function updateWishlistTab() {
  const favoris = clientData.favoris

  let wishlistHTML = `
    <div class="bg-white rounded-xl shadow-md overflow-hidden p-6">
      <h2 class="text-2xl text-accent mb-6">Mes favoris (${favoris.length})</h2>
  `

  if (favoris.length === 0) {
    wishlistHTML += `
      <div class="text-center py-12">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
          <i class='bx bx-heart text-3xl text-gray-400'></i>
        </div>
        <p class="text-gray-500 mb-4">Vous n'avez pas encore ajouté de produits à vos favoris.</p>
        <a href="categories.php" class="inline-block px-4 py-2 bg-accent text-white rounded-full hover:bg-accent/80 transition-colors">
          <i class='bx bx-shopping-bag mr-1'></i> Découvrir nos produits
        </a>
      </div>
    `
  } else {
    wishlistHTML += '<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">'

    favoris.forEach((produit) => {
      wishlistHTML += `
        <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 hover:shadow-md transition-shadow">
          <div class="relative h-48">
            <img src="${produit.image}" alt="${produit.nom}" class="w-full h-full object-cover" onerror="this.src='images/placeholder.jpeg'">
            <button onclick="removeFavorite(${produit.id})" class="absolute top-2 right-2 w-8 h-8 bg-white/80 rounded-full flex items-center justify-center text-red-500 hover:bg-white hover:text-red-600 transition-colors">
              ×
            </button>
          </div>
          <div class="p-4">
            <h3 class="font-medium text-lg mb-1">${produit.nom}</h3>
            <p class="text-accent font-medium mb-4">${produit.prix.toFixed(2)} DA</p>
            ${produit.description ? `<p class="text-gray-500 text-sm mb-4 line-clamp-2">${produit.description}</p>` : ""}
            <div class="flex justify-between">
              <button class="px-3 py-1 text-sm border border-gray-300 rounded-full hover:bg-gray-50 transition-colors">
                Voir détails
              </button>
              <button class="px-3 py-1 text-sm bg-accent text-white rounded-full hover:bg-accent/90 transition-colors">
                Ajouter au panier
              </button>
            </div>
          </div>
        </div>
      `
    })

    wishlistHTML += "</div>"
  }

  wishlistHTML += "</div>"
  document.getElementById("wishlist-tab").innerHTML = wishlistHTML
}

/**
 * Supprime un produit des favoris
 */
async function removeFavorite(productId) {
  if (!confirm("Êtes-vous sûr de vouloir retirer ce produit de vos favoris ?")) {
    return
  }

  try {
    const response = await fetch(`php/remove_favorite.php?id=${productId}`, {
      method: "GET",
      credentials: "include",
    })

    if (response.ok) {
      // Recharger les données et mettre à jour l'affichage
      await loadClientData()
      showNotification("Produit retiré des favoris", "success")
    } else {
      throw new Error("Erreur lors de la suppression")
    }
  } catch (error) {
    console.error("Erreur:", error)
    showNotification("Erreur lors de la suppression du favori", "error")
  }
}

/**
 * Utilitaires
 */
function formatDate(dateString) {
  if (!dateString) return "Non renseigné"
  const date = new Date(dateString)
  return date.toLocaleDateString("fr-FR")
}

function getStatusClass(status) {
  switch (status.toLowerCase()) {
    case "livré":
    case "livre":
      return "bg-green-100 text-green-800"
    case "expédié":
    case "expedie":
      return "bg-blue-100 text-blue-800"
    case "en cours":
      return "bg-yellow-100 text-yellow-800"
    default:
      return "bg-gray-100 text-gray-800"
  }
}

function showNotification(message, type = "success") {
  // Créer le conteneur de notifications s'il n'existe pas
  let container = document.getElementById("notifications-container")
  if (!container) {
    container = document.createElement("div")
    container.id = "notifications-container"
    container.className = "fixed bottom-4 right-4 z-50 flex flex-col gap-2 max-w-sm"
    document.body.appendChild(container)
  }

  const notification = document.createElement("div")
  notification.className = "transform transition-all duration-300 opacity-0 translate-y-2"

  let bgColor, textColor, icon
  switch (type) {
    case "error":
      bgColor = "bg-red-500"
      textColor = "text-white"
      icon = "❌"
      break
    case "success":
    default:
      bgColor = "bg-green-500"
      textColor = "text-white"
      icon = "✅"
      break
  }

  notification.className += ` ${bgColor} ${textColor} px-4 py-3 rounded-lg shadow-lg flex items-center gap-2 min-w-0`
  notification.innerHTML = `
    <span class="flex-shrink-0">${icon}</span>
    <span class="flex-1 text-sm font-medium">${message}</span>
    <button class="flex-shrink-0 ml-2 text-white hover:text-gray-200" onclick="this.parentElement.remove()">
      <span class="text-lg leading-none">&times;</span>
    </button>
  `

  container.appendChild(notification)

  setTimeout(() => {
    notification.classList.remove("opacity-0", "translate-y-2")
    notification.classList.add("opacity-100", "translate-y-0")
  }, 10)

  setTimeout(() => {
    if (notification.parentNode) {
      notification.classList.add("opacity-0", "translate-y-2")
      setTimeout(() => {
        if (notification.parentNode) {
          notification.parentNode.removeChild(notification)
        }
      }, 300)
    }
  }, 5000)
}

/**
 * Initialise le système d'onglets
 */
function initTabs() {
  const tabButtons = document.querySelectorAll(".tab-button")
  const tabPanes = document.querySelectorAll(".tab-pane")

  if (!tabButtons.length || !tabPanes.length) {
    console.log("Éléments des onglets non trouvés")
    return
  }

  console.log(`Initialisation de ${tabButtons.length} onglets`)

  tabButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault()

      const tabId = this.getAttribute("data-tab")
      if (!tabId) {
        console.error("Attribut data-tab manquant")
        return
      }

      console.log(`Onglet sélectionné: ${tabId}`)

      // Désactiver tous les boutons
      tabButtons.forEach((btn) => {
        btn.classList.remove("bg-accent", "text-white")
        btn.classList.add("bg-primary", "text-textColor", "hover:bg-primary/50")
      })

      // Masquer tous les contenus
      tabPanes.forEach((pane) => {
        pane.classList.add("hidden")
        pane.classList.remove("block")
      })

      // Activer le bouton sélectionné
      this.classList.add("bg-accent", "text-white")
      this.classList.remove("bg-primary", "text-textColor", "hover:bg-primary/50")

      // Afficher le contenu correspondant
      const selectedPane = document.getElementById(`${tabId}-tab`)
      if (selectedPane) {
        selectedPane.classList.remove("hidden")
        selectedPane.classList.add("block")

        // Mettre à jour le contenu de l'onglet si les données sont chargées
        if (clientData) {
          updateActiveTabContent()
        }
      } else {
        console.error(`Contenu d'onglet non trouvé: ${tabId}-tab`)
      }

      updateUrlWithTab(tabId)
    })
  })

  activateInitialTab()
}

/**
 * Met à jour l'URL avec le paramètre d'onglet
 */
function updateUrlWithTab(tabId) {
  if (history.pushState) {
    const url = new URL(window.location)
    url.searchParams.set("tab", tabId)
    window.history.pushState({ tab: tabId }, "", url)
  }
}

/**
 * Active l'onglet initial selon l'URL
 */
function activateInitialTab() {
  const urlParams = new URLSearchParams(window.location.search)
  const tabParam = urlParams.get("tab")

  if (tabParam) {
    const tabButton = document.querySelector(`.tab-button[data-tab="${tabParam}"]`)
    if (tabButton) {
      console.log(`Activation de l'onglet depuis l'URL: ${tabParam}`)
      tabButton.click()
      return
    }
  }

  // Activer l'onglet par défaut (profile)
  const defaultTab = document.querySelector('.tab-button[data-tab="profile"]')
  if (defaultTab) {
    console.log("Activation de l'onglet par défaut: profile")
    defaultTab.click()
  }
}

// Gestion de l'historique du navigateur
window.addEventListener("popstate", (event) => {
  if (event.state && event.state.tab) {
    const tabButton = document.querySelector(`.tab-button[data-tab="${event.state.tab}"]`)
    if (tabButton) {
      tabButton.click()
    }
  }
})

// Rendre les fonctions accessibles globalement
window.removeFavorite = removeFavorite
window.editProfile = editProfile
window.cancelEditProfile = cancelEditProfile
