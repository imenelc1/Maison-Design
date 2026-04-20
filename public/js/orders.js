document.addEventListener("DOMContentLoaded", () => {
  // Initialiser les filtres de commandes si nécessaire
  initOrderFilters();
});

// Fonction pour initialiser les filtres de commandes
function initOrderFilters() {
  const filterButtons = document.querySelectorAll('.order-filter');
  if (!filterButtons.length) return;
  
  filterButtons.forEach(button => {
    button.addEventListener('click', function() {
      const status = this.dataset.status;
      
      // Mettre à jour l'état actif des filtres
      filterButtons.forEach(btn => btn.classList.remove('active'));
      this.classList.add('active');
      
      // Filtrer les commandes par statut
      filterOrdersByStatus(status);
    });
  });
}

// Fonction pour filtrer les commandes par statut
function filterOrdersByStatus(status) {
  const orders = document.querySelectorAll('.order-item');
  
  orders.forEach(order => {
    if (status === 'all' || order.dataset.status === status) {
      order.classList.remove('hidden');
    } else {
      order.classList.add('hidden');
    }
  });
}