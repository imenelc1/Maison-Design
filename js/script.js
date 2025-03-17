/**
 * Maison Design - Script principal
 * Ce script gère toutes les fonctionnalités interactives du site
 */

document.addEventListener("DOMContentLoaded", () => {
  console.log("DOM chargé, initialisation du script...");

  // ======= MENU MOBILE =======
  initMobileMenu();

  // ======= SLIDER CATÉGORIES =======
  initCategorySlider();

  // ======= BARRE DE RECHERCHE AMÉLIORÉE =======
  initSearchBar();

  // ======= FORMULAIRES =======
  initForms();

  // ======= GESTION DES ERREURS =======
  handleUrlErrors();
});

/**
 * Initialise le menu mobile
 */
function initMobileMenu() {
  const openMenuBtn = document.getElementById("open-menu");
  const closeMenuBtn = document.getElementById("close-menu");
  const mobileMenu = document.querySelector(".header-content");

  if (!openMenuBtn || !closeMenuBtn || !mobileMenu) {
    console.error("Éléments du menu mobile non trouvés");
    return;
  }

  function openMenu() {
    mobileMenu.style.left = "0"; // Utilise left au lieu de right
    document.body.classList.add("overflow-hidden");
  }

  function closeMenu() {
    mobileMenu.style.left = "-250px"; // Utilise left au lieu de right
    document.body.classList.remove("overflow-hidden");
  }

  openMenuBtn.addEventListener("click", function (e) {
    e.stopPropagation();
    openMenu();
    console.log("Menu ouvert");
  });

  closeMenuBtn.addEventListener("click", function (e) {
    e.stopPropagation();
    closeMenu();
    console.log("Menu fermé");
  });

  // Fermer le menu si on clique sur un lien
  const menuLinks = mobileMenu.querySelectorAll("a");
  menuLinks.forEach((link) => {
    link.addEventListener("click", () => {
      if (window.innerWidth < 768) {
        closeMenu();
      }
    });
  });

  // Fermer le menu si on clique en dehors
  document.addEventListener("click", (e) => {
    if (
      window.innerWidth < 768 &&
      mobileMenu.style.left === "0px" && // Utilise left au lieu de right
      !mobileMenu.contains(e.target) &&
      e.target !== openMenuBtn
    ) {
      closeMenu();
    }
  });

  // Gérer le redimensionnement de la fenêtre
  window.addEventListener("resize", () => {
    if (window.innerWidth >= 768) {
      mobileMenu.style.left = ""; // Réinitialiser sur grand écran
    } else {
      mobileMenu.style.left = "-250px"; // Cacher sur mobile
    }
  });
}

/**
 * Initialise la barre de recherche améliorée
 */
