// Fonctions d'interaction UI uniquement
document.addEventListener("DOMContentLoaded", () => {
  // Initialiser les boutons de quantité dans le panier
  initQuantityButtons();
  
  // Ajouter des animations pour les notifications
  setupNotifications();
});


// Fonction pour afficher une notification
function afficherNotification(message) {
  // Créer l'élément de notification s'il n'existe pas
  let notification = document.getElementById("notification");
  if (!notification) {
    notification = document.createElement("div");
    notification.id = "notification";
    notification.className =
      "fixed bottom-4 right-4 bg-accent text-white px-4 py-2 rounded-lg shadow-lg transform translate-y-10 opacity-0 transition-all duration-300 z-50";
    document.body.appendChild(notification);
  }

  // Afficher le message
  notification.textContent = message;
  notification.classList.remove("translate-y-10", "opacity-0");

  // Masquer après 3 secondes
  setTimeout(() => {
    notification.classList.add("translate-y-10", "opacity-0");
  }, 3000);
}