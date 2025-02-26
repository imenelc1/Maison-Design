document.addEventListener("DOMContentLoaded", () => {
    // Variables pour le menu mobile (présent sur toutes les pages)
    const openMenuBtn = document.getElementById("open-menu")
    const closeMenuBtn = document.getElementById("close-menu")
    const headerContent = document.querySelector(".header-content")
    const menuList = document.querySelector(".menu-list")
  
    // Gestion du menu mobile
    function openMenu() {
      headerContent.classList.remove("mobile-hidden")
      headerContent.classList.add("active")
      openMenuBtn.style.display = "none"
      closeMenuBtn.style.display = "block"
      document.body.style.overflow = "hidden"
    }
  
    function closeMenu() {
      headerContent.classList.remove("active")
      setTimeout(() => {
        headerContent.classList.add("mobile-hidden")
      }, 300)
      closeMenuBtn.style.display = "none"
      openMenuBtn.style.display = "block"
      document.body.style.overflow = "auto"
    }
  
    openMenuBtn.addEventListener("click", openMenu)
    closeMenuBtn.addEventListener("click", closeMenu)
  
    // Gestion des sous-menus sur mobile
    const subMenuParents = document.querySelectorAll(".menu-list > li > a:not(:only-child)")
  
    subMenuParents.forEach((item) => {
      item.addEventListener("click", function (e) {
        if (window.innerWidth <= 768) {
          e.preventDefault()
          this.nextElementSibling.style.display = this.nextElementSibling.style.display === "block" ? "none" : "block"
        }
      })
    })
  
    // Fermer le menu mobile lors du clic sur un lien
    headerContent.querySelectorAll("a").forEach((link) => {
      link.addEventListener("click", () => {
        if (window.innerWidth <= 768) {
          closeMenu()
        }
      })
    })
  
    // Gestion du redimensionnement de la fenêtre
    window.addEventListener("resize", () => {
      if (window.innerWidth > 768) {
        headerContent.classList.remove("mobile-hidden", "active")
        openMenuBtn.style.display = "none"
        closeMenuBtn.style.display = "none"
        document.body.style.overflow = "auto"
      } else {
        if (!headerContent.classList.contains("active")) {
          headerContent.classList.add("mobile-hidden")
          openMenuBtn.style.display = "block"
          closeMenuBtn.style.display = "none"
        }
      }
    })
  
    // Fonctionnalités spécifiques à la page d'accueil
    const slidesWrapper = document.querySelector(".slides-wrapper")
    if (slidesWrapper) {
      // Variables pour le slider
      const slides = document.querySelectorAll(".slide")
      const prevButton = document.querySelector(".prev-arrow")
      const nextButton = document.querySelector(".next-arrow")
      const descriptions = document.querySelectorAll(".category-description")
      let currentIndex = 0
      const slideCount = slides.length
  
      // Fonction pour mettre à jour le slider
      function updateSlider() {
        const slideWidth = slides[0].clientWidth + 20
        slidesWrapper.style.transform = `translateX(${-currentIndex * slideWidth}px)`
        slidesWrapper.style.transition = "transform 0.5s ease-in-out"
  
        slides.forEach((slide, index) => {
          slide.classList.toggle("active", index === currentIndex)
        })
  
        descriptions.forEach((desc, index) => {
          desc.classList.toggle("active", index === currentIndex)
        })
      }
  
      // Navigation du slider
      function goToSlide(index) {
        currentIndex = (index + slideCount) % slideCount
        updateSlider()
      }
  
      prevButton.addEventListener("click", () => goToSlide(currentIndex - 1))
      nextButton.addEventListener("click", () => goToSlide(currentIndex + 1))
  
      // Ajouter la navigation en cliquant sur les images
      slides.forEach((slide, index) => {
        slide.addEventListener("click", () => goToSlide(index))
      })
  
      // Ajuster le comportement du slider sur mobile
      function updateSliderForMobile() {
        if (window.innerWidth <= 768) {
          slides.forEach((slide) => {
            slide.style.minWidth = "100%"
          })
        } else {
          slides.forEach((slide) => {
            slide.style.minWidth = "calc(33.333% - 14px)"
          })
        }
        updateSlider()
      }
  
      // Amélioration de l'accessibilité
      const sliderArrows = document.querySelectorAll(".slider-arrow")
      sliderArrows.forEach((arrow) => {
        arrow.setAttribute("aria-label", arrow.classList.contains("prev-arrow") ? "Précédent" : "Suivant")
      })
  
      // Initialisation
      updateSliderForMobile()
  
      // Défilement automatique du slider
      let autoSlideInterval = setInterval(() => goToSlide(currentIndex + 1), 5000)
  
      // Arrêter le défilement automatique lorsque la souris est sur le slider
      document.querySelector(".categories-slider").addEventListener("mouseenter", () => {
        clearInterval(autoSlideInterval)
      })
  
      // Reprendre le défilement automatique lorsque la souris quitte le slider
      document.querySelector(".categories-slider").addEventListener("mouseleave", () => {
        autoSlideInterval = setInterval(() => goToSlide(currentIndex + 1), 5000)
      })
  
      // Ajouter l'événement de redimensionnement pour le slider
      window.addEventListener("resize", updateSliderForMobile)
    }
  
    // Fonctionnalités spécifiques aux pages de connexion et d'inscription
    const loginForm = document.querySelector(".login-form")
    const registerForm = document.querySelector(".register-form")
  
    if (loginForm) {
      loginForm.addEventListener("submit", (e) => {
        e.preventDefault()
        // Ajoutez ici la logique de connexion
        console.log("Tentative de connexion")
      })
    }
  
    if (registerForm) {
      registerForm.addEventListener("submit", (e) => {
        e.preventDefault()
        // Ajoutez ici la logique d'inscription
        console.log("Tentative d'inscription")
      })
    }
  })
  
  