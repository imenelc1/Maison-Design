/**
 * Maison Design - Fonctionnalités du panneau d'administration avec PHP
 */

document.addEventListener("DOMContentLoaded", () => {
  if (document.querySelector(".tab-trigger")) {
    initAdminPanel();
  }
});

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

  // DOM Elements
  const tabTriggers = document.querySelectorAll(".tab-trigger");
  const tabContents = document.querySelectorAll(".tab-content");

  // Initialize tabs
  initTabs();
  initModals();
  initFormHandlers();
  initPagination();

  // Load initial data
  loadClients();
  loadProducts();
  loadOrders();

  // Initialize tabs
  function initTabs() {
    tabTriggers.forEach((trigger) => {
      trigger.addEventListener("click", () => {
        tabTriggers.forEach((t) => t.classList.remove("active"));
        tabContents.forEach((c) => {
          c.classList.remove("active");
          c.classList.add("hidden");
        });

        trigger.classList.add("active");
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
      "add-product-btn": "add-product-modal",
    };

    Object.keys(modalTriggers).forEach((triggerId) => {
      const trigger = document.getElementById(triggerId);
      if (trigger) {
        const modalId = modalTriggers[triggerId];
        trigger.addEventListener("click", () => {
          openModal(modalId);
        });
      }
    });

    const closeButtons = document.querySelectorAll(".modal-close");
    closeButtons.forEach((button) => {
      button.addEventListener("click", () => {
        const modal = button.closest(".modal");
        if (modal) {
          closeModal(modal.id);
        }
      });
    });

    modals.forEach((modal) => {
      modal.addEventListener("click", (e) => {
        if (e.target === modal) {
          closeModal(modal.id);
        }
      });
    });
  }

  // Load clients from PHP
  async function loadClients() {
    console.log("Chargement des clients...");
    try {
      const url = `php/clients.php?page=${clientsCurrentPage}&limit=${itemsPerPage}`;
      console.log("URL clients:", url);
      
      const response = await fetch(url);
      console.log("Réponse clients:", response.status, response.statusText);
      
      const data = await response.json();
      console.log("Données clients:", data);
      
      if (data.success) {
        renderClients(data.data);
        updatePagination('clients', data.page, data.totalPages);
        showToast(`${data.data.length} clients chargés`);
      } else {
        console.error("Erreur dans la réponse:", data);
        showToast('Erreur lors du chargement des clients: ' + (data.error || 'Erreur inconnue'));
      }
    } catch (error) {
      console.error('Erreur lors du chargement des clients:', error);
      showToast('Erreur de connexion lors du chargement des clients');
    }
  }

  // Load products from PHP
  async function loadProducts() {
    console.log("Chargement des produits...");
    try {
      const url = `php/produits.php?page=${productsCurrentPage}&limit=${itemsPerPage}`;
      console.log("URL produits:", url);
      
      const response = await fetch(url);
      console.log("Réponse produits:", response.status, response.statusText);
      
      const data = await response.json();
      console.log("Données produits:", data);
      
      if (data.success) {
        renderProducts(data.data);
        updatePagination('products', data.page, data.totalPages);
        showToast(`${data.data.length} produits chargés`);
      } else {
        console.error("Erreur dans la réponse:", data);
        showToast('Erreur lors du chargement des produits: ' + (data.error || 'Erreur inconnue'));
      }
    } catch (error) {
      console.error('Erreur lors du chargement des produits:', error);
      showToast('Erreur de connexion lors du chargement des produits');
    }
  }

  // Load orders from PHP
  async function loadOrders() {
    console.log("Chargement des commandes...");
    try {
      const url = `php/commandes.php?page=${ordersCurrentPage}&limit=${itemsPerPage}`;
      console.log("URL commandes:", url);
      
      const response = await fetch(url);
      console.log("Réponse commandes:", response.status, response.statusText);
      
      const data = await response.json();
      console.log("Données commandes:", data);
      
      if (data.success) {
        renderOrders(data.data);
        updatePagination('orders', data.page, data.totalPages);
        showToast(`${data.data.length} commandes chargées`);
      } else {
        console.error("Erreur dans la réponse:", data);
        showToast('Erreur lors du chargement des commandes: ' + (data.error || 'Erreur inconnue'));
      }
    } catch (error) {
      console.error('Erreur lors du chargement des commandes:', error);
      showToast('Erreur de connexion lors du chargement des commandes');
    }
  }

  // Render clients table
  function renderClients(clients) {
    console.log("Rendu des clients:", clients);
    const tableBody = document.getElementById("clients-table-body");
    if (!tableBody) {
      console.error("Element clients-table-body non trouvé");
      return;
    }

    tableBody.innerHTML = "";

    if (clients.length === 0) {
      tableBody.innerHTML = `
        <tr>
          <td colspan="7" class="px-6 py-4 text-center">Aucun client trouvé</td>
        </tr>
      `;
      return;
    }

    clients.forEach((client) => {
      const row = document.createElement("tr");
      row.innerHTML = `
        <td class="px-6 py-4 whitespace-nowrap">${client.id}</td>
        <td class="px-6 py-4 whitespace-nowrap">${client.nom}</td>
        <td class="px-6 py-4 whitespace-nowrap">${client.prenom}</td>
        <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">${client.email}</td>
        <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">${client.telephone || 'N/A'}</td>
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

    // Add event listeners for delete buttons
    document.querySelectorAll(".delete-client-btn").forEach((button) => {
      button.addEventListener("click", () => {
        const id = parseInt(button.getAttribute("data-id"));
        if (confirm('Êtes-vous sûr de vouloir supprimer ce client ?')) {
          deleteClient(id);
        }
      });
    });
  }

  // Render products table
  function renderProducts(products) {
    console.log("Rendu des produits:", products);
    const tableBody = document.getElementById("products-table-body");
    if (!tableBody) {
      console.error("Element products-table-body non trouvé");
      return;
    }

    tableBody.innerHTML = "";

    if (products.length === 0) {
      tableBody.innerHTML = `
        <tr>
          <td colspan="6" class="px-6 py-4 text-center">Aucun produit trouvé</td>
        </tr>
      `;
      return;
    }

    products.forEach((product) => {
      const row = document.createElement("tr");
      row.innerHTML = `
        <td class="px-6 py-4 whitespace-nowrap">${product.id}</td>
        <td class="px-6 py-4 whitespace-nowrap flex items-center gap-3">
          ${product.image ? `<img src="${product.image}" alt="${product.nom}" class="w-10 h-10 object-cover rounded-md">` : ''}
          <span>${product.nom}</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">${product.categorie || 'N/A'}</td>
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

    // Add event listeners
    document.querySelectorAll(".edit-product-btn").forEach((button) => {
      button.addEventListener("click", () => {
        const id = parseInt(button.getAttribute("data-id"));
        console.log('Edit product with ID:', id);
      });
    });

    document.querySelectorAll(".delete-product-btn").forEach((button) => {
      button.addEventListener("click", () => {
        const id = parseInt(button.getAttribute("data-id"));
        if (confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')) {
          deleteProduct(id);
        }
      });
    });
  }

  // Render orders table
  function renderOrders(orders) {
    console.log("Rendu des commandes:", orders);
    const tableBody = document.getElementById("orders-table-body");
    if (!tableBody) {
      console.error("Element orders-table-body non trouvé");
      return;
    }

    tableBody.innerHTML = "";

    if (orders.length === 0) {
      tableBody.innerHTML = `
        <tr>
          <td colspan="6" class="px-6 py-4 text-center">Aucune commande trouvée</td>
        </tr>
      `;
      return;
    }

    orders.forEach((order) => {
      const row = document.createElement("tr");

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

    // Add event listeners
    document.querySelectorAll(".view-order-btn").forEach((button) => {
      button.addEventListener("click", () => {
        const id = parseInt(button.getAttribute("data-id"));
        viewOrderDetails(id);
      });
    });

    document.querySelectorAll(".change-status-btn").forEach((button) => {
      button.addEventListener("click", () => {
        const id = parseInt(button.getAttribute("data-id"));
        console.log('Change status of order with ID:', id);
      });
    });
  }

  // Delete client
  async function deleteClient(id) {
    console.log("Suppression du client:", id);
    try {
      const response = await fetch('php/clients.php', {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: id })
      });

      const data = await response.json();
      console.log("Réponse suppression client:", data);
      
      if (data.success) {
        showToast(data.message);
        loadClients();
      } else {
        showToast(data.error || 'Erreur lors de la suppression');
      }
    } catch (error) {
      console.error('Erreur lors de la suppression du client:', error);
      showToast('Erreur de connexion');
    }
  }

  // Delete product
  async function deleteProduct(id) {
    console.log("Suppression du produit:", id);
    try {
      const response = await fetch('php/produits.php', {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: id })
      });

      const data = await response.json();
      console.log("Réponse suppression produit:", data);
      
      if (data.success) {
        showToast(data.message);
        loadProducts();
      } else {
        showToast(data.error || 'Erreur lors de la suppression');
      }
    } catch (error) {
      console.error('Erreur lors de la suppression du produit:', error);
      showToast('Erreur de connexion');
    }
  }

  // View order details
  async function viewOrderDetails(id) {
    console.log("Affichage des détails de la commande:", id);
    try {
      const response = await fetch(`php/commandes.php?details=true&id=${id}`);
      const data = await response.json();
      console.log("Détails de la commande:", data);
      
      if (data.success) {
        // Populate order details modal
        const detailsBody = document.getElementById("order-details-body");
        if (detailsBody) {
          detailsBody.innerHTML = "";

          if (data.data.length === 0) {
            detailsBody.innerHTML = `
              <tr>
                <td colspan="4" class="px-6 py-4 text-center">Aucun détail disponible</td>
              </tr>
            `;
          } else {
            data.data.forEach((item) => {
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
      } else {
        showToast('Erreur lors du chargement des détails');
      }
    } catch (error) {
      console.error('Erreur lors du chargement des détails:', error);
      showToast('Erreur de connexion');
    }
  }

  // Initialize form handlers
  function initFormHandlers() {
    console.log("Initialisation des gestionnaires de formulaires...");
    
    // Add product form
    const addProductForm = document.getElementById("add-product-form");
    if (addProductForm) {
      console.log("Formulaire d'ajout de produit trouvé");
      addProductForm.addEventListener("submit", async function (e) {
        e.preventDefault();
        console.log("Soumission du formulaire d'ajout de produit");

        const formData = new FormData(this);
        
        // Log des données du formulaire
        for (let [key, value] of formData.entries()) {
          console.log(`${key}: ${value}`);
        }

        try {
          const response = await fetch('php/produits.php', {
            method: 'POST',
            body: formData
          });

          console.log("Réponse ajout produit:", response.status, response.statusText);
          const data = await response.json();
          console.log("Données de réponse:", data);
          
          if (data.success) {
            showToast(data.message);
            this.reset();
            closeModal("add-product-modal");
            loadProducts();
          } else {
            showToast(data.error || 'Erreur lors de l\'ajout');
          }
        } catch (error) {
          console.error('Erreur lors de l\'ajout du produit:', error);
          showToast('Erreur de connexion');
        }
      });
    } else {
      console.error("Formulaire d'ajout de produit non trouvé");
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
          loadClients();
        }
      });
    }
    
    if (clientsNextPage) {
      clientsNextPage.addEventListener("click", () => {
        clientsCurrentPage++;
        loadClients();
      });
    }

    // Products pagination
    const productsPrevPage = document.getElementById("products-prev-page");
    const productsNextPage = document.getElementById("products-next-page");
    
    if (productsPrevPage) {
      productsPrevPage.addEventListener("click", () => {
        if (productsCurrentPage > 1) {
          productsCurrentPage--;
          loadProducts();
        }
      });
    }
    
    if (productsNextPage) {
      productsNextPage.addEventListener("click", () => {
        productsCurrentPage++;
        loadProducts();
      });
    }

    // Orders pagination
    const ordersPrevPage = document.getElementById("orders-prev-page");
    const ordersNextPage = document.getElementById("orders-next-page");
    
    if (ordersPrevPage) {
      ordersPrevPage.addEventListener("click", () => {
        if (ordersCurrentPage > 1) {
          ordersCurrentPage--;
          loadOrders();
        }
      });
    }
    
    if (ordersNextPage) {
      ordersNextPage.addEventListener("click", () => {
        ordersCurrentPage++;
        loadOrders();
      });
    }
  }

  // Update pagination display
  function updatePagination(type, currentPage, totalPages) {
    const paginationElement = document.getElementById(`${type}-pagination`);
    if (paginationElement) {
      paginationElement.textContent = `Page ${currentPage} sur ${totalPages || 1}`;
    }

    const prevButton = document.getElementById(`${type}-prev-page`);
    const nextButton = document.getElementById(`${type}-next-page`);
    
    if (prevButton) prevButton.disabled = currentPage === 1;
    if (nextButton) nextButton.disabled = currentPage >= totalPages;
  }

  // Utility functions
  function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.remove("hidden");
      modal.classList.add("show");
      document.body.style.overflow = "hidden";
    }
  }

  function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.remove("show");
      modal.classList.add("hidden");
      document.body.style.overflow = "";
    }
  }

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
    
    if (profileToggle && profileDropdown) {
        profileToggle.addEventListener('click', function() {
            profileDropdown.classList.toggle('hidden');
        });
        
        document.addEventListener('click', function(event) {
            if (!profileToggle.contains(event.target)) {
                profileDropdown.classList.add('hidden');
            }
        });
        
        const logoutBtn = document.getElementById('logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                window.location.href = 'php/connexion.php';
            });
        }
    }
});