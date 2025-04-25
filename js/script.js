/**
 * Maison Design - Script principal
 * Ce script gère toutes les fonctionnalités interactives du site
 */

document.addEventListener("DOMContentLoaded", () => {
  // MENU MOBILE
  initMobileMenu()

  // SLIDER CATÉGORIES
  initCategorySlider()

  //  BARRE DE RECHERCHE
  initSearchBar()

  // FORMULAIRES
  initForms()

  // GESTION DES ERREURS
  handleUrlErrors()
})

//   le menu mobile
function initMobileMenu() {
  const openMenuBtn = document.getElementById("open-menu")
  const closeMenuBtn = document.getElementById("close-menu")
  const mobileMenu = document.getElementById("mobile-menu") // Utiliser l'ID au lieu de la classe
  
  // Créer un overlay pour l'arrière-plan
  const overlay = document.createElement("div")
  overlay.className = "menu-overlay"
  document.body.appendChild(overlay)
  
  // Vérifier si les éléments existent
  if (!openMenuBtn || !closeMenuBtn || !mobileMenu) {
    console.error("Éléments du menu mobile non trouvés")
    return
  }

  // Fonction pour ouvrir le menu
  function openMenu() {
    mobileMenu.classList.add("active")
    overlay.classList.add("active")
    document.body.classList.add("overflow-hidden")
    console.log("Menu ouvert")
    
    // Vérification visuelle
    console.log("Position du menu:", mobileMenu.getBoundingClientRect())
    console.log("Classes:", mobileMenu.className)
  }

  // Fonction pour fermer le menu
  function closeMenu() {
    mobileMenu.classList.remove("active")
    overlay.classList.remove("active")
    document.body.classList.remove("overflow-hidden")
    console.log("Menu fermé")
  }

  // Écouteurs d'événements
  openMenuBtn.addEventListener("click", (e) => {
    e.stopPropagation()
    openMenu()
  })

  closeMenuBtn.addEventListener("click", (e) => {
    e.stopPropagation()
    closeMenu()
  })

  overlay.addEventListener("click", closeMenu)

  // Fermer le menu si on clique sur un lien
  const menuLinks = mobileMenu.querySelectorAll("a")
  menuLinks.forEach((link) => {
    link.addEventListener("click", () => {
      if (window.innerWidth < 768) {
        closeMenu()
      }
    })
  })

  // Gérer le redimensionnement de la fenêtre
  window.addEventListener("resize", () => {
    if (window.innerWidth >= 768) {
      closeMenu()
    }
  })
}

//  barre de recherche

function initSearchBar() {
  const searchToggle = document.getElementById("search-toggle")
  const searchDropdown = document.getElementById("search-dropdown")
  const searchModal = document.getElementById("search-modal")
  const closeSearchModal = document.getElementById("close-search-modal")
  // vérifier si l'élément existe
  if (!searchToggle) {
    console.log("Élément de recherche non trouvé")
    return
  }

  // fonction pour ouvrir/fermer le dropdown desktop
  function toggleDesktopSearch() {
    if (searchDropdown) {
      searchDropdown.classList.toggle("hidden") //affiche/cache le dropdown
      //animation pour l'apparition
      searchDropdown.classList.toggle("opacity-0")
      searchDropdown.classList.toggle("-translate-y-2.5")
    }
  }

  // fonction pour ouvrir le modal mobile
  function openMobileSearch() {
    if (searchModal) {
      searchModal.classList.remove("hidden", "opacity-0", "invisible") //affiche le modal
      document.body.classList.add("overflow-hidden") //bloque le defilement
      // faire focus sur l'input
      const searchInput = searchModal.querySelector("input")
      if (searchInput) searchInput.focus()
    }
  }

  // fonction pour fermer le modal mobile
  function closeMobileSearch() {
    if (searchModal) {
      searchModal.classList.add("hidden", "opacity-0", "invisible") //cache le modal
      document.body.classList.remove("overflow-hidden") //retabli le defilement
    }
  }

  // gestion du clic sur l'icône de recherche
  searchToggle.addEventListener("click", (e) => {
    e.stopPropagation()

    if (window.innerWidth < 768) {
      openMobileSearch() //mobile
    } else {
      toggleDesktopSearch() //desktop
    }
  })

  // fermeture du modal mobile
  if (closeSearchModal) {
    closeSearchModal.addEventListener("click", closeMobileSearch)
  }

  // fermer le dropdown/modal si clic en dehors
  document.addEventListener("click", (e) => {
    // pour le dropdown desktop
    if (searchDropdown && !searchDropdown.contains(e.target) && e.target !== searchToggle) {
      searchDropdown.classList.add("hidden", "opacity-0", "-translate-y-2.5")
    }

    // pour le modal mobile
    if (searchModal && e.target === searchModal) {
      closeMobileSearch()
    }
  })

  // empêcher la fermeture accidentelle(par exemple, si on clique sur le dropdown)
  if (searchDropdown) {
    searchDropdown.addEventListener("click", (e) => e.stopPropagation())
  }
}
// initialise le slider de catégories