function initSearchBar() {
  const searchToggle = document.getElementById("search-toggle");
  const searchDropdown = document.getElementById("search-dropdown");
  const searchModal = document.getElementById("search-modal");
  const closeSearchModal = document.getElementById("close-search-modal");
  
  if (!searchToggle) {
    console.log("Élément de recherche non trouvé");
    return;
  }
  
  // Fonction pour ouvrir le dropdown sur desktop ou le modal sur mobile
  searchToggle.addEventListener("click", function(e) {
    e.stopPropagation();
    console.log("Bouton de recherche cliqué");
    
    // Sur mobile, ouvrir le modal
    if (window.innerWidth < 768) {
      if (searchModal) {
        searchModal.classList.add("active");
        // Focus sur l'input quand le modal est ouvert
        const searchInput = searchModal.querySelector("input");
        if (searchInput) {
          setTimeout(() => {
            searchInput.focus();
          }, 300);
        }
        // Empêcher le défilement du body
        document.body.classList.add("overflow-hidden");
      }
    } 
    // Sur desktop, ouvrir le dropdown
    else {
      if (searchDropdown) {
        console.log("Toggle dropdown de recherche");
        searchDropdown.classList.toggle("active");
        
        if (searchDropdown.classList.contains("active")) {
          // Focus sur l'input quand le dropdown est ouvert
          const searchInput = searchDropdown.querySelector("input");
          if (searchInput) {
            setTimeout(() => {
              searchInput.focus();
            }, 300);
          }
        }
      }
    }
  });
  
  // Fermer le modal de recherche mobile
  if (closeSearchModal) {
    closeSearchModal.addEventListener("click", () => {
      if (searchModal) {
        searchModal.classList.remove("active");
        document.body.classList.remove("overflow-hidden");
      }
    });
  }
  
  // Fermer le dropdown si on clique en dehors (desktop)
  document.addEventListener("click", (e) => {
    if (
      searchDropdown && 
      searchDropdown.classList.contains("active") &&
      !searchDropdown.contains(e.target) &&
      e.target !== searchToggle
    ) {
      searchDropdown.classList.remove("active");
    }
  });
  
  // Fermer le modal si on clique en dehors du contenu (mobile)
  if (searchModal) {
    searchModal.addEventListener("click", (e) => {
      if (e.target === searchModal) {
        searchModal.classList.remove("active");
        document.body.classList.remove("overflow-hidden");
      }
    });
  }
  
  // Empêcher la fermeture du dropdown quand on clique dedans
  if (searchDropdown) {
    searchDropdown.addEventListener("click", (e) => {
      e.stopPropagation();
    });
  }
  
  // Gérer la soumission du formulaire de recherche
  const searchForms = document.querySelectorAll(".search-bar");
  searchForms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      const input = form.querySelector("input");
      if (input && input.value.trim()) {
        console.log("Recherche soumise:", input.value);
        // Ici, vous pouvez ajouter votre logique de recherche
        alert(`Recherche en cours pour: ${input.value}`);
        
        // Fermer le dropdown ou le modal après la recherche
        if (searchDropdown) searchDropdown.classList.remove("active");
        if (searchModal) searchModal.classList.remove("active");
      }
    });
  });
  
  // Gérer le redimensionnement de la fenêtre
  window.addEventListener("resize", () => {
    // Fermer le dropdown sur mobile
    if (window.innerWidth < 768 && searchDropdown) {
      searchDropdown.classList.remove("active");
    }
    // Fermer le modal sur desktop
    if (window.innerWidth >= 768 && searchModal) {
      searchModal.classList.remove("active");
      document.body.classList.remove("overflow-hidden");
    }
  });
}

/**
 * Initialise le slider de catégories
 * Correction complète pour faire fonctionner le slider
 */
function initCategorySlider() {
  const slidesWrapper = document.querySelector(".slides-wrapper");
  if (!slidesWrapper) {
    console.log("Slider non trouvé sur cette page");
    return;
  }

  const slides = document.querySelectorAll(".slide");
  const prevButton = document.querySelector(".prev-arrow");
  const nextButton = document.querySelector(".next-arrow");
  const descriptions = document.querySelectorAll(".category-description");
  
  if (!slides.length || !prevButton || !nextButton || !descriptions.length) {
    console.error("Éléments du slider non trouvés");
    return;
  }

  let currentIndex = 0;
  const slideCount = slides.length;

  // Fonction pour mettre à jour l'affichage du slider
  function updateSlider() {
    // Mettre à jour les états actifs et les transformations
    slides.forEach((slide, index) => {
      if (index === currentIndex) {
        slide.classList.add("active");
        slide.style.opacity = "1";
        slide.style.transform = "scale(1)";
      } else {
        slide.classList.remove("active");
        slide.style.opacity = "0.5";
        slide.style.transform = "scale(0.9)";
      }
    });

    // Mettre à jour les descriptions
    descriptions.forEach((desc, index) => {
      if (index === currentIndex) {
        desc.classList.add("active");
      } else {
        desc.classList.remove("active");
      }
    });

    // Calculer et appliquer la translation
    const slideWidth = slides[0].offsetWidth;
    const gap = 20; // Espacement entre les slides
    const translation = -(currentIndex * (slideWidth + gap));
    slidesWrapper.style.transform = `translateX(${translation}px)`;
  }

  // Fonction pour aller à un slide spécifique
  function goToSlide(index) {
    currentIndex = (index + slideCount) % slideCount; // Boucle circulaire
    updateSlider();
  }

  // Écouteurs d'événements
  prevButton.addEventListener("click", () => {
    goToSlide(currentIndex - 1);
  });
  
  nextButton.addEventListener("click", () => {
    goToSlide(currentIndex + 1);
  });
  
  // Permettre de cliquer sur un slide pour le sélectionner
  slides.forEach((slide, index) => {
    slide.addEventListener("click", () => {
      goToSlide(index);
    });
  });

  // Initialiser le slider
  updateSlider();
}

/**
 * Initialise les formulaires
 */
