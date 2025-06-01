document.addEventListener("DOMContentLoaded", () => {
  // Initialiser les interactions UI
  initImageGallery();
  initQuantityButtons();
});

// Fonction pour initialiser la galerie d'images
function initImageGallery() {
  const thumbnails = document.querySelectorAll('.thumbnail-item img');
  const mainImage = document.getElementById('main-product-image');
  
  if (!thumbnails.length || !mainImage) return;
  
  thumbnails.forEach(thumbnail => {
    thumbnail.addEventListener('click', function() {
      mainImage.src = this.dataset.fullImage || this.src;
    });
  });
}

// Fonction pour initialiser les boutons de quantité
function initQuantityButtons() {
  const decreaseBtn = document.getElementById('decrease-quantity');
  const increaseBtn = document.getElementById('increase-quantity');
  const quantityInput = document.getElementById('quantity');
  
  if (!decreaseBtn || !increaseBtn || !quantityInput) return;
  
  decreaseBtn.addEventListener('click', () => {
    const currentValue = parseInt(quantityInput.value);
    if (currentValue > 1) {
      quantityInput.value = currentValue - 1;
    }
  });
  
  increaseBtn.addEventListener('click', () => {
    const currentValue = parseInt(quantityInput.value);
    const maxStock = parseInt(quantityInput.max);
    if (currentValue < maxStock) {
      quantityInput.value = currentValue + 1;
    }
  });
}