function initCategorySlider() {
  const slidesWrapper = document.querySelector(".slides-wrapper")
  if (!slidesWrapper) {
    console.log("Slider non trouvé sur cette page")
    return
  }

  const slides = document.querySelectorAll(".slide")
  const prevButton = document.querySelector(".prev-arrow")
  const nextButton = document.querySelector(".next-arrow")
  const descriptions = document.querySelectorAll(".category-description")
  // vérifier si les éléments existent
  if (!slides.length || !prevButton || !nextButton || !descriptions.length) {
    console.error("Éléments du slider non trouvés")
    return
  }

  let currentIndex = 0 //index du slide active
  const slideCount = slides.length //nombre total de slides

  // fonction pour mettre à jour l'affichage du slider
  function updateSlider() {
    // Mettre à jour les états actifs et les transformations
    slides.forEach((slide, index) => {
      if (index === currentIndex) {
        slide.classList.add("active") //slide active
        slide.style.opacity = "1" //visible
        slide.style.transform = "scale(1)" //taille normale
      } else {
        slide.classList.remove("active") //slide inactive
        slide.style.opacity = "0.5" //un peu transparent
        slide.style.transform = "scale(0.9)" //on reduit legerement le slide
      }
    })

    // Mettre à jour les descriptions
    descriptions.forEach((desc, index) => {
      if (index === currentIndex) {
        desc.classList.add("active") //description active
      } else {
        desc.classList.remove("active") //description inactive
      }
    })

    // calculer et appliquer la translation(l'effet de défilement horizontal)
    const slideWidth = slides[0].offsetWidth // Largeur d'un slide
    const gap = 20 // Espacement entre les slides
    const translation = -(currentIndex * (slideWidth + gap)) //calcul du décalage
    slidesWrapper.style.transform = `translateX(${translation}px)` //déplacement
  }

  // fonction pour aller à un slide spécifique
  function goToSlide(index) {
    currentIndex = (index + slideCount) % slideCount // pour boucler
    updateSlider() //mettre a jour l'affichage
  }

  // Écouteurs d'événements
  //fleche gauche/droite
  prevButton.addEventListener("click", () => {
    goToSlide(currentIndex - 1)
  })

  nextButton.addEventListener("click", () => {
    goToSlide(currentIndex + 1)
  })

  // permettre de cliquer sur un slide pour le sélectionner
  slides.forEach((slide, index) => {
    slide.addEventListener("click", () => {
      goToSlide(index)
    })
  })

  // Initialiser le slider
  updateSlider()
}