function initForms() {
  // Formulaire de connexion
  const loginForm = document.querySelector(".login-form");
  if (loginForm) {
    console.log("Initialisation du formulaire de connexion");
    
    loginForm.addEventListener("submit", (e) => {
      e.preventDefault();
      console.log("Tentative de connexion");
      
      // Récupérer les valeurs du formulaire
      const email = loginForm.querySelector('input[type="email"]').value;
      const password = loginForm.querySelector('input[type="password"]').value;
      
      if (!email || !password) {
        showError("Veuillez remplir tous les champs.");
        return;
      }
      
      // Ici, vous pouvez ajouter votre logique de connexion
      console.log("Email:", email);
      console.log("Mot de passe:", password.replace(/./g, '*'));
      
      // Simuler une soumission de formulaire
      loginForm.submit();
    });
  }

  // Formulaire d'inscription
  const registerForm = document.querySelector(".register-form");
  if (registerForm) {
    console.log("Initialisation du formulaire d'inscription");
    
    registerForm.addEventListener("submit", (e) => {
      e.preventDefault();
      console.log("Tentative d'inscription");
      
      // Récupérer les valeurs du formulaire
      const name = registerForm.querySelector('input[name="nom"]')?.value;
      const email = registerForm.querySelector('input[type="email"]').value;
      const password = registerForm.querySelector('input[type="password"]').value;
      const confirmPassword = registerForm.querySelector('input[name="confirm-password"]')?.value;
      
      if (!email || !password || (confirmPassword && password !== confirmPassword)) {
        showError(confirmPassword && password !== confirmPassword 
          ? "Les mots de passe ne correspondent pas." 
          : "Veuillez remplir tous les champs.");
        return;
      }
      
      // Ici, vous pouvez ajouter votre logique d'inscription
      console.log("Nom:", name);
      console.log("Email:", email);
      console.log("Mot de passe:", password.replace(/./g, '*'));
      
      // Simuler une soumission de formulaire
      registerForm.submit();
    });
  }

  // Formulaire de contact
  const contactForm = document.querySelector(".contact-form");
  if (contactForm) {
    console.log("Initialisation du formulaire de contact");
    
    contactForm.addEventListener("submit", (e) => {
      e.preventDefault();
      console.log("Tentative d'envoi de message");
      
      // Récupérer les valeurs du formulaire
      const name = contactForm.querySelector('input[name="nom"]').value;
      const email = contactForm.querySelector('input[type="email"]').value;
      const message = contactForm.querySelector('textarea').value;
      
      if (!name || !email || !message) {
        showError("Veuillez remplir tous les champs.");
        return;
      }
      
      // Ici, vous pouvez ajouter votre logique d'envoi de message
      console.log("Nom:", name);
      console.log("Email:", email);
      console.log("Message:", message);
      
      // Simuler une soumission de formulaire
      alert("Votre message a été envoyé avec succès!");
      contactForm.submit();
    });
  }
}

/**
 * Gère les erreurs dans l'URL
 */
function handleUrlErrors() {
  const urlParams = new URLSearchParams(window.location.search);
  const error = urlParams.get("error");
  const errorMessage = document.getElementById("error-message");

  if (error && errorMessage) {
    console.log("Erreur détectée dans l'URL:", error);
    
    errorMessage.style.display = "block";

    if (error === "invalid") {
      errorMessage.textContent = "Email ou mot de passe incorrect.";
    } else if (error === "empty") {
      errorMessage.textContent = "Veuillez remplir tous les champs.";
    } else if (error === "password") {
      errorMessage.textContent = "Les mots de passe ne correspondent pas.";
    } else {
      errorMessage.textContent = "Une erreur s'est produite. Veuillez réessayer.";
    }
  }
}

/**
 * Affiche un message d'erreur
 */
function showError(message) {
  let errorMessage = document.getElementById("error-message");
  
  if (!errorMessage) {
    errorMessage = document.createElement("div");
    errorMessage.id = "error-message";
    errorMessage.className = "bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4";
    
    const form = document.querySelector("form");
    if (form) {
      form.insertBefore(errorMessage, form.firstChild);
    }
  }
  
  errorMessage.textContent = message;
  errorMessage.style.display = "block";
  
  // Faire défiler jusqu'au message d'erreur
  errorMessage.scrollIntoView({ behavior: "smooth", block: "start" });
}