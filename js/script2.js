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

  // ======= ADMIN PANEL =======
  // Vérifiez si nous sommes sur la page d'administration
  if (document.querySelector(".tab-trigger")) {
    initAdminPanel();
  }
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

/**
 * Initialise le panneau d'administration
 */
function initAdminPanel() {
  console.log("Initialisation du panneau d'administration...");

  // Ajouter Font Awesome pour les icônes
  if (!document.querySelector('link[href*="font-awesome"]')) {
    const fontAwesome = document.createElement('link');
    fontAwesome.rel = 'stylesheet';
    fontAwesome.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
    document.head.appendChild(fontAwesome);
  }

  // Créer un toast pour les notifications
  if (!document.getElementById('toast')) {
    const toast = document.createElement('div');
    toast.id = 'toast';
    toast.className = 'hidden fixed bottom-4 right-4 bg-gray-800 text-white px-4 py-2 rounded-md shadow-lg z-50';
    toast.innerHTML = '<p id="toast-message"></p>';
    document.body.appendChild(toast);
  }

  // Pagination state
  let clientsCurrentPage = 1;
  let productsCurrentPage = 1;
  let ordersCurrentPage = 1;
  const itemsPerPage = 4;

  // Search state
  let searchQuery = "";

  // Sample data
  const clients = [
    { id: 1, nom: "Imen", prenom: "lakhdar chaouch", email: "imen@example.com", telephone: "+213 555 123 456", adresse: "Alger, Algérie" },
    { id: 2, nom: "Ahmed", prenom: "abc" , email: "ahmed@example.com", telephone: "+213 555 789 012", adresse: "Oran, Algérie" },
    { id: 3, nom: "Fatima",prenom: "abc" , email: "fatima@example.com", telephone: "+213 555 345 678", adresse: "Constantine, Algérie" },
    { id: 4, nom: "Karim", prenom: "abc" , email: "karim@example.com", telephone: "+213 555 901 234", adresse: "Annaba, Algérie" },
    { id: 5, nom: "Leila", prenom: "abc" ,email: "leila@example.com", telephone: "+213 555 567 890", adresse: "Tlemcen, Algérie" },
  ];

  const products = [
    { id: 101, nom: "Chaise Moderne", categorie: "Chaises", prix: 4500, stock: 15 },
    { id: 102, nom: "Table à Manger", categorie: "Tables", prix: 12000, stock: 8 },
    { id: 103, nom: "Canapé 3 Places", categorie: "Canapés", prix: 25000, stock: 5 },
    { id: 104, nom: "Lit King Size", categorie: "Lits", prix: 18000, stock: 7 },
    { id: 105, nom: "Armoire Penderie", categorie: "Rangements", prix: 15000, stock: 10 },
    { id: 106, nom: "Bureau Ergonomique", categorie: "Bureaux", prix: 8500, stock: 12 },
  ];

  const orders = [
    { id: 5001, client: "Imen", date: "2025-03-10", statut: "En cours", total: 8500 },
    { id: 5002, client: "Ahmed", date: "2025-03-09", statut: "Livré", total: 12000 },
    { id: 5003, client: "Fatima", date: "2025-03-08", statut: "En attente", total: 5500 },
    { id: 5004, client: "Karim", date: "2025-03-07", statut: "Annulé", total: 7800 },
    { id: 5005, client: "Leila", date: "2025-03-06", statut: "Livré", total: 15000 },
    { id: 5006, client: "Mohamed", date: "2025-03-05", statut: "En cours", total: 9200 },
  ];

  // Order details
  const orderDetails = {
    5001: [
      { produit: "Chaise Moderne", quantite: 1, prix: 4500, total: 4500 },
      { produit: "Table Basse", quantite: 1, prix: 4000, total: 4000 },
    ],
    5002: [{ produit: "Canapé 3 Places", quantite: 1, prix: 12000, total: 12000 }],
    5003: [
      { produit: "Lampe de Bureau", quantite: 2, prix: 1500, total: 3000 },
      { produit: "Coussin Décoratif", quantite: 5, prix: 500, total: 2500 },
    ],
    5004: [{ produit: "Étagère Murale", quantite: 3, prix: 2600, total: 7800 }],
    5005: [{ produit: "Lit King Size", quantite: 1, prix: 15000, total: 15000 }],
    5006: [
      { produit: "Bureau Ergonomique", quantite: 1, prix: 8500, total: 8500 },
      { produit: "Lampe de Bureau", quantite: 1, prix: 700, total: 700 },
    ],
  };

  // DOM Elements
  const tabTriggers = document.querySelectorAll(".tab-trigger");
  const tabContents = document.querySelectorAll(".tab-content");

  // Initialize tabs
  initTabs();

  // Initialize modals
  initModals();

  // Load initial data
  renderClients();
  renderProducts();
  renderOrders();

  // Initialize form handlers
  initFormHandlers();

  // Initialize pagination
  initPagination();

  // Initialize tabs
  function initTabs() {
    tabTriggers.forEach((trigger) => {
      trigger.addEventListener("click", () => {
        // Retirer les classes actives et cacher les contenus
        tabTriggers.forEach((t) => t.classList.remove("active"));
        tabContents.forEach((c) => {
          c.classList.remove("active");
          c.classList.add("hidden");
        });
  
        // Activer l'onglet cliqué
        trigger.classList.add("active");
  
        // Afficher le contenu correspondant
        const tabId = trigger.getAttribute("data-tab");
        const tabContent = document.getElementById(`${tabId}-tab`);
        if (tabContent) {
          tabContent.classList.add("active");
          tabContent.classList.remove("hidden");
        }
      });
    });
  }
  

  // Initialize modals
  function initModals() {
    const modals = document.querySelectorAll(".modal");
    const modalTriggers = {
      "add-client-btn": "add-client-modal",
      "add-product-btn": "add-product-modal",
    };

    // Open modals
    Object.keys(modalTriggers).forEach((triggerId) => {
      const trigger = document.getElementById(triggerId);
      if (trigger) {
        const modalId = modalTriggers[triggerId];
        trigger.addEventListener("click", () => {
          openModal(modalId);
        });
      }
    });

    // Close modals
    const closeButtons = document.querySelectorAll(".modal-close");
    closeButtons.forEach((button) => {
      button.addEventListener("click", () => {
        const modal = button.closest(".modal");
        if (modal) {
          closeModal(modal.id);
        }
      });
    });

    // Close modal when clicking outside
    modals.forEach((modal) => {
      modal.addEventListener("click", (e) => {
        if (e.target === modal) {
          closeModal(modal.id);
        }
      });
    });
  }

  // Open modal
  function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.remove("hidden");
      modal.classList.add("show");
      document.body.style.overflow = "hidden";
    }
  }

  // Close modal
  function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.remove("show");
      modal.classList.add("hidden");
      document.body.style.overflow = "";
    }
  }

  // Filter data by search query
  function filterBySearch(data) {
    if (!searchQuery) return data;

    return data.filter((item) => {
      return Object.values(item).some((value) => String(value).toLowerCase().includes(searchQuery));
    });
  }

  // Render clients table
  function renderClients() {
    const tableBody = document.getElementById("clients-table-body");
    if (!tableBody) return;
    
    const filteredClients = filterBySearch(clients);
    const paginatedClients = paginate(filteredClients, clientsCurrentPage, itemsPerPage);

    // Update pagination text
    const totalPages = Math.ceil(filteredClients.length / itemsPerPage);
    const paginationElement = document.getElementById("clients-pagination");
    if (paginationElement) {
      paginationElement.textContent = `Page ${clientsCurrentPage} sur ${totalPages || 1}`;
    }

    // Enable/disable pagination buttons
    const prevButton = document.getElementById("clients-prev-page");
    const nextButton = document.getElementById("clients-next-page");
    
    if (prevButton) {
      prevButton.disabled = clientsCurrentPage === 1;
    }
    
    if (nextButton) {
      nextButton.disabled = clientsCurrentPage >= totalPages;
    }

    // Clear table
    tableBody.innerHTML = "";

    // Add rows
    if (paginatedClients.length === 0) {
      tableBody.innerHTML = `
        <tr>
          <td colspan="6" class="px-6 py-4 text-center">Aucun client trouvé</td>
        </tr>
      `;
      return;
    }

    paginatedClients.forEach((client) => {
      const row = document.createElement("tr");
      row.innerHTML = `
        <td class="px-6 py-4 whitespace-nowrap">${client.id}</td>
        <td class="px-6 py-4 whitespace-nowrap">${client.prenom}</td>
        <td class="px-6 py-4 whitespace-nowrap">${client.nom}</td>
        <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">${client.email}</td>
        <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">${client.telephone}</td>
        <td class="px-6 py-4 whitespace-nowrap hidden lg:table-cell">${client.adresse}</td>
        <td class="px-6 py-4 whitespace-nowrap">
          <div class="flex space-x-2">
            <button class="delete-client-btn btn-supprimer px-2 py-1 rounded-md" data-id="${client.id}">
              <i class="fas fa-trash-alt"></i>
            </button>
          </div>
        </td>
      `;
      tableBody.appendChild(row);
    });

    // Add event listeners to edit and delete buttons
    document.querySelectorAll(".edit-client-btn").forEach((button) => {
      button.addEventListener("click", () => {
        const id = Number.parseInt(button.getAttribute("data-id"));
        const client = clients.find((c) => c.id === id);

        if (client) {
          const form = document.getElementById("edit-client-form");
          if (form) {
            form.elements.id.value = client.id;
            form.elements.nom.value = client.nom;
            form.elements.prenom.value = client.prenom;
            form.elements.email.value = client.email;
            form.elements.telephone.value = client.telephone;
            form.elements.adresse.value = client.adresse;

            openModal("edit-client-modal");
          }
        }
      });
    });

    document.querySelectorAll(".delete-client-btn").forEach((button) => {
      button.addEventListener("click", () => {
        const id = Number.parseInt(button.getAttribute("data-id"));
        const client = clients.find((c) => c.id === id);

        if (client) {
          const form = document.getElementById("delete-client-form");
          if (form) {
            form.elements.id.value = client.id;
            openModal("delete-client-modal");
          }
        }
      });
    });
  }

  // Render products table
  function renderProducts() {
    const tableBody = document.getElementById("products-table-body");
    if (!tableBody) return;
  
    const filteredProducts = filterBySearch(products);
    const paginatedProducts = paginate(filteredProducts, productsCurrentPage, itemsPerPage);
  
    // Mise à jour pagination
    const totalPages = Math.ceil(filteredProducts.length / itemsPerPage);
    const paginationElement = document.getElementById("products-pagination");
    if (paginationElement) {
      paginationElement.textContent = `Page ${productsCurrentPage} sur ${totalPages || 1}`;
    }
  
    const prevButton = document.getElementById("products-prev-page");
    const nextButton = document.getElementById("products-next-page");
  
    if (prevButton) prevButton.disabled = productsCurrentPage === 1;
    if (nextButton) nextButton.disabled = productsCurrentPage >= totalPages;
  
    // Vider le tableau
    tableBody.innerHTML = "";
  
    // Aucun résultat
    if (paginatedProducts.length === 0) {
      tableBody.innerHTML = `
        <tr>
          <td colspan="6" class="px-6 py-4 text-center">Aucun produit trouvé</td>
        </tr>
      `;
      return;
    }
  
    // Affichage des produits
    paginatedProducts.forEach((product) => {
      const row = document.createElement("tr");
      row.innerHTML = `
        <td class="px-6 py-4 whitespace-nowrap">${product.id}</td>
        <td class="px-6 py-4 whitespace-nowrap flex items-center gap-3">
          ${product.image ? `<img src="${product.image}" alt="${product.nom}" class="w-10 h-10 object-cover rounded-md">` : ''}
          <span>${product.nom}</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">${product.categorie}</td>
        <td class="px-6 py-4 whitespace-nowrap">${product.prix} DA</td>
        <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">${product.stock}</td>
        <td class="px-6 py-4 whitespace-nowrap">
          <div class="flex space-x-2">
            <button class="edit-product-btn btn-modifier px-2 py-1 rounded-md" data-id="${product.id}">
              <i class="fas fa-pencil-alt"></i>
            </button>
            <button class="delete-product-btn btn-supprimer px-2 py-1 rounded-md" data-id="${product.id}">
              <i class="fas fa-trash-alt"></i>
            </button>
          </div>
        </td>
      `;
      tableBody.appendChild(row);
    });
  
    // Écouteurs pour modifier/supprimer
    document.querySelectorAll(".edit-product-btn").forEach((button) => {
      button.addEventListener("click", () => {
        const id = Number.parseInt(button.getAttribute("data-id"));
        const product = products.find((p) => p.id === id);
        if (product) {
          const form = document.getElementById("edit-product-form");
          if (form) {
            form.elements.id.value = product.id;
            form.elements.nom.value = product.nom;
            form.elements.categorie.value = product.categorie;
            form.elements.prix.value = product.prix;
            form.elements.stock.value = product.stock;
            openModal("edit-product-modal");
          }
        }
      });
    });
  
    document.querySelectorAll(".delete-product-btn").forEach((button) => {
      button.addEventListener("click", () => {
        const id = Number.parseInt(button.getAttribute("data-id"));
        const product = products.find((p) => p.id === id);
        if (product) {
          const form = document.getElementById("delete-product-form");
          if (form) {
            form.elements.id.value = product.id;
            openModal("delete-product-modal");
          }
        }
      });
    });
  }
  
  // Render orders table
  function renderOrders() {
    const tableBody = document.getElementById("orders-table-body");
    if (!tableBody) return;
    
    const filteredOrders = filterBySearch(orders);
    const paginatedOrders = paginate(filteredOrders, ordersCurrentPage, itemsPerPage);

    // Update pagination text
    const totalPages = Math.ceil(filteredOrders.length / itemsPerPage);
    const paginationElement = document.getElementById("orders-pagination");
    if (paginationElement) {
      paginationElement.textContent = `Page ${ordersCurrentPage} sur ${totalPages || 1}`;
    }

    // Enable/disable pagination buttons
    const prevButton = document.getElementById("orders-prev-page");
    const nextButton = document.getElementById("orders-next-page");
    
    if (prevButton) {
      prevButton.disabled = ordersCurrentPage === 1;
    }
    
    if (nextButton) {
      nextButton.disabled = ordersCurrentPage >= totalPages;
    }

    // Clear table
    tableBody.innerHTML = "";

    // Add rows
    if (paginatedOrders.length === 0) {
      tableBody.innerHTML = `
        <tr>
          <td colspan="6" class="px-6 py-4 text-center">Aucune commande trouvée</td>
        </tr>
      `;
      return;
    }

    paginatedOrders.forEach((order) => {
      const row = document.createElement("tr");

      // Get status badge class
      let statusClass = "";
      switch (order.statut) {
        case "En attente":
          statusClass = "status-pending";
          break;
        case "En cours":
          statusClass = "status-processing";
          break;
        case "Livré":
          statusClass = "status-delivered";
          break;
        case "Annulé":
          statusClass = "status-cancelled";
          break;
      }

      row.innerHTML = `
        <td class="px-6 py-4 whitespace-nowrap">${order.id}</td>
        <td class="px-6 py-4 whitespace-nowrap">${order.client}</td>
        <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">${order.date}</td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="status-badge ${statusClass}">${order.statut}</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">${order.total} DA</td>
        <td class="px-6 py-4 whitespace-nowrap">
          <div class="flex space-x-2">
            <button class="view-order-btn btn-details px-2 py-1 rounded-md" data-id="${order.id}">
              <i class="fas fa-eye"></i>
            </button>
            <button class="change-status-btn btn-statut px-2 py-1 rounded-md" data-id="${order.id}">
              <i class="fas fa-exchange-alt"></i>
            </button>
          </div>
        </td>
      `;
      tableBody.appendChild(row);
    });

    // Add event listeners to view and change status buttons
    document.querySelectorAll(".view-order-btn").forEach((button) => {
      button.addEventListener("click", () => {
        const id = Number.parseInt(button.getAttribute("data-id"));
        const order = orders.find((o) => o.id === id);

        if (order) {
          // Populate order details
          const orderIdElement = document.getElementById("order-id");
          const orderClientElement = document.getElementById("order-client");
          const orderDateElement = document.getElementById("order-date");
          const orderStatusElement = document.getElementById("order-status");
          const orderTotalElement = document.getElementById("order-total");
          
          if (orderIdElement) orderIdElement.textContent = order.id;
          if (orderClientElement) orderClientElement.textContent = order.client;
          if (orderDateElement) orderDateElement.textContent = order.date;

          // Status badge
          let statusClass = "";
          switch (order.statut) {
            case "En attente":
              statusClass = "status-pending";
              break;
            case "En cours":
              statusClass = "status-processing";
              break;
            case "Livré":
              statusClass = "status-delivered";
              break;
            case "Annulé":
              statusClass = "status-cancelled";
              break;
          }

          if (orderStatusElement) {
            orderStatusElement.innerHTML = `<span class="status-badge ${statusClass}">${order.statut}</span>`;
          }
          
          if (orderTotalElement) {
            orderTotalElement.textContent = `${order.total} DA`;
          }

          // Populate order items
          const detailsBody = document.getElementById("order-details-body");
          if (detailsBody) {
            detailsBody.innerHTML = "";

            const details = orderDetails[order.id] || [];

            if (details.length === 0) {
              detailsBody.innerHTML = `
                <tr>
                  <td colspan="4" class="px-6 py-4 text-center">Aucun détail disponible</td>
                </tr>
              `;
            } else {
              details.forEach((item) => {
                const row = document.createElement("tr");
                row.innerHTML = `
                  <td class="px-6 py-4 whitespace-nowrap">${item.produit}</td>
                  <td class="px-6 py-4 whitespace-nowrap">${item.quantite}</td>
                  <td class="px-6 py-4 whitespace-nowrap">${item.prix} DA</td>
                  <td class="px-6 py-4 whitespace-nowrap">${item.total} DA</td>
                `;
                detailsBody.appendChild(row);
              });
            }
          }

          openModal("view-order-modal");
        }
      });
    });

    document.querySelectorAll(".change-status-btn").forEach((button) => {
      button.addEventListener("click", () => {
        const id = Number.parseInt(button.getAttribute("data-id"));
        const order = orders.find((o) => o.id === id);

        if (order) {
          const form = document.getElementById("change-status-form");
          if (form) {
            form.elements.id.value = order.id;

            // Set current status
            const statusSelect = form.elements.statut;
            for (let i = 0; i < statusSelect.options.length; i++) {
              if (statusSelect.options[i].value === order.statut) {
                statusSelect.selectedIndex = i;
                break;
              }
            }

            openModal("change-status-modal");
          }
        }
      });
    });
  }

  // Initialize form handlers
  function initFormHandlers() {

    // Delete client form
    const deleteClientForm = document.getElementById("delete-client-form");
    if (deleteClientForm) {
      deleteClientForm.addEventListener("submit", function (e) {
        e.preventDefault();

        const id = Number.parseInt(this.elements.id.value);
        const clientIndex = clients.findIndex((c) => c.id === id);

        if (clientIndex !== -1) {
          const clientName = clients[clientIndex].nom;
          clients.splice(clientIndex, 1);

          this.reset();
          closeModal("delete-client-modal");
          renderClients();
          showToast(`Client ${clientName} supprimé avec succès`);
        }
      });
    }

    // Add product form
    const addProductForm = document.getElementById("add-product-form");
    if (addProductForm) {
      addProductForm.addEventListener("submit", function (e) {
        e.preventDefault();

        const imageFile = this.elements.image.files[0];
        const imageUrl = imageFile ? URL.createObjectURL(imageFile) : '';
        const newProduct = {
          id: Math.max(...products.map((p) => p.id), 0) + 1,
          nom: this.elements.nom.value,
          categorie: this.elements.categorie.value,
          prix: Number.parseInt(this.elements.prix.value),
          stock: Number.parseInt(this.elements.stock.value),
          image: imageUrl
        };

        products.push(newProduct);
        this.reset();
        closeModal("add-product-modal");
        renderProducts();
        showToast(`Produit ${newProduct.nom} ajouté avec succès`);
      });
    }

    // Edit product form
    const editProductForm = document.getElementById("edit-product-form");
    if (editProductForm) {
      editProductForm.addEventListener("submit", function (e) {
        e.preventDefault();

        const id = Number.parseInt(this.elements.id.value);
        const productIndex = products.findIndex((p) => p.id === id);

        if (productIndex !== -1) {
          products[productIndex] = {
            id: id,
            nom: this.elements.nom.value,
            categorie: this.elements.categorie.value,
            prix: Number.parseInt(this.elements.prix.value),
            stock: Number.parseInt(this.elements.stock.value),
          };

          this.reset();
          closeModal("edit-product-modal");
          renderProducts();
          showToast(`Produit modifié avec succès`);
        }
      });
    }

    // Delete product form
    const deleteProductForm = document.getElementById("delete-product-form");
    if (deleteProductForm) {
      deleteProductForm.addEventListener("submit", function (e) {
        e.preventDefault();

        const id = Number.parseInt(this.elements.id.value);
        const productIndex = products.findIndex((p) => p.id === id);

        if (productIndex !== -1) {
          const productName = products[productIndex].nom;
          products.splice(productIndex, 1);

          this.reset();
          closeModal("delete-product-modal");
          renderProducts();
          showToast(`Produit ${productName} supprimé avec succès`);
        }
      });
    }

    // Change order status form
    const changeStatusForm = document.getElementById("change-status-form");
    if (changeStatusForm) {
      changeStatusForm.addEventListener("submit", function (e) {
        e.preventDefault();

        const id = Number.parseInt(this.elements.id.value);
        const orderIndex = orders.findIndex((o) => o.id === id);

        if (orderIndex !== -1) {
          orders[orderIndex].statut = this.elements.statut.value;

          this.reset();
          closeModal("change-status-modal");
          renderOrders();
          showToast(`Statut de la commande #${id} modifié avec succès`);
        }
      });
    }
  }

  // Initialize pagination
  function initPagination() {
    // Clients pagination
    const clientsPrevPage = document.getElementById("clients-prev-page");
    const clientsNextPage = document.getElementById("clients-next-page");
    
    if (clientsPrevPage) {
      clientsPrevPage.addEventListener("click", () => {
        if (clientsCurrentPage > 1) {
          clientsCurrentPage--;
          renderClients();
        }
      });
    }
    
    if (clientsNextPage) {
      clientsNextPage.addEventListener("click", () => {
        const filteredClients = filterBySearch(clients);
        const totalPages = Math.ceil(filteredClients.length / itemsPerPage);

        if (clientsCurrentPage < totalPages) {
          clientsCurrentPage++;
          renderClients();
        }
      });
    }

    // Products pagination
    const productsPrevPage = document.getElementById("products-prev-page");
    const productsNextPage = document.getElementById("products-next-page");
    
    if (productsPrevPage) {
      productsPrevPage.addEventListener("click", () => {
        if (productsCurrentPage > 1) {
          productsCurrentPage--;
          renderProducts();
        }
      });
    }
    
    if (productsNextPage) {
      productsNextPage.addEventListener("click", () => {
        const filteredProducts = filterBySearch(products);
        const totalPages = Math.ceil(filteredProducts.length / itemsPerPage);

        if (productsCurrentPage < totalPages) {
          productsCurrentPage++;
          renderProducts();
        }
      });
    }

    // Orders pagination
    const ordersPrevPage = document.getElementById("orders-prev-page");
    const ordersNextPage = document.getElementById("orders-next-page");
    
    if (ordersPrevPage) {
      ordersPrevPage.addEventListener("click", () => {
        if (ordersCurrentPage > 1) {
          ordersCurrentPage--;
          renderOrders();
        }
      });
    }
    
    if (ordersNextPage) {
      ordersNextPage.addEventListener("click", () => {
        const filteredOrders = filterBySearch(orders);
        const totalPages = Math.ceil(filteredOrders.length / itemsPerPage);

        if (ordersCurrentPage < totalPages) {
          ordersCurrentPage++;
          renderOrders();
        }
      });
    }
  }

  // Paginate data
  function paginate(data, page, itemsPerPage) {
    const start = (page - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return data.slice(start, end);
  }

  // Show toast notification
  function showToast(message) {
    const toast = document.getElementById("toast");
    const toastMessage = document.getElementById("toast-message");
    
    if (!toast || !toastMessage) return;

    toastMessage.textContent = message;
    toast.classList.remove("hidden");
    toast.classList.add("show");

    setTimeout(() => {
      toast.classList.remove("show");
      setTimeout(() => {
        toast.classList.add("hidden");
      }, 300);
    }, 3000);
  }
}
// Gestion du menu profil
document.addEventListener('DOMContentLoaded', function() {
    const profileToggle = document.getElementById('profile-toggle');
    const profileDropdown = document.getElementById('profile-dropdown');
    
    // Afficher/cacher le menu profil
    profileToggle.addEventListener('click', function() {
        profileDropdown.classList.toggle('hidden');
    });
    
    // Cacher le menu quand on clique ailleurs
    document.addEventListener('click', function(event) {
        if (!profileToggle.contains(event.target)) {
            profileDropdown.classList.add('hidden');
        }
    });
    
    // Gestion de la déconnexion
    document.getElementById('logout-btn').addEventListener('click', function(e) {
        e.preventDefault();
        // Ici tu peux ajouter la logique de déconnexion
        // Par exemple, rediriger vers la page de connexion :
        window.location.href = 'connexion.html';
        
        // Ou faire une requête AJAX pour déconnecter l'utilisateur
        // puis rediriger
    });
});

// Gestion des onglets
document.querySelectorAll('.tab-trigger').forEach(trigger => {
    trigger.addEventListener('click', function() {
        // Retire active de tous les boutons
        document.querySelectorAll('.tab-trigger').forEach(t => t.classList.remove('active'));
        
        // Ajoute active au bouton cliqué
        this.classList.add('active');
        
        // Cache tous les contenus
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        
        // Affiche le contenu correspondant
        const tabId = this.getAttribute('data-tab') + '-tab';
        document.getElementById(tabId).classList.add('active');
    });
});