//  les formulaires
function initForms() {
  // formulaire de connexion
  const loginForm = document.querySelector(".login-form")
  if (loginForm) {
    console.log("Initialisation du formulaire de connexion")

    loginForm.addEventListener("submit", (e) => {
      e.preventDefault() //empeche le rechargement de la page
      console.log("Tentative de connexion")

      // récupérer les valeurs du formulaire
      const email = loginForm.querySelector('input[type="email"]').value
      const password = loginForm.querySelector('input[type="password"]').value

      if (!email || !password) {
        showError("Veuillez remplir tous les champs.")
        return
      }
      console.log("Email:", email)
      console.log("Mot de passe:", password.replace(/./g, "*")) //masquer le mot de passe

      // Simuler une soumission de formulaire
      loginForm.submit()
    })
  }

  // formulaire d'inscription
  const registerForm = document.querySelector(".register-form")
  if (registerForm) {
    console.log("Initialisation du formulaire d'inscription")

    registerForm.addEventListener("submit", (e) => {
      e.preventDefault()
      console.log("Tentative d'inscription")

      // récupérer les valeurs du formulaire
      const name = registerForm.querySelector('input[name="nom"]')?.value
      const email = registerForm.querySelector('input[type="email"]').value
      const password = registerForm.querySelector('input[type="password"]').value
      const confirmPassword = registerForm.querySelector('input[name="confirm-password"]')?.value

      if (!email || !password || (confirmPassword && password !== confirmPassword)) {
        showError(
          confirmPassword && password !== confirmPassword
            ? "Les mots de passe ne correspondent pas."
            : "Veuillez remplir tous les champs.",
        )
        return
      }

      console.log("Nom:", name)
      console.log("Email:", email)
      console.log("Mot de passe:", password.replace(/./g, "*"))

      // Simuler une soumission de formulaire
      registerForm.submit()
    })
  }

  // Formulaire de contact
  const contactForm = document.querySelector(".contact-form")
  if (contactForm) {
    console.log("Initialisation du formulaire de contact")

    contactForm.addEventListener("submit", (e) => {
      e.preventDefault()
      console.log("Tentative d'envoi de message")

      // Récupérer les valeurs du formulaire
      const name = contactForm.querySelector('input[name="nom"]').value
      const email = contactForm.querySelector('input[type="email"]').value
      const message = contactForm.querySelector("textarea").value

      if (!name || !email || !message) {
        showError("Veuillez remplir tous les champs.")
        return
      }

      console.log("Nom:", name)
      console.log("Email:", email)
      console.log("Message:", message)

      // Simuler une soumission de formulaire
      alert("Votre message a été envoyé avec succès!")
      contactForm.submit()
    })
  }
}

// Gère les erreurs dans l'URL

function handleUrlErrors() {
  const urlParams = new URLSearchParams(window.location.search)
  const error = urlParams.get("error")
  const errorMessage = document.getElementById("error-message")

  if (error && errorMessage) {
    console.log("Erreur détectée dans l'URL:", error)

    errorMessage.style.display = "block"

    if (error === "invalid") {
      errorMessage.textContent = "Email ou mot de passe incorrect."
    } else if (error === "empty") {
      errorMessage.textContent = "Veuillez remplir tous les champs."
    } else if (error === "password") {
      errorMessage.textContent = "Les mots de passe ne correspondent pas."
    } else {
      errorMessage.textContent = "Une erreur s'est produite. Veuillez réessayer."
    }
  }
}

//Affiche un message d'erreur
function showError(message) {
  let errorMessage = document.getElementById("error-message")

  if (!errorMessage) {
    errorMessage = document.createElement("div")
    errorMessage.id = "error-message"
    errorMessage.className = "bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"

    const form = document.querySelector("form")
    if (form) {
      form.insertBefore(errorMessage, form.firstChild)
    }
  }

  errorMessage.textContent = message
  errorMessage.style.display = "block"

  // faire défiler jusqu'au message d'erreur
  errorMessage.scrollIntoView({ behavior: "smooth", block: "start" })
}
