document.addEventListener("DOMContentLoaded", () => {
  // Éléments du DOM
  const openMenuBtn = document.getElementById("open-menu")
  const closeMenuBtn = document.getElementById("close-menu")
  const mobileMenu = document.getElementById("mobile-menu")
  const menuOverlay = document.getElementById("menu-overlay")
  const searchToggle = document.getElementById("search-toggle")
  const searchDropdown = document.getElementById("search-dropdown")
  const searchModal = document.getElementById("search-modal")
  const closeSearchModal = document.getElementById("close-search-modal")
  const mobileDropdown = document.querySelector(".mobile-dropdown button")
  const mobileSubmenu = document.getElementById("mobile-submenu")
  const chevronMobile = document.getElementById("chevron-mobile")

  // Nouveaux éléments pour le menu utilisateur
  const userMenuToggle = document.getElementById("user-menu-toggle")
  const userDropdown = document.getElementById("user-dropdown")
  const userChevron = document.getElementById("user-chevron")

  // État du menu utilisateur
  let userMenuOpen = false

  // Fonction pour ouvrir le menu mobile
  
  function openMobileMenu() {
    if (mobileMenu) {
      mobileMenu.style.transform = "translateX(0)"
    
    }
    if (menuOverlay) {
      menuOverlay.style.opacity = "1"
      menuOverlay.style.visibility = "visible"
    }
    document.body.style.overflow = "hidden"
  }

  // Fonction pour fermer le menu mobile 
  function closeMobileMenu() {
    if (mobileMenu) {
      mobileMenu.style.transform = "translateX(100%)"
      
    }
    if (menuOverlay) {
      menuOverlay.style.opacity = "0"
      menuOverlay.style.visibility = "invisible"
    }
    document.body.style.overflow = ""
  }

  // Fonction pour ouvrir la recherche
  function openSearch() {
    if (window.innerWidth < 640) {
      // Mobile
      if (searchModal) {
        searchModal.style.opacity = "1"
        searchModal.style.visibility = "visible"
        const modalContent = searchModal.querySelector(".search-modal-content")
        if (modalContent) {
          modalContent.style.transform = "translateY(0)"
        }
        document.body.style.overflow = "hidden"
      }
    } else {
      // Desktop
      if (searchDropdown) {
        searchDropdown.style.opacity = "1"
        searchDropdown.style.visibility = "visible"
        searchDropdown.style.transform = "translateY(0)"
      }
    }
  }

  // Fonction pour fermer la recherche
  function closeSearch() {
    // Mobile
    if (searchModal) {
      searchModal.style.opacity = "0"
      searchModal.style.visibility = "invisible"
      const modalContent = searchModal.querySelector(".search-modal-content")
      if (modalContent) {
        modalContent.style.transform = "translateY(-20px)"
      }
      document.body.style.overflow = ""
    }

    // Desktop
    if (searchDropdown) {
      searchDropdown.style.opacity = "0"
      searchDropdown.style.visibility = "invisible"
      searchDropdown.style.transform = "translateY(8px)"
    }
  }

  // Fonction pour toggle le sous-menu mobile
  function toggleMobileSubmenu() {
    if (!mobileSubmenu || !chevronMobile) return

    const isOpen = mobileSubmenu.style.maxHeight && mobileSubmenu.style.maxHeight !== "0px"

    if (isOpen) {
      mobileSubmenu.style.maxHeight = "0px"
      chevronMobile.style.transform = "rotate(0deg)"
    } else {
      mobileSubmenu.style.maxHeight = mobileSubmenu.scrollHeight + "px"
      chevronMobile.style.transform = "rotate(180deg)"
    }
  }

  // Fonction pour toggle le menu utilisateur
  function toggleUserMenu() {
    if (!userDropdown) return

    userMenuOpen = !userMenuOpen

    if (userMenuOpen) {
      userDropdown.style.opacity = "1"
      userDropdown.style.visibility = "visible"
      userDropdown.style.transform = "translateY(0)"
      if (userChevron) {
        userChevron.style.transform = "rotate(180deg)"
      }
    } else {
      userDropdown.style.opacity = "0"
      userDropdown.style.visibility = "invisible"
      userDropdown.style.transform = "translateY(8px)"
      if (userChevron) {
        userChevron.style.transform = "rotate(0deg)"
      }
    }
  }

  // Fonction pour fermer le menu utilisateur
  function closeUserMenu() {
    if (userMenuOpen && userDropdown) {
      userMenuOpen = false
      userDropdown.style.opacity = "0"
      userDropdown.style.visibility = "invisible"
      userDropdown.style.transform = "translateY(8px)"
      if (userChevron) {
        userChevron.style.transform = "rotate(0deg)"
      }
    }
  }

  // Event listeners existants
  if (openMenuBtn) {
    openMenuBtn.addEventListener("click", (e) => {
      e.preventDefault()
      openMobileMenu()
    })
  }

  if (closeMenuBtn) {
    closeMenuBtn.addEventListener("click", (e) => {
      e.preventDefault()
      closeMobileMenu()
    })
  }

  if (menuOverlay) {
    menuOverlay.addEventListener("click", closeMobileMenu)
  }

  if (searchToggle) {
    searchToggle.addEventListener("click", (e) => {
      e.stopPropagation()
      openSearch()
    })
  }

  if (closeSearchModal) {
    closeSearchModal.addEventListener("click", closeSearch)
  }

  if (searchModal) {
    searchModal.addEventListener("click", (e) => {
      if (e.target === searchModal) {
        closeSearch()
      }
    })
  }

  if (mobileDropdown) {
    mobileDropdown.addEventListener("click", toggleMobileSubmenu)
  }

  // Event listeners pour le menu utilisateur
  if (userMenuToggle) {
    userMenuToggle.addEventListener("click", (e) => {
      e.stopPropagation()
      toggleUserMenu()
    })
  }

  // Fermer les dropdowns en cliquant ailleurs
  document.addEventListener("click", (e) => {
    // Fermer la recherche
    if (searchToggle && searchDropdown && !searchToggle.contains(e.target) && !searchDropdown.contains(e.target)) {
      closeSearch()
    }

    // Fermer le menu utilisateur
    if (userMenuToggle && userDropdown) {
      if (!userMenuToggle.contains(e.target) && !userDropdown.contains(e.target)) {
        closeUserMenu()
      }
    }
  })

  // Gérer le redimensionnement de la fenêtre
  window.addEventListener("resize", () => {
    if (window.innerWidth >= 1024) {
      closeMobileMenu()
    }
    closeSearch()
    closeUserMenu()
  })

  // Fermer les menus avec Escape
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      closeMobileMenu()
      closeSearch()
      closeUserMenu()
    }
  })

  // Améliorer l'accessibilité
  const focusableElements = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'

  function trapFocus(element) {
    if (!element) return

    const focusableContent = element.querySelectorAll(focusableElements)
    const firstFocusableElement = focusableContent[0]
    const lastFocusableElement = focusableContent[focusableContent.length - 1]

    element.addEventListener("keydown", (e) => {
      if (e.key === "Tab") {
        if (e.shiftKey) {
          if (document.activeElement === firstFocusableElement) {
            lastFocusableElement.focus()
            e.preventDefault()
          }
        } else {
          if (document.activeElement === lastFocusableElement) {
            firstFocusableElement.focus()
            e.preventDefault()
          }
        }
      }
    })
  }

  // Appliquer le trap focus quand le menu mobile est ouvert
  if (openMenuBtn) {
    openMenuBtn.addEventListener("click", () => {
      setTimeout(() => {
        if (mobileMenu) {
          trapFocus(mobileMenu)
        }
      }, 100)
    })
  }

  // Support tactile pour le menu utilisateur sur tablettes
  if (userMenuToggle && "ontouchstart" in window) {
    userMenuToggle.addEventListener("touchstart", (e) => {
      e.preventDefault()
      toggleUserMenu()
    })
  }
})
